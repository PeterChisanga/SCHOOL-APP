<?php

namespace App\Http\Controllers;

use App\Models\Secretary;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SecretaryController extends Controller {
    public function index() {
        $schoolId = Auth::user()->school_id;
        $secretaries = Secretary::where('school_id', $schoolId)->get();

        return view('secretaries.index', compact('secretaries'));
    }

    public function show($id)
    {
        $secretary = Secretary::with('school', 'user')->findOrFail($id);

        return view('secretaries.show', compact('secretary'));
    }

    public function create()
    {
        $schoolId = Auth::user()->school_id;

        return view('secretaries.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string',
        ]);

        $userData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone,
            'user_type' => 'secretary',
        ];

        $schoolId = Auth::user()->school_id;
        $userData['school_id'] = $schoolId;

        $user = User::create($userData);

        Secretary::create([
            'school_id' => $schoolId,
            'user_id' => $user->id,
        ]);

        return redirect('/secretaries')->with('success', 'Secretary registered successfully.');
    }

    public function edit(Secretary $secretary)
    {
        return view('secretaries.edit', compact('secretary'));
    }

    public function update(Request $request, Secretary $secretary)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string',
            'password' => 'nullable|string|confirmed|min:8',
        ]);

        $userData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $secretary->user->update($userData);

        return redirect()->route('secretaries.index')->with('success', 'Secretary updated successfully.');
    }

    public function destroy(Secretary $secretary)
    {
        $secretary->delete();
        $secretary->user->delete();

        return redirect()->route('secretaries.index')->with('success', 'Secretary deleted successfully.');
    }
}
