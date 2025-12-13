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

    // public function create(Request $request) {
    //     $schoolId = Auth::user()->school_id;

    //     $subjects = Subject::where('school_id', $schoolId)->get();
    //     $classes = ClassModel::where('school_id', $schoolId)->get();
    //     $classId = $request->input('class_id');

    //     $pupils = Pupil::where('school_id', $schoolId)
    //                 ->when($classId, function ($query) use ($classId) {
    //                     return $query->where('class_id', $classId);
    //                 })
    //                 ->get();

    //     return view('examResults.create', compact('subjects', 'pupils', 'classes', 'classId'));
    // }

    // public function store(Request $request) {
    //     $this->validate($request, [
    //         'term' => 'required|string|max:255',
    //         'subject_id' => 'required|exists:subjects,id',
    //         'pupil_results.*.mid_term_raw' => 'required|numeric|min:0',
    //         'pupil_results.*.mid_term_max' => 'required|numeric|min:1',
    //         'pupil_results.*.mid_term_mark' => 'required|numeric|min:0|max:100',
    //         'pupil_results.*.end_term_raw' => 'required|numeric|min:0',
    //         'pupil_results.*.end_term_max' => 'required|numeric|min:1',
    //         'pupil_results.*.end_of_term_mark' => 'required|numeric|min:0|max:100',
    //     ]);

    //     $schoolId = Auth::user()->school_id;

    //     foreach ($request->input('pupil_results') as $pupilId => $result) {
    //         $pupil = Pupil::findOrFail($pupilId);
    //         if ($pupil->school_id !== $schoolId) {
    //             return redirect()->route('examResults.index')
    //                 ->with('error', 'You are not authorized to add an exam result for this pupil.');
    //         }

    //         // Validate raw marks against max marks
    //         if ($result['mid_term_raw'] > $result['mid_term_max']) {
    //             return redirect()->back()->withErrors(['pupil_results.' . $pupilId . '.mid_term_raw' => 'Mid-term raw mark cannot exceed total mark.']);
    //         }
    //         if ($result['end_term_raw'] > $result['end_term_max']) {
    //             return redirect()->back()->withErrors(['pupil_results.' . $pupilId . '.end_term_raw' => 'End-term raw mark cannot exceed total mark.']);
    //         }

    //         // Verify percentage consistency
    //         $midTermPercentage = ($result['mid_term_max'] > 0) ? ($result['mid_term_raw'] / $result['mid_term_max']) * 100 : 0;
    //         $endTermPercentage = ($result['end_term_max'] > 0) ? ($result['end_term_raw'] / $result['end_term_max']) * 100 : 0;

    //         if (abs($midTermPercentage - $result['mid_term_mark']) > 0.01) {
    //             return redirect()->back()->withErrors(['pupil_results.' . $pupilId . '.mid_term_mark' => 'Mid-term percentage does not match raw mark calculation.']);
    //         }
    //         if (abs($endTermPercentage - $result['end_of_term_mark']) > 0.01) {
    //             return redirect()->back()->withErrors(['pupil_results.' . $pupilId . '.end_of_term_mark' => 'End-term percentage does not match raw mark calculation.']);
    //         }

    //         ExamResult::create([
    //             'pupil_id' => $pupilId,
    //             'subject_id' => $request->input('subject_id'),
    //             'term' => $request->input('term'),
    //             'mid_term_raw' => $result['mid_term_raw'],
    //             'mid_term_max' => $result['mid_term_max'],
    //             'mid_term_mark' => $result['mid_term_mark'],
    //             'end_term_raw' => $result['end_term_raw'],
    //             'end_term_max' => $result['end_term_max'],
    //             'end_of_term_mark' => $result['end_of_term_mark'],
    //         ]);
    //     }

    //     return redirect()->route('examResults.index')
    //         ->with('success', 'Exam results recorded successfully!');
    // }

    // public function create(Request $request) {
    //     $schoolId = Auth::user()->school_id;

    //     $subjects = Subject::where('school_id', $schoolId)->get();
    //     $classes = ClassModel::where('school_id', $schoolId)->get();
    //     $classId = $request->input('class_id');

    //     $pupils = Pupil::where('school_id', $schoolId)
    //                 ->when($classId, function ($query) use ($classId) {
    //                     return $query->where('class_id', $classId);
    //                 })
    //                 ->get();

    //     return view('examResults.create', compact('subjects', 'pupils', 'classes', 'classId'));
    // }

    // public function store(Request $request) {
    //     $this->validate($request, [
    //         'term' => 'required|string|max:255',
    //         'subject_id' => 'required|exists:subjects,id',
    //         'pupil_results.*.mid_term_mark' => 'required|numeric|min:0|max:100',
    //         'pupil_results.*.end_of_term_mark' => 'required|numeric|min:0|max:100',
    //     ]);

    //     $schoolId = Auth::user()->school_id;

    //     foreach ($request->input('pupil_results') as $pupilId => $result) {
    //         $pupil = Pupil::findOrFail($pupilId);
    //         if ($pupil->school_id !== $schoolId) {
    //             return redirect()->route('examResults.index')
    //                 ->with('error', 'You are not authorized to add an exam result for this pupil.');
    //         }

    //         ExamResult::create([
    //             'pupil_id' => $pupilId,
    //             'subject_id' => $request->input('subject_id'),
    //             'term' => $request->input('term'),
    //             'mid_term_mark' => $result['mid_term_mark'],
    //             'end_of_term_mark' => $result['end_of_term_mark'],
    //         ]);
    //     }

    //     return redirect()->route('examResults.index')
    //         ->with('success', 'Exam results recorded successfully!');
    // }

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
                    'mid_term_mark' => 'required|numeric|min:0|max:100',
                    'end_of_term_mark' => 'required|numeric|min:0|max:100',
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

        // Calculate position in class for each term
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
                    'total' => $total,
                ];
            })->sortByDesc('total')->values();

            $currentPosition = 1;
            $previousTotal = null;
            $skipPositions = 0;
            foreach ($pupilTotals as $index => $pupilData) {
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

        return view('examResults.show', compact('examResult', 'terms', 'positions'));
    }

    // public function exportPdf(Pupil $pupil, $term) {
    //     $schoolId = Auth::user()->school_id;

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
    //             return ($result->mid_term_mark + $result->end_of_term_mark) / 2;
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

    //     // Pass selected term, exam results, school, and position to the PDF view
    //     $pdf = PDF::loadView('examResults.pdf', [
    //         'pupil' => $pupil,
    //         'school' => $school,
    //         'examResultsForTerm' => $examResultsForTerm,
    //         'term' => $term,
    //         'position' => $position,
    //     ]);

    //     return $pdf->download("exam_results_{$pupil->first_name}_{$term}.pdf");
    // }

    public function exportPdf(Pupil $pupil, $term) {
        $schoolId = Auth::user()->school_id;
        $school = School::find($schoolId);

        if ($pupil->school_id !== $schoolId) {
            return redirect()->route('examResults.index')
                ->with('error', 'You are not authorized to export this exam result.');
        }

        // Fetch results for the selected term
        $examResultsForTerm = $pupil->examResults->where('term', $term);

        // Calculate position in class for the term
        $classId = $pupil->class_id;
        $classResults = ExamResult::whereHas('pupil', function ($query) use ($classId) {
            $query->where('class_id', $classId);
        })->where('term', $term)->get();

        $pupilTotals = $classResults->groupBy('pupil_id')->map(function ($pupilResults) {
            $total = $pupilResults->sum(function ($result) {
                return ($result->mid_term_mark ?? 0) + ($result->end_of_term_mark ?? 0) / 2;
            });
            return [
                'pupil_id' => $pupilResults->first()->pupil_id,
                'total' => $total,
            ];
        })->sortByDesc('total')->values();

        $currentPosition = 1;
        $previousTotal = null;
        $skipPositions = 0;
        $position = null;
        foreach ($pupilTotals as $index => $pupilData) {
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

        // Determine year based on latest result or current year
        $year = $examResultsForTerm->isNotEmpty() ? $examResultsForTerm->last()->created_at->format('Y') : now()->format('Y');

        // Pass selected term, exam results, school, position, and year to the PDF view
        $pdf = PDF::loadView('examResults.pdf', [
            'pupil' => $pupil,
            'school' => $school,
            'examResultsForTerm' => $examResultsForTerm,
            'term' => $term,
            'position' => $position,
            'year' => $year,
        ]);

        return $pdf->download("exam_results_{$pupil->first_name}_{$term}.pdf");
    }

    // public function edit(ExamResult $examResult) {
    //     $schoolId = Auth::user()->school_id;

    //     if ($examResult->pupil->school_id !== $schoolId) {
    //         return redirect()->route('examResults.index')
    //             ->with('error', 'You are not authorized to edit this exam result.');
    //     }

    //     $subjects = Subject::where('school_id', $schoolId)->get();

    //     return view('examResults.edit', compact('examResult', 'subjects'));
    // }

    // public function update(Request $request, ExamResult $examResult) {
    //     $this->validate($request, [
    //         'term' => 'required|string|max:255',
    //         'subject_id' => 'required|exists:subjects,id',
    //         'mid_term_mark' => 'required|numeric|min:0|max:100',
    //         'end_of_term_mark' => 'required|numeric|min:0|max:100',
    //     ]);

    //     $schoolId = Auth::user()->school_id;

    //     if ($examResult->pupil->school_id !== $schoolId) {
    //         return redirect()->route('examResults.index')
    //             ->with('error', 'You are not authorized to update this exam result.');
    //     }

    //     $examResult->update($request->all());

    //     return redirect()->route('examResults.index')
    //         ->with('success', 'Exam result updated successfully!');
    // }

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
