<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class SchoolController extends Controller
{
    public function create() {
        try {
            return view('schools.create');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the school creation form.');
        }
    }

    public function store(Request $request) {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
            ]);

            $userId = Auth::id();

            $schoolData = $request->all();
            $schoolData['owner_id'] = $userId;

            $school = School::create($schoolData);

            $user = User::find($userId);
            $user->school_id = $school->id;
            $user->save();

            return redirect()->route('admin.dashboard')
                ->with('success', 'School registered successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error storing school: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to register school: ' . $e->getMessage())->withInput();
        }
    }

    public function show() {
        try {
            $schoolId = Auth::user()->school_id;
            $school = School::find($schoolId);
            $school->load('users');

            return view('schools.show', compact('school'));
        } catch (Exception $e) {
            \Log::error('Error showing school: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load school details.');
        }
    }

    public function edit(School $school) {
        try {
            return view('schools.edit', compact('school'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the school edit form.');
        }
    }

    public function update(Request $request, School $school) {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($school->photo) {
                    \Storage::disk('public')->delete($school->photo);
                }

                // Store the new photo
                $photoPath = $request->file('photo')->store('school_logos', 'public');
                $school->photo = $photoPath;
            }

            $school->update($request->except('photo'));

            return redirect()->route('schools.show', $school->id)
                ->with('success', 'School updated successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error updating school: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update school: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(School $school) {
        try {
            $school->delete();

            return redirect()->route('schools.index')
                ->with('success', 'School deleted successfully!');
        } catch (Exception $e) {
            \Log::error('Error deleting school: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete school.');
        }
    }
}
