<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'question_id',
        'selected_answer_id',
        'is_skipped',
        'is_correct',
        'answered_at',
    ];

    protected $casts = [
        'is_skipped' => 'boolean',
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedAnswer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'selected_answer_id');
    }
}
