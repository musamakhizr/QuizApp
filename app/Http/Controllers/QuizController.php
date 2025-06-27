<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Services\QuizService;
use App\Exceptions\QuizException;
use Illuminate\Http\JsonResponse;
// use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartQuizRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    private QuizService $quizService;

    public function __construct(QuizService $quizService)
    {
        // $this->middleware('quiz.session')->except(['index', 'startQuiz']);
        $this->quizService = $quizService;
    }

    public function index(): View
    {
        return view('Quiz.index');
    }

    public function startQuiz(StartQuizRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $user = User::create([
                'name' => $data['name'],
            ]);

            Session::put('user_id', $user->id);
            Session::put('user_name', $user->name);

            Log::info('Session Started', ['user_id' => $user->id, 'user_name' => $user->name]);
            return response()->json([
                'success' => true,
                'message' => 'Quiz started successfully!',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'redirect' => route('quiz.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start quiz. Please try again.',
            ], 500);
        }
    }

    public function getQuestion(): JsonResponse
    {
        try {
            $userId = Session::get('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please restart the quiz.',
                ], 401);
            }

            $user = User::findOrFail($userId);

            if ($this->quizService->isQuizCompleted($user)) {
                return response()->json([
                    'success' => true,
                    'completed' => true,
                    'message' => 'Quiz completed!',
                    'redirect' => route('quiz.results'),
                ]);
            }

            $question = $this->quizService->getNextQuestion($user);

            if (!$question) {
                return response()->json([
                    'success' => true,
                    'completed' => true,
                    'message' => 'No more questions available.',
                    'redirect' => route('quiz.results'),
                ]);
            }

            return response()->json([
                'success' => true,
                'completed' => false,
                'question' => [
                    'id' => $question->id,
                    'text' => $question->question_text,
                    'answers' => $question->answers->map(function ($answer) {
                        return [
                            'id' => $answer->id,
                            'text' => $answer->answer_text,
                            'order' => $answer->option_order,
                        ];
                    }),
                ],
                'progress' => [
                    'current' => $user->getCompletedQuestionsCount() + 1,
                    'total' => 5,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load question.',
            ], 500);
        }
    }

    public function submitAnswer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|integer|exists:questions,id',
            'answer_id' => 'nullable|integer|exists:answers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $userId = Session::get('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please restart the quiz.',
                ], 401);
            }

            $user = User::findOrFail($userId);

            $attempt = $this->quizService->submitAnswer(
                $user,
                $request->question_id,
                $request->answer_id
            );

            $isCompleted = $this->quizService->isQuizCompleted($user);

            return response()->json([
                'success' => true,
                'message' => $attempt->is_skipped ? 'Question skipped.' : 'Answer submitted.',
                'result' => [
                    'is_correct' => $attempt->is_correct,
                    'is_skipped' => $attempt->is_skipped,
                ],
                'quiz_completed' => $isCompleted,
                'redirect' => $isCompleted ? route('quiz.results') : route('quiz.index'),
            ]);
        } catch (QuizException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit answer.',
            ], 500);
        }
    }

    public function getResults(): JsonResponse
    {
        try {
            $userId = Session::get('user_id');
            if (!$userId) {
                Log::warning('No user session found for results');
                return response()->json([
                    'success' => false,
                    'message' => 'No user session found.',
                ], 401);
            }
            $user = User::findOrFail($userId);
            $results = $this->quizService->getQuizResults($user);

            return response()->json([
                'success' => true,
                'results' => [
                    'user' => $user,
                    'total_questions' => $results['total_questions'],
                    'total_answered' => $results['total_answered'],
                    'correct_answers' => $results['correct_answers'],
                    'wrong_answers' => $results['wrong_answers'],
                    'skipped_answers' => $results['skipped_answers'],
                    'remaining_questions' => $results['remaining_questions'],
                    'percentage' => $results['percentage'],
                ],
                'history' => [], // Placeholder for future history
            ]);
        } catch (\Exception $e) {
            Log::error('Get Results Error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load results: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function showResults()
    {
        $userId = Session::get('user_id');

        if (!$userId) {
            return redirect()->route('quiz.index')
                ->with('error', 'Session expired. Please restart the quiz.');
        }

        // return view('Quiz.results');

        $user = User::findOrFail($userId);
        $results = $this->quizService->getQuizResults($user);

        return view('quiz.results', [
            'user' => $user,
            'results' => $results,
        ]);
    }

    public function resetQuiz(): JsonResponse
    {
        try {
            $userId = Session::get('user_id');
            if ($userId) {
                $user = User::findOrFail($userId);
                $user->quizAttempts()->delete(); // Clear existing attempts
                Session::forget('user_id'); // Remove user session
                Session::forget('user_name'); // Remove user name
                Log::info('Quiz Reset', ['user_id' => $userId]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Quiz reset successfully.',
                'redirect' => route('quiz.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset quiz: ' . $e->getMessage(),
            ], 500);
        }
    }
}
