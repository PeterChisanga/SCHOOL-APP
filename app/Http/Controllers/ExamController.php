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

    // public function index(Request $request) {
    //     $schoolId = Auth::user()->school_id;
    //     $classes = ClassModel::where('school_id', $schoolId)->get();
    //     $subjects = Subject::where('school_id', $schoolId)->get();

    //     $query = ExamResult::with(['pupil.class', 'subject'])->whereHas('pupil', function ($query) use ($schoolId) {
    //         $query->where('school_id', $schoolId);
    //     });

    //     if ($request->filled('class_id')) {
    //         $query->whereHas('pupil', function ($q) use ($request) {
    //             $q->where('class_id', $request->class_id);
    //         });
    //     }

    //     if ($request->filled('subject_id')) {
    //         $query->where('subject_id', $request->subject_id);
    //     }

    //     $examResults = $query
    //                 ->orderBy('updated_at', 'desc')
    //                 ->orderBy('created_at', 'desc')
    //                 ->get();

    //     return view('examResults.index', compact('examResults', 'classes', 'subjects'));
    // }

    public function index(Request $request) {
        $schoolId = Auth::user()->school_id;
        $classes  = ClassModel::where('school_id', $schoolId)->get();
        $subjects = Subject::where('school_id', $schoolId)->get();

        $query = ExamResult::with(['pupil.class', 'subject'])
            ->whereHas('pupil', function ($query) use ($schoolId) {
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

        if ($request->filled('term')) {
            $query->where('term', $request->term);
        }

        $examResults = $query
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by pupil_id + term so each row = one pupil's full term results
        $groupedResults = $examResults->groupBy(function ($result) {
            return $result->pupil_id . '_' . $result->term;
        });

        // Fetch all assessments for this school, grouped by pupil_id + term
        $assessments = \App\Models\Assessment::with('subject')
            ->whereHas('pupil', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->when($request->filled('class_id'), function ($q) use ($request) {
                $q->whereHas('pupil', function ($q2) use ($request) {
                    $q2->where('class_id', $request->class_id);
                });
            })
            ->when($request->filled('term'), function ($q) use ($request) {
                $q->where('term', $request->term);
            })
            ->get()
            ->groupBy(function ($a) {
                return $a->pupil_id . '_' . $a->term;
            });

        return view('examResults.index', compact(
            'examResults', 'groupedResults', 'assessments', 'classes', 'subjects'
        ));
    }

    public function create(Request $request) {
        $schoolId = Auth::user()->school_id;

        $subjects = Subject::where('school_id', $schoolId)->get();
        $classes = ClassModel::where('school_id', $schoolId)->get();
        $pupils = Pupil::where('school_id', $schoolId)->get(); // Fetch all pupils for dropdown
        $classId = $request->input('class_id');
        $pupilId = $request->input('pupil_id');

        // Fetch pupils for the selected class, if any
        $classPupils = $classId
            ? Pupil::where('school_id', $schoolId)->where('class_id', $classId)->get()
            : collect();

        return view('examResults.create', compact('subjects', 'classes', 'pupils', 'classId', 'pupilId', 'classPupils'));
    }

    // public function store(Request $request) {
    //     $schoolId = Auth::user()->school_id;
    //     $isPremium = Auth::user()->isPremium();

    //     $validationRules = [
    //         'subject_id' => 'required|exists:subjects,id',
    //         'term' => 'required|string|max:255',
    //     ];

    //     if ($isPremium) {
    //         $validationRules += [
    //             'mid_term_raw' => 'required|numeric|min:0',
    //             'mid_term_max' => 'required|numeric|min:1',
    //             'end_term_raw' => 'required|numeric|min:0',
    //             'end_term_max' => 'required|numeric|min:1',
    //         ];
    //     } else {
    //         $validationRules += [
    //             'mid_term_mark' => 'required|numeric|min:0|max:100',
    //             'end_of_term_mark' => 'required|numeric|min:0|max:100',
    //         ];
    //     }

    //     $this->validate($request, $validationRules);

    //     if ($request->entry_type == 'single' && !empty($request->single_pupil['pupil_id'])) {
    //         $pupilId = $request->single_pupil['pupil_id'];
    //         $data = $request->single_pupil;

    //         $examResultData = [
    //             'pupil_id' => $pupilId,
    //             'subject_id' => $request->subject_id,
    //             'term' => $request->term,
    //             'comments' => $data['comments'],
    //         ];

    //         if ($isPremium) {
    //             $midTermPercentage = ($data['mid_term_max'] > 0) ? min(($data['mid_term_raw'] / $data['mid_term_max']) * 100, 100) : 0;
    //             $endTermPercentage = ($data['end_term_max'] > 0) ? min(($data['end_term_raw'] / $data['end_term_max']) * 100, 100) : 0;

    //             $examResultData += [
    //                 'mid_term_raw' => $data['mid_term_raw'],
    //                 'mid_term_max' => $data['mid_term_max'],
    //                 'mid_term_mark' => $midTermPercentage,
    //                 'end_term_raw' => $data['end_term_raw'],
    //                 'end_term_max' => $data['end_term_max'],
    //                 'end_of_term_mark' => $endTermPercentage,
    //             ];
    //         } else {
    //             $examResultData += [
    //                 'mid_term_mark' => $data['mid_term_mark'],
    //                 'end_of_term_mark' => $data['end_of_term_mark'],
    //             ];
    //         }

    //         ExamResult::create($examResultData);
    //     } else {
    //         foreach ($request->pupil_results as $pupilId => $result) {
    //             $examResultData = [
    //                 'pupil_id' => $pupilId,
    //                 'subject_id' => $request->subject_id,
    //                 'term' => $request->term,
    //                 'mid_term_mark' => $result['mid_term_mark'],
    //                 'end_of_term_mark' => $result['end_of_term_mark'],
    //                 'comments' => $result['comments'],
    //             ];

    //             ExamResult::create($examResultData);
    //         }
    //     }

    //     return redirect()->route('examResults.index')
    //         ->with('success', 'Exam results saved successfully!');
    // }

    public function store(Request $request) {
        $schoolId = Auth::user()->school_id;
        $isPremium = Auth::user()->isPremium();

        $validationRules = [
            'subject_id' => 'required|exists:subjects,id',
            'term' => 'required|string|max:255',
        ];

        if ($isPremium) {
            if ($request->entry_type == 'single' && !empty($request->single_pupil['pupil_id'])) {
                $validationRules += [
                    'single_pupil.mid_term_raw' => 'required|numeric|min:0',
                    'single_pupil.mid_term_max' => 'required|numeric|min:1',
                    'single_pupil.end_term_raw' => 'required|numeric|min:0',
                    'single_pupil.end_term_max' => 'required|numeric|min:1',
                ];
            } else {
                // Bulk (premium) uses pupil_results array
                $validationRules += [
                    'pupil_results.*.mid_term_raw' => 'required|numeric|min:0',
                    'pupil_results.*.mid_term_max' => 'required|numeric|min:1',
                    'pupil_results.*.end_term_raw' => 'required|numeric|min:0',
                    'pupil_results.*.end_term_max' => 'required|numeric|min:1',
                ];
            }
        } else {
            if ($request->entry_type == 'single' && !empty($request->single_pupil['pupil_id'])) {
                $validationRules += [
                    'single_pupil.mid_term_mark' => 'required|numeric|min:0|max:100',
                    'single_pupil.end_of_term_mark' => 'required|numeric|min:0|max:100',
                ];
            } else {
                $validationRules += [
                    'pupil_results.*.mid_term_mark' => 'required|numeric|min:0|max:100',
                    'pupil_results.*.end_of_term_mark' => 'required|numeric|min:0|max:100',
                ];
            }
        }

        $this->validate($request, $validationRules);

        if ($request->entry_type == 'single' && !empty($request->single_pupil['pupil_id'])) {
            $pupilId = $request->single_pupil['pupil_id'];
            $data = $request->single_pupil;

            $examResultData = [
                'pupil_id' => $pupilId,
                'subject_id' => $request->subject_id,
                'term' => $request->term,
                'comments' => $data['comments'] ?? null,
            ];

            if ($isPremium) {
                $midTermPercentage = ($data['mid_term_max'] > 0) ? min(($data['mid_term_raw'] / $data['mid_term_max']) * 100, 100) : 0;
                $endTermPercentage = ($data['end_term_max'] > 0) ? min(($data['end_term_raw'] / $data['end_term_max']) * 100, 100) : 0;

                $examResultData += [
                    'mid_term_raw' => $data['mid_term_raw'],
                    'mid_term_max' => $data['mid_term_max'],
                    'mid_term_mark' => $midTermPercentage,
                    'end_term_raw' => $data['end_term_raw'],
                    'end_term_max' => $data['end_term_max'],
                    'end_of_term_mark' => $endTermPercentage,
                ];
            } else {
                $examResultData += [
                    'mid_term_mark' => $data['mid_term_mark'],
                    'end_of_term_mark' => $data['end_of_term_mark'],
                ];
            }

            ExamResult::create($examResultData);
        } else {
            foreach ($request->pupil_results as $pupilId => $result) {
                $examResultData = [
                    'pupil_id' => $pupilId,
                    'subject_id' => $request->subject_id,
                    'term' => $request->term,
                    'comments' => $result['comments'] ?? null,
                ];

                if ($isPremium) {
                    $midTermPercentage = ($result['mid_term_max'] > 0) ? min(($result['mid_term_raw'] / $result['mid_term_max']) * 100, 100) : 0;
                    $endTermPercentage = ($result['end_term_max'] > 0) ? min(($result['end_term_raw'] / $result['end_term_max']) * 100, 100) : 0;

                    $examResultData += [
                        'mid_term_raw' => $result['mid_term_raw'],
                        'mid_term_max' => $result['mid_term_max'],
                        'mid_term_mark' => $midTermPercentage,
                        'end_term_raw' => $result['end_term_raw'],
                        'end_term_max' => $result['end_term_max'],
                        'end_of_term_mark' => $endTermPercentage,
                    ];
                } else {
                    $examResultData += [
                        'mid_term_mark' => $result['mid_term_mark'],
                        'end_of_term_mark' => $result['end_of_term_mark'],
                    ];
                }

                ExamResult::create($examResultData);
            }
        }

        return redirect()->route('examResults.index')
            ->with('success', 'Exam results saved successfully!');
    }

    public function show(ExamResult $examResult) {
        $schoolId = Auth::user()->school_id;

        if ($examResult->pupil->school_id !== $schoolId) {
            return redirect()->route('examResults.index')
                ->with('error', 'You are not authorized to view this exam result.');
        }

        $terms = $examResult->pupil->examResults->pluck('term')->unique();

        // Fetch assessments grouped by term for this pupil
        $assessmentsByTerm = $examResult->pupil->assessments()
            ->with('subject')
            ->get()
            ->groupBy('term');

        // Calculate position in class for each term (existing logic unchanged)
        $positions = [];
        $classId = $examResult->pupil->class_id;
        foreach ($terms as $term) {
            $classResults = ExamResult::whereHas('pupil', function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })->where('term', $term)->get();

            $pupilTotals = $classResults->groupBy('pupil_id')->map(function ($pupilResults) {
                $total = $pupilResults->sum(function ($result) {
                    return ($result->mid_term_mark + $result->end_of_term_mark) / 2;
                });
                return [
                    'pupil_id' => $pupilResults->first()->pupil_id,
                    'total'    => $total,
                ];
            })->sortByDesc('total')->values();

            $currentPosition = 1;
            $previousTotal   = null;
            $skipPositions   = 0;
            foreach ($pupilTotals as $pupilData) {
                if ($previousTotal !== $pupilData['total']) {
                    $currentPosition += $skipPositions;
                    $skipPositions = 1;
                } else {
                    $skipPositions++;
                }
                if ($pupilData['pupil_id'] == $examResult->pupil->id) {
                    $positions[$term] = $currentPosition;
                    break;
                }
                $previousTotal = $pupilData['total'];
            }
            if (!isset($positions[$term])) {
                $positions[$term] = '-';
            }
        }

        return view('examResults.show', compact('examResult', 'terms', 'positions', 'assessmentsByTerm'));
    }

    // public function exportPdf(Pupil $pupil, $term) {
    //     $schoolId = $pupil->school_id;
    //     $school = School::find($schoolId);

    //     if ($pupil->school_id !== $schoolId) {
    //         return redirect()->route('examResults.index')
    //             ->with('error', 'You are not authorized to export this exam result.');
    //     }

    //     // Fetch results for the selected term only
    //     $examResultsForTerm = $pupil->examResults->where('term', $term);

    //     // Calculate position in class for the term
    //     $classId = $pupil->class_id;
    //     $classResults = ExamResult::whereHas('pupil', function ($query) use ($classId) {
    //         $query->where('class_id', $classId);
    //     })->where('term', $term)->get();

    //     $pupilTotals = $classResults->groupBy('pupil_id')->map(function ($pupilResults) {
    //         $total = $pupilResults->sum(function ($result) {
    //             return ($result->mid_term_mark ?? 0) + ($result->end_of_term_mark ?? 0) / 2;
    //         });
    //         return [
    //             'pupil_id' => $pupilResults->first()->pupil_id,
    //             'total' => $total,
    //         ];
    //     })->sortByDesc('total')->values();

    //     $currentPosition = 1;
    //     $previousTotal = null;
    //     $skipPositions = 0;
    //     $position = null;
    //     foreach ($pupilTotals as $index => $pupilData) {
    //         if ($previousTotal !== $pupilData['total']) {
    //             $currentPosition += $skipPositions;
    //             $skipPositions = 1;
    //         } else {
    //             $skipPositions++;
    //         }
    //         if ($pupilData['pupil_id'] == $pupil->id) {
    //             $position = $currentPosition;
    //             break;
    //         }
    //         $previousTotal = $pupilData['total'];
    //     }

    //     // Determine year based on latest result or current year
    //     $year = $examResultsForTerm->isNotEmpty() ? $examResultsForTerm->last()->created_at->format('Y') : now()->format('Y');

    //     // Pass selected term, exam results, school, position, and year to the PDF view
    //     $pdf = PDF::loadView('examResults.pdf', [
    //         'pupil' => $pupil,
    //         'school' => $school,
    //         'examResultsForTerm' => $examResultsForTerm,
    //         'term' => $term,
    //         'position' => $position,
    //         'year' => $year,
    //     ]);

    //     return $pdf->download("exam_results_{$pupil->first_name}_{$term}.pdf");
    // }

    public function exportPdf(Pupil $pupil, $term) {
        $schoolId = $pupil->school_id;
        $school   = School::find($schoolId);

        // Fetch results for the selected term only
        $examResultsForTerm = $pupil->examResults->where('term', $term);

        // Fetch all assessments for this pupil and term
        $assessmentsForTerm = $pupil->assessments()
            ->with('subject')
            ->where('term', $term)
            ->orderBy('assessment_date')
            ->get();

        // Group assessments by subject_id
        $assessmentsBySubject = $assessmentsForTerm->groupBy('subject_id');

        // Collect all unique CA titles for column headers
        $allCaTitles = $assessmentsForTerm->pluck('title')->unique()->values();

        // Calculate position using the new weighted final mark
        $classId      = $pupil->class_id;
        $classResults = ExamResult::whereHas('pupil', function ($query) use ($classId) {
            $query->where('class_id', $classId);
        })->where('term', $term)->get();

        // For each pupil in the class, calculate their weighted final mark total
        $pupilTotals = $classResults->groupBy('pupil_id')->map(function ($pupilResults) use ($assessmentsBySubject) {
            $subjectFinalMarks = collect();

            foreach ($pupilResults as $result) {
                $caPercentages = collect();

                if ($result->mid_term_mark !== null) {
                    $caPercentages->push($result->mid_term_mark);
                }

                $subjectAssessments = \App\Models\Assessment::where('pupil_id', $result->pupil_id)
                    ->where('subject_id', $result->subject_id)
                    ->where('term', $result->term)
                    ->get();

                foreach ($subjectAssessments as $ca) {
                    $caPercentages->push($ca->percentage);
                }

                $caAverage    = $caPercentages->isNotEmpty() ? $caPercentages->avg() : null;
                $caWeighted   = $caAverage !== null ? $caAverage * 0.40 : null;
                $examWeighted = $result->end_of_term_mark !== null ? $result->end_of_term_mark * 0.60 : null;

                if ($caWeighted !== null && $examWeighted !== null) {
                    $subjectFinalMarks->push($caWeighted + $examWeighted);
                }
            }

            return [
                'pupil_id' => $pupilResults->first()->pupil_id,
                'total'    => $subjectFinalMarks->avg() ?? 0,
            ];
        })->sortByDesc('total')->values();

        $currentPosition = 1;
        $previousTotal   = null;
        $skipPositions   = 0;
        $position        = null;
        foreach ($pupilTotals as $pupilData) {
            if ($previousTotal !== $pupilData['total']) {
                $currentPosition += $skipPositions;
                $skipPositions = 1;
            } else {
                $skipPositions++;
            }
            if ($pupilData['pupil_id'] == $pupil->id) {
                $position = $currentPosition;
                break;
            }
            $previousTotal = $pupilData['total'];
        }

        $year = $examResultsForTerm->isNotEmpty()
            ? $examResultsForTerm->last()->created_at->format('Y')
            : now()->format('Y');

        $pdf = PDF::loadView('examResults.pdf', [
            'pupil'               => $pupil,
            'school'              => $school,
            'examResultsForTerm'  => $examResultsForTerm,
            'assessmentsBySubject'=> $assessmentsBySubject,
            'allCaTitles'         => $allCaTitles,
            'term'                => $term,
            'position'            => $position,
            'year'                => $year,
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
        $schoolId = Auth::user()->school_id;
        $isPremium = Auth::user()->isPremium();

        if ($examResult->pupil->school_id !== $schoolId) {
            return redirect()->route('examResults.index')
                ->with('error', 'You are not authorized to update this exam result.');
        }

        $validationRules = [
            'subject_id' => 'required|exists:subjects,id',
            'term' => 'required|string|max:255',
        ];

        if ($isPremium) {
            $validationRules += [
                'mid_term_raw' => 'required|numeric|min:0',
                'mid_term_max' => 'required|numeric|min:1',
                'end_term_raw' => 'required|numeric|min:0',
                'end_term_max' => 'required|numeric|min:1',
            ];
        } else {
            $validationRules += [
                'mid_term_mark' => 'required|numeric|min:0|max:100',
                'end_of_term_mark' => 'required|numeric|min:0|max:100',
            ];
        }

        $this->validate($request, $validationRules);

        $updateData = [
            'subject_id' => $request->subject_id,
            'term' => $request->term,
            'comments' => $request->comments,
        ];

        if ($isPremium) {
            if ($request->mid_term_raw > $request->mid_term_max) {
                return redirect()->back()->withErrors(['mid_term_raw' => 'Mid-term raw mark cannot exceed total mark.']);
            }
            if ($request->end_term_raw > $request->end_term_max) {
                return redirect()->back()->withErrors(['end_term_raw' => 'End-term raw mark cannot exceed total mark.']);
            }

            $midTermPercentage = ($request->mid_term_max > 0) ? min(($request->mid_term_raw / $request->mid_term_max) * 100, 100) : 0;
            $endTermPercentage = ($request->end_term_max > 0) ? min(($request->end_term_raw / $request->end_term_max) * 100, 100) : 0;

            $updateData += [
                'mid_term_raw' => $request->mid_term_raw,
                'mid_term_max' => $request->mid_term_max,
                'mid_term_mark' => $midTermPercentage,
                'end_term_raw' => $request->end_term_raw,
                'end_term_max' => $request->end_term_max,
                'end_of_term_mark' => $endTermPercentage,
            ];
        } else {
            $updateData += [
                'mid_term_mark' => $request->mid_term_mark,
                'end_of_term_mark' => $request->end_of_term_mark,
            ];
        }

        $examResult->update($updateData);

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
