<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            // Programming Fundamentals questions
            [
                'subject_id' => 1,
                'question_type_id' => 1, // MCQ
                'text' => 'What does the acronym "CPU" stand for?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 1, // MCQ
                'text' => 'Which of the following is not a programming language?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 3, // True/False
                'text' => 'Python is a compiled language.',
                'difficulty_level' => 'medium',
                'marks' => 1,
            ],
            
            // Data Structures questions
            [
                'subject_id' => 2,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the time complexity of binary search?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 2,
                'question_type_id' => 1, // MCQ
                'text' => 'Which data structure works on LIFO principle?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            
            // Database Systems questions
            [
                'subject_id' => 3,
                'question_type_id' => 1, // MCQ
                'text' => 'Which SQL keyword is used to retrieve data from a database?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 3,
                'question_type_id' => 4, // Short Answer
                'text' => 'Define normalization in the context of database design.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            
            // Calculus questions
            [
                'subject_id' => 4,
                'question_type_id' => 1, // MCQ
                'text' => 'The derivative of sin(x) is:',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 4,
                'question_type_id' => 6, // Fill in the Blank
                'text' => 'The integral of e^x is ______.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            
            // English Composition questions
            [
                'subject_id' => 8,
                'question_type_id' => 5, // Essay
                'text' => 'Write a short essay on the importance of education in society.',
                'difficulty_level' => 'medium',
                'marks' => 5,
            ],
            
            // Islamic History questions
            [
                'subject_id' => 9,
                'question_type_id' => 1, // MCQ
                'text' => 'In which year did the Battle of Badr take place?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            
            // Additional Programming Fundamentals questions
            [
                'subject_id' => 1,
                'question_type_id' => 1, // MCQ
                'text' => 'Which of the following is not a primitive data type in Java?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the output of the code: print(2 + "2") in Python?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 3, // True/False
                'text' => 'JavaScript is a statically typed language.',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the difference between "==" and "===" in JavaScript.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 1, // MCQ
                'text' => 'Which loop is guaranteed to execute at least once?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 6, // Fill in the Blank
                'text' => 'In object-oriented programming, ______ refers to the process where one class acquires properties of another class.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 2, // Multiple Select
                'text' => 'Which of the following are valid ways to comment code in JavaScript?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 4, // Short Answer
                'text' => 'What is the purpose of a constructor in a class?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 1, // MCQ
                'text' => 'What does IDE stand for?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 3, // True/False
                'text' => 'Arrays in most programming languages are zero-indexed.',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            
            // Additional Data Structures questions
            [
                'subject_id' => 2,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the worst-case time complexity for insertion in an AVL tree?',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            [
                'subject_id' => 2,
                'question_type_id' => 3, // True/False
                'text' => 'A queue follows the First-In-First-Out (FIFO) principle.',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 2,
                'question_type_id' => 1, // MCQ
                'text' => 'Which sorting algorithm has the best average-case time complexity?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 2,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the difference between a linked list and an array.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 2,
                'question_type_id' => 1, // MCQ
                'text' => 'What data structure would you use to check if a syntax has balanced parentheses?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 2,
                'question_type_id' => 6, // Fill in the Blank
                'text' => 'The time complexity of searching an element in a hash table is ______ in the average case.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 2,
                'question_type_id' => 2, // Multiple Select
                'text' => 'Which of the following data structures are non-linear?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 2,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the space complexity of depth-first search?',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            
            // Additional Database Systems questions
            [
                'subject_id' => 3,
                'question_type_id' => 1, // MCQ
                'text' => 'Which of the following is not a type of SQL join?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 3,
                'question_type_id' => 3, // True/False
                'text' => 'A foreign key can reference a non-primary key column in another table.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 3,
                'question_type_id' => 1, // MCQ
                'text' => 'Which normal form deals with transitive dependencies?',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            [
                'subject_id' => 3,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the concept of database transactions and ACID properties.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            [
                'subject_id' => 3,
                'question_type_id' => 2, // Multiple Select
                'text' => 'Which of the following are valid constraints in SQL?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 3,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the purpose of an index in a database?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 3,
                'question_type_id' => 6, // Fill in the Blank
                'text' => 'The SQL command used to remove a table from the database is ______.',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            
            // Additional Calculus questions
            [
                'subject_id' => 4,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the derivative of ln(x)?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 4,
                'question_type_id' => 6, // Fill in the Blank
                'text' => 'The fundamental theorem of calculus establishes the connection between ______ and differentiation.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 4,
                'question_type_id' => 3, // True/False
                'text' => 'The derivative of a constant is always zero.',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 4,
                'question_type_id' => 1, // MCQ
                'text' => 'Which rule is used to find the derivative of a function raised to a power?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 4,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the concept of a limit in calculus.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 4,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the integral of 1/x?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            
            // Additional English Composition questions
            [
                'subject_id' => 8,
                'question_type_id' => 5, // Essay
                'text' => 'Compare and contrast the themes of two novels you have read.',
                'difficulty_level' => 'hard',
                'marks' => 5,
            ],
            [
                'subject_id' => 8,
                'question_type_id' => 1, // MCQ
                'text' => 'Which of the following is not a literary device?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 8,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the difference between a simile and a metaphor with examples.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 8,
                'question_type_id' => 6, // Fill in the Blank
                'text' => 'The three main parts of an essay are introduction, ______, and conclusion.',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 8,
                'question_type_id' => 3, // True/False
                'text' => 'A thesis statement is typically placed at the end of the introduction paragraph.',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 8,
                'question_type_id' => 5, // Essay
                'text' => 'Discuss the impact of social media on modern communication.',
                'difficulty_level' => 'medium',
                'marks' => 4,
            ],
            
            // Additional Islamic History questions
            [
                'subject_id' => 9,
                'question_type_id' => 1, // MCQ
                'text' => 'Who was the first Caliph of Islam?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 9,
                'question_type_id' => 3, // True/False
                'text' => 'The Quran was compiled into a single book during the lifetime of Prophet Muhammad (PBUH).',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 9,
                'question_type_id' => 4, // Short Answer
                'text' => 'Describe the significance of the Treaty of Hudaibiyah.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            [
                'subject_id' => 9,
                'question_type_id' => 1, // MCQ
                'text' => 'In which city did Prophet Muhammad (PBUH) deliver his farewell sermon?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 9,
                'question_type_id' => 6, // Fill in the Blank
                'text' => 'The Islamic calendar begins with the event of ______.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            
            // Adding Physics questions
            [
                'subject_id' => 5, // Assuming Physics is subject_id 5
                'question_type_id' => 1, // MCQ
                'text' => 'What is the SI unit of force?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 5,
                'question_type_id' => 3, // True/False
                'text' => 'Energy can be created or destroyed.',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 5,
                'question_type_id' => 1, // MCQ
                'text' => 'Which of the following is not one of Newton\'s laws of motion?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 5,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the concept of gravitational potential energy.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 5,
                'question_type_id' => 6, // Fill in the Blank
                'text' => 'The formula for calculating work done is ______.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 5,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the phenomenon that explains why the sky appears blue?',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            
            // Adding Chemistry questions
            [
                'subject_id' => 6, // Assuming Chemistry is subject_id 6
                'question_type_id' => 1, // MCQ
                'text' => 'What is the chemical symbol for gold?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 6,
                'question_type_id' => 3, // True/False
                'text' => 'Acids turn blue litmus paper red.',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 6,
                'question_type_id' => 1, // MCQ
                'text' => 'Which of the following is not a noble gas?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 6,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the difference between covalent and ionic bonds.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 6,
                'question_type_id' => 6, // Fill in the Blank
                'text' => 'The pH of a neutral solution is ______.',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 6,
                'question_type_id' => 2, // Multiple Select
                'text' => 'Which of the following are transition metals?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            
            // Adding Biology questions
            [
                'subject_id' => 7, // Assuming Biology is subject_id 7
                'question_type_id' => 1, // MCQ
                'text' => 'What is the powerhouse of the cell?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 7,
                'question_type_id' => 3, // True/False
                'text' => 'Photosynthesis occurs in animal cells.',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 7,
                'question_type_id' => 1, // MCQ
                'text' => 'Which of the following is not a part of the digestive system?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 7,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the process of DNA replication.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            [
                'subject_id' => 7,
                'question_type_id' => 6, // Fill in the Blank
                'text' => 'The smallest unit of life is the ______.',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 7,
                'question_type_id' => 2, // Multiple Select
                'text' => 'Which of the following are types of blood cells?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            
            // Adding more Programming Fundamentals questions
            [
                'subject_id' => 1,
                'question_type_id' => 2, // Multiple Select
                'text' => 'Which of the following are object-oriented programming languages?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the purpose of version control systems like Git?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the concept of recursion with an example.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            
            // Adding more Data Structures questions
            [
                'subject_id' => 2,
                'question_type_id' => 4, // Short Answer
                'text' => 'What is a B-tree and what are its applications?',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            [
                'subject_id' => 2,
                'question_type_id' => 1, // MCQ
                'text' => 'Which data structure is most suitable for implementing a priority queue?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            
            // Adding more Database Systems questions
            [
                'subject_id' => 3,
                'question_type_id' => 1, // MCQ
                'text' => 'Which of the following is not a NoSQL database?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 3,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the difference between DDL and DML commands in SQL.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            
            // Adding more Calculus questions
            [
                'subject_id' => 4,
                'question_type_id' => 2, // Multiple Select
                'text' => 'Which of the following functions are differentiable at x = 0?',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            [
                'subject_id' => 4,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the chain rule for differentiation with an example.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            
            // Adding more Physics questions
            [
                'subject_id' => 5,
                'question_type_id' => 2, // Multiple Select
                'text' => 'Which of the following are vector quantities?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 5,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the concept of entropy in thermodynamics.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            
            // Adding more Chemistry questions
            [
                'subject_id' => 6,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the IUPAC name for CH₃COOH?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 6,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the concept of Le Chatelier\'s principle.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            
            // Adding more Biology questions
            [
                'subject_id' => 7,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the primary function of ribosomes?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 7,
                'question_type_id' => 4, // Short Answer
                'text' => 'Describe the process of cellular respiration.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            
            // Adding more English Composition questions
            [
                'subject_id' => 8,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the term for a word that is opposite in meaning to another word?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 8,
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the concept of irony with examples.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            
            // Adding more Islamic History questions
            [
                'subject_id' => 9,
                'question_type_id' => 1, // MCQ
                'text' => 'Which battle is known as the Victory of Victories in Islamic history?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 9,
                'question_type_id' => 4, // Short Answer
                'text' => 'Describe the contributions of Islamic scholars to science and mathematics.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            
            // Adding more questions with different types
            [
                'subject_id' => 1, // Programming Fundamentals
                'question_type_id' => 5, // Essay
                'text' => 'Discuss the advantages and disadvantages of functional programming compared to object-oriented programming.',
                'difficulty_level' => 'hard',
                'marks' => 5,
            ],
            [
                'subject_id' => 2, // Data Structures
                'question_type_id' => 5, // Essay
                'text' => 'Compare and contrast various graph traversal algorithms and their applications.',
                'difficulty_level' => 'hard',
                'marks' => 5,
            ],
            [
                'subject_id' => 3, // Database Systems
                'question_type_id' => 5, // Essay
                'text' => 'Discuss the evolution of database systems from hierarchical to relational to NoSQL models.',
                'difficulty_level' => 'hard',
                'marks' => 5,
            ],
            [
                'subject_id' => 5, // Physics
                'question_type_id' => 5, // Essay
                'text' => 'Explain the implications of Einstein\'s theory of relativity on our understanding of time and space.',
                'difficulty_level' => 'hard',
                'marks' => 5,
            ],
            [
                'subject_id' => 6, // Chemistry
                'question_type_id' => 5, // Essay
                'text' => 'Discuss the environmental impacts of fossil fuels and potential alternative energy sources.',
                'difficulty_level' => 'hard',
                'marks' => 5,
            ],
            [
                'subject_id' => 7, // Biology
                'question_type_id' => 5, // Essay
                'text' => 'Explain the ethical implications of genetic engineering and CRISPR technology.',
                'difficulty_level' => 'hard',
                'marks' => 5,
            ],
            [
                'subject_id' => 4, // Calculus
                'question_type_id' => 5, // Essay
                'text' => 'Discuss the real-world applications of differential equations in various fields of science.',
                'difficulty_level' => 'hard',
                'marks' => 5,
            ],
            
            // A few more challenging questions for each subject
            [
                'subject_id' => 1, // Programming Fundamentals
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the concept of polymorphism in object-oriented programming.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            [
                'subject_id' => 2, // Data Structures
                'question_type_id' => 4, // Short Answer
                'text' => 'Describe the process of rebalancing in an AVL tree after insertion.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            [
                'subject_id' => 3, // Database Systems
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the concept of database sharding and its benefits.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            [
                'subject_id' => 4, // Calculus
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the concept of a definite integral as the limit of a Riemann sum.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            [
                'subject_id' => 5, // Physics
                'question_type_id' => 4, // Short Answer
                'text' => 'Explain the dual nature of light as a wave and a particle.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],

    [
        'subject_id' => 1,
        'question_type_id' => 1,
        'text' => 'Which keyword is used to define a class in Java?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 3,
        'text' => 'Python uses static typing.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 4,
        'text' => 'Explain polymorphism with an example.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 6,
        'text' => 'In Java, the ______ keyword is used to inherit a class.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1,
        'text' => 'What is the default value of a boolean variable in Java?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1,
        'text' => 'Which data structure uses LIFO principle?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 3,
        'text' => 'Java supports multiple inheritance for classes.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 4,
        'text' => 'What are access modifiers in Java? List them.',
        'difficulty_level' => 'easy',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1,
        'text' => 'Which loop is guaranteed to execute at least once in Java?',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 6,
        'text' => 'A ______ is a blueprint for creating objects.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1,
        'text' => 'What is the size of a "char" in Java?',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 3,
        'text' => 'The "finally" block is executed only if an exception is thrown.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 4,
        'text' => 'Describe the use of the "super" keyword in Java.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1,
        'text' => 'What is the parent class of all Java classes?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 6,
        'text' => 'In Java, the ______ method is used to compare two strings ignoring case.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1,
        'text' => 'Which method is called when an object is created in Java?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 3,
        'text' => 'The "static" keyword can be used with a class.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 4,
        'text' => 'What is method overloading and how is it different from overriding?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1,
        'text' => 'What does OOP stand for?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1,
        'text' => 'What is the output of 5 + 3 + "2" in Java?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],

    // Subject 2: Algorithms (20 questions)
    [
        'subject_id' => 2,
        'question_type_id' => 1,
        'text' => 'Which sorting algorithm has the worst-case time complexity O(n²)?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 3,
        'text' => 'A stack follows FIFO order.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 4,
        'text' => 'Describe Dijkstra\'s algorithm.',
        'difficulty_level' => 'hard',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 6,
        'text' => 'The height of a balanced binary tree with N nodes is ______.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1,
        'text' => 'What is the time complexity of binary search?',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1,
        'text' => 'Which data structure uses a hash function?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 3,
        'text' => 'Dynamic programming is mainly used for optimization problems.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 4,
        'text' => 'What is memoization in dynamic programming?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1,
        'text' => 'In a max-heap, the parent node is always:',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 6,
        'text' => 'In a graph, the number of edges incident to a vertex is called its ______.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1,
        'text' => 'Which algorithm uses divide and conquer strategy?',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 3,
        'text' => 'A binary tree can have more than two children per node.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 4,
        'text' => 'Explain the difference between BFS and DFS.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1,
        'text' => 'What is the space complexity of merge sort?',
        'difficulty_level' => 'hard',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1,
        'text' => 'The postfix notation of A+B*C is:',
        'difficulty_level' => 'hard',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 3,
        'text' => 'Breadth-First Search uses a stack data structure.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 4,
        'text' => 'What is a spanning tree?',
        'difficulty_level' => 'hard',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1,
        'text' => 'Which algorithm finds the shortest path in a weighted graph?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 6,
        'text' => 'The process of visiting all nodes in a tree is called ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1,
        'text' => 'Which of the following is a linear data structure?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],

    // Continue this pattern for subjects 3-5 with similar structure
    // (Database, Mathematics, Physics) ensuring 20 questions per subject
    // with mixed question types, difficulties, and marks.

    // Example of remaining structure (truncated for brevity):
    [
        'subject_id' => 3,
        'question_type_id' => 1,
        'text' => 'Which SQL command deletes a table?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 3,
        'text' => 'A primary key can have NULL values.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1,
        'text' => 'What is the integral of 1/x?',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 3,
        'text' => 'The speed of light is constant.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],

            [
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is a linear data structure?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 3, // True/False
        'text' => 'Python is a statically typed programming language.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the worst-case time complexity of bubble sort?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 3, // True/False
        'text' => 'A stack follows the FIFO principle.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL clause is used to filter records?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The ______ statement is used to delete records from a table.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the derivative of ln(x)?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 3, // True/False
        'text' => 'The integral of a constant is always zero.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the SI unit of force?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The formula for kinetic energy is ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is not a programming paradigm?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 3, // True/False
        'text' => 'C++ supports multiple inheritance.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'Which data structure uses LIFO principle?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The time complexity of binary search is ______.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL keyword is used to sort the result set?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 3, // True/False
        'text' => 'A primary key can have NULL values.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the limit of (1 + 1/n)^n as n approaches infinity?',
        'difficulty_level' => 'hard',
        'marks' => 3,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The derivative of x^2 is ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the unit of electric current?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 3, // True/False
        'text' => 'The nucleus of an atom is positively charged.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is a compiled language?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The process of finding and fixing errors in code is called ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'Which algorithm is used for finding the shortest path in a graph?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 3, // True/False
        'text' => 'A queue follows the LIFO principle.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL statement is used to create a new table?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The ______ keyword is used to select unique records from a table.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the value of sin(π/2)?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 3, // True/False
        'text' => 'The derivative of a constant is zero.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the unit of energy?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The formula for work done is ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is a dynamically typed language?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 3, // True/False
        'text' => 'JavaScript is a single-threaded language.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'Which sorting algorithm has the best average-case time complexity?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The time complexity of merge sort is ______.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL statement is used to delete a table?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 3, // True/False
        'text' => 'A foreign key can have duplicate values.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the value of cos(0)?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The integral of 1/x is ______.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the unit of power?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 3, // True/False
        'text' => 'The speed of sound is greater in solids than in gases.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is a functional programming language?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The process of converting code into machine language is called ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'Which data structure is used for implementing recursion?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 3, // True/False
        'text' => 'A linked list is a linear data structure.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL statement is used to add a new column to a table?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The ______ statement is used to combine rows from two or more tables.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the value of tan(π/4)?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 3, // True/False
        'text' => 'The derivative of a sum is the sum of the derivatives.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the unit of electric charge?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The formula for power is ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is a scripting language?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 3, // True/False
        'text' => 'HTML is a programming language.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'Which algorithm is used for matrix multiplication?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The time complexity of insertion sort is ______.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL statement is used to remove a column from a table?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 3, // True/False
        'text' => 'A view in SQL is a physical table.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the value of sin(0)?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The integral of cos(x) is ______.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the unit of resistance?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 3, // True/False
        'text' => 'The force of gravity is stronger on the Moon than on Earth.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is a markup language?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The process of converting high-level code to machine code is called ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'Which data structure is used for implementing a priority queue?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 3, // True/False
        'text' => 'A binary search tree is always balanced.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL statement is used to rename a table?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The ______ keyword is used to filter groups in SQL.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
            [
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is a high-level programming language?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 3, // True/False
        'text' => 'Python supports method overloading by default.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'Which algorithm is used to find the minimum spanning tree of a graph?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 3, // True/False
        'text' => 'A hash table guarantees O(1) time complexity for all operations.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL keyword is used to retrieve unique values from a column?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The ______ clause is used to group rows that have the same values.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the value of the derivative of a constant function?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 3, // True/False
        'text' => 'The integral of a function represents the area under its curve.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the SI unit of frequency?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The formula for gravitational force is ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is a dynamically typed language?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 3, // True/False
        'text' => 'C++ supports garbage collection.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'Which data structure is used for implementing a LIFO system?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The time complexity of heap sort is ______.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL statement is used to create a new database?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 3, // True/False
        'text' => 'A primary key can contain duplicate values.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the value of the integral of 0?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The derivative of sin(x) is ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the unit of electric potential?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 3, // True/False
        'text' => 'The speed of light is faster in water than in air.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is a statically typed language?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The process of converting source code into machine code is called ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'Which algorithm is used to find the shortest path in a weighted graph?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 3, // True/False
        'text' => 'A binary tree can have more than two children per node.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL statement is used to modify the structure of a table?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The ______ keyword is used to sort the result set in descending order.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the value of the derivative of x^3?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 3, // True/False
        'text' => 'The integral of a function can be negative.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the unit of capacitance?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The formula for Ohm\'s Law is ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is a scripting language?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 3, // True/False
        'text' => 'JavaScript is a case-sensitive language.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'Which sorting algorithm has a worst-case time complexity of O(n log n)?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The time complexity of selection sort is ______.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL statement is used to delete a database?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 3, // True/False
        'text' => 'A foreign key can reference a non-primary key column.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the value of the integral of 2x?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The derivative of cos(x) is ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the unit of magnetic flux?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 3, // True/False
        'text' => 'The Earth\'s magnetic field is strongest at the equator.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is a markup language?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The process of finding errors in code is called ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'Which data structure is used for implementing a FIFO system?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 3, // True/False
        'text' => 'A binary search tree is always a balanced tree.',
        'difficulty_level' => 'medium',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL statement is used to add a new row to a table?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The ______ keyword is used to filter rows in SQL.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the value of the derivative of e^x?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 3, // True/False
        'text' => 'The integral of a function is always positive.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the unit of inductance?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 5,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The formula for potential energy is ______.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 1, // MCQ
        'text' => 'Which of the following is a compiled language?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 1,
        'question_type_id' => 3, // True/False
        'text' => 'Java is platform-independent.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 1, // MCQ
        'text' => 'Which algorithm is used to detect cycles in a graph?',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 2,
        'question_type_id' => 6, // Fill in the Blank
        'text' => 'The time complexity of bubble sort is ______.',
        'difficulty_level' => 'medium',
        'marks' => 2,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 1, // MCQ
        'text' => 'Which SQL statement is used to change the data type of a column?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 3,
        'question_type_id' => 3, // True/False
        'text' => 'A unique key can have NULL values.',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    [
        'subject_id' => 4,
        'question_type_id' => 1, // MCQ
        'text' => 'What is the value of the integral of x^2?',
        'difficulty_level' => 'easy',
        'marks' => 1,
    ],
    
        ];

        foreach ($questions as $question) {
            Question::create($question);
        }
    }
}
