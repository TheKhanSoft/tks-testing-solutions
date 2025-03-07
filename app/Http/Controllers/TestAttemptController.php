<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestAttemptFormRequest; // You might need to create this form request
use App\Services\TestAttemptService;
use App\Models\TestAttempt;
use App\Models\User; // Import User model
use App\Models\Paper; // Import Paper model
use Illuminate\Http\Request;

class TestAttemptController extends Controller
{
    protected $testAttemptService;

    public function __construct(TestAttemptService $testAttemptService)
    {
        $this->testAttemptService = $testAttemptService;
    }

    /**
     * Display a listing of test attempts.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $testAttempts = $this->testAttemptService->getPaginatedTestAttempts();
        return view('test_attempts.index', compact('testAttempts')); // Assuming you have a test_attempts.index view
    }

    /**
     * Show the form for creating a new test attempt.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $users = User::all(); // Fetch users for dropdown
        $papers = Paper::all(); // Fetch papers for dropdown
        return view('test_attempts.create', compact('users', 'papers')); // Assuming you have a test_attempts.create view
    }

    /**
     * Store a newly created test attempt in storage.
     *
     * @param  TestAttemptFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TestAttemptFormRequest $request)
    {
        $validatedData = $request->validated();
        $this->testAttemptService->createTestAttempt($validatedData);

        return redirect()->route('test-attempts.index')->with('success', 'Test Attempt created successfully!');
    }

    /**
     * Display the specified test attempt.
     *
     * @param  TestAttempt  $testAttempt
     * @return \Illuminate\View\View
     */
    public function show(TestAttempt $testAttempt)
    {
        return view('test_attempts.show', compact('testAttempt')); // Assuming you have a test_attempts.show view
    }

    /**
     * Show the form for editing the specified test attempt.
     *
     * @param  TestAttempt  $testAttempt
     * @return \Illuminate\View\View
     */
    public function edit(TestAttempt $testAttempt)
    {
        $users = User::all(); // Fetch users for dropdown
        $papers = Paper::all(); // Fetch papers for dropdown
        return view('test_attempts.edit', compact('testAttempt', 'users', 'papers')); // Assuming you have a test_attempts.edit view
    }

    /**
     * Update the specified test attempt in storage.
     *
     * @param  TestAttemptFormRequest  $request
     * @param  TestAttempt  $testAttempt
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(TestAttemptFormRequest $request, TestAttempt $testAttempt)
    {
        $validatedData = $request->validated();
        $this->testAttemptService->updateTestAttempt($testAttempt, $validatedData);

        return redirect()->route('test-attempts.index')->with('success', 'Test Attempt updated successfully!');
    }

    /**
     * Remove the specified test attempt from storage.
     *
     * @param  TestAttempt  $testAttempt
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TestAttempt $testAttempt)
    {
        $this->testAttemptService->deleteTestAttempt($testAttempt);

        return redirect()->route('test-attempts.index')->with('success', 'Test Attempt deleted successfully!');
    }
}