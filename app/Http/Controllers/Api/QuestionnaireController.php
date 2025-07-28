<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatQuestionnaireAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionnaireController extends Controller
{
     public function saveAnswer(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|integer',
            'user_id' => 'required|integer',
            'answers' => 'required|array',
        ]);

        $record = ChatQuestionnaireAnswer::updateOrCreate(
            ['chat_id' => $request->chat_id, 'user_id' => $request->user_id],
            ['answers' => $request->answers]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Answers saved successfully.',
            'data' => $record
        ]);
    }

public function getProgress($chat_id)
{
    $record = ChatQuestionnaireAnswer::where('chat_id', $chat_id)->first();
    Log::info('Chat ID: ' . $chat_id);
    if (!$record) {
        return response()->json(['status' => 'success', 'progress' => 0, 'completed_sections' => 0]);
    }

    Log::info('Raw answers value: ' , [$record->answers]);

    $answers = is_array($record->answers) ? $record->answers : json_decode($record->answers, true);

    Log::info('Decoded answers array: ', $answers);

    $sectionKeys = [
        'selectedFace', // Category 1
        'maintainSkinTone', 'selectedLighter', 'selectedDarker', // Category 2
        'eyes', 'lips', 'selectedHips', 'selectedButt', 'height', 'nose', 'selectedTummy', 'chin', 'arm', 'other' // Category 3
    ];

    $filled = array_filter($sectionKeys, fn($key) => !empty($answers[$key] ?? null));

    return response()->json([
        'status' => 'success',
        'progress' => round(count($filled) / count($sectionKeys) * 100),
        'completed_sections' => count($filled),
        'debug_answers' => $answers, // <-- Optional debug help
        'raw' => $record->answers     // <-- Optional raw value
    ]);
}



    public function getAnswers($chat_id)
    {
        $record = ChatQuestionnaireAnswer::where('chat_id', $chat_id)->first();

        if (!$record) {
            return response()->json(['status' => 'error', 'message' => 'No answers found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $record->answers,
        ]);
    }
}
