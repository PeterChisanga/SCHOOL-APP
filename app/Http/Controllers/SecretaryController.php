<?php

namespace App\Http\Controllers;

use App\Models\Secretary;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class SecretaryController extends Controller {
    public function index() {
        try {
            $schoolId = Auth::user()->school_id;
            $secretaries = Secretary::where('school_id', $schoolId)->get();

            return view('secretaries.index', compact('secretaries'));
        } catch (Exception $e) {
            \Log::error('Error fetching secretaries: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch secretaries.');
        }
    }

    public function show($id) {
        try {
            $secretary = Secretary::with('school', 'user')->findOrFail($id);

            return view('secretaries.show', compact('secretary'));
        } catch (Exception $e) {
            \Log::error('Error showing secretary: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load secretary details.');
        }
    }

    public function create() {
        try {
            $schoolId = Auth::user()->school_id;

            return view('secretaries.create');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the secretary creation form.');
        }
    }

    public function store(Request $request) {
        try {
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
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error storing secretary: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to register secretary: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Secretary $secretary) {
        try {
            return view('secretaries.edit', compact('secretary'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the secretary edit form.');
        }
    }

    public function update(Request $request, Secretary $secretary) {
        try {
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
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error updating secretary: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update secretary: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Secretary $secretary) {
        try {
            $secretary->delete();
            $secretary->user->delete();

            return redirect()->route('secretaries.index')->with('success', 'Secretary deleted successfully.');
        } catch (Exception $e) {
            \Log::error('Error deleting secretary: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete secretary.');
        }
    }
}
