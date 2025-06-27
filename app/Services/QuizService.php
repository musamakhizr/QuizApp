<?php

namespace App\Services;

use App\Models\User;
use App\Models\Answer;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Exceptions\QuizException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizService
{
    private const TOTAL_QUESTIONS = 5;

    public function createOrGetUser(string $name, string $sessionId): User
    {
        return User::firstOrCreate(
            ['session_id' => $sessionId],
            ['name' => $name]
        );
    }

    public function getRandomQuestions(): Collection
    {
        return Question::active()
            ->with('answers')
            ->random()
            ->limit(self::TOTAL_QUESTIONS)
            ->get();
    }

    public function getNextQuestion(User $user): ?Question
    {
        $answeredQuestionIds = $user->quizAttempts()
            ->pluck('question_id')
            ->toArray();

        return Question::active()
            ->whereNotIn('id', $answeredQuestionIds)
            ->with(['answers' => function ($query) {
                $query->orderBy('option_order');
            }])
            ->inRandomOrder()
            ->first();
    }

    public function submitAnswer(User $user, int $questionId, ?int $answerId = null): QuizAttempt
    {
        // Validate question exists and is active
        $question = Question::active()->findOrFail($questionId);

        // Check if already answered
        if ($user->quizAttempts()->where('question_id', $questionId)->exists()) {
            throw new QuizException('Question already answered');
        }

        $isSkipped = is_null($answerId);
        $isCorrect = false;

        if (!$isSkipped) {
            $answer = Answer::where('question_id', $questionId)
                ->findOrFail($answerId);
            $isCorrect = $answer->is_correct;
        }

        return QuizAttempt::create([
            'user_id' => $user->id,
            'question_id' => $questionId,
            'selected_answer_id' => $answerId,
            'is_skipped' => $isSkipped,
            'is_correct' => $isCorrect,
            'answered_at' => now(),
        ]);
    }


    public function getQuizResults(User $user): array
    {

        $stats = DB::table('quiz_attempts')
            ->where('user_id', $user->id)
            ->selectRaw('
            COUNT(*)                                                             AS total_attempts,
            SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END)                      AS correct_answers,
            SUM(CASE WHEN is_skipped = 1 THEN 1 ELSE 0 END)                      AS skipped_answers,
            SUM(CASE WHEN is_skipped = 0 AND is_correct = 0 THEN 1 ELSE 0 END)   AS wrong_answers
        ')
            ->first();


        $totalQuestions  = self::TOTAL_QUESTIONS;               // hardcoded length will adjust in future if get dynamic
        $attempted       = (int) ($stats->total_attempts   ?? 0);
        $correct         = (int) ($stats->correct_answers  ?? 0);
        $skipped         = (int) ($stats->skipped_answers  ?? 0);
        $wrong           = (int) ($stats->wrong_answers    ?? 0);
        $answered        = $attempted - $skipped;              // anything not skipped
        $remaining       = max(0, $totalQuestions - $attempted);

        $percentage      = $answered > 0
            ? round(($correct / $answered) * 100, 2)
            : 0;

        return [
            'user'                => $user,
            'total_questions'     => $totalQuestions,
            'total_answered'      => $answered,
            'correct_answers'     => $correct,
            'wrong_answers'       => $wrong,
            'skipped_answers'     => $skipped,
            'remaining_questions' => $remaining,
            'percentage'          => number_format($percentage, 2),
        ];
    }

    // public function getQuizResults(User $user): array
    // {
    //     $attempts = $user->quizAttempts;

    //     $totalQuestions = self::TOTAL_QUESTIONS; // Hardcoded, adjust if dynamic
    //     $totalAnswered  = $attempts
    //     ->where('is_skipped', false)
    //     ->count();

    //     $correctAnswers = $attempts
    //     ->where('is_correct', true)
    //     ->count();

    //     $wrongAnswers   = $attempts
    //     ->where('is_correct', false)
    //     ->where('is_skipped', false)
    //     ->count();

    //     $skippedAnswers = $attempts
    //     ->where('is_skipped', true)
    //     ->count();

    //     $remaining      = max(0, $totalQuestions - $attempts->count());

    //     $percentage     = $totalAnswered
    //     ? round(($correctAnswers / $totalAnswered) * 100, 2)
    //     : 0;

    //     Log::info('Quiz Results Calculated', [
    //         'user_id' => $user->id,
    //         'total_questions' => $totalQuestions,
    //         'total_answered' => $totalAnswered,
    //         'correct_answers' => $correctAnswers,
    //         'wrong_answers' => $wrongAnswers,
    //         'skipped_answers' => $skippedAnswers,
    //         'remaining_questions' => $remaining,
    //         'percentage' => $percentage,
    //     ]);

    //     return [
    //         'user' => $user,
    //         'total_questions' => $totalQuestions,
    //         'total_answered' => $totalAnswered,
    //         'correct_answers' => $correctAnswers,
    //         'wrong_answers' => $wrongAnswers,
    //         'skipped_answers' => $skippedAnswers,
    //         'remaining_questions' => $remaining,
    //         'percentage' => number_format($percentage, 2),
    //     ];
    // }

    public function isQuizCompleted(User $user): bool
    {
        return $user->getCompletedQuestionsCount() >= self::TOTAL_QUESTIONS;
    }

    public function getUserHistory(User $user): Collection
    {
        return $user->quizAttempts()
            ->with(['question', 'selectedAnswer'])
            ->orderBy('answered_at', 'desc')
            ->get();
    }
}
