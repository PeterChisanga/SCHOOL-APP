<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class TeacherController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;
        $teachers = Teacher::where('school_id', $schoolId)->get();

        return view('teachers.index', compact('teachers'));
    }

    public function show($id)
    {
        $teacher = Teacher::with('school', 'class')->findOrFail($id);

        return view('teachers.show', compact('teacher'));
    }

    public function create()
    {
        return view('teachers.create');
    }

    public function store(Request $request)
    {
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
            'class_id' => null,
            'user_id' => $user->id,
        ]);

        return redirect('/teachers')->with('success', 'Teacher registered successfully.');
    }
}
