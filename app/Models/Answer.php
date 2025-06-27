<?php

namespace App\Models;

use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'answer_text',
        'is_correct',
        'option_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'option_order' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'selected_answer_id');
    }
}
