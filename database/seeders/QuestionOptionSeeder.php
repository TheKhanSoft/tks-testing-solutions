<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Programming Fundamentals MCQ options
        $this->addOptions(1, 1, [
            'Central Processing Unit' => true,
            'Computer Personal Unit' => false,
            'Central Processor Unit' => false,
            'Control Processing Unit' => false,
        ]);

        $this->addOptions(1, 2, [
            'Java' => false,
            'Python' => false,
            'HTML' => true,
            'C++' => false,
        ]);

        $this->addOptions(1, 12, [
            'String' => true,
            'int' => false,
            'boolean' => false,
            'char' => false,
        ]);

        $this->addOptions(1, 15, [
            'For loop' => false,
            'While loop' => false,
            'Do-while loop' => true,
            'If statement' => false,
        ]);

        $this->addOptions(1, 19, [
            'Internet Development Environment' => false,
            'Integrated Development Environment' => true,
            'Interface Design Engine' => false,
            'Internal Development Engine' => false,
        ]);

        // Data Structures MCQ options
        $this->addOptions(2, 4, [
            'O(1)' => false,
            'O(n)' => false,
            'O(log n)' => true,
            'O(n log n)' => false,
        ]);

        $this->addOptions(2, 5, [
            'Queue' => false,
            'Stack' => true,
            'Array' => false,
            'LinkedList' => false,
        ]);

        $this->addOptions(2, 21, [
            'O(1)' => false,
            'O(n)' => false,
            'O(log n)' => true,
            'O(n^2)' => false,
        ]);

        $this->addOptions(2, 23, [
            'Quick Sort' => false,
            'Bubble Sort' => false,
            'Heap Sort' => true,
            'Selection Sort' => false,
        ]);

        $this->addOptions(2, 25, [
            'Queue' => false,
            'Stack' => true,
            'Array' => false,
            'LinkedList' => false,
        ]);

        // Database Systems MCQ options
        $this->addOptions(3, 6, [
            'SELECT' => true,
            'MODIFY' => false,
            'FETCH' => false,
            'QUERY' => false,
        ]);

        $this->addOptions(3, 31, [
            'INNER JOIN' => false,
            'OUTER JOIN' => false,
            'CROSS JOIN' => false,
            'SIDE JOIN' => true,
        ]);

        $this->addOptions(3, 33, [
            'First Normal Form' => false,
            'Second Normal Form' => false,
            'Third Normal Form' => true,
            'Fourth Normal Form' => false,
        ]);

        $this->addOptions(3, 36, [
            'To speed up searches' => true,
            'To make data entry easier' => false,
            'To ensure data integrity' => false,
            'To reduce the database size' => false,
        ]);

        // Calculus MCQ options
        $this->addOptions(4, 8, [
            'cos(x)' => true,
            'tan(x)' => false,
            '-sin(x)' => false,
            'sec(x)' => false,
        ]);

        $this->addOptions(4, 42, [
            '1/x' => true,
            'ln(x)' => false,
            'x/ln(x)' => false,
            '1/ln(x)' => false,
        ]);

        $this->addOptions(4, 44, [
            'Product rule' => false,
            'Chain rule' => false,
            'Power rule' => true,
            'Quotient rule' => false,
        ]);

        $this->addOptions(4, 47, [
            'x^2' => false,
            'ln|x|' => true,
            'e^x' => false,
            '1/x^2' => false,
        ]);

        // Physics MCQ options
        $this->addOptions(5, 63, [
            'Watt' => false,
            'Newton' => true,
            'Joule' => false,
            'Pascal' => false,
        ]);

        $this->addOptions(5, 65, [
            'An object in motion stays in motion' => false,
            'Force equals mass times acceleration' => false,
            'For every action there is an equal and opposite reaction' => false,
            'Force is directly proportional to distance squared' => true,
        ]);

        $this->addOptions(5, 68, [
            'Reflection' => false,
            'Refraction' => false,
            'Rayleigh scattering' => true,
            'Radiation' => false,
        ]);

        // Chemistry MCQ options
        $this->addOptions(6, 70, [
            'Go' => false,
            'Gl' => false,
            'Au' => true,
            'Gd' => false,
        ]);

        $this->addOptions(6, 72, [
            'Helium' => false,
            'Oxygen' => true,
            'Argon' => false,
            'Neon' => false,
        ]);

        // Biology MCQ options
        $this->addOptions(7, 79, [
            'Mitochondria' => true,
            'Nucleus' => false,
            'Ribosome' => false,
            'Golgi apparatus' => false,
        ]);

        $this->addOptions(7, 81, [
            'Lungs' => false,
            'Liver' => false,
            'Heart' => true,
            'Stomach' => false,
        ]);

        // Multiple Select questions options
        $this->addOptions(2, 27, [
            'Tree' => true,
            'Graph' => true,
            'Array' => false,
            'Stack' => false,
        ], true);

        $this->addOptions(1, 17, [
            '// This is a comment' => true,
            '/* This is a comment */' => true,
            '<!-- This is a comment -->' => false,
            '# This is a comment' => false,
        ], true);

        $this->addOptions(3, 35, [
            'PRIMARY KEY' => true,
            'FOREIGN KEY' => true,
            'UNIQUE' => true,
            'SEQUENCE' => false,
        ], true);

        // Add options for the additional questions you've added in QuestionSeeder
        // This would be based on the content of those questions
        
        // Here are more MCQ options examples:
        
        // For Programming Fundamentals additional questions
        $this->addOptions(1, 139, [
            'class' => true,
            'define' => false,
            'struct' => false,
            'type' => false,
        ]);
        
        $this->addOptions(1, 143, [
            'true' => false,
            'false' => true,
            'null' => false,
            'undefined' => false,
        ]);
        
        // For Data Structures additional questions
        $this->addOptions(2, 159, [
            'Bubble Sort' => true,
            'Quick Sort' => false,
            'Merge Sort' => false,
            'Heap Sort' => false,
        ]);
        
        // For more comprehensive seeding, you would add options for all MCQ and Multiple Select questions
        // in your QuestionSeeder based on their IDs and expected answers
    }

    /**
     * Add options for a question
     * 
     * @param int $subjectId The subject ID
     * @param int $questionNumber The question number in the QuestionSeeder (position in the array)
     * @param array $options Array of options with boolean indicating if it's correct
     * @param bool $multipleSelect Whether this is a multiple select question
     */
    private function addOptions(int $subjectId, int $questionNumber, array $options, bool $multipleSelect = false): void
    {
        // Find the question based on subject ID and position
        $question = Question::where('subject_id', $subjectId)
            ->where('question_type_id', $multipleSelect ? 2 : 1)
            ->get()[$questionNumber - 1] ?? null;

        if ($question) {
            $order = 1;
            foreach ($options as $text => $isCorrect) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'text' => $text,
                    'is_correct' => $isCorrect,
                    'order' => $order++,
                ]);
            }
        }
    }
}
