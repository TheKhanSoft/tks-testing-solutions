<?php

namespace Database\Seeders;

use App\Models\PaperQuestion;
use Illuminate\Database\Seeder;

class PaperQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $paperQuestions = [
            // Programming Basics Test (Paper 1)
            [
                'paper_id' => 1,
                'question_id' => 1, // What does CPU stand for?
                'order_index' => 1,
            ],
            [
                'paper_id' => 1,
                'question_id' => 2, // Which is not a programming language
                'order_index' => 2,
            ],
            [
                'paper_id' => 1,
                'question_id' => 4, // Binary search complexity
                'order_index' => 3,
            ],
            [
                'paper_id' => 1,
                'question_id' => 5, // LIFO data structure
                'order_index' => 4,
            ],
            [
                'paper_id' => 1,
                'question_id' => 8, // Derivative of sin(x)
                'order_index' => 5,
            ],
            [
                'paper_id' => 1,
                'question_id' => 9, // Integral of e^x
                'order_index' => 6,
            ],
            [
                'paper_id' => 1,
                'question_id' => 12, // Not primitive data type in Java
                'order_index' => 7,
            ],
            [
                'paper_id' => 1,
                'question_id' => 15, // Loop guaranteed to execute at least once
                'order_index' => 8,
            ],
            [
                'paper_id' => 1,
                'question_id' => 19, // IDE stands for?
                'order_index' => 9,
            ],
            [
                'paper_id' => 1,
                'question_id' => 20, // Arrays zero-indexed
                'order_index' => 10,
            ],
            
            // Data Structures and Algorithms (Paper 2)
            [
                'paper_id' => 2,
                'question_id' => 21, // AVL tree insertion complexity
                'order_index' => 1,
            ],
            [
                'paper_id' => 2,
                'question_id' => 22, // Queue FIFO
                'order_index' => 2,
            ],
            [
                'paper_id' => 2,
                'question_id' => 23, // Sorting algorithm best avg case
                'order_index' => 3,
            ],
            [
                'paper_id' => 2,
                'question_id' => 24, // Linked list vs array
                'order_index' => 4,
            ],
            [
                'paper_id' => 2,  'question_id' => 6, // SQL keyword
                'question_id' => 25, // Balanced parentheses data structure    'order_index' => 1,
                'order_index' => 5,
            ],
            [
                'paper_id' => 2,
                'question_id' => 26, // Hash table search complexity
                'order_index' => 6,
            ],
            [
                'paper_id' => 2,
                'question_id' => 27, // Non-linear data structuresquestion);
                'order_index' => 7,
            ],
            [
                'paper_id' => 2,
                'question_id' => 28, // Graph traversal algorithm
                'order_index' => 8,
            ],
            [
                'paper_id' => 2,                
                'question_id' => 89, // B-tree applications
                'order_index' => 9,
            ],
            [
                'paper_id' => 2,
                'question_id' => 90, // Priority queue implementation
                'order_index' => 10,
            ],
            
            // Database Management Systems (Paper 3)
            [
                'paper_id' => 3,
                'question_id' => 6, // SQL keyword for retrieval
                'order_index' => 1,
            ],
            [
                'paper_id' => 3,
                'question_id' => 7, // Normalization definition
                'order_index' => 2,
            ],
            [
                'paper_id' => 3,
                'question_id' => 31, // Not a type of SQL join
                'order_index' => 3,
            ],
            [
                'paper_id' => 3,
                'question_id' => 32, // Foreign key reference
                'order_index' => 4,
            ],
            [
                'paper_id' => 3,
                'question_id' => 33, // Normal form with transitive dep
                'order_index' => 5,
            ],
            [
                'paper_id' => 3,
                'question_id' => 34, // Database transactions and ACID
                'order_index' => 6,
            ],
            [
                'paper_id' => 3,
                'question_id' => 35, // SQL constraints
                'order_index' => 7,
            ],
            [
                'paper_id' => 3,
                'question_id' => 36, // Database index purpose
                'order_index' => 8,
            ],
            [
                'paper_id' => 3,
                'question_id' => 37, // SQL command to remove table
                'order_index' => 9,
            ],
            [
                'paper_id' => 3,
                'question_id' => 91, // Not a NoSQL database
                'order_index' => 10,
            ],
            
            // NEW: Mathematics Test (Paper 4)
            [
                'paper_id' => 4,
                'question_id' => 42, // Derivative of ln(x)
                'order_index' => 1,
            ],
            [
                'paper_id' => 4,
                'question_id' => 43, // Fundamental theorem of calculus
                'order_index' => 2,
            ],
            [
                'paper_id' => 4,
                'question_id' => 44, // Derivative of a constant
                'order_index' => 3,
            ],
            [
                'paper_id' => 4,
                'question_id' => 45, // Power rule in derivatives
                'order_index' => 4,
            ],
            [
                'paper_id' => 4,
                'question_id' => 46, // Concept of limit
                'order_index' => 5,
            ],
            [
                'paper_id' => 4,
                'question_id' => 47, // Integral of 1/x
                'order_index' => 6,
            ],
            [
                'paper_id' => 4,
                'question_id' => 93, // Chain rule explanation
                'order_index' => 7,
            ],
            [
                'paper_id' => 4,
                'question_id' => 198, // Value of cos(0)
                'order_index' => 8,
            ],
            
            // NEW: Physics Test (Paper 5)
            [
                'paper_id' => 5,
                'question_id' => 63, // SI unit of force
                'order_index' => 1,
            ],
            [
                'paper_id' => 5,
                'question_id' => 64, // Energy creation/destruction
                'order_index' => 2,
            ],
            [
                'paper_id' => 5,
                'question_id' => 65, // Newton's laws of motion
                'order_index' => 3,
            ],
            [
                'paper_id' => 5,
                'question_id' => 66, // Gravitational potential energy
                'order_index' => 4,
            ],
            [
                'paper_id' => 5,
                'question_id' => 67, // Formula for work done
                'order_index' => 5,
            ],
            [
                'paper_id' => 5,
                'question_id' => 68, // Why sky appears blue
                'order_index' => 6,
            ],
            [
                'paper_id' => 5,
                'question_id' => 94, // Vector quantities
                'order_index' => 7,
            ],
            [
                'paper_id' => 5,
                'question_id' => 95, // Entropy in thermodynamics
                'order_index' => 8,
            ],
            
            // NEW: Chemistry Test (Paper 6)
            [
                'paper_id' => 6,
                'question_id' => 70, // Chemical symbol for gold
                'order_index' => 1,
            ],
            [
                'paper_id' => 6,
                'question_id' => 71, // Acids and litmus paper
                'order_index' => 2,
            ],
            [
                'paper_id' => 6,
                'question_id' => 72, // Noble gases
                'order_index' => 3,
            ],
            [
                'paper_id' => 6,
                'question_id' => 73, // Covalent vs ionic bonds
                'order_index' => 4,
            ],
            [
                'paper_id' => 6,
                'question_id' => 74, // pH of neutral solution
                'order_index' => 5,
            ],
            [
                'paper_id' => 6,
                'question_id' => 75, // Transition metals
                'order_index' => 6,
            ],
            [
                'paper_id' => 6,
                'question_id' => 96, // IUPAC name for acetic acid
                'order_index' => 7,
            ],
            [
                'paper_id' => 6,
                'question_id' => 97, // Le Chatelier's principle
                'order_index' => 8,
            ],
        ];

        foreach ($paperQuestions as $question) {
            PaperQuestion::create($question);
        }
    }
}
