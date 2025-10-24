# 📋 Admin Questionnaire Management API Documentation

## Overview

This API allows administrators to create, update, delete, and manage questionnaires and questions dynamically. The questionnaire system supports 3 categories and 14 questions that users see on the frontend.

**Base URL:** `https://editbymercy.hmstech.xyz/api/admin/questionnaire-management`

**Authentication:** All routes require `auth:sanctum` middleware

---

## 📊 API Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Get all questionnaires with stats |
| GET | `/{id}` | Get single questionnaire details |
| POST | `/` | Create new questionnaire category |
| PUT | `/{id}` | Update questionnaire category |
| DELETE | `/{id}` | Delete questionnaire category |
| POST | `/{id}/toggle-status` | Toggle active/inactive status |
| POST | `/{id}/questions` | Add question to questionnaire |
| PUT | `/questions/{questionId}` | Update specific question |
| DELETE | `/questions/{questionId}` | Delete specific question |
| POST | `/{id}/reorder-questions` | Reorder questions in category |
| GET | `/meta/question-types` | Get available question types |

---

## 📋 Questionnaire Category Management

### 1. Get All Questionnaires
**GET** `/api/admin/questionnaire-management`

Get all questionnaires with pagination and stats.

**Query Parameters:**
- `status` - Filter by status (`active`, `inactive`)
- `per_page` - Items per page (default: 20)

**Response:**
```json
{
  "status": "success",
  "message": "Questionnaires fetched successfully",
  "data": {
    "stats": {
      "total_questionnaires": 3,
      "active_questionnaires": 3,
      "total_questions": 14
    },
    "questionnaires": [
      {
        "id": 1,
        "title": "Face",
        "icon": "happy-outline",
        "color": "#992C55",
        "description": "Select one or multiple options",
        "order": 1,
        "is_active": true,
        "questions_count": 1,
        "created_at": "10/24/25 - 04:37 AM"
      },
      {
        "id": 2,
        "title": "Skin",
        "icon": "color-palette-outline",
        "color": "#992C55",
        "description": "Select one or multiple options",
        "order": 2,
        "is_active": true,
        "questions_count": 3,
        "created_at": "10/24/25 - 04:37 AM"
      },
      {
        "id": 3,
        "title": "Change in body size",
        "icon": "body-outline",
        "color": "#992C55",
        "description": "Select one or multiple options",
        "order": 3,
        "is_active": true,
        "questions_count": 10,
        "created_at": "10/24/25 - 04:37 AM"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 3
    }
  }
}
```

---

### 2. Get Single Questionnaire
**GET** `/api/admin/questionnaire-management/{id}`

Get detailed information about a specific questionnaire including all its questions.

**Response:**
```json
{
  "status": "success",
  "message": "Questionnaire details fetched successfully",
  "data": {
    "id": 2,
    "title": "Skin",
    "icon": "color-palette-outline",
    "color": "#992C55",
    "description": "Select one or multiple options",
    "order": 2,
    "is_active": true,
    "questions": [
      {
        "id": 2,
        "type": "toggle",
        "label": "Maintain skin tone",
        "options": null,
        "state_key": "maintainSkinTone",
        "order": 1,
        "is_required": false
      },
      {
        "id": 3,
        "type": "radioGroup",
        "label": "Lighter",
        "options": ["A little", "Very light", "Extremely light"],
        "state_key": "selectedLighter",
        "order": 2,
        "is_required": false
      },
      {
        "id": 4,
        "type": "radioGroup",
        "label": "Darker",
        "options": ["A little", "Very Dark", "Extremely Dark"],
        "state_key": "selectedDarker",
        "order": 3,
        "is_required": false
      }
    ],
    "created_at": "10/24/25 - 04:37 AM"
  }
}
```

---

### 3. Create New Questionnaire Category
**POST** `/api/admin/questionnaire-management`

Create a new questionnaire category.

**Request Body:**
```json
{
  "title": "Hair",
  "icon": "cut-outline",
  "color": "#992C55",
  "description": "Select hair styling options",
  "order": 4,
  "is_active": true
}
```

**Validation Rules:**
- `title` - Required, max 255 characters
- `icon` - Optional, Ionicon name (default: "help-circle-outline")
- `color` - Optional, hex color (default: "#992C55")
- `description` - Optional text
- `order` - Optional integer (auto-increments if not provided)
- `is_active` - Optional boolean (default: true)

**Response:**
```json
{
  "status": "success",
  "message": "Questionnaire created successfully",
  "data": {
    "id": 4,
    "title": "Hair",
    "order": 4
  }
}
```

---

### 4. Update Questionnaire Category
**PUT** `/api/admin/questionnaire-management/{id}`

Update an existing questionnaire category.

**Request Body:**
```json
{
  "title": "Hair & Makeup",
  "icon": "cut-outline",
  "color": "#FF5733",
  "description": "Select hair and makeup options",
  "order": 4,
  "is_active": true
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Questionnaire updated successfully",
  "data": {
    "id": 4,
    "title": "Hair & Makeup"
  }
}
```

---

### 5. Delete Questionnaire Category
**DELETE** `/api/admin/questionnaire-management/{id}`

Delete a questionnaire category. All associated questions will be deleted due to cascade.

**Response:**
```json
{
  "status": "success",
  "message": "Questionnaire deleted successfully",
  "data": null
}
```

---

### 6. Toggle Questionnaire Status
**POST** `/api/admin/questionnaire-management/{id}/toggle-status`

Toggle the active/inactive status of a questionnaire.

**Response:**
```json
{
  "status": "success",
  "message": "Status updated successfully",
  "data": {
    "id": 1,
    "is_active": false
  }
}
```

---

## ❓ Question Management

### 7. Add Question to Questionnaire
**POST** `/api/admin/questionnaire-management/{id}/questions`

Add a new question to a specific questionnaire category.

**Request Body:**
```json
{
  "type": "radioGroup",
  "label": "Hair Style",
  "options": ["Straight", "Curly", "Wavy"],
  "state_key": "selectedHairStyle",
  "order": 1,
  "is_required": false
}
```

**Validation Rules:**
- `type` - Required, must be one of: `select`, `toggle`, `radioGroup`, `textarea`
- `label` - Optional, max 255 characters (not needed for `select` type)
- `options` - Optional array (required for `select` and `radioGroup` types)
- `state_key` - Required, unique, max 255 characters (must be camelCase)
- `order` - Optional integer (auto-increments if not provided)
- `is_required` - Optional boolean (default: false)

**Response:**
```json
{
  "status": "success",
  "message": "Question added successfully",
  "data": {
    "id": 15,
    "type": "radioGroup",
    "state_key": "selectedHairStyle"
  }
}
```

---

### 8. Update Question
**PUT** `/api/admin/questionnaire-management/questions/{questionId}`

Update an existing question.

**Request Body:**
```json
{
  "type": "radioGroup",
  "label": "Hair Style Preference",
  "options": ["Straight", "Curly", "Wavy", "Natural"],
  "state_key": "selectedHairStyle",
  "order": 1,
  "is_required": true
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Question updated successfully",
  "data": {
    "id": 15,
    "type": "radioGroup"
  }
}
```

---

### 9. Delete Question
**DELETE** `/api/admin/questionnaire-management/questions/{questionId}`

Delete a specific question.

**Response:**
```json
{
  "status": "success",
  "message": "Question deleted successfully",
  "data": null
}
```

---

### 10. Reorder Questions
**POST** `/api/admin/questionnaire-management/{id}/reorder-questions`

Reorder questions within a questionnaire category.

**Request Body:**
```json
{
  "question_orders": [
    {
      "question_id": 2,
      "order": 1
    },
    {
      "question_id": 3,
      "order": 3
    },
    {
      "question_id": 4,
      "order": 2
    }
  ]
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Questions reordered successfully",
  "data": null
}
```

---

## 🛠️ Helper Endpoints

### 11. Get Question Types
**GET** `/api/admin/questionnaire-management/meta/question-types`

Get all available question types with descriptions.

**Response:**
```json
{
  "status": "success",
  "message": "Question types fetched successfully",
  "data": [
    {
      "value": "select",
      "label": "Multi-Select",
      "description": "User can select multiple options from a list",
      "requires_options": true
    },
    {
      "value": "toggle",
      "label": "Toggle (Yes/No)",
      "description": "Simple boolean toggle switch",
      "requires_options": false
    },
    {
      "value": "radioGroup",
      "label": "Radio Group",
      "description": "User can select only one option from a list",
      "requires_options": true
    },
    {
      "value": "textarea",
      "label": "Text Area",
      "description": "Free text input field",
      "requires_options": false
    }
  ]
}
```

---

## 📝 Question Types Explained

### 1. **select** - Multi-Select
- Users can select **multiple options** from a list
- **Requires options array**
- Example: Face makeup options (Little/natural Makeup, Excess Makeup, No Makeup)

### 2. **toggle** - Boolean Switch
- Simple **yes/no** or **on/off** toggle
- **No options needed**
- Example: Maintain skin tone (true/false)

### 3. **radioGroup** - Single Selection
- Users can select **only one option** from a list
- **Requires options array**
- Example: Skin lighter options (A little, Very light, Extremely light)

### 4. **textarea** - Free Text Input
- Users can type **free-form text**
- **No options needed**
- Example: Eyes description, Lips description, Other requirements

---

## 🎯 State Keys

**State keys** are unique identifiers for each question that match the frontend's state management. They must:
- Be unique across all questions
- Use camelCase naming convention
- Match frontend expectations

**Current State Keys:**
```
Category 1 (Face):
- selectedFace

Category 2 (Skin):
- maintainSkinTone
- selectedLighter
- selectedDarker

Category 3 (Body):
- eyes
- lips
- selectedHips
- selectedButt
- height
- nose
- selectedTummy
- chin
- arm
- other
```

---

## 🧪 Example Use Cases

### Use Case 1: Add New Category "Hair"

```bash
# Step 1: Create category
curl -X POST https://editbymercy.hmstech.xyz/api/admin/questionnaire-management \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Hair",
    "icon": "cut-outline",
    "color": "#992C55",
    "description": "Select hair styling options",
    "order": 4
  }'

# Step 2: Add questions to category
curl -X POST https://editbymercy.hmstech.xyz/api/admin/questionnaire-management/4/questions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "radioGroup",
    "label": "Hair Style",
    "options": ["Straight", "Curly", "Wavy"],
    "state_key": "selectedHairStyle",
    "order": 1
  }'
```

---

### Use Case 2: Update Existing Question

```bash
curl -X PUT https://editbymercy.hmstech.xyz/api/admin/questionnaire-management/questions/3 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "label": "Skin Lightening",
    "options": ["A little lighter", "Much lighter", "Very light"]
  }'
```

---

### Use Case 3: Reorder Questions

```bash
curl -X POST https://editbymercy.hmstech.xyz/api/admin/questionnaire-management/2/reorder-questions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "question_orders": [
      {"question_id": 4, "order": 1},
      {"question_id": 3, "order": 2},
      {"question_id": 2, "order": 3}
    ]
  }'
```

---

## ⚠️ Important Notes

1. **Cascade Delete:** Deleting a questionnaire will delete all its questions
2. **State Keys:** Must be unique and cannot be changed once users have started answering
3. **Order:** Questions are displayed in ascending order (1, 2, 3...)
4. **Active Status:** Inactive questionnaires won't appear in the user-facing API
5. **Options:** Required for `select` and `radioGroup` types, null for `toggle` and `textarea`

---

## 🔒 Permissions

All routes require:
- ✅ Valid Sanctum authentication token
- ✅ Admin role (enforced by `auth:sanctum` middleware)

---

## 📊 Response Format

All responses follow this structure:

**Success:**
```json
{
  "status": "success",
  "message": "Operation successful",
  "data": { ... }
}
```

**Error:**
```json
{
  "status": "error",
  "message": "Error description",
  "data": null
}
```

---

## 🎯 Status Codes

- `200` - Success
- `201` - Created
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## 📁 Related Files

**Controller:** `/app/Http/Controllers/Admin/QuestionnaireManagementController.php`  
**Routes:** `/routes/admin.php` (prefix: `questionnaire-management`)  
**Models:** 
- `/app/Models/Questionnaire.php`
- `/app/Models/QuestionnaireQuestion.php`
- `/app/Models/QuestionnaireAnswer.php`

---

## 🚀 Quick Start

1. **Get all questionnaires:**
```bash
curl https://editbymercy.hmstech.xyz/api/admin/questionnaire-management \
  -H "Authorization: Bearer YOUR_TOKEN"
```

2. **Get question types:**
```bash
curl https://editbymercy.hmstech.xyz/api/admin/questionnaire-management/meta/question-types \
  -H "Authorization: Bearer YOUR_TOKEN"
```

3. **View specific questionnaire:**
```bash
curl https://editbymercy.hmstech.xyz/api/admin/questionnaire-management/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

**Documentation Version:** 1.0  
**Last Updated:** October 24, 2025  
**API Base URL:** `https://editbymercy.hmstech.xyz/api/admin/questionnaire-management`

