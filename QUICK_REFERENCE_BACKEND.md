# Quick Reference: Backend Questionnaire API

## 📌 Quick Commands

```bash
# 1. Run migrations
php artisan migrate
# OR via API
curl -X GET https://editbymercy.hmstech.xyz/api/migrate

# 2. Run seeder (populate questionnaire data)
php artisan db:seed --class=QuestionnaireSeeder
# OR via API
curl -X GET https://editbymercy.hmstech.xyz/api/seed/questionnaire

# 3. Test endpoint
curl -X GET https://editbymercy.hmstech.xyz/api/questionnaire/all \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🗄️ Database Tables

### 1. `questionnaires`
| Column | Type | Example |
|--------|------|---------|
| id | bigint | 1 |
| title | string | "Face" |
| icon | string | "happy-outline" |
| color | string | "#992C55" |
| description | text | "Select one or multiple options" |
| order | integer | 1 |
| is_active | boolean | true |

### 2. `questionnaire_questions`
| Column | Type | Example |
|--------|------|---------|
| id | bigint | 1 |
| questionnaire_id | bigint | 1 |
| type | string | "select" |
| label | string | "Maintain skin tone" |
| options | json | ["Option 1", "Option 2"] |
| state_key | string | "selectedFace" |
| order | integer | 1 |
| is_required | boolean | false |

### 3. `questionnaire_answers`
| Column | Type | Example |
|--------|------|---------|
| id | bigint | 1 |
| chat_id | bigint | 123 |
| user_id | bigint | 456 |
| answers | json | {"selectedFace": "No Makeup"} |
| completed_sections | integer | 4 |
| progress | integer | 28 |

---

## 🔌 API Endpoints

### 1. Get All Questionnaires
```http
GET /api/questionnaire/all
Authorization: Bearer {token}
```

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
      "questions": [...]
    }
  ]
}
```

### 2. Save Answer
```http
POST /api/questionnaire/save-answer
Authorization: Bearer {token}
Content-Type: application/json

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

### 3. Get Progress
```http
GET /api/questionnaire/progress/{chat_id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "status": "success",
  "progress": 33,
  "completed_sections": 4,
  "answers": {...}
}
```

### 4. Get Answers (For Agents)
```http
GET /api/questionnaire/answers/{chat_id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "chat_id": 123,
    "user_id": 456,
    "answers": {...},
    "progress": 100,
    "completed_sections": 14
  }
}
```

---

## 📊 Progress Calculation

Total sections = **14**

| Section | Count | Cumulative |
|---------|-------|------------|
| Face | 1 | 1 |
| Skin | 3 | 4 |
| Body | 10 | 14 |

**Progress Formula:**
```
progress = (completed_sections / 14) * 100
```

**Examples:**
- 1 section done = 7% progress
- 4 sections done = 28% progress  
- 7 sections done = 50% progress
- 14 sections done = 100% progress

---

## 🎯 Question Types

| Type | Description | Example |
|------|-------------|---------|
| `select` | Multi-select options | Face makeup options |
| `toggle` | Boolean on/off | Maintain skin tone |
| `radioGroup` | Single selection from list | Lighter options |
| `textarea` | Free text input | Eyes description |

---

## 🔑 State Keys (Must Match Frontend)

### Category 1: Face
- `selectedFace`

### Category 2: Skin
- `maintainSkinTone`
- `selectedLighter`
- `selectedDarker`

### Category 3: Body
- `eyes`
- `lips`
- `selectedHips`
- `selectedButt`
- `height`
- `nose`
- `selectedTummy`
- `chin`
- `arm`
- `other`

---

## ✅ Validation Rules

### Save Answer Request
```php
[
    'chat_id' => 'required|exists:chats,id',
    'user_id' => 'required|exists:users,id',
    'answers' => 'required|array'
]
```

---

## 🧪 Test Data

Use this to test the API after seeder runs:

```bash
# Get all questionnaires
curl -X GET https://editbymercy.hmstech.xyz/api/questionnaire/all \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Save first answer
curl -X POST https://editbymercy.hmstech.xyz/api/questionnaire/save-answer \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "chat_id": 1,
    "user_id": 1,
    "answers": {
      "selectedFace": "Little/natural Makeup"
    }
  }'

# Check progress
curl -X GET https://editbymercy.hmstech.xyz/api/questionnaire/progress/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🐛 Common Errors

### Error: 401 Unauthorized
**Cause:** Invalid or missing token  
**Fix:** Ensure Bearer token is valid

### Error: 422 Validation Error
**Cause:** Missing required fields  
**Fix:** Check request body has chat_id, user_id, answers

### Error: 500 Server Error
**Cause:** Database or code issue  
**Fix:** Check Laravel logs: `storage/logs/laravel.log`

### Error: Table doesn't exist
**Cause:** Migrations not run  
**Fix:** Run `php artisan migrate`

### Error: No data returned
**Cause:** Seeder not run  
**Fix:** Run `php artisan db:seed --class=QuestionnaireSeeder`

---

## 📝 Checklist for Backend Dev

- [ ] Create 3 migration files
- [ ] Create 3 model files  
- [ ] Create controller with 4 methods
- [ ] Add 4 routes to `routes/api.php`
- [ ] Create seeder file
- [ ] Run migrations: `php artisan migrate`
- [ ] Run seeder: `php artisan db:seed --class=QuestionnaireSeeder`
- [ ] Test GET `/questionnaire/all` endpoint
- [ ] Test POST `/save-answer` endpoint
- [ ] Test GET `/progress/{chat_id}` endpoint
- [ ] Test GET `/answers/{chat_id}` endpoint
- [ ] Verify progress calculation logic
- [ ] Check logs for errors
- [ ] Share API URL with frontend team

---

## 🚀 After Implementation

Once backend is ready, frontend will:
1. Fetch questionnaire data on app load
2. Use it instead of hardcoded file
3. Everything else works the same!

No changes needed to:
- UI/UX
- Modal components
- Styling
- User flow
- Answer submission

---

## 📞 Support

If issues arise:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database has data: `SELECT * FROM questionnaires`
3. Test endpoints with Postman
4. Check authentication middleware is working
5. Verify JSON response format matches expected structure

---

**File Location:** Share this file with your Laravel developer along with `QUESTIONNAIRE_BACKEND_GUIDE.md`

