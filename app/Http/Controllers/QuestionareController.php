<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrUpdateQuestionnaireRequest;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionareController extends Controller
{
   
public function storeOrUpdateQuestionnaire(StoreOrUpdateQuestionnaireRequest $request)
{
    $data = $request->validated();
    DB::beginTransaction();
    try {
        $existingSectionIds = [];
        foreach ($data['sections'] as $sectionData) {
            $section = Section::updateOrCreate(
                ['id' => $sectionData['id'] ?? null],
                ['title' => $sectionData['title'], 'description' => $sectionData['description'] ?? null]
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
        Section::whereNotIn('id', $existingSectionIds)->delete();
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
    ->whereNull('deleted_at') // Optional: only get non-deleted sections
    ->orderBy('order') // if you have order column
    ->get();

    return response()->json([
        'status' => 'success',
        'data' => $sections
    ]);
}

}
