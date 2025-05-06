<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pupil;
use App\Models\ClassModel;
use App\Models\Teacher;
use App\Models\Secretary;
use App\Models\Subject;
use App\Models\ParentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Exception;

class UserController extends Controller
{
    public function create() {
        try {
            return view('users.create');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the user creation form.');
        }
    }

    public function store(Request $request) {
        try {
            $this->validate($request, [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email', // Email is nullable
                'password' => 'required|string|min:8|confirmed',
                'phone_number' => 'required|string|max:255|unique:users,phone_number', // Add unique rule
            ]);

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'user_type' => $request->user_type,
            ]);

            return redirect('/login')->with('success', 'Account created successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error storing user: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create account: ' . $e->getMessage())->withInput();
        }
    }

    public function login(Request $request) {
        try {
            $request->validate([
                'login' => 'required|string',
                'password' => 'required|string',
            ]);

            $loginField = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';
            $credentials = [
                $loginField => $request->input('login'),
                'password' => $request->input('password'),
            ];

            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                if ($user->school_id !== null) {
                    if ($user->user_type == 'admin') {
                        return redirect('/admin/dashboard');
                    } elseif ($user->user_type == 'secretary') {
                        return redirect('/secretary/dashboard');
                    } elseif ($user->user_type == 'teacher') {
                        return redirect('/teacher/dashboard');
                    } elseif ($user->user_type == 'parent') {
                        return redirect('/parent/dashboard');
                    } elseif ($user->user_type == 'student') {
                        return redirect('/student/dashboard');
                    }
                } else {
                    return redirect()->route('schools.create');
                }
            }

            return redirect()->back()->withInput()->withErrors(['login' => 'Invalid login credentials.']);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error during login: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Login failed. Please try again.');
        }
    }
    
    public function adminDashboard() {
        try {
            $schoolId = Auth::user()->school_id;

            $studentsCount = Pupil::where('school_id', $schoolId)->count();
            $teachersCount = Teacher::where('school_id', $schoolId)->count();
            $parentsCount = ParentModel::where('school_id', $schoolId)->count();
            $classesCount = ClassModel::where('school_id', $schoolId)->count();
            $subjectsCount = Subject::where('school_id', $schoolId)->count();

            $classes = ClassModel::where('school_id', $schoolId)->get();
            $classNames = $classes->pluck('name');
            $studentsPerClass = $classes->map(function ($class) {
                return $class->pupils()->count();
            });

            return view('dashboard.admin', compact(
                'studentsCount', 'teachersCount', 'parentsCount',
                'classesCount', 'subjectsCount', 'classNames', 'studentsPerClass'
            ));
        } catch (Exception $e) {
            \Log::error('Error loading admin dashboard: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load admin dashboard.');
        }
    }

    public function teacherDashboard() {
        try {
            $schoolId = Auth::user()->school_id;

            $studentsCount = Pupil::where('school_id', $schoolId)->count();
            $teachersCount = Teacher::where('school_id', $schoolId)->count();
            $parentsCount = ParentModel::where('school_id', $schoolId)->count();
            $classesCount = ClassModel::where('school_id', $schoolId)->count();
            $subjectsCount = Subject::where('school_id', $schoolId)->count();

            $classes = ClassModel::where('school_id', $schoolId)->get();
            $classNames = $classes->pluck('name');
            $studentsPerClass = $classes->map(function ($class) {
                return $class->pupils()->count();
            });

            return view('dashboard.teacher', compact(
                'studentsCount', 'teachersCount', 'parentsCount',
                'classesCount', 'subjectsCount', 'classNames', 'studentsPerClass'
            ));
        } catch (Exception $e) {
            \Log::error('Error loading teacher dashboard: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load teacher dashboard.');
        }
    }

    public function secretaryDashboard() {
        try {
            $schoolId = Auth::user()->school_id;

            $studentsCount = Pupil::where('school_id', $schoolId)->count();
            $teachersCount = Teacher::where('school_id', $schoolId)->count();
            $parentsCount = ParentModel::where('school_id', $schoolId)->count();
            $classesCount = ClassModel::where('school_id', $schoolId)->count();
            $subjectsCount = Subject::where('school_id', $schoolId)->count();

            $classes = ClassModel::where('school_id', $schoolId)->get();
            $classNames = $classes->pluck('name');
            $studentsPerClass = $classes->map(function ($class) {
                return $class->pupils()->count();
            });

            return view('dashboard.secretary', compact(
                'studentsCount', 'teachersCount', 'parentsCount',
                'classesCount', 'subjectsCount', 'classNames', 'studentsPerClass'
            ));
        } catch (Exception $e) {
            \Log::error('Error loading secretary dashboard: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load secretary dashboard.');
        }
    }

    public function logout() {
        try {
            Auth::logout();
            return redirect('/login')->with('success', 'Logged out successfully.');
        } catch (Exception $e) {
            \Log::error('Error during logout: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to log out. Please try again.');
        }
    }

    public function show(User $user) {
        try {
            $user = Auth::user();
            return view('users.show', compact('user'));
        } catch (Exception $e) {
            \Log::error('Error showing user profile: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load user profile.');
        }
    }

    public function changePassword(Request $request) {
        try {
            $user = Auth::user();

            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return redirect()->route('users.show')->with('success', 'Password changed successfully');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error changing password: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to change password: ' . $e->getMessage());
        }
    }

    public function update(Request $request, User $user) {
        try {
            $this->validate($request, [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
                'phone_number' => 'required|string|max:255|unique:users,phone_number,' . $user->id,
            ]);

            $user->update($request->all());

            return response()->json([
                'message' => 'User updated successfully!',
                'user' => $user,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            \Log::error('Error updating user: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update user: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id) {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        } catch (Exception $e) {
            \Log::error('Error deleting user: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete user.');
        }
    }
}
