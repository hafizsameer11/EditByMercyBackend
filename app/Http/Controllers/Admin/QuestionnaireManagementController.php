<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\QuestionnaireQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuestionnaireManagementController extends Controller
{
    /**
     * Get all questionnaires with questions for admin
     * GET /api/admin/questionnaires
     */
    public function index(Request $request)
    {
        try {
            $query = Questionnaire::withCount('questions')
                ->orderBy('order');

            // Filter by active status
            if ($request->has('status')) {
                $isActive = $request->status === 'active' ? true : false;
                $query->where('is_active', $isActive);
            }

            $perPage = $request->get('per_page', 20);
            $questionnaires = $query->paginate($perPage);

            $transformedQuestionnaires = $questionnaires->getCollection()->map(function ($questionnaire) {
                return [
                    'id' => $questionnaire->id,
                    'title' => $questionnaire->title,
                    'icon' => $questionnaire->icon,
                    'color' => $questionnaire->color,
                    'description' => $questionnaire->description,
                    'order' => $questionnaire->order,
                    'is_active' => $questionnaire->is_active,
                    'questions_count' => $questionnaire->questions_count,
                    'created_at' => $questionnaire->created_at->format('m/d/y - h:i A'),
                ];
            });

            // Calculate stats
            $totalQuestionnaires = Questionnaire::count();
            $activeQuestionnaires = Questionnaire::where('is_active', true)->count();
            $totalQuestions = QuestionnaireQuestion::count();

            return ResponseHelper::success([
                'stats' => [
                    'total_questionnaires' => $totalQuestionnaires,
                    'active_questionnaires' => $activeQuestionnaires,
                    'total_questions' => $totalQuestions,
                ],
                'questionnaires' => $transformedQuestionnaires,
                'pagination' => [
                    'current_page' => $questionnaires->currentPage(),
                    'last_page' => $questionnaires->lastPage(),
                    'per_page' => $questionnaires->perPage(),
                    'total' => $questionnaires->total(),
                ]
            ], 'Questionnaires fetched successfully', 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch questionnaires: ' . $e->getMessage());
            return ResponseHelper::error('Failed to fetch questionnaires: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single questionnaire with all questions
     * GET /api/admin/questionnaires/{id}
     */
    public function show($id)
    {
        try {
            $questionnaire = Questionnaire::with('questions')->findOrFail($id);

            $data = [
                'id' => $questionnaire->id,
                'title' => $questionnaire->title,
                'icon' => $questionnaire->icon,
                'color' => $questionnaire->color,
                'description' => $questionnaire->description,
                'order' => $questionnaire->order,
                'is_active' => $questionnaire->is_active,
                'questions' => $questionnaire->questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'type' => $question->type,
                        'label' => $question->label,
                        'options' => $question->options,
                        'state_key' => $question->state_key,
                        'order' => $question->order,
                        'is_required' => $question->is_required,
                    ];
                }),
                'created_at' => $questionnaire->created_at->format('m/d/y - h:i A'),
            ];

            return ResponseHelper::success($data, 'Questionnaire details fetched successfully', 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch questionnaire: ' . $e->getMessage());
            return ResponseHelper::error('Questionnaire not found: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Create new questionnaire category
     * POST /api/admin/questionnaires
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'icon' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:7',
                'description' => 'nullable|string',
                'order' => 'nullable|integer',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $questionnaire = Questionnaire::create([
                'title' => $request->title,
                'icon' => $request->icon ?? 'help-circle-outline',
                'color' => $request->color ?? '#992C55',
                'description' => $request->description ?? 'Select one or multiple options',
                'order' => $request->order ?? Questionnaire::max('order') + 1,
                'is_active' => $request->is_active ?? true,
            ]);

            return ResponseHelper::success([
                'id' => $questionnaire->id,
                'title' => $questionnaire->title,
                'order' => $questionnaire->order,
            ], 'Questionnaire created successfully', 201);
        } catch (\Exception $e) {
            Log::error('Failed to create questionnaire: ' . $e->getMessage());
            return ResponseHelper::error('Failed to create questionnaire: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update questionnaire category
     * PUT /api/admin/questionnaires/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $questionnaire = Questionnaire::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'icon' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:7',
                'description' => 'nullable|string',
                'order' => 'nullable|integer',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $questionnaire->update($request->only([
                'title', 'icon', 'color', 'description', 'order', 'is_active'
            ]));

            return ResponseHelper::success([
                'id' => $questionnaire->id,
                'title' => $questionnaire->title,
            ], 'Questionnaire updated successfully', 200);
        } catch (\Exception $e) {
            Log::error('Failed to update questionnaire: ' . $e->getMessage());
            return ResponseHelper::error('Failed to update questionnaire: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete questionnaire category
     * DELETE /api/admin/questionnaires/{id}
     */
    public function destroy($id)
    {
        try {
            $questionnaire = Questionnaire::findOrFail($id);
            $questionnaire->delete();

            return ResponseHelper::success(null, 'Questionnaire deleted successfully', 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete questionnaire: ' . $e->getMessage());
            return ResponseHelper::error('Failed to delete questionnaire: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Toggle questionnaire active status
     * POST /api/admin/questionnaires/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        try {
            $questionnaire = Questionnaire::findOrFail($id);
            $questionnaire->is_active = !$questionnaire->is_active;
            $questionnaire->save();

            return ResponseHelper::success([
                'id' => $questionnaire->id,
                'is_active' => $questionnaire->is_active,
            ], 'Status updated successfully', 200);
        } catch (\Exception $e) {
            Log::error('Failed to toggle status: ' . $e->getMessage());
            return ResponseHelper::error('Failed to toggle status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Add question to questionnaire
     * POST /api/admin/questionnaires/{id}/questions
     */
    public function addQuestion(Request $request, $id)
    {
        try {
            $questionnaire = Questionnaire::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'type' => 'required|in:select,toggle,radioGroup,textarea',
                'label' => 'nullable|string|max:255',
                'options' => 'nullable|array',
                'state_key' => 'required|string|max:255|unique:questionnaire_questions,state_key',
                'order' => 'nullable|integer',
                'is_required' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $question = QuestionnaireQuestion::create([
                'questionnaire_id' => $questionnaire->id,
                'type' => $request->type,
                'label' => $request->label,
                'options' => $request->options,
                'state_key' => $request->state_key,
                'order' => $request->order ?? QuestionnaireQuestion::where('questionnaire_id', $id)->max('order') + 1,
                'is_required' => $request->is_required ?? false,
            ]);

            return ResponseHelper::success([
                'id' => $question->id,
                'type' => $question->type,
                'state_key' => $question->state_key,
            ], 'Question added successfully', 201);
        } catch (\Exception $e) {
            Log::error('Failed to add question: ' . $e->getMessage());
            return ResponseHelper::error('Failed to add question: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update question
     * PUT /api/admin/questionnaires/questions/{questionId}
     */
    public function updateQuestion(Request $request, $questionId)
    {
        try {
            $question = QuestionnaireQuestion::findOrFail($questionId);

            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|in:select,toggle,radioGroup,textarea',
                'label' => 'nullable|string|max:255',
                'options' => 'nullable|array',
                'state_key' => 'sometimes|string|max:255|unique:questionnaire_questions,state_key,' . $questionId,
                'order' => 'nullable|integer',
                'is_required' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $question->update($request->only([
                'type', 'label', 'options', 'state_key', 'order', 'is_required'
            ]));

            return ResponseHelper::success([
                'id' => $question->id,
                'type' => $question->type,
            ], 'Question updated successfully', 200);
        } catch (\Exception $e) {
            Log::error('Failed to update question: ' . $e->getMessage());
            return ResponseHelper::error('Failed to update question: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete question
     * DELETE /api/admin/questionnaires/questions/{questionId}
     */
    public function deleteQuestion($questionId)
    {
        try {
            $question = QuestionnaireQuestion::findOrFail($questionId);
            $question->delete();

            return ResponseHelper::success(null, 'Question deleted successfully', 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete question: ' . $e->getMessage());
            return ResponseHelper::error('Failed to delete question: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reorder questions within a questionnaire
     * POST /api/admin/questionnaires/{id}/reorder-questions
     */
    public function reorderQuestions(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'question_orders' => 'required|array',
                'question_orders.*.question_id' => 'required|exists:questionnaire_questions,id',
                'question_orders.*.order' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            DB::beginTransaction();

            foreach ($request->question_orders as $item) {
                QuestionnaireQuestion::where('id', $item['question_id'])
                    ->update(['order' => $item['order']]);
            }

            DB::commit();

            return ResponseHelper::success(null, 'Questions reordered successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reorder questions: ' . $e->getMessage());
            return ResponseHelper::error('Failed to reorder questions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all question types available
     * GET /api/admin/questionnaires/question-types
     */
    public function getQuestionTypes()
    {
        try {
            $types = [
                [
                    'value' => 'select',
                    'label' => 'Multi-Select',
                    'description' => 'User can select multiple options from a list',
                    'requires_options' => true,
                ],
                [
                    'value' => 'toggle',
                    'label' => 'Toggle (Yes/No)',
                    'description' => 'Simple boolean toggle switch',
                    'requires_options' => false,
                ],
                [
                    'value' => 'radioGroup',
                    'label' => 'Radio Group',
                    'description' => 'User can select only one option from a list',
                    'requires_options' => true,
                ],
                [
                    'value' => 'textarea',
                    'label' => 'Text Area',
                    'description' => 'Free text input field',
                    'requires_options' => false,
                ],
            ];

            return ResponseHelper::success($types, 'Question types fetched successfully', 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch question types: ' . $e->getMessage());
            return ResponseHelper::error('Failed to fetch question types: ' . $e->getMessage(), 500);
        }
    }
}

