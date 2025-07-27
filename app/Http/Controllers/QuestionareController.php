<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\StoreOrUpdateQuestionnaireRequest;
use App\Models\Form;
use App\Models\Message;
use App\Models\Order;
use App\Models\QuestionnaireAssignment;
use App\Models\QuestionResponse;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionareController extends Controller
{

    public function storeOrUpdateQuestionnaire(StoreOrUpdateQuestionnaireRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $form = Form::first();
            if (!$form) {
                $form = Form::create(['title' => 'Main Questionnaire']);
            }

            $existingSectionIds = [];
            foreach ($data['sections'] as $sectionData) {
                $section = Section::updateOrCreate(
                    ['id' => $sectionData['id'] ?? null],
                    [
                        'title' => $sectionData['title'],
                        'description' => $sectionData['description'] ?? null,
                        'form_id' => $form->id
                    ] // Assuming form_id is set to the first form's ID
                );
                $existingSectionIds[] = $section->id;
                $existingQuestionIds = [];
                foreach ($sectionData['questions'] as $qData) {
                    $question = $section->questions()->updateOrCreate(
                        ['id' => $qData['id'] ?? null],
                        ['text' => $qData['text'], 'type' => $qData['type']]
                    );
                    $existingQuestionIds[] = $question->id;

                    $existingOptionIds = [];

                    if (!empty($qData['options'])) {
                        foreach ($qData['options'] as $optData) {
                            $option = $question->options()->updateOrCreate(
                                ['id' => $optData['id'] ?? null],
                                ['option_text' => $optData['option_text']]
                            );
                            $existingOptionIds[] = $option->id;
                        }
                        $question->options()->whereNotIn('id', $existingOptionIds)->delete();
                    } else {
                        $question->options()->delete();
                    }
                }
                $section->questions()->whereNotIn('id', $existingQuestionIds)->delete();
            }
            // Section::whereNotIn('id', $existingSectionIds)->delete();
            DB::commit();
            return response()->json(['message' => 'Questionnaire saved successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getQuestionnaire()
    {
        $sections = Section::with([
            'questions' => function ($q) {
                $q->with('options');
            }
        ])
            ->orderBy('order') // if you have order column
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sections
        ]);
    }

    //for agent and user
    public function getForm()
    {
        try {
            $sections = Section::with(['questions.options'])->get();
            return ResponseHelper::success($sections, "Form fetched successfully.", 200);
        } catch (\Exception $e) {
            return ResponseHelper::success($e->getMessage(), 500);
        }
    }

    //route for agent to assign questionnaire to user
    public function assignToUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'chat_id' => 'required|exists:chats,id',
                'user_id' => 'required|exists:users,id',
            ]);

            $existing = QuestionnaireAssignment::where('chat_id', $validated['chat_id'])
                ->where('user_id', $validated['user_id'])
                ->where('status', '!=', 'closed')
                ->first();

            if ($existing) {
                return response()->json(['message' => 'Already assigned'], 409);
            }

            $totalSections = Section::count();

            $assignment = QuestionnaireAssignment::create([
                'chat_id' => $validated['chat_id'],
                'user_id' => $validated['user_id'],
                'status' => 'assigned',
                'total_sections' => $totalSections, 
            ]);
            //create message and add form_id in message
            $message = Message::create([
                'chat_id' => $validated['chat_id'],
                'form_id' => $assignment->id,
                'sender_id' => Auth::id(),
                'type' => 'form',
                'message' => 'Please fill out the following form to submit your query.',
            ]);
            $order=Order::where('chat_id', $validated['chat_id'])->first();
            $order->is_form_assigned=true;
            $order->save();
            return ResponseHelper::success($assignment, "Questionnaire assigned successfully.", 200);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    //route for user to get assigned form
    public function getAssignedForm(Request $request)
    {
        $userId = Auth::id(); // Or from $request if user is passed
        $assignment = QuestionnaireAssignment::where('user_id', $userId)
            ->where('status', '!=', 'closed')
            ->latest()
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'No active form found'], 404);
        }

        $sections = Section::with(['questions.options'])->get();

        return response()->json([
            'assignment' => $assignment,
            'sections' => $sections,
        ]);
    }

    //route for agent to close the form
    public function closeAssignment(Request $request)
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:questionnaire_assignments,id'
        ]);

        $assignment = QuestionnaireAssignment::findOrFail($validated['assignment_id']);
        $assignment->status = 'closed';
        $assignment->save();

        return response()->json(['message' => 'Form closed']);
    }
    public function reopenAssignment(Request $request)
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:questionnaire_assignments,id'
        ]);

        $assignment = QuestionnaireAssignment::findOrFail($validated['assignment_id']);
        $assignment->status = 'assigned';
        $assignment->completed_sections = 0; // Optionally reset progress
        $assignment->save();

        return response()->json(['message' => 'Form reopened']);
    }

    //function for getting user submitted answers

    public function getUserAnswers(Request $request)
    {
        $assignmentId = $request->query('assignment_id');
        $userId = Auth::id(); // Or from $request if user is passed

        $responses = QuestionResponse::with('question')
            ->where('user_id', $userId)
            ->get()
            ->groupBy(fn($r) => $r->question->section_id);

        return response()->json(['data' => $responses]);
    }
    public function getAssignmentProgress($assignment_id)
    {
        $assignment = QuestionnaireAssignment::with('user')->findOrFail($assignment_id);

        return response()->json([
            'assignment_id' => $assignment->id,
            'user' => $assignment->user->name ?? '',
            'completed_sections' => $assignment->completed_sections,
            'total_sections' => $assignment->total_sections,
            'status' => $assignment->status
        ]);
    }

    public function getAnswersByUser($user_id)
    {
        $responses = QuestionResponse::with(['question.options', 'question.section'])
            ->where('user_id', $user_id)
            ->get()
            ->groupBy(fn($r) => $r->question->section_id);

        return response()->json(['data' => $responses]);
    }

    //route for submitting section for user

    public function submitSection(Request $request)
    {
        $data = $request->validate([
            'assignment_id' => 'required|exists:questionnaire_assignments,id',
            'section_id' => 'required|exists:sections,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.response' => 'required|string',
        ]);

        $userId = Auth::id(); // Or pass in the request if admin submits on behalf of user

        $assignment = QuestionnaireAssignment::findOrFail($data['assignment_id']);

        foreach ($data['answers'] as $answer) {
            QuestionResponse::updateOrCreate(
                [
                    'user_id' => $userId,
                    'question_id' => $answer['question_id'],
                ],
                [
                    'response' => $answer['response'],
                ]
            );
        }

        // Mark progress (optional)
        $assignment->increment('completed_sections');
        $assignment->status = 'in_progress';
        $assignment->save();

        return response()->json(['message' => 'Section submitted successfully.']);
    }
}
