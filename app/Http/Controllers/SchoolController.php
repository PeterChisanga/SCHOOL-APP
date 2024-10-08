<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolController extends Controller
{
    public function create()
    {
        return view('schools.create');
    }

    public function store(Request $request)
    {
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
    }

    public function show() {
        $schoolId = Auth::user()->school_id;
        $school = School::find($schoolId);
        $school->load('users');

        return view('schools.show', compact('school'));
    }

    public function edit(School $school)
    {
        return view('schools.edit', compact('school'));
    }

    public function update(Request $request, School $school) {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            //  'photo' => 'nullable|image|max:2048',
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
    }

    public function destroy(School $school)
    {
        $school->delete();

        return redirect()->route('schools.index')
            ->with('success', 'School deleted successfully!');
    }
}
