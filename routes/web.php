<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/home', 'users.index')->name('home');
Volt::route('/', 'users.index')->name('users.index');
Volt::route('/login', 'auth.login')->name('login');
Volt::route('/register', 'auth.register')->name('register');
Volt::route('/profile', 'settings.profile')->name('profile');
Volt::route('/settings', 'appearance')->name('settings');

Volt::route('/question-types', 'question-types.index')->name('question-type.index');

Route::prefix('questions')->group(function () {
    Volt::route('/', 'questions.index')->name('questions.index');
    Volt::route('/import', 'questions.import')->name('questions.import');
    Volt::route('/{question}/edit', 'questions.edit')->name('questions.edit');
    Volt::route('/{question}/show', 'questions.show')->name('questions.show');
    Route::get('/download-template', [\App\Http\Controllers\QuestionController::class, 'downloadTemplate'])
        ->name('questions.download-template');
    Route::get('/download-error-log', [\App\Http\Controllers\QuestionController::class, 'downloadErrorLog'])
        ->name('questions.download-error-log');
});


Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Volt::route('/dashboard', 'dashboard.index')->name('dashboard');
    
    // Question Types
    Route::prefix('question-types')->group(function () {
        Volt::route('/', 'question-types.index')->name('question-types.index');
    });

    // Questions
    // Route::prefix('questions')->group(function () {
    //     Volt::route('/', 'questions.index')->name('questions.index');
    //     Volt::route('/import', 'questions.import')->name('questions.import');
    //     Volt::route('/{question}/edit', 'questions.edit')->name('questions.edit');
    //     Volt::route('/{question}/show', 'questions.show')->name('questions.show');
    //     Route::get('/download-template', [\App\Http\Controllers\QuestionController::class, 'downloadTemplate'])
    //         ->name('questions.download-template');
    //     Route::get('/download-error-log', [\App\Http\Controllers\QuestionController::class, 'downloadErrorLog'])
    //         ->name('questions.download-error-log');
    // });

    // Papers
    Route::prefix('papers')->group(function () {
        Volt::route('/', 'papers.index')->name('papers.index');
    });

    // Test Attempts
    Route::prefix('test-attempts')->group(function () {
        Volt::route('/', 'test-attempts.index')->name('test-attempts.index');
    });

    // Departments
    Route::prefix('departments')->group(function () {
        Volt::route('/', 'departments.index')->name('departments.index');
        Volt::route('/{department}/faculty-members', 'departments.faculty-members')->name('departments.faculty-members');
        Volt::route('/{department}/subjects', 'departments.subjects')->name('departments.subjects');
    });

    Route::prefix('subjects')->group(function () {
        Volt::route('/', 'subjects.index')->name('subjects.index');
        Volt::route('/{subjects}/faculty-members', 'subjects.faculty-members')->name('subjects.faculty-members');
        Volt::route('/{subjects}/departments', 'subjects.departments')->name('subjects.subjects');
    });

    // Faculty Members
    Route::prefix('faculty-members')->group(function () {
        Volt::route('/', 'faculty-members.index')->name('faculty-members.index');
        Volt::route('/{faculty}/subjects', 'faculty-members.subjects')->name('faculty.subjects');
    });

    // User Categories
    Route::prefix('user-categories')->group(function () {
        Volt::route('/', 'user-categories.index')->name('user-categories.index');
        Volt::route('/{category}/users', 'user-categories.users')->name('user-categories.users');
        Volt::route('/{category}/papers', 'user-categories.papers')->name('user-categories.papers');
    });

    // Users
    Route::prefix('users')->group(function () {
        Volt::route('/', 'users.index')->name('users.index');
    });
});
