## Questionnaire Management – Admin API Guide

All routes are under:

- **Base URL:** `/api/admin/questionnaire-management`
- **Middleware:** `auth:sanctum`
- **Format:** JSON
- **Wrapper:** All responses use `ResponseHelper`:

```json
{
  "status": "success | error",
  "message": "Readable message",
  "data": { }
}
```

---

## 1. List Questionnaires

### GET `/api/admin/questionnaire-management`

**Description:** List questionnaires with stats + pagination.

**Query params (optional):**

- **`status`**: `"active"` or `"inactive"` (filters `is_active`)
- **`per_page`**: integer, default `20`
- **`page`**: integer, default `1` (standard Laravel pagination)

**Response 200:**

```json
{
  "status": "success",
  "message": "Questionnaires fetched successfully",
  "data": {
    "stats": {
      "total_questionnaires": 10,
      "active_questionnaires": 7,
      "total_questions": 120
    },
    "questionnaires": [
      {
        "id": 1,
        "title": "Body Retouching",
        "icon": "help-circle-outline",
        "color": "#992C55",
        "description": "Select one or multiple options",
        "order": 1,
        "is_active": true,
        "questions_count": 8,
        "created_at": "10/26/25 - 10:00 AM"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 1
    }
  }
}
```

---

## 2. Get Single Questionnaire (with Questions)

### GET `/api/admin/questionnaire-management/{id}`

**Description:** Get one questionnaire and all its questions.

**Response 200:**

```json
{
  "status": "success",
  "message": "Questionnaire details fetched successfully",
  "data": {
    "id": 1,
    "title": "Body Retouching",
    "icon": "help-circle-outline",
    "color": "#992C55",
    "description": "Select one or multiple options",
    "order": 1,
    "is_active": true,
    "questions": [
      {
        "id": 10,
        "type": "select",
        "label": "What are your main concerns?",
        "options": ["Acne", "Wrinkles", "Dark spots"],
        "state_key": "body_concerns",
        "order": 1,
        "is_required": true
      }
    ],
    "created_at": "10/26/25 - 10:00 AM"
  }
}
```

**Response 404:**

```json
{
  "status": "error",
  "message": "Questionnaire not found: ...",
  "data": null
}
```

---

## 3. Create Questionnaire

### POST `/api/admin/questionnaire-management`

**Description:** Create a new questionnaire category.

**Request body:**

```json
{
  "title": "Body Retouching",
  "icon": "help-circle-outline",
  "color": "#992C55",
  "description": "Select one or multiple options",
  "order": 1,
  "is_active": true
}
```

**Response 201:**

```json
{
  "status": "success",
  "message": "Questionnaire created successfully",
  "data": {
    "id": 1,
    "title": "Body Retouching",
    "order": 1
  }
}
```

---

## 4. Update Questionnaire

### PUT `/api/admin/questionnaire-management/{id}`

**Description:** Update an existing questionnaire.

**Request body (all fields optional):**

```json
{
  "title": "Updated Title",
  "icon": "new-icon",
  "color": "#FF0000",
  "description": "Updated description",
  "order": 2,
  "is_active": false
}
```

**Response 200:**

```json
{
  "status": "success",
  "message": "Questionnaire updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Title"
  }
}
```

---

## 5. Delete Questionnaire

### DELETE `/api/admin/questionnaire-management/{id}`

**Description:** Delete questionnaire (and its questions via FK).

**Response 200:**

```json
{
  "status": "success",
  "message": "Questionnaire deleted successfully",
  "data": null
}
```

---

## 6. Toggle Questionnaire Status

### POST `/api/admin/questionnaire-management/{id}/toggle-status`

**Description:** Flip `is_active` between `true` and `false`.

**Request body:** none.

**Response 200:**

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

## 7. Add Question to Questionnaire

### POST `/api/admin/questionnaire-management/{id}/questions`

**Description:** Add a new question inside a questionnaire.

**Request body:**

```json
{
  "type": "select",
  "label": "What are your main concerns?",
  "options": ["Acne", "Wrinkles"],
  "state_key": "body_concerns",
  "order": 1,
  "is_required": true
}
```

**Response 201:**

```json
{
  "status": "success",
  "message": "Question added successfully",
  "data": {
    "id": 10,
    "type": "select",
    "state_key": "body_concerns"
  }
}
```

---

## 8. Update Question

### PUT `/api/admin/questionnaire-management/questions/{questionId}`

**Description:** Edit a question.

**Request body (all optional):**

```json
{
  "type": "radioGroup",
  "label": "Updated label",
  "options": ["Option 1", "Option 2"],
  "state_key": "body_concerns_updated",
  "order": 2,
  "is_required": false
}
```

**Response 200:**

```json
{
  "status": "success",
  "message": "Question updated successfully",
  "data": {
    "id": 10,
    "type": "radioGroup"
  }
}
```

---

## 9. Delete Question

### DELETE `/api/admin/questionnaire-management/questions/{questionId}`

**Description:** Delete a question.

**Response 200:**

```json
{
  "status": "success",
  "message": "Question deleted successfully",
  "data": null
}
```

---

## 10. Reorder Questions

### POST `/api/admin/questionnaire-management/{id}/reorder-questions`

**Description:** Update display order of questions in one questionnaire.

**Request body:**

```json
{
  "question_orders": [
    {
      "question_id": 10,
      "order": 1
    },
    {
      "question_id": 11,
      "order": 2
    }
  ]
}
```

**Response 200:**

```json
{
  "status": "success",
  "message": "Questions reordered successfully",
  "data": null
}
```

---

## 11. Get Question Types (Meta)

### GET `/api/admin/questionnaire-management/meta/question-types`

**Description:** Static list of supported question types for the admin UI.

**Response 200:**

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


