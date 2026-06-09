<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Subject;
use App\Models\ClassModel;
use App\Models\Pupil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class AssessmentController extends Controller {

    public function index(Request $request) {
        try {
            $schoolId = Auth::user()->school_id;

            $classes   = ClassModel::where('school_id', $schoolId)->get();
            $subjects  = Subject::where('school_id', $schoolId)->get();

            $query = Assessment::with(['pupil.class', 'subject'])
                ->whereHas('pupil', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
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

            $assessments = $query->orderBy('assessment_date', 'desc')->get();

            return view('assessments.index', compact('assessments', 'classes', 'subjects'));
        } catch (Exception $e) {
            \Log::error('Error fetching assessments: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch assessments.');
        }
    }

    public function create(Request $request) {
        try {
            $schoolId   = Auth::user()->school_id;
            $subjects   = Subject::where('school_id', $schoolId)->get();
            $classes    = ClassModel::where('school_id', $schoolId)->get();
            $pupils     = Pupil::where('school_id', $schoolId)->orderBy('first_name')->get();
            $classId    = $request->input('class_id');
            $pupilId    = $request->input('pupil_id');

            $classPupils = $classId
                ? Pupil::where('school_id', $schoolId)
                        ->where('class_id', $classId)
                        ->orderBy('first_name')
                        ->get()
                : collect();

            return view('assessments.create', compact(
                'subjects', 'classes', 'pupils', 'classId', 'pupilId', 'classPupils'
            ));
        } catch (Exception $e) {
            \Log::error('Error loading assessment create form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load assessment form.');
        }
    }

    public function store(Request $request) {
        try {
            $schoolId  = Auth::user()->school_id;
            $isPremium = Auth::user()->isPremium();

            $validationRules = [
                'subject_id'      => 'required|exists:subjects,id',
                'term'            => 'required|string|max:255',
                'title'           => 'required|string|max:255',
                'assessment_date' => 'required|date',
                'entry_type'      => 'required|in:single,bulk',
            ];

            if ($request->entry_type == 'single' && !empty($request->single_pupil['pupil_id'])) {
                if ($isPremium) {
                    $validationRules += [
                        'single_pupil.raw_mark' => 'required|numeric|min:0',
                        'single_pupil.max_mark' => 'required|numeric|min:1',
                    ];
                } else {
                    $validationRules += [
                        'single_pupil.percentage' => 'required|numeric|min:0|max:100',
                    ];
                }
            } else {
                if ($isPremium) {
                    $validationRules += [
                        'pupil_results.*.raw_mark' => 'required|numeric|min:0',
                        'pupil_results.*.max_mark' => 'required|numeric|min:1',
                    ];
                } else {
                    $validationRules += [
                        'pupil_results.*.percentage' => 'required|numeric|min:0|max:100',
                    ];
                }
            }

            $this->validate($request, $validationRules);

            if ($request->entry_type == 'single' && !empty($request->single_pupil['pupil_id'])) {
                $data      = $request->single_pupil;
                $pupilId   = $data['pupil_id'];

                $percentage = $isPremium
                    ? (($data['max_mark'] > 0) ? min(($data['raw_mark'] / $data['max_mark']) * 100, 100) : 0)
                    : $data['percentage'];

                Assessment::create([
                    'pupil_id'        => $pupilId,
                    'subject_id'      => $request->subject_id,
                    'title'           => $request->title,
                    'term'            => $request->term,
                    'assessment_date' => $request->assessment_date,
                    'raw_mark'        => $isPremium ? $data['raw_mark'] : null,
                    'max_mark'        => $isPremium ? $data['max_mark'] : null,
                    'percentage'      => $percentage,
                    'comments'        => $data['comments'] ?? null,
                ]);
            } else {
                foreach ($request->pupil_results as $pupilId => $result) {
                    $percentage = $isPremium
                        ? (($result['max_mark'] > 0) ? min(($result['raw_mark'] / $result['max_mark']) * 100, 100) : 0)
                        : $result['percentage'];

                    Assessment::create([
                        'pupil_id'        => $pupilId,
                        'subject_id'      => $request->subject_id,
                        'title'           => $request->title,
                        'term'            => $request->term,
                        'assessment_date' => $request->assessment_date,
                        'raw_mark'        => $isPremium ? $result['raw_mark'] : null,
                        'max_mark'        => $isPremium ? $result['max_mark'] : null,
                        'percentage'      => $percentage,
                        'comments'        => $result['comments'] ?? null,
                    ]);
                }
            }

            return redirect()->route('assessments.index')
                ->with('success', 'Assessment saved successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error storing assessment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to save assessment.')->withInput();
        }
    }

    public function show(Assessment $assessment) {
        try {
            $schoolId = Auth::user()->school_id;

            if ($assessment->pupil->school_id !== $schoolId) {
                return redirect()->route('assessments.index')
                    ->with('error', 'You are not authorized to view this assessment.');
            }

            return view('assessments.show', compact('assessment'));
        } catch (Exception $e) {
            \Log::error('Error showing assessment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load assessment.');
        }
    }

    public function edit(Assessment $assessment) {
        try {
            $schoolId = Auth::user()->school_id;

            if ($assessment->pupil->school_id !== $schoolId) {
                return redirect()->route('assessments.index')
                    ->with('error', 'You are not authorized to edit this assessment.');
            }

            $subjects = Subject::where('school_id', $schoolId)->get();

            return view('assessments.edit', compact('assessment', 'subjects'));
        } catch (Exception $e) {
            \Log::error('Error loading assessment edit form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load assessment edit form.');
        }
    }

    public function update(Request $request, Assessment $assessment) {
        try {
            $schoolId  = Auth::user()->school_id;
            $isPremium = Auth::user()->isPremium();

            if ($assessment->pupil->school_id !== $schoolId) {
                return redirect()->route('assessments.index')
                    ->with('error', 'You are not authorized to update this assessment.');
            }

            $validationRules = [
                'subject_id'      => 'required|exists:subjects,id',
                'term'            => 'required|string|max:255',
                'title'           => 'required|string|max:255',
                'assessment_date' => 'required|date',
            ];

            if ($isPremium) {
                $validationRules += [
                    'raw_mark' => 'required|numeric|min:0',
                    'max_mark' => 'required|numeric|min:1',
                ];
            } else {
                $validationRules += [
                    'percentage' => 'required|numeric|min:0|max:100',
                ];
            }

            $this->validate($request, $validationRules);

            $percentage = $isPremium
                ? (($request->max_mark > 0) ? min(($request->raw_mark / $request->max_mark) * 100, 100) : 0)
                : $request->percentage;

            $assessment->update([
                'subject_id'      => $request->subject_id,
                'title'           => $request->title,
                'term'            => $request->term,
                'assessment_date' => $request->assessment_date,
                'raw_mark'        => $isPremium ? $request->raw_mark : null,
                'max_mark'        => $isPremium ? $request->max_mark : null,
                'percentage'      => $percentage,
                'comments'        => $request->comments,
            ]);

            return redirect()->route('assessments.index')
                ->with('success', 'Assessment updated successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error updating assessment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update assessment.')->withInput();
        }
    }

    public function destroy(Assessment $assessment) {
        try {
            $schoolId = Auth::user()->school_id;

            if ($assessment->pupil->school_id !== $schoolId) {
                return redirect()->route('assessments.index')
                    ->with('error', 'You are not authorized to delete this assessment.');
            }

            $assessment->delete();

            return redirect()->route('assessments.index')
                ->with('success', 'Assessment deleted successfully!');
        } catch (Exception $e) {
            \Log::error('Error deleting assessment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete assessment.');
        }
    }
}
