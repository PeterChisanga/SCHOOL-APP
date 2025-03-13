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

class UserController extends Controller
{
    public function create() {
        return view('users.create');
    }

    public function store(Request $request) {
        $this->validate($request, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'required|string',
            'user_type' => 'required|string',
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
    }

    public function login(Request $request) {
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
                if(Auth::user()->user_type == 'admin') {
                    return redirect('/admin/dashboard');
                } else if(Auth::user()->user_type == 'secretary') {
                    return redirect('/secretary/dashboard');
                } else if(Auth::user()->user_type == 'teacher') {
                    return redirect('/teacher/dashboard');
                } else if(Auth::user()->user_type == 'parent') {
                    return redirect('/parent/dashboard');
                } else if(Auth::user()->user_type == 'student') {
                    return redirect('/student/dashboard');
                } else if(Auth::user()->user_type == 'secretary') {
                    return redirect('/secretary/dashboard');
                }
            } else {
                return redirect()->route('schools.create');
            }
        }

        return redirect()->back()->withInput()->withErrors(['login' => 'Invalid login credentials.']);
    }

    public function adminDashboard()
    {
        $schoolId = Auth::user()->school_id;

        $studentsCount = Pupil::where('school_id', $schoolId)->count();
        $teachersCount = Teacher::where('school_id', $schoolId)->count();
        $parentsCount = ParentModel::where('school_id', $schoolId)->count();
        $classesCount = ClassModel::where('school_id', $schoolId)->count();
        $subjectsCount = Subject::where('school_id', $schoolId)->count();

        $classes = ClassModel::where('school_id', $schoolId)->get();
        $classNames = $classes->pluck('name');
        $studentsPerClass = $classes->map(function($class) {
            return $class->pupils()->count();
        });

        return view('dashboard.admin', compact(
            'studentsCount', 'teachersCount', 'parentsCount',
            'classesCount', 'subjectsCount', 'classNames', 'studentsPerClass'
        ));
    }

    public function teacherDashboard() {
        $schoolId = Auth::user()->school_id;

        $studentsCount = Pupil::where('school_id', $schoolId)->count();
        $teachersCount = Teacher::where('school_id', $schoolId)->count();
        $parentsCount = ParentModel::where('school_id', $schoolId)->count();
        $classesCount = ClassModel::where('school_id', $schoolId)->count();
        $subjectsCount = Subject::where('school_id', $schoolId)->count();

        $classes = ClassModel::where('school_id', $schoolId)->get();
        $classNames = $classes->pluck('name');
        $studentsPerClass = $classes->map(function($class) {
            return $class->pupils()->count();
        });

        return view('dashboard.teacher', compact(
            'studentsCount', 'teachersCount', 'parentsCount',
            'classesCount', 'subjectsCount', 'classNames', 'studentsPerClass'
        ));
    }

    public function secretaryDashboard() {
        $schoolId = Auth::user()->school_id;

        $studentsCount = Pupil::where('school_id', $schoolId)->count();
        $teachersCount = Teacher::where('school_id', $schoolId)->count();
        $parentsCount = ParentModel::where('school_id', $schoolId)->count();
        $classesCount = ClassModel::where('school_id', $schoolId)->count();
        $subjectsCount = Subject::where('school_id', $schoolId)->count();

        $classes = ClassModel::where('school_id', $schoolId)->get();
        $classNames = $classes->pluck('name');
        $studentsPerClass = $classes->map(function($class) {
            return $class->pupils()->count();
        });

        return view('dashboard.secretary', compact(
            'studentsCount', 'teachersCount', 'parentsCount',
            'classesCount', 'subjectsCount', 'classNames', 'studentsPerClass'
        ));
    }

    public function logout() {
        Auth::logout();
        return redirect('/login')->with('success', 'Logged out successfully.');
    }

    public function show(User $user) {
        $user = Auth::user();
        return view('users.show', compact('user'));
    }

    public function changePassword(Request $request) {
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
    }

    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            'phone_number' => 'required|string',
        ]);

        $user->update($request->all());

        return response()->json([
            'message' => 'User updated successfully!',
            'user' => $user,
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
