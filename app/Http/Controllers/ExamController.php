<?php

namespace App\Http\Controllers;

use App\Models\ExamResult;
use App\Models\Subject;
use App\Models\ClassModel;
use App\Models\Pupil;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;


class ExamController extends Controller {

    public function index(Request $request) {
        $schoolId = Auth::user()->school_id;
        $classes = ClassModel::where('school_id', $schoolId)->get();
        $subjects = Subject::where('school_id', $schoolId)->get();

        $query = ExamResult::with(['pupil.class', 'subject'])->whereHas('pupil', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        });

        if ($request->filled('class_id')) {
            $query->whereHas('pupil', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $examResults = $query
                    ->orderBy('updated_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('examResults.index', compact('examResults', 'classes', 'subjects'));
    }


    public function create(Request $request) {
        $schoolId = Auth::user()->school_id;

        $subjects = Subject::where('school_id', $schoolId)->get();
        $classes = ClassModel::where('school_id', $schoolId)->get();
        $classId = $request->input('class_id');

        $pupils = Pupil::where('school_id', $schoolId)
                    ->when($classId, function ($query) use ($classId) {
                        return $query->where('class_id', $classId);
                    })
                    ->get();

        return view('examResults.create', compact('subjects', 'pupils', 'classes', 'classId'));
    }

    public function store(Request $request) {
        $this->validate($request, [
            'term' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'pupil_results.*.mid_term_mark' => 'required|numeric|min:0|max:100',
            'pupil_results.*.end_of_term_mark' => 'required|numeric|min:0|max:100',
        ]);

        $schoolId = Auth::user()->school_id;

        foreach ($request->input('pupil_results') as $pupilId => $result) {
            $pupil = Pupil::findOrFail($pupilId);
            if ($pupil->school_id !== $schoolId) {
                return redirect()->route('examResults.index')
                    ->with('error', 'You are not authorized to add an exam result for this pupil.');
            }

            ExamResult::create([
                'pupil_id' => $pupilId,
                'subject_id' => $request->input('subject_id'),
                'term' => $request->input('term'),
                'mid_term_mark' => $result['mid_term_mark'],
                'end_of_term_mark' => $result['end_of_term_mark'],
            ]);
        }

        return redirect()->route('examResults.index')
            ->with('success', 'Exam results recorded successfully!');
    }

    public function show(ExamResult $examResult) {
        $schoolId = Auth::user()->school_id;

        if ($examResult->pupil->school_id !== $schoolId) {
            return redirect()->route('examResults.index')
                ->with('error', 'You are not authorized to view this exam result.');
        }

        $terms = $examResult->pupil->examResults->pluck('term')->unique();

        return view('examResults.show', compact('examResult', 'terms'));
    }

    // public function exportPdf(Pupil $pupil) {
    //     $schoolId = Auth::user()->school_id;

    //     $school = School::find($schoolId);

    //     if ($pupil->school_id !== $schoolId) {
    //         return redirect()->route('examResults.index')
    //             ->with('error', 'You are not authorized to export this exam result.');
    //     }

    //     $pdf = PDF::loadView('examResults.pdf', compact('pupil','school'));
    //     return $pdf->download('exam_results_' . $pupil->first_name . '.pdf');
    // }


    public function exportPdf(Pupil $pupil, $term) {
        $schoolId = Auth::user()->school_id;

        $school = School::find($schoolId);

        if ($pupil->school_id !== $schoolId) {
            return redirect()->route('examResults.index')
                ->with('error', 'You are not authorized to export this exam result.');
        }

        // Fetch results for the selected term only
        $examResultsForTerm = $pupil->examResults->where('term', $term);

        // Pass selected term, exam results, and school to the PDF view
        $pdf = PDF::loadView('examResults.pdf', [
            'pupil' => $pupil,
            'school' => $school,
            'examResultsForTerm' => $examResultsForTerm,
            'term' => $term
        ]);

        return $pdf->download("exam_results_{$pupil->first_name}_{$term}.pdf");
    }

    public function edit(ExamResult $examResult) {
        $schoolId = Auth::user()->school_id;

        if ($examResult->pupil->school_id !== $schoolId) {
            return redirect()->route('examResults.index')
                ->with('error', 'You are not authorized to edit this exam result.');
        }

        $subjects = Subject::where('school_id', $schoolId)->get();

        return view('examResults.edit', compact('examResult', 'subjects'));
    }

    public function update(Request $request, ExamResult $examResult) {
        $this->validate($request, [
            'term' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'mid_term_mark' => 'required|numeric|min:0|max:100',
            'end_of_term_mark' => 'required|numeric|min:0|max:100',
        ]);

        $schoolId = Auth::user()->school_id;

        if ($examResult->pupil->school_id !== $schoolId) {
            return redirect()->route('examResults.index')
                ->with('error', 'You are not authorized to update this exam result.');
        }

        $examResult->update($request->all());

        return redirect()->route('examResults.index')
            ->with('success', 'Exam result updated successfully!');
    }

    public function destroy(ExamResult $examResult) {
        $schoolId = Auth::user()->school_id;

        if ($examResult->pupil->school_id !== $schoolId) {
            return redirect()->route('examResults.index')
                ->with('error', 'You are not authorized to delete this exam result.');
        }

        $examResult->delete();

        return redirect()->route('examResults.index')
            ->with('success', 'Exam result deleted successfully!');
    }
}
