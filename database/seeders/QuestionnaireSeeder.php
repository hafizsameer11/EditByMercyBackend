<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Questionnaire;
use App\Models\QuestionnaireQuestion;
use Illuminate\Support\Facades\DB;

class QuestionnaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data
        QuestionnaireQuestion::truncate();
        Questionnaire::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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
