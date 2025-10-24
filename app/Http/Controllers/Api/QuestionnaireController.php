<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\QuestionnaireAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuestionnaireController extends Controller
{
    /**
     * Get all questionnaire categories and questions
     * GET /api/questionnaire/all
     */
    public function getAll()
    {
        try {
            $questionnaires = Questionnaire::with('questions')
                ->where('is_active', true)
                ->orderBy('order')
                ->get();

            $formattedData = $questionnaires->map(function ($questionnaire) {
                return [
                    'id' => $questionnaire->id,
                    'title' => $questionnaire->title,
                    'icon' => $questionnaire->icon,
                    'color' => $questionnaire->color,
                    'description' => $questionnaire->description,
                    'questions' => $questionnaire->questions->map(function ($question) {
                        $formatted = [
                            'id' => $question->id,
                            'type' => $question->type,
                            'stateKey' => $question->state_key,
                            'order' => $question->order,
                        ];

                        if ($question->label) {
                            $formatted['label'] = $question->label;
                        }

                        if ($question->options) {
                            $formatted['options'] = $question->options;
                        }

                        return $formatted;
                    })->values()
                ];
            })->values();

            return ResponseHelper::success($formattedData, 'Questionnaires fetched successfully', 200);

        } catch (\Exception $e) {
            Log::error('Failed to fetch questionnaire: ' . $e->getMessage());
            return ResponseHelper::error('Failed to fetch questionnaire: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Save questionnaire answers
     * POST /api/questionnaire/save-answer
     */
    public function saveAnswer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'chat_id' => 'required|exists:chats,id',
                'user_id' => 'required|exists:users,id',
                'answers' => 'required|array'
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $chatId = $request->chat_id;
            $userId = $request->user_id;
            $newAnswers = $request->answers;

            // Find or create answer record
            $answerRecord = QuestionnaireAnswer::firstOrNew(['chat_id' => $chatId]);

            // Merge new answers with existing ones
            $existingAnswers = $answerRecord->answers ?? [];
            $mergedAnswers = array_merge($existingAnswers, $newAnswers);

            // Calculate completed sections based on answered questions
            $completedSections = $this->calculateCompletedSections($mergedAnswers);
            
            // Calculate progress percentage
            $totalSections = 14; // 1 (Face) + 3 (Skin) + 10 (Body)
            $progress = (int) (($completedSections / $totalSections) * 100);

            // Update or create the record
            $answerRecord->user_id = $userId;
            $answerRecord->answers = $mergedAnswers;
            $answerRecord->completed_sections = $completedSections;
            $answerRecord->progress = $progress;
            $answerRecord->save();

            return ResponseHelper::success([
                'completed_sections' => $completedSections,
                'progress' => $progress
            ], 'Answers saved successfully', 200);

        } catch (\Exception $e) {
            Log::error('Failed to save answers: ' . $e->getMessage());
            return ResponseHelper::error('Failed to save answers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get progress for a specific chat
     * GET /api/questionnaire/progress/{chat_id}
     */
    public function getProgress($chatId)
    {
        try {
            $answerRecord = QuestionnaireAnswer::where('chat_id', $chatId)->first();

            if (!$answerRecord) {
                return ResponseHelper::success([
                    'progress' => 0,
                    'completed_sections' => 0,
                    'answers' => []
                ], 'No progress found', 200);
            }

            return ResponseHelper::success([
                'progress' => $answerRecord->progress,
                'completed_sections' => $answerRecord->completed_sections,
                'answers' => $answerRecord->answers
            ], 'Progress fetched successfully', 200);

        } catch (\Exception $e) {
            Log::error('Failed to fetch progress: ' . $e->getMessage());
            return ResponseHelper::error('Failed to fetch progress: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Calculate completed sections based on answers
     */
    private function calculateCompletedSections($answers)
    {
        $count = 0;

        // Define the state keys for each section
        $sectionKeys = [
            // Face (1)
            'selectedFace',
            
            // Skin (3)
            'maintainSkinTone',
            'selectedLighter',
            'selectedDarker',
            
            // Body (10)
            'eyes',
            'lips',
            'selectedHips',
            'selectedButt',
            'height',
            'nose',
            'selectedTummy',
            'chin',
            'arm',
            'other'
        ];

        foreach ($sectionKeys as $key) {
            if (isset($answers[$key]) && !empty($answers[$key])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get answers for a specific chat (for agents to view)
     * GET /api/questionnaire/answers/{chat_id}
     */
    public function getAnswers($chatId)
    {
        try {
            $answerRecord = QuestionnaireAnswer::where('chat_id', $chatId)
                ->with(['user', 'chat'])
                ->first();

            if (!$answerRecord) {
                return ResponseHelper::error('No answers found for this chat', 404);
            }

            return ResponseHelper::success([
                'chat_id' => $answerRecord->chat_id,
                'user_id' => $answerRecord->user_id,
                'answers' => $answerRecord->answers,
                'progress' => $answerRecord->progress,
                'completed_sections' => $answerRecord->completed_sections,
                'updated_at' => $answerRecord->updated_at
            ], 'Answers fetched successfully', 200);

        } catch (\Exception $e) {
            Log::error('Failed to fetch answers: ' . $e->getMessage());
            return ResponseHelper::error('Failed to fetch answers: ' . $e->getMessage(), 500);
        }
    }
}
