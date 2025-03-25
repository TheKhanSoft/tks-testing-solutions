<?php

namespace App\Services;

use App\Models\Question;
use App\Support\ImportFields;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\Services\ExportImportService;

class QuestionService extends BaseService
{
    protected $exportImportService;

    /**
     * QuestionService constructor.
     */
    public function __construct(ExportImportService $exportImportService)
    {
        $this->modelClass = Question::class;
        $this->exportImportService = $exportImportService;
    }
    
    /**
     * Get all questions.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Question>
     */
    public function getAllQuestions(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
    }

    /**
     * Get paginated questions.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Question>
     */
    public function getPaginatedQuestions(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return $this->getPaginated($perPage, $columns, $relations);
    }

    /**
     * Get a question by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Question|null
     */
    public function getQuestionById(int $id, array $columns = ['*'], array $relations = []): ?Question
    {
        return $this->getById($id, $columns, $relations);
    }

    /**
     * Get a question by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Question
     */
    public function getQuestionByIdOrFail(int $id, array $columns = ['*'], array $relations = []): Question
    {
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new question with options
     *
     * @param array $data
     * @return Question
     */
    public function createQuestion(array $data): Question
    {
        $question = Question::create([
            'text' => $data['text'],
            'subject_id' => $data['subject_id'],
            'question_type_id' => $data['question_type_id'],
            'difficulty_level' => $data['difficulty_level'],
            'marks' => $data['marks'],
            'status' => $data['status'],
        ]);
        
        // Save options if provided
        if (isset($data['options']) && is_array($data['options'])) {
            foreach ($data['options'] as $option) {
                $question->options()->create([
                    'text' => $option['text'],
                    'is_correct' => $option['is_correct'] ?? false
                ]);
            }
        }
        
        return $question;
    }

    /**
     * Update a question and its options
     *
     * @param Question $question
     * @param array $data
     * @return Question
     */
    public function updateQuestion(Question $question, array $data): Question
    {
        DB::transaction(function () use ($question, $data) {
            $question->update([
                'text' => $data['text'],
                'subject_id' => $data['subject_id'],
                'question_type_id' => $data['question_type_id'],
                'difficulty_level' => $data['difficulty_level'],
                'marks' => $data['marks'],
                'status' => $data['status'],
            ]);
            //dd($data['options']);

            // Update or create options
            if (isset($data['options']) && is_array($data['options'])) {
                $updatedOptionIds = [];
                
                foreach ($data['options'] as $optionData) {
                    $updatedOptionIds[] = $optionData['id'] ?? null;
                    // Update or create the option
                    $question->options()->updateOrCreate(
                        ['id' => $optionData['id'] ?? null], // Check if the option already exists
                        $optionData // Data to update or create
                    );
                }
                
                // Delete options that weren't updated
                $question->options()
                    ->whereNotIn('id', $updatedOptionIds)
                    ->delete();
                
            }
        });
        
        return $question->fresh(['options']);
    }
    
    /**
     * Delete a question and its related options
     *
     * @param Question $question
     * @return bool
     */
    public function deleteQuestion(Question $question): bool
    {
        // Delete options first
        $question->options()->delete();
        
        // Delete the question
        return $question->delete();
    }
    
    /**
     * Check if a question is of multiple choice type
     *
     * @param int|string $questionTypeId
     * @return bool
     */
    public function shouldShowOptions($questionTypeId): bool
    {
        // Adjust these IDs based on your actual multiple choice question types
        $mcqTypes = [1, 2, 6];
        return in_array((int)$questionTypeId, $mcqTypes);
    }

    /**
     * Restore a soft-deleted question.
     *
     * @param int $id
     * @return bool
     */
    public function restoreQuestion(int $id): bool
    {
        return $this->restore($id);
    }

    /**
     * Force delete a question permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteQuestion(int $id): ?bool
    {
        return $this->forceDelete($id);
    }

    /**
     * Search questions by question text or explanation.
     *
     * @param string $searchTerm
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function searchQuestions(string $searchTerm, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        return $this->search($searchTerm, $perPage, $columns, $relations);
    }

    /**
     * Get questions by question type.
     *
     * @param int $questionTypeId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsByType(int $questionTypeId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Question::ofType($questionTypeId)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get questions by subject.
     *
     * @param int $subjectId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsBySubject(int $subjectId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Question::ofSubject($subjectId)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get questions with question type.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $questionTypeRelations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsWithQuestionType(int $perPage = 10, array $columns = ['*'], array $questionTypeRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('questionType', $questionTypeRelations, $perPage, $columns);
    }

    /**
     * Get questions with subject.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $subjectRelations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsWithSubject(int $perPage = 10, array $columns = ['*'], array $subjectRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('subject', $subjectRelations, $perPage, $columns);
    }

    /**
     * Get questions with options.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $optionRelations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsWithOptions(int $perPage = 10, array $columns = ['*'], array $optionRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('options', $optionRelations, $perPage, $columns);
    }

    /**
     * Get questions with answers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $answerRelations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsWithAnswers(int $perPage = 10, array $columns = ['*'], array $answerRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('answers', $answerRelations, $perPage, $columns);
    }
    
    /**
     * Get random questions.
     *
     * @param int $count
     * @param array|null $subjectIds
     * @param array|null $questionTypeIds
     * @param array $relations
     * @return Collection<int, Question>
     */
    public function getRandomQuestions(int $count, ?array $subjectIds = null, ?array $questionTypeIds = null, array $relations = []): Collection
    {
        $query = Question::query()->with($relations);
        
        if ($subjectIds) {
            $query->whereIn('subject_id', $subjectIds);
        }
        
        if ($questionTypeIds) {
            $query->whereIn('question_type_id', $questionTypeIds);
        }
        
        return $query->inRandomOrder()->limit($count)->get();
    }
    
    /**
     * Get questions for a paper.
     *
     * @param int $paperId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsForPaper(int $paperId, ?int $perPage = null, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Question::whereHas('papers', function($query) use ($paperId) {
            $query->where('paper_id', $paperId);
        })->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get questions by difficulty level.
     *
     * @param int $difficultyLevel
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsByDifficultyLevel(int $difficultyLevel, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Question::where('difficulty_level', $difficultyLevel)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Process question data and determine if options are needed
     *
     * @param array $data
     * @return array
     */
    public function processQuestionData(array $data): array
    {
        $mcqTypes = [1, 2, 6]; // Multiple choice question type IDs
        $needsOptions = in_array((int)$data['question_type_id'], $mcqTypes);
        
        if (!$needsOptions) {
            unset($data['options']);
        }
        
        return $data;
    }

    /**
     * Bulk update question status
     *
     * @param array $questionIds
     * @param string $status
     * @return void
     */
    public function bulkUpdateStatus(array $questionIds, string $status): void
    {
        Question::whereIn('id', $questionIds)->update(['status' => $status]);
    }

    /**
     * Bulk update question difficulty
     *
     * @param array $questionIds
     * @param string $difficulty
     * @return void
     */
    public function bulkUpdateDifficulty(array $questionIds, string $difficulty): void
    {
        Question::whereIn('id', $questionIds)->update(['difficulty_level' => $difficulty]);
    }

    /**
     * Bulk update question subject
     *
     * @param array $questionIds
     * @param int $subjectId
     * @return void
     */
    public function bulkUpdateSubject(array $questionIds, int $subjectId): void
    {
        Question::whereIn('id', $questionIds)->update(['subject_id' => $subjectId]);
    }

    /**
     * Bulk update question type
     *
     * @param array $questionIds
     * @param int $typeId
     * @return void
     */
    public function bulkUpdateQuestionType(array $questionIds, int $typeId): void
    {
        Question::whereIn('id', $questionIds)->update(['question_type_id' => $typeId]);
    }

    /**
     * Bulk delete questions
     *
     * @param array $questionIds
     * @return void
     */
    public function bulkDelete(array $questionIds): void
    {
        $questions = Question::whereIn('id', $questionIds)->get();
        foreach ($questions as $question) {
            $this->deleteQuestion($question);
        }
    }

    /**
     * Export questions.
     *
     * @param string $format
     * @param array $filters
     * @return string
     */
    public function exportQuestions(string $format, array $filters = []): string
    {
        // Get the questions with eager loading
        $questions = $this->getFilteredQuestions($filters)
            ->with(['subject:id,name', 'questionType:id,name'])
            ->get();
        
        // Transform questions into export format
        $exportData = collect($questions)->map(function ($question) {
            return [
                'text' => strip_tags($question->text),
                'subject' => $question->subject->name ?? 'N/A',
                'type' => $question->questionType->name ?? 'N/A',
                'difficulty_level' => ucfirst($question->difficulty_level),
                'marks' => $question->marks,
                'status' => $question->status
            ];
        });

        $headers = [
            ['key' => 'text', 'label' => 'Question'],
            ['key' => 'subject', 'label' => 'Subject'],
            ['key' => 'type', 'label' => 'Question Type'],
            ['key' => 'difficulty_level', 'label' => 'Difficulty'],
            ['key' => 'marks', 'label' => 'Marks'],
            ['key' => 'status', 'label' => 'Status']
        ];

        // Convert format to lowercase for consistency
        $format = strtolower($format);
        if ($format === 'excel (xlsx)') {
            $format = 'xlsx';
        }

        return $this->exportImportService->export(
            format: $format,
            data: $exportData,
            headers: $headers,
            viewData: ['title' => 'Questions List'],
            filename: 'questions-export'
        );
    }

    /**
     * Import questions.
     *
     * @param string $filePath
     * @return array
     */
    public function importQuestions($filePath): array
    {
        $columnMap = ImportFields::$questionFields;
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $debug = [];

        try {
            // Capture the value before import process
            $initialSuccessCount = $successCount;
            
            $importResult = $this->exportImportService->import(
                file: $filePath,
                columnMap: $columnMap,
                rowValidator: function($data, $rowIndex) use (&$errors) {
                    $rowNum = $rowIndex + 2; // Add 2 to account for header row and 0-based index
                    
                    if (empty($data['question_text'])) {
                        return "Row {$rowNum}: Question text is required";
                    }
                    if (!in_array($data['difficulty_level'], ['easy', 'medium', 'hard'])) {
                        return "Row {$rowNum}: Invalid difficulty level";
                    }
                    if (!is_numeric($data['subject_id'])) {
                        return "Row {$rowNum}: Invalid subject ID";
                    }
                    if (!is_numeric($data['question_type_id'])) {
                        return "Row {$rowNum}: Invalid question type ID";
                    }
                    if (!is_numeric($data['marks']) || $data['marks'] < 0) {
                        return "Row {$rowNum}: Invalid marks";
                    }
                    if (isset($data['negative_marks']) && (!is_numeric($data['negative_marks']) || $data['negative_marks'] < 0)) {
                        return "Row {$rowNum}: Invalid negative marks";
                    }
                    return true;
                },
                rowProcessor: function($data) use (&$successCount, &$errors) {
                    try {
                        DB::transaction(function() use ($data, &$successCount) {
                            // Create question
                            $question = Question::create([
                                'text' => $data['question_text'],
                                'subject_id' => $data['subject_id'],
                                'question_type_id' => $data['question_type_id'],
                                'difficulty_level' => $data['difficulty_level'],
                                'marks' => $data['marks'],
                                'negative_marks' => $data['negative_marks'] ?? 0,
                                'status' => $data['status'] ?? 'active',
                                'explanation' => $data['explanation'] ?? null
                            ]);

                            // Process options if provided
                            if (!empty($data['options'])) {
                                $options = array_map(function($optionStr) {
                                    $parts = explode('|', $optionStr);
                                    if (count($parts) !== 3) {
                                        throw new \Exception('Invalid option format');
                                    }
                                    return [
                                        'text' => $parts[0],
                                        'is_correct' => (bool)$parts[1],
                                        'order' => (int)$parts[2]
                                    ];
                                }, explode('||', $data['options']));

                                foreach ($options as $option) {
                                    $question->options()->create($option);
                                }
                            }
                            
                            // Increment success count
                            $successCount++;
                        });
                    } catch (\Exception $e) {
                        // Add the error to our list
                        $errors[] = "Error processing row: " . $e->getMessage();
                        // Don't increment success count
                    }
                }
            );

            // Capture value after import process
            $finalSuccessCount = $successCount;
            
            // Debug store
            $debug = [
                'rows' => $importResult['rows'] ?? [],
                'headers' => $importResult['headers'] ?? [],
                'columnIndexes' => $importResult['columnIndexes'] ?? [],
                'processed' => $importResult['processed'] ?? 0,
                'skipped' => $importResult['skipped'] ?? 0,
                'initialSuccessCount' => $initialSuccessCount,
                'finalSuccessCount' => $finalSuccessCount,
                'rowCount' => count($importResult['rows'] ?? [])
            ];
            
            // Only use the error count from the import result
            $errorCount = $importResult['skipped'] ?? 0;
            $errors = array_merge($errors, $importResult['errors'] ?? []);

            // Try to manually count created questions if successCount is still 0
            if ($successCount === 0) {
                // Let's count questions created in the last minute as a fallback
                $recentQuestions = Question::where('created_at', '>=', now()->subMinutes(1))->count();
                $debug['recentQuestionsCount'] = $recentQuestions;
                
                if ($recentQuestions > 0) {
                    $successCount = $recentQuestions;
                }
            }
            
            return [
                'success' => $successCount > 0,
                'message' => "Successfully imported {$finalSuccessCount} questions!" . 
                            ($errorCount > 0 ? " (failed: {$errorCount})" : ""),
                'rows' => $importResult['rows'] ?? [],
                'processed' => $importResult['processed'] ?? 0,
                'skipped' => $importResult['skipped'] ?? 0,
                'initialSuccessCount' => $initialSuccessCount,
                'finalSuccessCount' => $finalSuccessCount,
                'rowCount' => count($importResult['rows'] ?? []),
                'errors' => $errors,
                'debug' => $debug
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
                'debug' => $debug
            ];
        }
    }

    /**
     * Get filtered questions.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getFilteredQuestions($filters)
    {
        return Question::query()
            ->when($filters['search'] ?? null, function($query, $search) {
                return $query->where('text', 'like', "%{$search}%");
            })
            ->when($filters['difficulty_level'] ?? null, function($query, $level) {
                return $query->where('difficulty_level', $level);
            })
            ->when($filters['subject_id'] ?? null, function($query, $subjectId) {
                return $query->where('subject_id', $subjectId);
            })
            ->when($filters['question_type_id'] ?? null, function($query, $typeId) {
                return $query->where('question_type_id', $typeId);
            })
            ->when($filters['sort_by'] ?? null, function($query, $sortBy) {
                return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'asc');
            });
    }
}