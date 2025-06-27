<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\QuizAttempt;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];


    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function getCompletedQuestionsCount(): int
    {
        return $this->quizAttempts()->count();
    }

    public function getCorrectAnswersCount(): int
    {
        return $this->quizAttempts()->where('is_correct', true)->count();
    }

    public function getSkippedAnswersCount(): int
    {
        return $this->quizAttempts()->where('is_skipped', true)->count();
    }
}
