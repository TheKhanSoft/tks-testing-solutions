<?php

namespace Database\Seeders;

use App\Models\CandidatePaper;
use Illuminate\Database\Seeder;

class CandidatePaperSeeder extends Seeder
{
    public function run(): void
    {
        $candidatePapers = [
            // Usman's answers for Computer Science Entry Test
            [
                'test_attempt_id' => 1,
                'candiate_id' => 1,
                'question_id' => 1,
                'paper_id' => 1,
                'candidate_answer' => 'Central Processing Unit',
                'is_correct' => true,
                'marks_obtained' => 1,
                'status' => 'attempted',
            ],
            [
                'test_attempt_id' => 1,
                'candiate_id' => 1,
                'question_id' => 2,
                'paper_id' => 1,
                'candidate_answer' => 'HTML',
                'is_correct' => true,
                'marks_obtained' => 1,
                'status' => 'attempted',
            ],
            [
                'test_attempt_id' => 1,
                'candiate_id' => 1,
                'question_id' => 4,
                'paper_id' => 1,
                'candidate_answer' => 'O(log n)',
                'is_correct' => true,
                'marks_obtained' => 2,
                'status' => 'attempted',
            ],
            [
                'test_attempt_id' => 1,
                'candiate_id' => 1,
                'question_id' => 5,
                'paper_id' => 1,
                'candidate_answer' => 'Stack',
                'is_correct' => true,
                'marks_obtained' => 1,
                'status' => 'attempted',
            ],
            
            // Aisha's answers for Computer Science Entry Test
            [
                'test_attempt_id' => 3,
                'candiate_id' => 2,
                'question_id' => 1,
                'paper_id' => 1,
                'candidate_answer' => 'Central Processing Unit',
                'is_correct' => true,
                'marks_obtained' => 1,
                'status' => 'attempted',
            ],
            [
                'test_attempt_id' => 3,
                'candiate_id' => 2,
                'question_id' => 2,
                'paper_id' => 1,
                'candidate_answer' => 'HTML',
                'is_correct' => true,
                'marks_obtained' => 1,
                'status' => 'attempted',
            ],
            [
                'test_attempt_id' => 3,
                'candiate_id' => 2,
                'question_id' => 4,
                'paper_id' => 1,
                'candidate_answer' => 'O(n)',
                'is_correct' => false,
                'marks_obtained' => 0,
                'status' => 'attempted',
            ],
            
            // Mohammad Bilal's answers for Programming Midterm
            [
                'test_attempt_id' => 4,
                'candiate_id' => 3,
                'question_id' => 1,
                'paper_id' => 3,
                'candidate_answer' => 'Central Program Unit',
                'is_correct' => false,
                'marks_obtained' => 0,
                'status' => 'attempted',
            ],
            [
                'test_attempt_id' => 4,
                'candiate_id' => 3,
                'question_id' => 2,
                'paper_id' => 3,
                'candidate_answer' => 'HTML',
                'is_correct' => true,
                'marks_obtained' => 1,
                'status' => 'attempted',
            ],
            [
                'test_attempt_id' => 4,
                'candiate_id' => 3,
                'question_id' => 3,
                'paper_id' => 3,
                'candidate_answer' => 'False',
                'is_correct' => true,
                'marks_obtained' => 1,
                'status' => 'attempted',
            ],
            
            // Fatima's answers for GAT General
            [
                'test_attempt_id' => 5,
                'candiate_id' => 4,
                'question_id' => 8,
                'paper_id' => 2,
                'candidate_answer' => 'cos(x)',
                'is_correct' => true,
                'marks_obtained' => 2,
                'status' => 'attempted',
            ],
            [
                'test_attempt_id' => 5,
                'candiate_id' => 4,
                'question_id' => 9,
                'paper_id' => 2,
                'candidate_answer' => 'e^x',
                'is_correct' => true,
                'marks_obtained' => 2,
                'status' => 'attempted',
            ],
            [
                'test_attempt_id' => 5,
                'candiate_id' => 4,
                'question_id' => 11,
                'paper_id' => 2,
                'candidate_answer' => '624 CE',
                'is_correct' => true,
                'marks_obtained' => 2,
                'status' => 'attempted',
            ],
        ];

        foreach ($candidatePapers as $paper) {
            CandidatePaper::create($paper);
        }
    }
}
