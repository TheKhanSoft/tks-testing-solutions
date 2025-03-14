<?php

namespace App\Services;

use App\Models\User;
use App\Models\FacultyMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;

class AuthService
{
    /**
     * @var UserService
     */
    protected UserService $userService;

    /**
     * @var FacultyMemberService
     */
    protected FacultyMemberService $facultyMemberService;

    /**
     * AuthService constructor.
     *
     * @param UserService $userService
     * @param FacultyMemberService $facultyMemberService
     */
    public function __construct(
        UserService $userService,
        FacultyMemberService $facultyMemberService
    ) {
        $this->userService = $userService;
        $this->facultyMemberService = $facultyMemberService;
    }

    /**
     * Authenticate a user.
     *
     * @param string $email
     * @param string $password
     * @param string $guard
     * @return User|FacultyMember
     * @throws AuthenticationException
     */
    public function authenticate(string $email, string $password, string $guard = 'web'): User|FacultyMember
    {
        $credentials = [
            'email' => $email,
            'password' => $password,
        ];

        if (!Auth::guard($guard)->attempt($credentials)) {
            throw new AuthenticationException('Invalid credentials');
        }

        return Auth::guard($guard)->user();
    }

    /**
     * Log out the currently authenticated user.
     *
     * @param string $guard
     * @return void
     */
    public function logout(string $guard = 'web'): void
    {
        Auth::guard($guard)->logout();
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return User
     */
    public function registerUser(array $data): User
    {
        return $this->userService->createUser($data);
    }

    /**
     * Register a new faculty member.
     *
     * @param array $data
     * @return FacultyMember
     */
    public function registerFacultyMember(array $data): FacultyMember
    {
        return $this->facultyMemberService->createFacultyMember($data);
    }

    /**
     * Change password for a user.
     *
     * @param User $user
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws AuthenticationException
     */
    public function changeUserPassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new AuthenticationException('Current password is incorrect');
        }

        $this->userService->updateUser($user, [
            'password' => $newPassword,
        ]);

        return true;
    }

    /**
     * Change password for a faculty member.
     *
     * @param FacultyMember $facultyMember
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws AuthenticationException
     */
    public function changeFacultyMemberPassword(FacultyMember $facultyMember, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $facultyMember->password)) {
            throw new AuthenticationException('Current password is incorrect');
        }

        $this->facultyMemberService->updateFacultyMember($facultyMember, [
            'password' => $newPassword,
        ]);

        return true;
    }

    /**
     * Get the currently authenticated user.
     *
     * @param string $guard
     * @return User|FacultyMember|null
     */
    public function getCurrentUser(string $guard = 'web'): User|FacultyMember|null
    {
        return Auth::guard($guard)->user();
    }

    /**
     * Check if a user is authenticated.
     *
     * @param string $guard
     * @return bool
     */
    public function isAuthenticated(string $guard = 'web'): bool
    {
        return Auth::guard($guard)->check();
    }

    /**
     * Generate a password reset token.
     *
     * @param string $email
     * @param string $guard
     * @return string|null
     */
    public function generatePasswordResetToken(string $email, string $guard = 'web'): ?string
    {
        // This would integrate with Laravel's password reset functionality
        // Implementation depends on specific requirements
        return null;
    }
}
