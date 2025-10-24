# Admin Panel API Documentation

## 📋 Overview
Complete admin panel backend for EditByMercy with user management, chat monitoring, order tracking, and dashboard analytics.

## 🔐 Authentication
All routes require `auth:sanctum` middleware.
```
Authorization: Bearer {your_token}
```

---

## 📊 DASHBOARD API

### GET `/api/admin/dashboard`
Get dashboard statistics and recent data.

**Response:**
```json
{
  "status": "success",
  "data": {
    "stats": {
      "total_users": 2500,
      "amount_generated": "N246,500.00",
      "amount_generated_raw": 246500,
      "active_orders": 20,
      "completed_orders": 10
    },
    "active_chats": [
      {
        "id": 1,
        "user": { "id": 1, "name": "Sasha", "profile_picture": "url" },
        "service_type": "Photo Editing",
        "last_message": {
          "text": "Welcome to Edits by Mercy",
          "created_at": "May 21, 2025 - 08:22 AM"
        }
      }
    ],
    "recent_orders": [
      {
        "id": 1,
        "customer": { "id": 1, "name": "Sasha", "profile_picture": "url" },
        "service_name": "Photo Editing",
        "amount": "N25,000.00",
        "editor": { "id": 2, "name": "Chris", "profile_picture": "url" },
        "date": "05/09/25 - 07:22 AM",
        "status": "completed",
        "chat_id": 1
      }
    ]
  }
}
```

---

## 👥 USER MANAGEMENT API

### GET `/api/admin/users`
Get all users with stats and filtering.

**Query Parameters:**
- `status` - Filter by online/offline
- `search` - Search by name, email, or phone
- `date` - Filter by registration date
- `per_page` - Items per page (default: 20)

**Response:**
```json
{
  "status": "success",
  "data": {
    "stats": {
      "total_users": 2500,
      "online_users": 150,
      "active_users": 15
    },
    "users": [
      {
        "id": 1,
        "name": "Adewale",
        "email": "abcdefg@gmail.com",
        "phone": "08012345678",
        "profile_picture": "url",
        "no_of_orders": 10,
        "date_registered": "05/09/25 - 07:22 AM",
        "is_online": true,
        "is_blocked": false,
        "is_verified": true
      }
    ],
    "pagination": { ... }
  }
}
```

### GET `/api/admin/users/{id}`
Get single user details.

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Adewale",
    "email": "abcdefg@gmail.com",
    "phone": "08012345678",
    "profile_picture": "url",
    "role": "user",
    "is_online": true,
    "is_blocked": false,
    "is_verified": true,
    "no_of_orders": 10,
    "date_registered": "05/09/25 - 07:22 AM"
  }
}
```

### POST `/api/admin/users`
Create new user.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "08012345678",
  "password": "password123",
  "profile_picture": "file", // optional
  "role": "user" // optional: user, admin, support, editor, chief_editor
}
```

### PUT `/api/admin/users/{id}`
Update user details.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "08012345678",
  "password": "newpassword123", // optional
  "profile_picture": "file", // optional
  "role": "user", // optional
  "is_blocked": false // optional
}
```

### DELETE `/api/admin/users/{id}`
Delete user (also deletes profile picture).

### POST `/api/admin/users/{id}/toggle-block`
Block/Unblock a user.

**Response:**
```json
{
  "status": "success",
  "data": {
    "is_blocked": true
  },
  "message": "User blocked successfully"
}
```

### GET `/api/admin/users/{id}/chats`
Get user's chats.

**Query Parameters:**
- `service_type` - Filter by service type
- `date` - Filter by date

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "agent_name": "Chris",
      "agent_profile": "url",
      "service": "Photo Editing",
      "order_amount": "N25,000.00",
      "no_of_photos": 10,
      "date": "05/09/25 - 07:22 AM",
      "status": "completed",
      "unread_count": 2,
      "has_questionnaire": true
    }
  ]
}
```

### GET `/api/admin/users/{id}/orders`
Get user's orders with stats.

**Query Parameters:**
- `status` - Filter by status
- `service_type` - Filter by service type
- `date` - Filter by date
- `per_page` - Items per page

**Response:**
```json
{
  "status": "success",
  "data": {
    "stats": {
      "total_orders": 12,
      "active": 2,
      "completed": 10
    },
    "orders": [ ... ],
    "pagination": { ... }
  }
}
```

### GET `/api/admin/users/{id}/activity`
Get user's recent activity log.

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "type": "order",
      "activity": "Adewale created an order",
      "details": "Service: Photo Editing - Amount: N25,000.00",
      "date": "05/09/25 - 07:22 AM"
    }
  ]
}
```

---

## 💬 CHATS MANAGEMENT API

### GET `/api/admin/chats`
Get all chats with filtering.

**Query Parameters:**
- `service_type` - Filter by service type
- `date` - Filter by date
- `search` - Search by participant name
- `tab` - Filter by type: `activity`, `chats`, `orders`
- `per_page` - Items per page

**Response:**
```json
{
  "status": "success",
  "data": {
    "stats": {
      "total_chats": 2500,
      "active_chats": 150,
      "chats_with_orders": 15
    },
    "chats": [
      {
        "id": 1,
        "agent_name": "Sasha",
        "agent_profile": "url",
        "service": "Photo Editing",
        "order_amount": "N25,000.00",
        "no_of_photos": 10,
        "date": "05/09/25 - 07:22 AM",
        "status": "completed",
        "unread_count": 0,
        "has_questionnaire": true,
        "chat_id": 1
      }
    ],
    "pagination": { ... }
  }
}
```

### GET `/api/admin/chats/{id}`
Get single chat with all messages.

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "type": "user-agent",
    "participant_a": { "id": 1, "name": "Sasha", "profile_picture": "url", "role": "user" },
    "participant_b": { "id": 2, "name": "Chris", "profile_picture": "url", "role": "agent" },
    "order": {
      "id": 1,
      "customer_name": "Sasha",
      "service_type": "Photo Editing",
      "status": "completed",
      "payment_status": "success",
      "total_amount": 25000,
      "no_of_photos": 10,
      "delivery_date": "2025-05-15"
    },
    "messages": [
      {
        "id": 1,
        "sender_id": 1,
        "sender_name": "Sasha",
        "sender_profile": "url",
        "type": "text",
        "message": "Hello",
        "created_at": "08:22 AM",
        "date": "May 21, 2025"
      }
    ]
  }
}
```

### POST `/api/admin/chats/{id}/new-order`
Create new order in existing chat.

**Request Body:**
```json
{
  "service_type": "Photo Editing",
  "customer_name": "Maleek"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "order": { ... },
    "message": "Order created and added to chat"
  },
  "message": "New order created successfully"
}
```

### GET `/api/admin/chats/available/list`
Get available chats for sharing/forwarding.

**Query Parameters:**
- `search` - Search chats by name

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Sasha",
      "profile_picture": "url",
      "last_chat": "Last chat with Adewale",
      "is_online": true
    }
  ]
}
```

### POST `/api/admin/chats/share`
Share/forward message to another chat.

**Request Body:**
```json
{
  "from_chat_id": 1,
  "to_chat_id": 2,
  "message_id": 10, // optional: for forwarding existing message
  "content": "Text content" // optional: for new message
}
```

### DELETE `/api/admin/chats/{id}`
Delete chat (soft delete).

---

## 📦 ORDERS MANAGEMENT API

### GET `/api/admin/orders`
Get all orders with stats and filtering.

**Query Parameters:**
- `status` - Filter by status: `pending`, `processing`, `success`, `failed`, or service types
- `service_type` - Filter by service type
- `date` - Filter by date
- `search` - Search by customer, editor, service, or order ID
- `per_page` - Items per page

**Response:**
```json
{
  "status": "success",
  "data": {
    "stats": {
      "total_orders": 12,
      "active": 2,
      "completed": 10
    },
    "orders": [
      {
        "id": 1,
        "customer": { "id": 1, "name": "Sasha", "profile_picture": "url" },
        "service_name": "Photo Editing",
        "amount": "N25,000.00",
        "amount_raw": 25000,
        "editor": { "id": 2, "name": "Chris", "profile_picture": "url" },
        "date": "05/09/25 - 07:22 AM",
        "status": "completed",
        "payment_status": "success",
        "chat_id": 1,
        "no_of_photos": 10,
        "delivery_date": "2025-05-15"
      }
    ],
    "pagination": { ... }
  }
}
```

### GET `/api/admin/orders/{id}`
Get single order details.

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "status": "completed",
    "payment_status": "success",
    "agent_name": "Sasha",
    "agent_profile": "url",
    "category": "Photo Editing",
    "no_of_photos": 10,
    "amount_paid": "N25,000.00",
    "amount_paid_raw": 25000,
    "txn_id": "sdklfnsk234jmflkwelmvdln",
    "delivery_date": "2025-05-15",
    "created_at": "05/09/25 - 07:22 AM",
    "customer": { "id": 1, "name": "Sasha", "email": "...", "phone": "...", "profile_picture": "url" },
    "editor": { "id": 2, "name": "Chris", "email": "...", "profile_picture": "url" },
    "chat_id": 1
  }
}
```

### PUT `/api/admin/orders/{id}`
Update order details.

**Request Body:**
```json
{
  "service_type": "Photo Editing",
  "total_amount": 25000,
  "no_of_photos": 10,
  "delivery_date": "2025-05-15",
  "agent_id": 2
}
```

### PATCH `/api/admin/orders/{id}/status`
Update order status.

**Request Body:**
```json
{
  "status": "completed" // pending, processing, success, failed
}
```

### PATCH `/api/admin/orders/{id}/payment-status`
Update payment status.

**Request Body:**
```json
{
  "payment_status": "success" // unpaid, initialized, success, failed
}
```

### DELETE `/api/admin/orders/{id}`
Delete order.

### POST `/api/admin/orders/bulk-update`
Bulk update multiple orders.

**Request Body:**
```json
{
  "order_ids": [1, 2, 3],
  "action": "update_status", // update_status, update_payment_status, delete
  "status": "completed", // required if action=update_status
  "payment_status": "success" // required if action=update_payment_status
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "affected_count": 3,
    "action": "update_status"
  },
  "message": "Bulk action completed successfully"
}
```

---

## 💳 TRANSACTIONS API

### GET `/api/admin/transactions`
Get all transactions with stats and filtering.

**Query Parameters:**
- `status` - Filter by status (pending, completed, failed)
- `service_type` - Filter by service type
- `date` - Filter by date
- `search` - Search by customer name, service, or transaction ID
- `per_page` - Items per page

**Response:**
```json
{
  "status": "success",
  "data": {
    "stats": {
      "total_transactions": 200,
      "completed_transactions": 198,
      "total_amount": "N250,000.00",
      "total_amount_raw": 250000
    },
    "transactions": [
      {
        "id": 1,
        "customer_name": "Sasha",
        "customer_profile": "url",
        "service_name": "Photo Editing",
        "amount": "N25,000.00",
        "amount_raw": 25000,
        "date": "05/09/25 - 07:22 AM",
        "status": "completed",
        "order_id": 1
      }
    ],
    "pagination": { ... }
  }
}
```

### GET `/api/admin/transactions/{id}`
Get single transaction details.

### PATCH `/api/admin/transactions/{id}/status`
Update transaction status.

**Request Body:**
```json
{
  "status": "completed" // pending, completed, failed
}
```

### GET `/api/admin/transactions/export/data`
Export transactions data (applies same filters as index).

---

## 👨‍💼 MANAGE ADMIN API

### GET `/api/admin/manage-admin`
Get all admin users with stats and filtering.

**Query Parameters:**
- `status` - Filter by Online/Offline
- `role` - Filter by role (admin, support, editor, chief_editor)
- `date` - Filter by registration date
- `search` - Search by name or email
- `per_page` - Items per page

**Response:**
```json
{
  "status": "success",
  "data": {
    "stats": {
      "total_admins": 200,
      "online_admins": 100,
      "offline_admins": 100
    },
    "admins": [
      {
        "id": 1,
        "name": "Adewale",
        "email": "abcdefg@gmail.com",
        "role": "Owner",
        "role_raw": "admin",
        "profile_picture": "url",
        "no_of_orders": 10,
        "date_registered": "05/09/25 - 07:22 AM",
        "is_online": true
      }
    ],
    "pagination": { ... }
  }
}
```

### POST `/api/admin/manage-admin`
Create new admin user.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role": "support", // admin, support, editor, chief_editor
  "profile_picture": "file" // optional
}
```

### PUT `/api/admin/manage-admin/{id}`
Update admin user.

### DELETE `/api/admin/manage-admin/{id}`
Delete admin user.

### POST `/api/admin/manage-admin/bulk-action`
Bulk actions on admin users.

**Request Body:**
```json
{
  "admin_ids": [1, 2, 3],
  "action": "change_role", // change_role, delete
  "role": "editor" // required if action=change_role
}
```

---

## 🔔 NOTIFICATIONS API

### GET `/api/admin/notifications`
Get all sent notifications.

**Query Parameters:**
- `search` - Search by title or content
- `date` - Filter by date
- `per_page` - Items per page

**Response:**
```json
{
  "status": "success",
  "data": {
    "notifications": [
      {
        "id": 1,
        "user": {
          "id": 1,
          "name": "Sasha",
          "profile_picture": "url"
        },
        "title": "Welcome to Edit by Mercy",
        "content": "Thank you for joining...",
        "is_read": false,
        "created_at": "05/09/25 - 07:22 AM"
      }
    ],
    "pagination": { ... }
  }
}
```

### POST `/api/admin/notifications/send`
Send new notification to users.

**Request Body:**
```json
{
  "subject": "Welcome Message",
  "message": "Thank you for joining Edit by Mercy...",
  "image": "file", // optional
  "recipient_type": "all", // all, specific
  "user_ids": [1, 2, 3] // required if recipient_type=specific
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "sent_count": 100,
    "failed_count": 0,
    "total_recipients": 100,
    "image_path": "url" // if image uploaded
  },
  "message": "Notifications sent successfully"
}
```

### GET `/api/admin/notifications/templates`
Get notification templates/suggestions.

### GET `/api/admin/notifications/users`
Get users for notification targeting.

**Query Parameters:**
- `search` - Search by name or email

### DELETE `/api/admin/notifications/{id}`
Delete notification.

---

## 🎨 BANNERS/FEED API

### GET `/api/admin/banners`
Get all banners.

**Query Parameters:**
- `category` - Filter by category
- `search` - Search by caption
- `date` - Filter by date
- `per_page` - Items per page

**Response:**
```json
{
  "status": "success",
  "data": {
    "banners": [
      {
        "id": 1,
        "caption": "Get the best Service",
        "description": "Edit by Mercy is the best photo editing platform...",
        "featured_image": "url",
        "category": "Promotions",
        "category_id": 1,
        "likes_count": 150,
        "created_at": "05/09/25 - 07:22 AM"
      }
    ],
    "categories": [...],
    "pagination": { ... }
  }
}
```

### POST `/api/admin/banners`
Create new banner.

**Request Body:**
```json
{
  "caption": "Get the best Service",
  "description": "Edit by Mercy is the best...",
  "banner_image": "file", // required
  "banner_link": "https://example.com", // optional
  "category_id": 1 // optional
}
```

### GET `/api/admin/banners/{id}`
Get single banner details.

### PUT `/api/admin/banners/{id}`
Update banner.

### DELETE `/api/admin/banners/{id}`
Delete banner.

### GET `/api/admin/banners/categories/list`
Get all banner categories.

### POST `/api/admin/banners/categories/create`
Create new category.

**Request Body:**
```json
{
  "name": "Promotions"
}
```

---

## 📋 QUESTIONNAIRE MANAGEMENT API

Complete CRUD API for managing questionnaires and questions dynamically.

**See full documentation:** `ADMIN_QUESTIONNAIRE_API.md`

**Base URL:** `/api/admin/questionnaire-management`

### Quick Reference

**Questionnaire Management:**
- `GET /` - List all with stats
- `GET /{id}` - Get details
- `POST /` - Create new
- `PUT /{id}` - Update
- `DELETE /{id}` - Delete
- `POST /{id}/toggle-status` - Toggle active status

**Question Management:**
- `POST /{id}/questions` - Add question
- `PUT /questions/{questionId}` - Update question
- `DELETE /questions/{questionId}` - Delete question
- `POST /{id}/reorder-questions` - Reorder questions

**Helper:**
- `GET /meta/question-types` - Get available question types

**Question Types:** `select`, `toggle`, `radioGroup`, `textarea`

---

## 📝 OLD QUESTIONNAIRE API (Legacy)

These endpoints exist for backward compatibility:
- `POST /api/admin/questionnaire` - Create/update questionnaire (old)
- `GET /api/admin/questionnaire` - Get questionnaire (old)

**⚠️ Use the new `/questionnaire-management` API for new implementations.**

---

## 🎯 Status Codes

- `200` - Success
- `201` - Created
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## 📁 Files Created

### Controllers
- `/app/Http/Controllers/Admin/DashboardController.php` - Dashboard stats and data
- `/app/Http/Controllers/Admin/UserManagementController.php` - Complete user management
- `/app/Http/Controllers/Admin/ChatsController.php` - Chat monitoring and management
- `/app/Http/Controllers/Admin/OrdersController.php` - Order management
- `/app/Http/Controllers/Admin/TransactionsController.php` - Transaction management
- `/app/Http/Controllers/Admin/ManageAdminController.php` - Admin user management
- `/app/Http/Controllers/Admin/NotificationsController.php` - Send notifications to users
- `/app/Http/Controllers/Admin/BannersController.php` - Banner/Feed management
- `/app/Http/Controllers/Admin/QuestionnaireManagementController.php` - **NEW** Questionnaire CRUD management

### Models Updated
- `/app/Models/User.php` - Added fcmToken to fillable, added relations (orders, chats, isOnline)
- `/app/Models/Feed.php` - Added description and link fields
- `/app/Models/FeedCategory.php` - Added fillable and relations

### Routes
- `/routes/admin.php` - All admin routes with proper organization

---

## ✨ Features Implemented

### Dashboard
- ✅ Total users, amount generated, active/completed orders stats
- ✅ Recent active chats (last 7 days)  
- ✅ Recent orders list

### User Management
- ✅ List users with online/offline status
- ✅ Search and filter users
- ✅ View user details
- ✅ Create/update/delete users
- ✅ Block/unblock users
- ✅ View user's chats with filters
- ✅ View user's orders with stats
- ✅ View user's activity log

### Chats Management
- ✅ List all chats with stats (total, active, with orders)
- ✅ View chat details with messages
- ✅ Create new order in chat
- ✅ Get available chats for sharing
- ✅ Share/forward messages to other chats
- ✅ Delete chats

### Orders Management
- ✅ List orders with stats and filters
- ✅ View order details
- ✅ Update order information
- ✅ Update order status
- ✅ Update payment status
- ✅ Bulk update orders
- ✅ Delete orders

### Transactions Management
- ✅ List transactions with stats (total, completed, amount)
- ✅ Filter by status, service type, date
- ✅ View transaction details
- ✅ Update transaction status
- ✅ Export transactions data

### Manage Admin
- ✅ List admin users with stats (total, online, offline)
- ✅ Filter by role and status
- ✅ Create/update/delete admin users
- ✅ Bulk actions (change role, delete)
- ✅ Role management (admin, support, editor, chief_editor)

### Notifications
- ✅ List all sent notifications
- ✅ Send notifications to all or specific users
- ✅ Upload images with notifications
- ✅ Get notification templates
- ✅ Target specific users
- ✅ Firebase push notification integration

### Banners/Feed Management
- ✅ List banners with categories
- ✅ Create/update/delete banners
- ✅ Upload banner images (max 10MB)
- ✅ Add banner links
- ✅ Manage categories
- ✅ Filter by category and date

---

## 🚀 Usage Examples

### Get Dashboard
```bash
GET /api/admin/dashboard
Authorization: Bearer {token}
```

### Search Users
```bash
GET /api/admin/users?search=John&status=online
Authorization: Bearer {token}
```

### Create New User
```bash
POST /api/admin/users
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role": "user"
}
```

### Filter Orders
```bash
GET /api/admin/orders?status=completed&service_type=Photo%20Editing
Authorization: Bearer {token}
```

---

## 📌 Notes

- All routes use **ResponseHelper** for consistent JSON responses
- No DTOs used - direct model manipulation as requested
- Business logic kept in controllers as requested
- All existing migrations and models verified for compatibility
- Profile pictures stored in `storage/app/public/profile_picture/`
- File uploads handled with proper validation
- Soft deletes implemented for chats (is_deleted_by_admin)

