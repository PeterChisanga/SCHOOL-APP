<?php

namespace App\Http\Controllers;

use App\Models\Pupil;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class PupilController extends Controller {

    public function index(Request $request) {
        try {
            $schoolId = Auth::user()->school_id;

            $classes = ClassModel::where('school_id', $schoolId)->get();

            $classId = $request->input('class_id');

            $pupils = Pupil::with(['school', 'class'])
                        ->where('school_id', $schoolId)
                        ->when($classId, function ($query, $classId) {
                            return $query->where('class_id', $classId);
                        })
                        ->get();

            return view('pupils.index', compact('pupils', 'classes'));
        } catch (Exception $e) {
            \Log::error('Error fetching pupils: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch pupils.');
        }
    }

    public function create() {
        try {
            $schoolId = Auth::user()->school_id;

            $classes = ClassModel::where('school_id', $schoolId)->get();

            return view('pupils.create', compact('classes'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the pupil creation form.');
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'gender' => 'required|string|max:255',
                'religion' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
                'admission_date' => 'nullable|date',
                'health_conditions' => 'nullable|string|max:255',
                'class_id' => 'required|exists:classes,id',
            ]);

            $schoolId = Auth::user()->school_id;

            $pupilData = $request->all();
            $pupilData['school_id'] = $schoolId;

            $pupil = Pupil::create($pupilData);

            return redirect()->route('pupils.show', $pupil->id)
                ->with('success', 'Pupil Registered successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error storing pupil: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to register pupil: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Pupil $pupil) {
        try {
            return view('pupils.show', compact('pupil'));
        } catch (Exception $e) {
            \Log::error('Error showing pupil: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load pupil details.');
        }
    }

    public function edit(Pupil $pupil) {
        try {
            $schoolId = Auth::user()->school_id;

            $classes = ClassModel::where('school_id', $schoolId)->get();

            return view('pupils.edit', compact('pupil', 'classes'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the pupil edit form.');
        }
    }

    public function update(Request $request, Pupil $pupil) {
        try {
            $this->validate($request, [
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'gender' => 'required|string|max:255',
                'blood_group' => 'required|string|max:255',
                'religion' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
                'admission_date' => 'nullable|date',
                'health_conditions' => 'nullable|string|max:255',
                'class_id' => 'required|exists:classes,id',
            ]);

            $schoolId = Auth::user()->school_id;

            if ($pupil->school_id !== $schoolId) {
                return redirect()->route('pupils.index')
                    ->with('error', 'You are not authorized to update this pupil.');
            }

            $pupil->update($request->all());

            return redirect()->route('pupils.show', $pupil->id)
                ->with('success', 'Pupil updated successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error updating pupil: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update pupil: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Pupil $pupil) {
        try {
            $pupil->delete();
            return redirect()->route('pupils.index')->with('success', 'Pupil and associated data deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Error deleting pupil: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete pupil.');
        }
    }
}
