<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
class Quiz extends Model
{
    use HasFactory;


    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->orderBy('option_order');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function correctAnswer(): HasMany
    {
        return $this->hasMany(Answer::class)->where('is_correct', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRandom(Builder $query): Builder
    {
        return $query->inRandomOrder();
    }
}
