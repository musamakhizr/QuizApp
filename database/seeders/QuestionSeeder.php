<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            [
                'question' => 'What does PHP stand for?',
                'answers' => [
                    ['text' => 'PHP: Hypertext Preprocessor', 'correct' => true],
                    ['text' => 'Personal Home Page', 'correct' => false],
                    ['text' => 'Private Home Page', 'correct' => false],
                    ['text' => 'PHP: Hypertext Protocol', 'correct' => false],
                ]
            ],
            [
                'question' => 'Which of the following is the correct way to create an array in PHP?',
                'answers' => [
                    ['text' => '$array = array();', 'correct' => true],
                    ['text' => '$array = [];', 'correct' => false],
                    ['text' => 'Both A and B', 'correct' => false],
                    ['text' => 'None of the above', 'correct' => false],
                ]
            ],
            [
                'question' => 'What is the correct way to include a file in PHP?',
                'answers' => [
                    ['text' => 'include "file.php";', 'correct' => false],
                    ['text' => 'require "file.php";', 'correct' => false],
                    ['text' => 'include_once "file.php";', 'correct' => false],
                    ['text' => 'All of the above', 'correct' => true],
                ]
            ],
            [
                'question' => 'Which superglobal variable holds information about headers, paths, and script locations?',
                'answers' => [
                    ['text' => '$_GET', 'correct' => false],
                    ['text' => '$_POST', 'correct' => false],
                    ['text' => '$_SERVER', 'correct' => true],
                    ['text' => '$_SESSION', 'correct' => false],
                ]
            ],
            [
                'question' => 'What is the difference between "==" and "===" in PHP?',
                'answers' => [
                    ['text' => 'No difference', 'correct' => false],
                    ['text' => '== compares values, === compares values and types', 'correct' => true],
                    ['text' => '== is for strings, === is for numbers', 'correct' => false],
                    ['text' => '=== is deprecated', 'correct' => false],
                ]
            ],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create([
                'question_text' => $questionData['question'],
                'is_active' => true,
            ]);

            foreach ($questionData['answers'] as $index => $answerData) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answerData['text'],
                    'is_correct' => $answerData['correct'],
                    'option_order' => $index + 1,
                ]);
            }
        }
    }
}
