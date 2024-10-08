<?php

namespace App\Http\Controllers;

use App\Models\Pupil;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PupilController extends Controller {

    public function index(Request $request) {
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
    }


    public function create()
    {
        $schoolId = Auth::user()->school_id;

        $classes = ClassModel::where('school_id', $schoolId)->get();

        return view('pupils.create', compact('classes'));
    }

    public function store(Request $request)
    {
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

        $pupilData = $request->all();
        $pupilData['school_id'] = $schoolId;

        $pupil = Pupil::create($pupilData);

        return redirect()->route('pupils.show', $pupil->id)
        ->with('success', 'Pupil Registered successfully!');
    }

    public function show(Pupil $pupil)
    {
        return view('pupils.show', compact('pupil'));
    }

    public function edit(Pupil $pupil)
    {

         $schoolId = Auth::user()->school_id;

        $classes = ClassModel::where('school_id', $schoolId)->get();

        return view('pupils.edit', compact('pupil','classes'));
    }

    public function update(Request $request, Pupil $pupil)
    {
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
    }


    public function destroy(Pupil $pupil)
    {
        $pupil->delete();

        return redirect()->route('pupils.index')
            ->with('success', 'Pupil deleted successfully!');
    }
}
