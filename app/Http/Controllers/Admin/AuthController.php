<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show admin login form.
     */
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Handle admin login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['username'])
            ->orWhere('name', $credentials['username'])
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if (!$user->is_admin) {
                return back()->withErrors(['username' => 'Access denied. You do not have administrator privileges.']);
            }
            
            if ($user->status !== 'active') {
                return back()->withErrors(['username' => 'Your account is inactive.']);
            }

            Auth::guard('admin')->login($user);
            $user->updateLastLogin();

            // Log activity
            ActivityLog::logAction(
                $user->id,
                'admin_login',
                'admin',
                $user->id,
                ['username' => $user->name]
            );

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['username' => 'Invalid credentials.']);
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        $adminId = Auth::guard('admin')->id();

        // Log activity
        if ($adminId) {
            ActivityLog::logAction(
                $adminId,
                'admin_logout',
                'admin',
                $adminId
            );
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Show admin profile.
     */
    public function showProfile()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile', compact('admin'));
    }

    /**
     * Update admin profile.
     */
    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:admin_users,email,' . $admin->id,
        ]);

        $admin->update($validated);

        // Log activity
        ActivityLog::logAction(
            $admin->id,
            'profile_updated',
            'admin',
            $admin->id,
            $validated
        );

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Change admin password.
     */
    public function changePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $admin->password = Hash::make($validated['new_password']);
        $admin->save();

        // Log activity
        ActivityLog::logAction(
            $admin->id,
            'password_changed',
            'admin',
            $admin->id
        );

        return back()->with('success', 'Password changed successfully.');
    }
}
