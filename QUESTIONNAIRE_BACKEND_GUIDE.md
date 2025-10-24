# Questionnaire Backend Implementation Guide (Laravel)

## Overview
This guide will help you implement a dynamic questionnaire system in Laravel that fetches questions from the database while maintaining the exact same 3-section structure and behavior as the current frontend implementation.

---

## 1. Database Schema

### Migration Files

#### 1.1. Questionnaires Table
```php
<?php
// database/migrations/xxxx_create_questionnaires_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g., "Face", "Skin", "Change in body size"
            $table->string('icon')->nullable(); // Ionicon name, e.g., "happy-outline"
            $table->string('color')->default('#992C55');
            $table->text('description')->nullable(); // e.g., "Select one or multiple options"
            $table->integer('order')->default(0); // 1, 2, 3 for sorting
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('questionnaires');
    }
};
```

#### 1.2. Questionnaire Questions Table
```php
<?php
// database/migrations/xxxx_create_questionnaire_questions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('questionnaire_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'select', 'toggle', 'radioGroup', 'textarea'
            $table->string('label')->nullable(); // Question label
            $table->json('options')->nullable(); // Array of options for select/radio
            $table->string('state_key'); // e.g., 'selectedFace', 'maintainSkinTone'
            $table->integer('order')->default(0); // Order within the category
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('questionnaire_questions');
    }
};
```

#### 1.3. Questionnaire Answers Table (Already exists, but ensure this structure)
```php
<?php
// database/migrations/xxxx_create_questionnaire_answers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('questionnaire_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('answers'); // Store all answers as JSON
            $table->integer('completed_sections')->default(0);
            $table->integer('progress')->default(0); // 0-100
            $table->timestamps();
            
            // Ensure one answer record per chat
            $table->unique('chat_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('questionnaire_answers');
    }
};
```

---

## 2. Models

### 2.1. Questionnaire Model
```php
<?php
// app/Models/Questionnaire.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'icon',
        'color',
        'description',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    public function questions()
    {
        return $this->hasMany(QuestionnaireQuestion::class)->orderBy('order');
    }
}
```

### 2.2. QuestionnaireQuestion Model
```php
<?php
// app/Models/QuestionnaireQuestion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_id',
        'type',
        'label',
        'options',
        'state_key',
        'order',
        'is_required'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'order' => 'integer'
    ];

    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }
}
```

### 2.3. QuestionnaireAnswer Model
```php
<?php
// app/Models/QuestionnaireAnswer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'answers',
        'completed_sections',
        'progress'
    ];

    protected $casts = [
        'answers' => 'array',
        'completed_sections' => 'integer',
        'progress' => 'integer'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## 3. Database Seeder

Create a seeder to populate the questionnaire with the exact same data as frontend:

```php
<?php
// database/seeders/QuestionnaireSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Questionnaire;
use App\Models\QuestionnaireQuestion;

class QuestionnaireSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        QuestionnaireQuestion::truncate();
        Questionnaire::truncate();

        // Category 1: Face
        $face = Questionnaire::create([
            'title' => 'Face',
            'icon' => 'happy-outline',
            'color' => '#992C55',
            'description' => 'Select one or multiple options',
            'order' => 1,
            'is_active' => true
        ]);

        QuestionnaireQuestion::create([
            'questionnaire_id' => $face->id,
            'type' => 'select',
            'label' => null,
            'options' => ['Little/natural Makeup', 'Excess Makeup', 'No Makeup'],
            'state_key' => 'selectedFace',
            'order' => 1,
            'is_required' => false
        ]);

        // Category 2: Skin
        $skin = Questionnaire::create([
            'title' => 'Skin',
            'icon' => 'color-palette-outline',
            'color' => '#992C55',
            'description' => 'Select one or multiple options',
            'order' => 2,
            'is_active' => true
        ]);

        QuestionnaireQuestion::create([
            'questionnaire_id' => $skin->id,
            'type' => 'toggle',
            'label' => 'Maintain skin tone',
            'options' => null,
            'state_key' => 'maintainSkinTone',
            'order' => 1,
            'is_required' => false
        ]);

        QuestionnaireQuestion::create([
            'questionnaire_id' => $skin->id,
            'type' => 'radioGroup',
            'label' => 'Lighter',
            'options' => ['A little', 'Very light', 'Extremely light'],
            'state_key' => 'selectedLighter',
            'order' => 2,
            'is_required' => false
        ]);

        QuestionnaireQuestion::create([
            'questionnaire_id' => $skin->id,
            'type' => 'radioGroup',
            'label' => 'Darker',
            'options' => ['A little', 'Very Dark', 'Extremely Dark'],
            'state_key' => 'selectedDarker',
            'order' => 3,
            'is_required' => false
        ]);

        // Category 3: Change in body size
        $body = Questionnaire::create([
            'title' => 'Change in body size',
            'icon' => 'body-outline',
            'color' => '#992C55',
            'description' => 'Select one or multiple options',
            'order' => 3,
            'is_active' => true
        ]);

        $bodyQuestions = [
            ['type' => 'textarea', 'label' => 'Eyes', 'state_key' => 'eyes'],
            ['type' => 'textarea', 'label' => 'Lips', 'state_key' => 'lips'],
            ['type' => 'radioGroup', 'label' => 'Hips', 'state_key' => 'selectedHips', 'options' => ['Wide', 'Very Wide', 'Extremely Wide']],
            ['type' => 'radioGroup', 'label' => 'Butt', 'state_key' => 'selectedButt', 'options' => ['Big', 'Very Big', 'Extremely Wide']],
            ['type' => 'textarea', 'label' => 'Height', 'state_key' => 'height'],
            ['type' => 'textarea', 'label' => 'Nose', 'state_key' => 'nose'],
            ['type' => 'radioGroup', 'label' => 'Tummy', 'state_key' => 'selectedTummy', 'options' => ['Small', 'Very Small', 'Extremely Small']],
            ['type' => 'textarea', 'label' => 'Chin', 'state_key' => 'chin'],
            ['type' => 'textarea', 'label' => 'Arm', 'state_key' => 'arm'],
            ['type' => 'textarea', 'label' => 'Other Requirements', 'state_key' => 'other'],
        ];

        foreach ($bodyQuestions as $index => $question) {
            QuestionnaireQuestion::create([
                'questionnaire_id' => $body->id,
                'type' => $question['type'],
                'label' => $question['label'],
                'options' => $question['options'] ?? null,
                'state_key' => $question['state_key'],
                'order' => $index + 1,
                'is_required' => false
            ]);
        }
    }
}
```

**Run the seeder:**
```bash
php artisan db:seed --class=QuestionnaireSeeder
```

---

## 4. API Controller

```php
<?php
// app/Http/Controllers/Api/QuestionnaireController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\QuestionnaireAnswer;
use Illuminate\Http\Request;
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

            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch questionnaire',
                'error' => $e->getMessage()
            ], 500);
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
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
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

            return response()->json([
                'status' => 'success',
                'message' => 'Answers saved successfully',
                'completed_sections' => $completedSections,
                'progress' => $progress
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save answers',
                'error' => $e->getMessage()
            ], 500);
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
                return response()->json([
                    'status' => 'success',
                    'progress' => 0,
                    'completed_sections' => 0,
                    'answers' => []
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'progress' => $answerRecord->progress,
                'completed_sections' => $answerRecord->completed_sections,
                'answers' => $answerRecord->answers
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch progress',
                'error' => $e->getMessage()
            ], 500);
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
                return response()->json([
                    'status' => 'error',
                    'message' => 'No answers found for this chat'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'chat_id' => $answerRecord->chat_id,
                    'user_id' => $answerRecord->user_id,
                    'answers' => $answerRecord->answers,
                    'progress' => $answerRecord->progress,
                    'completed_sections' => $answerRecord->completed_sections,
                    'updated_at' => $answerRecord->updated_at
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch answers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 5. API Routes

Add these routes to your `routes/api.php`:

```php
<?php
// routes/api.php

use App\Http\Controllers\Api\QuestionnaireController;

Route::middleware('auth:sanctum')->group(function () {
    // Get all questionnaires (categories + questions)
    Route::get('/questionnaire/all', [QuestionnaireController::class, 'getAll']);
    
    // Save answers
    Route::post('/questionnaire/save-answer', [QuestionnaireController::class, 'saveAnswer']);
    
    // Get progress
    Route::get('/questionnaire/progress/{chat_id}', [QuestionnaireController::class, 'getProgress']);
    
    // Get answers (for agents)
    Route::get('/questionnaire/answers/{chat_id}', [QuestionnaireController::class, 'getAnswers']);
});
```

---

## 6. Expected API Response Format

### 6.1. GET `/api/questionnaire/all`

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "title": "Face",
      "icon": "happy-outline",
      "color": "#992C55",
      "description": "Select one or multiple options",
      "questions": [
        {
          "id": 1,
          "type": "select",
          "stateKey": "selectedFace",
          "options": ["Little/natural Makeup", "Excess Makeup", "No Makeup"],
          "order": 1
        }
      ]
    },
    {
      "id": 2,
      "title": "Skin",
      "icon": "color-palette-outline",
      "color": "#992C55",
      "description": "Select one or multiple options",
      "questions": [
        {
          "id": 2,
          "type": "toggle",
          "label": "Maintain skin tone",
          "stateKey": "maintainSkinTone",
          "order": 1
        },
        {
          "id": 3,
          "type": "radioGroup",
          "label": "Lighter",
          "stateKey": "selectedLighter",
          "options": ["A little", "Very light", "Extremely light"],
          "order": 2
        },
        {
          "id": 4,
          "type": "radioGroup",
          "label": "Darker",
          "stateKey": "selectedDarker",
          "options": ["A little", "Very Dark", "Extremely Dark"],
          "order": 3
        }
      ]
    },
    {
      "id": 3,
      "title": "Change in body size",
      "icon": "body-outline",
      "color": "#992C55",
      "description": "Select one or multiple options",
      "questions": [
        {
          "id": 5,
          "type": "textarea",
          "label": "Eyes",
          "stateKey": "eyes",
          "order": 1
        },
        // ... other body questions
      ]
    }
  ]
}
```

### 6.2. POST `/api/questionnaire/save-answer`

**Request:**
```json
{
  "chat_id": 123,
  "user_id": 456,
  "answers": {
    "selectedFace": "Little/natural Makeup"
  }
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Answers saved successfully",
  "completed_sections": 1,
  "progress": 7
}
```

### 6.3. GET `/api/questionnaire/progress/{chat_id}`

**Response:**
```json
{
  "status": "success",
  "progress": 33,
  "completed_sections": 4,
  "answers": {
    "selectedFace": "Little/natural Makeup",
    "maintainSkinTone": true,
    "selectedLighter": "A little",
    "selectedDarker": null
  }
}
```

---

## 7. Testing Steps

### Step 1: Run Migrations
```bash
php artisan migrate
```

### Step 2: Run Seeder
```bash
php artisan db:seed --class=QuestionnaireSeeder
```

### Step 3: Test Endpoints

**Test getting all questionnaires:**
```bash
curl -X GET http://your-api-url/api/questionnaire/all \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Test saving answers:**
```bash
curl -X POST http://your-api-url/api/questionnaire/save-answer \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "chat_id": 1,
    "user_id": 1,
    "answers": {
      "selectedFace": "Little/natural Makeup"
    }
  }'
```

**Test getting progress:**
```bash
curl -X GET http://your-api-url/api/questionnaire/progress/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 8. Important Notes

1. **Exact Same Behavior**: The frontend will work exactly the same - 3 categories, same questions, same flow
2. **Backward Compatible**: The answer saving endpoint already exists and will continue to work
3. **Progress Calculation**: The backend now calculates progress based on 14 total sections (1+3+10)
4. **Admin Panel**: You can later add admin endpoints to create/update/delete questions without touching the frontend
5. **State Keys Must Match**: The `state_key` in the database must exactly match the frontend's expected keys

---

## 9. Next Steps for Admin Panel (Optional)

If you want to add an admin panel later:

```php
// Admin routes to manage questionnaires
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/questionnaires', [Admin\QuestionnaireController::class, 'index']);
    Route::post('/questionnaires', [Admin\QuestionnaireController::class, 'store']);
    Route::put('/questionnaires/{id}', [Admin\QuestionnaireController::class, 'update']);
    Route::delete('/questionnaires/{id}', [Admin\QuestionnaireController::class, 'destroy']);
});
```

---

## 10. Troubleshooting

### Issue: Migration fails
- Check if tables already exist
- Drop tables manually if needed: `php artisan migrate:fresh`

### Issue: Seeder doesn't populate data
- Check database connection
- Verify models are using correct table names
- Run: `php artisan db:seed --class=QuestionnaireSeeder --force`

### Issue: API returns 500 error
- Check Laravel logs: `storage/logs/laravel.log`
- Verify database tables exist
- Check authentication token is valid

---

## Summary

This implementation will:
✅ Keep the exact same 3-section structure  
✅ Work exactly like the current frontend  
✅ Allow you to update questions from the database  
✅ Maintain all existing functionality  
✅ Add flexibility for future changes  

The frontend will fetch questions dynamically but the user experience remains identical!


