<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;

        // Fetch subjects belonging to the authenticated user's school
        $subjects = Subject::where('school_id', $schoolId)->get();

        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('subjects.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $schoolId = Auth::user()->school_id;

        // Create a new subject
        Subject::create([
            'name' => $request->input('name'),
            'school_id' => $schoolId,
        ]);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject created successfully!');
    }

    public function show(Subject $subject)
    {
        $schoolId = Auth::user()->school_id;

        // Check if the subject belongs to the authenticated user's school
        if ($subject->school_id !== $schoolId) {
            return redirect()->route('subjects.index')
                ->with('error', 'You are not authorized to view this subject.');
        }

        return view('subjects.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        $schoolId = Auth::user()->school_id;

        // Check if the subject belongs to the authenticated user's school
        if ($subject->school_id !== $schoolId) {
            return redirect()->route('subjects.index')
                ->with('error', 'You are not authorized to edit this subject.');
        }

        return view('subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $schoolId = Auth::user()->school_id;

        // Check if the subject belongs to the authenticated user's school
        if ($subject->school_id !== $schoolId) {
            return redirect()->route('subjects.index')
                ->with('error', 'You are not authorized to update this subject.');
        }

        // Update the subject
        $subject->update([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject updated successfully!');
    }

    public function destroy(Subject $subject)
    {
        $schoolId = Auth::user()->school_id;

        // Check if the subject belongs to the authenticated user's school
        if ($subject->school_id !== $schoolId) {
            return redirect()->route('subjects.index')
                ->with('error', 'You are not authorized to delete this subject.');
        }

        // Delete the subject
        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully!');
    }
}
