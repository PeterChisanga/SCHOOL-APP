<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class SubjectController extends Controller
{
    public function index() {
        try {
            $schoolId = Auth::user()->school_id;

            // Fetch subjects belonging to the authenticated user's school
            $subjects = Subject::where('school_id', $schoolId)->get();

            return view('subjects.index', compact('subjects'));
        } catch (Exception $e) {
            \Log::error('Error fetching subjects: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch subjects.');
        }
    }

    public function create() {
        try {
            return view('subjects.create');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the subject creation form.');
        }
    }

    public function store(Request $request) {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:255',
            ]);

            $schoolId = Auth::user()->school_id;

            Subject::create([
                'name' => $request->input('name'),
                'school_id' => $schoolId,
            ]);

            return redirect()->route('subjects.index')
                ->with('success', 'Subject created successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error storing subject: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create subject: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Subject $subject) {
        try {
            $schoolId = Auth::user()->school_id;

            if ($subject->school_id !== $schoolId) {
                return redirect()->route('subjects.index')
                    ->with('error', 'You are not authorized to view this subject.');
            }

            return view('subjects.show', compact('subject'));
        } catch (Exception $e) {
            \Log::error('Error showing subject: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load subject details.');
        }
    }

    public function edit(Subject $subject) {
        try {
            $schoolId = Auth::user()->school_id;

            if ($subject->school_id !== $schoolId) {
                return redirect()->route('subjects.index')
                    ->with('error', 'You are not authorized to edit this subject.');
            }

            return view('subjects.edit', compact('subject'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the subject edit form.');
        }
    }

    public function update(Request $request, Subject $subject) {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:255',
            ]);

            $schoolId = Auth::user()->school_id;

            if ($subject->school_id !== $schoolId) {
                return redirect()->route('subjects.index')
                    ->with('error', 'You are not authorized to update this subject.');
            }

            $subject->update([
                'name' => $request->input('name'),
            ]);

            return redirect()->route('subjects.index')
                ->with('success', 'Subject updated successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error updating subject: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update subject: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Subject $subject) {
        try {
            $schoolId = Auth::user()->school_id;

            if ($subject->school_id !== $schoolId) {
                return redirect()->route('subjects.index')
                    ->with('error', 'You are not authorized to delete this subject.');
            }

            $subject->delete();

            return redirect()->route('subjects.index')
                ->with('success', 'Subject deleted successfully!');
        } catch (Exception $e) {
            \Log::error('Error deleting subject: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete subject.');
        }
    }
}