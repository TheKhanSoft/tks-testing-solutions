# TKS Testing Solutions Database Seeders

This directory contains all the database seeders for the TKS Testing Solutions application. These seeders populate the database with sample data for development and testing purposes.

## Seeder Order and Dependencies

The seeders are designed to be run in a specific order due to dependencies between tables. The `DatabaseSeeder` class handles this ordering automatically.

## Available Seeders

### Core System Seeders
- **UserSeeder**: Creates default user accounts with various roles
- **PermissionsSeeder**: Sets up user roles and permissions

### Academic Structure Seeders
- **DepartmentSeeder**: Creates academic departments
- **SubjectSeeder**: Creates subjects for various disciplines
- **DepartmentSubjectSeeder**: Establishes relationships between departments and subjects

### Faculty Seeders
- **FacultyMemberSeeder**: Creates faculty member accounts
- **FacultySubjectSeeder**: Assigns subjects to faculty members for teaching

### Testing Content Seeders
- **QuestionTypeSeeder**: Creates different types of questions (Multiple Choice, Essay, etc.)
- **QuestionSeeder**: Creates sample questions for each subject
- **QuestionOptionSeeder**: Creates options for multiple-choice questions with explanations
- **TagSeeder**: Creates tags for categorizing questions by topic
- **QuestionTagSeeder**: Associates questions with relevant tags

### Test & Exam Structure Seeders
- **PaperCategorySeeder**: Creates categories for papers (Midterm, Final, Quiz, etc.)
- **UserCategorySeeder**: Creates user categories (Undergraduate, Graduate, etc.)
- **PaperSeeder**: Creates sample test papers with configurable settings
- **PaperQuestionSeeder**: Assigns questions to papers
- **PaperUserCategorySeeder**: Defines which user categories can access specific papers

### Test Attempt Seeders
- **TestAttemptSeeder**: Creates sample test attempts by users
- **AnswerSeeder**: Creates sample answers for test attempts
- **TestAttemptAnalyticsSeeder**: Populates analytics data for test attempts
- **TestProgressSeeder**: Simulates user progress through tests including visited and flagged questions

## Running the Seeders

To run all seeders in the correct order:

```bash
php artisan db:seed
```

To run a specific seeder:

```bash
php artisan db:seed --class=QuestionSeeder
```

## Database Design Notes

### Question Management
- For multiple-choice questions, correct answers are defined by the `is_correct` flag in the question_options table
- For text-based questions (essay, short answer, fill-in-the-blank), the correct answer is stored in the `correct_answer` field in the questions table
- The `has_multiple_correct_answers` flag indicates whether multiple options can be correct for a multiple-choice question
- Each option can have an explanation to display after answering
- Questions can be tagged with multiple topics for better organization and searching

### Test Configuration
- Papers can be configured with various settings like:
  - Question/option shuffling
  - Immediate or delayed results
  - Custom passing percentage
  - Availability periods
- Test attempts now track analytics like percentage scores, time taken, and question counts
- The test progress system allows for question navigation, flagging, and resuming from where left off

### Performance Optimizations
- Unique constraints prevent duplicate relationships
- Selective indexes on frequently queried columns improve performance
- Soft deletes allow recovery of accidentally deleted content
