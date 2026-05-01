<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(): View
    {
        return view('login');
    }

    /**
     * Show the registration form.
     */
    public function showRegister(): View
    {
        return view('register');
    }

    public function showForgotPassword(): View
    {
        return view('forgot-password');
    }

    public function showForgotPasswordSent(Request $request): View
    {
        $email = (string) $request->session()->get('password_reset_email', '');

        return view('forgot-password-sent', compact('email'));
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::sendResetLink($request->only('email'));

        return redirect()
            ->route('password.sent')
            ->with('password_reset_email', (string) $request->string('email'));
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    /**
     * Handle the registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:student,faculty'],
            'enrollment_type' => ['nullable', 'string', 'in:New Student,Old Student', 'required_if:role,student'],
            'academic_level' => ['nullable', 'string', 'required_if:role,student'],
            'course' => ['nullable', 'string', 'required_if:role,student'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'enrollment_type' => $data['enrollment_type'] ?? null,
            'academic_level' => $data['academic_level'] ?? null,
            'course' => $data['course'] ?? null,
        ]);

        return redirect()->route('login')
            ->with('status', 'Registration successful! Please log in with your new account.')
            ->withInput(['email' => $data['email'], 'role' => $data['role']]);
    }

    /**
     * Handle the authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $request->merge(['email' => trim($request->email)]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'role' => ['required', 'in:student,faculty,admin'],
        ]);  // Login accepts admin role, but registration does not

        $selectedRole = $credentials['role'];
        unset($credentials['role']);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->role !== $selectedRole) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'This account is a '.ucfirst($user->role).', not a '.ucfirst($selectedRole).'.',
                ])->withInput();
            }

            if ($user->status === 'inactive') {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact the administrator.',
                ])->withInput();
            }

            if ($user->role === 'student' && $user->status === 'pending') {
                Auth::logout();
                $hasRequiredProof = false;
                if ($user->enrollment_type === 'New Student' && $user->receipt_proof) {
                    $hasRequiredProof = true;
                } elseif ($user->enrollment_type === 'Old Student' && $user->student_id_proof) {
                    $hasRequiredProof = true;
                }

                if ($hasRequiredProof) {
                    return back()->withErrors([
                        'email' => 'Your account is under review by an administrator. Please wait for activation.',
                    ])->withInput();
                }

                $request->session()->put('pending_user_id', $user->id);

                return back()->with('show_verification', true)
                    ->with('enrollment_type', $user->enrollment_type)
                    ->withInput();
            }

            $request->session()->regenerate();

            // Role-based redirect after login
            return match ($selectedRole) {
                'admin' => redirect()->intended(route('admin.dashboard')),
                'faculty' => redirect()->intended(route('faculty.dashboard')),
                'student' => redirect()->intended(route('student.dashboard')),
                default => redirect()->intended('/'),
            };
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
            'force_password_change' => false,
        ]);

        return redirect()
            ->route(Auth::user()->role.'.dashboard')
            ->with('status', 'Password updated successfully. You can now access the full system.');
    }

    public function verifyUpload(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('pending_user_id');
        if (! $userId) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please try again.']);
        }

        $user = User::find($userId);
        if (! $user || $user->status !== 'pending') {
            return redirect()->route('login')->withErrors(['email' => 'Invalid account state.']);
        }

        if ($user->enrollment_type === 'New Student') {
            $request->validate([
                'receipt_proof' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
            ]);

            if ($request->hasFile('receipt_proof')) {
                $user->receipt_proof = $request->file('receipt_proof')->store('verifications', 'public');
            }
        } elseif ($user->enrollment_type === 'Old Student') {
            $request->validate([
                'student_id_proof' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
            ]);

            if ($request->hasFile('student_id_proof')) {
                $user->student_id_proof = $request->file('student_id_proof')->store('verifications', 'public');
            }
        } else {
            return redirect()->route('login')->withErrors(['email' => 'Invalid enrollment type.']);
        }

        $user->save();
        $request->session()->forget('pending_user_id');

        return redirect()->route('login')->with('status', 'Verification documents submitted successfully! Your account is now under review.');
    }
}
