<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class TeacherController extends Controller {
    public function index() {
        try {
            $schoolId = Auth::user()->school_id;
            $teachers = Teacher::where('school_id', $schoolId)->get();

            return view('teachers.index', compact('teachers'));
        } catch (Exception $e) {
            \Log::error('Error fetching teachers: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch teachers.');
        }
    }

    public function show($id) {
        try {
            $teacher = Teacher::with('school', 'class')->findOrFail($id);

            return view('teachers.show', compact('teacher'));
        } catch (Exception $e) {
            \Log::error('Error showing teacher: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load teacher details.');
        }
    }

    public function create() {
        try {
            $schoolId = Auth::user()->school_id;
            $classes = ClassModel::where('school_id', $schoolId)->get();

            return view('teachers.create', compact('classes'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the teacher creation form.');
        }
    }

    public function store(Request $request) {
        try {
            $this->validate($request, [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'marital_status' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'required|string',
                'gender' => 'required|string',
                'date_of_birth' => 'required|date',
            ]);

            $userData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone,
                'user_type' => 'teacher',
            ];

            $schoolId = Auth::user()->school_id;
            $userData['school_id'] = $schoolId;

            $user = User::create($userData);

            Teacher::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name ?? null,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address ?? null,
                'date_of_birth' => $request->date_of_birth,
                'admission_date' => $request->admission_date ?? null,
                'qualification' => $request->qualification ?? null,
                'salary' => $request->salary ?? null,
                'school_id' => $schoolId,
                'class_id' => $request->class_id ?? null,
                'user_id' => $user->id,
            ]);

            return redirect('/teachers')->with('success', 'Teacher registered successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error storing teacher: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to register teacher: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Teacher $teacher) {
        try {
            $schoolId = Auth::user()->school_id;
            $classes = ClassModel::where('school_id', $schoolId)->get();

            return view('teachers.edit', compact('teacher', 'classes'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the teacher edit form.');
        }
    }

    public function update(Request $request, Teacher $teacher) {
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'marital_status' => 'required|string|max:255',
                'phone' => 'required|string',
                'gender' => 'required|string',
                'date_of_birth' => 'required|date',
                'password' => 'nullable|string|confirmed|min:8',
            ]);

            $teacher->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'admission_date' => $request->admission_date,
                'class_id' => $request->class_id,
                'qualification' => $request->qualification,
                'salary' => $request->salary,
            ]);

            $userData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $teacher->user->update($userData);

            return redirect()->route('teachers.index')->with('success', 'Teacher updated successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error updating teacher: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update teacher: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Teacher $teacher) {
        try {
            $teacher->delete();
            $teacher->user->delete();

            return redirect()->route('teachers.index')->with('success', 'Teacher deleted successfully.');
        } catch (Exception $e) {
            \Log::error('Error deleting teacher: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete teacher.');
        }
    }
}
