# 📚 Complete Admin Frontend Implementation Guide

This comprehensive guide covers all admin APIs with exact request/response formats, authentication, and integration examples for frontend developers.

---

## 🔐 Authentication

All admin routes require authentication using Laravel Sanctum.

### Headers Required
```javascript
{
  'Authorization': 'Bearer YOUR_ACCESS_TOKEN',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
```

### Authentication Flow
1. Login via `/api/login` to get access token
2. Store token in secure storage (AsyncStorage, SecureStore, etc.)
3. Include token in Authorization header for all admin requests
4. Handle 401 responses by redirecting to login

---

## 📊 1. Dashboard

### GET `/api/admin/dashboard`

**Description:** Get dashboard statistics and recent data

**Response:**
```json
{
  "status": "success",
  "message": "Dashboard data fetched successfully",
  "data": {
    "stats": {
      "total_users": 1250,
      "amount_generated": 125000.50,
      "active_orders": 45,
      "completed_orders": 890
    },
    "active_chats": [
      {
        "id": 1,
        "user_name": "John Doe",
        "user_avatar": "https://example.com/storage/profile_picture/user.jpg",
        "last_message": "Hello, I need help with...",
        "last_message_time": "2 minutes ago",
        "unread_count": 3,
        "service_type": "Body Retouching"
      }
    ],
    "recent_orders": [
      {
        "id": 1,
        "user_name": "Jane Smith",
        "service_type": "Body Retouching",
        "status": "pending",
        "total_amount": 150.00,
        "created_at": "10/26/25 - 02:30 PM"
      }
    ]
  }
}
```

### GET `/api/admin/dashboard/orders`

**Description:** Get paginated orders list with filters

**Query Parameters:**
- `status` (optional): 'pending', 'processing', 'success', 'failed'
- `search` (optional): Search by user name or order ID
- `date` (optional): Filter by date (YYYY-MM-DD)
- `per_page` (optional): Items per page (default: 20)
- `page` (optional): Page number

**Response:**
```json
{
  "status": "success",
  "message": "Orders fetched successfully",
  "data": {
    "orders": [
      {
        "id": 1,
        "user_name": "John Doe",
        "service_type": "Body Retouching",
        "status": "pending",
        "payment_status": "pending",
        "total_amount": 150.00,
        "date": "10/26/25 - 02:30 PM"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 20,
      "total": 95
    }
  }
}
```

---

## 👥 2. User Management

### GET `/api/admin/users`

**Description:** Get all users with stats and filtering

**Query Parameters:**
- `status` (optional): 'online', 'offline'
- `search` (optional): Search by name, email, or phone
- `date` (optional): Filter by registration date (YYYY-MM-DD)
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
  "status": "success",
  "message": "Users fetched successfully",
  "data": {
    "stats": {
      "total_users": 1250,
      "online_users": 234,
      "active_users": 567
    },
    "users": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+1234567890",
        "profile_picture": "https://example.com/storage/profile_picture/user.jpg",
        "no_of_orders": 15,
        "date_registered": "10/15/25 - 03:45 PM",
        "is_online": true,
        "is_blocked": false,
        "is_verified": true
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 63,
      "per_page": 20,
      "total": 1250
    }
  }
}
```

### GET `/api/admin/users/{id}`

**Description:** Get single user details with activities, chats, and orders

**Response:**
```json
{
  "status": "success",
  "message": "User details fetched successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "profile_picture": "https://example.com/storage/profile_picture/user.jpg",
    "role": "user",
    "is_online": true,
    "is_blocked": false,
    "is_verified": true,
    "no_of_orders": 15,
    "date_registered": "10/15/25 - 03:45 PM",
    "created_at": "2025-10-15T15:45:00.000000Z",
    "updated_at": "2025-10-26T14:30:00.000000Z",
    "activities": [
      {
        "id": 123,
        "activity": "message_sent",
        "description": "Sent a text message",
        "ip_address": "192.168.1.1",
        "user_agent": "Mozilla/5.0...",
        "metadata": {
          "chat_id": 5,
          "message_type": "text"
        },
        "created_at": "10/26/25 - 02:30 PM",
        "created_at_timestamp": 1729957800,
        "time_ago": "2 hours ago"
      },
      {
        "id": 122,
        "activity": "order_created",
        "description": "Created order for Body Retouching",
        "ip_address": "192.168.1.1",
        "user_agent": "Mozilla/5.0...",
        "metadata": {
          "order_id": 45,
          "service_type": "Body Retouching"
        },
        "created_at": "10/26/25 - 01:15 PM",
        "created_at_timestamp": 1729953300,
        "time_ago": "3 hours ago"
      }
    ],
    "chats": [
      {
        "id": 1,
        "type": "user-agent",
        "other_user": {
          "id": 2,
          "name": "Agent Smith",
          "profile_picture": "https://example.com/storage/profile_picture/agent.jpg"
        },
        "agent": {
          "id": 2,
          "name": "Agent Smith"
        },
        "last_message": "Thank you for your order",
        "last_message_time": "5 minutes ago",
        "created_at": "10/26/25 - 10:00 AM"
      }
    ],
    "orders": [
      {
        "id": 45,
        "service_type": "Body Retouching",
        "status": "completed",
        "payment_status": "success",
        "total_amount": 150.00,
        "created_at": "10/25/25 - 09:30 AM",
        "updated_at": "10/25/25 - 05:45 PM"
      },
      {
        "id": 44,
        "service_type": "Face Retouching",
        "status": "pending",
        "payment_status": "pending",
        "total_amount": 100.00,
        "created_at": "10/24/25 - 02:15 PM",
        "updated_at": "10/24/25 - 02:15 PM"
      }
    ]
  }
}
```

### POST `/api/admin/users`

**Description:** Create new user

**Request Body:**
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "phone": "+1234567890",
  "password": "securePassword123",
  "role": "user",
  "profile_picture": "FILE" // multipart/form-data
}
```

**Response:**
```json
{
  "status": "success",
  "message": "User created successfully",
  "data": {
    "id": 1251,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1234567890",
    "role": "user",
    "created_at": "2025-10-26T14:30:00.000000Z"
  }
}
```

### PUT `/api/admin/users/{id}`

**Description:** Update user information

**Request Body:**
```json
{
  "name": "Jane Smith Updated",
  "email": "jane.updated@example.com",
  "phone": "+1234567891",
  "password": "newPassword123", // optional
  "role": "admin",
  "is_blocked": false
}
```

**Response:**
```json
{
  "status": "success",
  "message": "User updated successfully",
  "data": {
    "id": 1,
    "name": "Jane Smith Updated",
    "email": "jane.updated@example.com",
    "updated_at": "2025-10-26T14:35:00.000000Z"
  }
}
```

### DELETE `/api/admin/users/{id}`

**Description:** Delete user

**Response:**
```json
{
  "status": "success",
  "message": "User deleted successfully",
  "data": null
}
```

### POST `/api/admin/users/{id}/toggle-block`

**Description:** Block or unblock user

**Response:**
```json
{
  "status": "success",
  "message": "User blocked successfully",
  "data": {
    "is_blocked": true
  }
}
```

### GET `/api/admin/users/{id}/chats`

**Description:** Get user's chat history

**Response:**
```json
{
  "status": "success",
  "message": "User chats fetched successfully",
  "data": [
    {
      "id": 1,
      "agent_name": "Agent Smith",
      "service_type": "Body Retouching",
      "status": "active",
      "last_message": "Thank you for your order",
      "last_message_time": "5 minutes ago",
      "created_at": "10/26/25 - 10:00 AM"
    }
  ]
}
```

### GET `/api/admin/users/{id}/orders`

**Description:** Get user's order history

**Response:**
```json
{
  "status": "success",
  "message": "User orders fetched successfully",
  "data": [
    {
      "id": 1,
      "service_type": "Body Retouching",
      "status": "completed",
      "payment_status": "success",
      "total_amount": 150.00,
      "created_at": "10/25/25 - 09:30 AM"
    }
  ]
}
```

### GET `/api/admin/users/{id}/activity`

**Description:** Get user's activity log

**Response:**
```json
{
  "status": "success",
  "message": "User activity fetched successfully",
  "data": [
    {
      "id": 123,
      "type": "message_sent",
      "activity": "Sent a text message",
      "details": "Sent a text message",
      "date": "10/26/25 - 02:30 PM",
      "time_ago": "2 hours ago",
      "ip_address": "192.168.1.1",
      "metadata": {
        "chat_id": 5,
        "message_type": "text"
      }
    },
    {
      "id": 122,
      "type": "login",
      "activity": "User logged in",
      "details": "User logged in",
      "date": "10/26/25 - 09:00 AM",
      "time_ago": "6 hours ago",
      "ip_address": "192.168.1.1",
      "metadata": {}
    }
  ]
}
```

---

## 💬 3. Chat Management

### GET `/api/admin/chats`

**Description:** Get all chats with filtering

**Query Parameters:**
- `status` (optional): 'active', 'closed'
- `service_type` (optional): Service category
- `search` (optional): Search by user name
- `date` (optional): Filter by date (YYYY-MM-DD)
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
  "status": "success",
  "message": "Chats fetched successfully",
  "data": {
    "stats": {
      "total_chats": 500,
      "active_chats": 45,
      "chats_with_orders": 320
    },
    "chats": [
      {
        "id": 1,
        "user_name": "John Doe",
        "agent_name": "Agent Smith",
        "service_type": "Body Retouching",
        "last_message": "Hello, I need help",
        "last_message_time": "2 minutes ago",
        "unread_count": 3,
        "status": "active",
        "created_at": "10/26/25 - 10:00 AM"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 25,
      "per_page": 20,
      "total": 500
    }
  }
}
```

### GET `/api/admin/chats/{id}`

**Description:** Get single chat details with messages

**Response:**
```json
{
  "status": "success",
  "message": "Chat details fetched successfully",
  "data": {
    "id": 1,
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com",
      "profile_picture": "https://example.com/storage/profile_picture/user.jpg"
    },
    "agent": {
      "id": 2,
      "name": "Agent Smith",
      "email": "agent@example.com"
    },
    "service_type": "Body Retouching",
    "status": "active",
    "created_at": "10/26/25 - 10:00 AM",
    "messages": [
      {
        "id": 1,
        "sender_id": 5,
        "sender_name": "John Doe",
        "message": "Hello, I need help with my order",
        "type": "text",
        "is_read": true,
        "created_at": "10/26/25 - 10:05 AM"
      }
    ],
    "order": {
      "id": 1,
      "service_type": "Body Retouching",
      "status": "pending",
      "total_amount": 150.00
    }
  }
}
```

### DELETE `/api/admin/chats/{id}`

**Description:** Delete chat

**Response:**
```json
{
  "status": "success",
  "message": "Chat deleted successfully",
  "data": null
}
```

### POST `/api/admin/chats/{id}/create-order`

**Description:** Create new order within existing chat

**Request Body:**
```json
{
  "service_type": "Body Retouching",
  "description": "Order description",
  "amount": 150.00
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Order created successfully",
  "data": {
    "id": 46,
    "chat_id": 1,
    "service_type": "Body Retouching",
    "status": "pending",
    "total_amount": 150.00,
    "created_at": "2025-10-26T14:40:00.000000Z"
  }
}
```

### GET `/api/admin/chats/available`

**Description:** Get chats available for message sharing

**Response:**
```json
{
  "status": "success",
  "message": "Available chats fetched successfully",
  "data": [
    {
      "id": 1,
      "user_name": "John Doe",
      "service_type": "Body Retouching",
      "last_message": "Thanks!",
      "created_at": "10/26/25 - 10:00 AM"
    }
  ]
}
```

### POST `/api/admin/chats/share`

**Description:** Share message to another chat

**Request Body:**
```json
{
  "message_id": 123,
  "target_chat_id": 5,
  "content": "Optional additional message"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Message shared successfully",
  "data": {
    "id": 456,
    "chat_id": 5,
    "message": "Shared message content",
    "created_at": "2025-10-26T14:45:00.000000Z"
  }
}
```

---

## 📦 4. Order Management

### GET `/api/admin/orders`

**Description:** Get all orders with filtering

**Query Parameters:**
- `status` (optional): 'pending', 'processing', 'success', 'failed'
- `payment_status` (optional): 'pending', 'success', 'failed'
- `service_type` (optional): Service category
- `search` (optional): Search by user name
- `date_from` (optional): Start date (YYYY-MM-DD)
- `date_to` (optional): End date (YYYY-MM-DD)
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
  "status": "success",
  "message": "Orders fetched successfully",
  "data": {
    "stats": {
      "total_orders": 935,
      "active_orders": 45,
      "completed_orders": 890
    },
    "orders": [
      {
        "id": 1,
        "user_name": "John Doe",
        "service_type": "Body Retouching",
        "status": "pending",
        "payment_status": "pending",
        "total_amount": 150.00,
        "created_at": "10/26/25 - 02:30 PM"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 47,
      "per_page": 20,
      "total": 935
    }
  }
}
```

### GET `/api/admin/orders/{id}`

**Description:** Get single order details

**Response:**
```json
{
  "status": "success",
  "message": "Order details fetched successfully",
  "data": {
    "id": 1,
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "agent": {
      "id": 2,
      "name": "Agent Smith"
    },
    "service_type": "Body Retouching",
    "status": "pending",
    "payment_status": "pending",
    "total_amount": 150.00,
    "description": "Order description",
    "attachments": [
      "https://example.com/storage/orders/file1.jpg"
    ],
    "created_at": "10/26/25 - 02:30 PM",
    "updated_at": "10/26/25 - 02:35 PM"
  }
}
```

### PUT `/api/admin/orders/{id}`

**Description:** Update order details

**Request Body:**
```json
{
  "status": "processing",
  "payment_status": "success",
  "total_amount": 175.00,
  "description": "Updated description"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Order updated successfully",
  "data": {
    "id": 1,
    "status": "processing",
    "payment_status": "success",
    "total_amount": 175.00,
    "updated_at": "2025-10-26T14:50:00.000000Z"
  }
}
```

### DELETE `/api/admin/orders/{id}`

**Description:** Delete order

**Response:**
```json
{
  "status": "success",
  "message": "Order deleted successfully",
  "data": null
}
```

### POST `/api/admin/orders/{id}/update-status`

**Description:** Update order status

**Request Body:**
```json
{
  "status": "success"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Order status updated successfully",
  "data": {
    "id": 1,
    "status": "success",
    "updated_at": "2025-10-26T14:55:00.000000Z"
  }
}
```

### POST `/api/admin/orders/{id}/update-payment-status`

**Description:** Update payment status

**Request Body:**
```json
{
  "payment_status": "success"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Payment status updated successfully",
  "data": {
    "id": 1,
    "payment_status": "success",
    "updated_at": "2025-10-26T15:00:00.000000Z"
  }
}
```

### POST `/api/admin/orders/bulk-update`

**Description:** Bulk update multiple orders

**Request Body:**
```json
{
  "order_ids": [1, 2, 3, 4, 5],
  "action": "update_status",
  "status": "processing"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Orders updated successfully",
  "data": {
    "updated_count": 5
  }
}
```

---

## 💰 5. Transaction Management

### GET `/api/admin/transactions`

**Description:** Get all transactions with filtering

**Query Parameters:**
- `status` (optional): 'pending', 'completed', 'failed'
- `type` (optional): 'payment', 'refund'
- `search` (optional): Search by user name or transaction ID
- `date_from` (optional): Start date (YYYY-MM-DD)
- `date_to` (optional): End date (YYYY-MM-DD)
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
  "status": "success",
  "message": "Transactions fetched successfully",
  "data": {
    "stats": {
      "total_transactions": 890,
      "completed_transactions": 845,
      "total_amount": 125000.50
    },
    "transactions": [
      {
        "id": 1,
        "user_name": "John Doe",
        "order_id": 45,
        "amount": 150.00,
        "status": "completed",
        "type": "payment",
        "payment_method": "card",
        "transaction_date": "10/26/25 - 02:30 PM"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 45,
      "per_page": 20,
      "total": 890
    }
  }
}
```

### GET `/api/admin/transactions/{id}`

**Description:** Get single transaction details

**Response:**
```json
{
  "status": "success",
  "message": "Transaction details fetched successfully",
  "data": {
    "id": 1,
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "order": {
      "id": 45,
      "service_type": "Body Retouching"
    },
    "amount": 150.00,
    "status": "completed",
    "type": "payment",
    "payment_method": "card",
    "transaction_id": "txn_1234567890",
    "created_at": "10/26/25 - 02:30 PM"
  }
}
```

### POST `/api/admin/transactions/{id}/update-status`

**Description:** Update transaction status

**Request Body:**
```json
{
  "status": "completed"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Transaction status updated successfully",
  "data": {
    "id": 1,
    "status": "completed",
    "updated_at": "2025-10-26T15:05:00.000000Z"
  }
}
```

### GET `/api/admin/transactions/export`

**Description:** Export transactions to CSV

**Query Parameters:**
- Same as GET `/api/admin/transactions`

**Response:** CSV file download

---

## 👨‍💼 6. Admin User Management

### GET `/api/admin/manage-admin`

**Description:** Get all admin users (non-regular users)

**Query Parameters:**
- `role` (optional): 'admin', 'support', 'editor', 'chief_editor'
- `status` (optional): 'online', 'offline'
- `search` (optional): Search by name or email
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
  "status": "success",
  "message": "Admin users fetched successfully",
  "data": {
    "stats": {
      "total_admins": 25,
      "online_admins": 8,
      "offline_admins": 17
    },
    "admins": [
      {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "role": "admin",
        "is_online": true,
        "created_at": "10/15/25 - 03:45 PM"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 2,
      "per_page": 20,
      "total": 25
    }
  }
}
```

### GET `/api/admin/manage-admin/{id}`

**Description:** Get single admin user details

**Response:**
```json
{
  "status": "success",
  "message": "Admin details fetched successfully",
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "phone": "+1234567890",
    "role": "admin",
    "is_online": true,
    "created_at": "10/15/25 - 03:45 PM"
  }
}
```

### POST `/api/admin/manage-admin`

**Description:** Create new admin user

**Request Body:**
```json
{
  "name": "New Admin",
  "email": "newadmin@example.com",
  "password": "securePassword123",
  "role": "support",
  "phone": "+1234567890"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Admin user created successfully",
  "data": {
    "id": 26,
    "name": "New Admin",
    "email": "newadmin@example.com",
    "role": "support",
    "created_at": "2025-10-26T15:10:00.000000Z"
  }
}
```

### PUT `/api/admin/manage-admin/{id}`

**Description:** Update admin user

**Request Body:**
```json
{
  "name": "Updated Admin Name",
  "email": "updated@example.com",
  "role": "editor",
  "password": "newPassword123" // optional
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Admin user updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Admin Name",
    "role": "editor",
    "updated_at": "2025-10-26T15:15:00.000000Z"
  }
}
```

### DELETE `/api/admin/manage-admin/{id}`

**Description:** Delete admin user

**Response:**
```json
{
  "status": "success",
  "message": "Admin user deleted successfully",
  "data": null
}
```

### POST `/api/admin/manage-admin/bulk-action`

**Description:** Perform bulk actions on admin users

**Request Body:**
```json
{
  "admin_ids": [1, 2, 3],
  "action": "update_role",
  "role": "editor"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Bulk action completed successfully",
  "data": {
    "updated_count": 3
  }
}
```

---

## 📢 7. Notifications

### GET `/api/admin/notifications`

**Description:** Get sent notifications history

**Query Parameters:**
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
  "status": "success",
  "message": "Notifications fetched successfully",
  "data": {
    "notifications": [
      {
        "id": 1,
        "subject": "System Update",
        "message": "We have updated our system...",
        "recipients": "all",
        "sent_to_count": 1250,
        "created_at": "10/26/25 - 01:00 PM"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 10,
      "per_page": 20,
      "total": 195
    }
  }
}
```

### POST `/api/admin/notifications/send`

**Description:** Send notification to users

**Request Body (multipart/form-data):**
```json
{
  "subject": "Important Update",
  "message": "This is an important message...",
  "target": "all", // or "specific"
  "user_ids": [1, 2, 3], // required if target is "specific"
  "image": "FILE" // optional
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Notification sent successfully",
  "data": {
    "id": 196,
    "subject": "Important Update",
    "sent_to_count": 1250,
    "created_at": "2025-10-26T15:20:00.000000Z"
  }
}
```

### GET `/api/admin/notifications/templates`

**Description:** Get notification templates

**Response:**
```json
{
  "status": "success",
  "message": "Templates fetched successfully",
  "data": [
    {
      "id": 1,
      "name": "Welcome Message",
      "subject": "Welcome to our service!",
      "message": "Thank you for joining us..."
    }
  ]
}
```

### GET `/api/admin/notifications/users`

**Description:** Get users list for targeting notifications

**Response:**
```json
{
  "status": "success",
  "message": "Users list fetched successfully",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "is_online": true
    }
  ]
}
```

### DELETE `/api/admin/notifications/{id}`

**Description:** Delete notification record

**Response:**
```json
{
  "status": "success",
  "message": "Notification deleted successfully",
  "data": null
}
```

---

## 🎯 8. Banner/Feed Management

### GET `/api/admin/banners`

**Description:** Get all banners/feeds

**Query Parameters:**
- `category_id` (optional): Filter by category
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
  "status": "success",
  "message": "Banners fetched successfully",
  "data": {
    "banners": [
      {
        "id": 1,
        "category_name": "Promotions",
        "caption": "Summer Sale",
        "description": "Get 50% off on all services",
        "featured_image": "https://example.com/storage/feeds/banner1.jpg",
        "link": "https://example.com/promo",
        "created_at": "10/26/25 - 09:00 AM"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 20,
      "total": 95
    }
  }
}
```

### GET `/api/admin/banners/{id}`

**Description:** Get single banner details

**Response:**
```json
{
  "status": "success",
  "message": "Banner details fetched successfully",
  "data": {
    "id": 1,
    "admin_id": 1,
    "category_id": 2,
    "category_name": "Promotions",
    "caption": "Summer Sale",
    "description": "Get 50% off on all services",
    "featured_image": "https://example.com/storage/feeds/banner1.jpg",
    "link": "https://example.com/promo",
    "created_at": "10/26/25 - 09:00 AM"
  }
}
```

### POST `/api/admin/banners`

**Description:** Create new banner

**Request Body (multipart/form-data):**
```json
{
  "category_id": 2,
  "caption": "New Sale",
  "description": "Limited time offer",
  "link": "https://example.com/sale",
  "featured_image": "FILE"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Banner created successfully",
  "data": {
    "id": 96,
    "category_id": 2,
    "caption": "New Sale",
    "description": "Limited time offer",
    "featured_image": "https://example.com/storage/feeds/banner96.jpg",
    "link": "https://example.com/sale",
    "created_at": "2025-10-26T15:25:00.000000Z"
  }
}
```

### PUT `/api/admin/banners/{id}`

**Description:** Update banner

**Request Body (multipart/form-data):**
```json
{
  "category_id": 2,
  "caption": "Updated Sale",
  "description": "Extended offer",
  "link": "https://example.com/sale-extended",
  "featured_image": "FILE" // optional
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Banner updated successfully",
  "data": {
    "id": 1,
    "caption": "Updated Sale",
    "description": "Extended offer",
    "updated_at": "2025-10-26T15:30:00.000000Z"
  }
}
```

### DELETE `/api/admin/banners/{id}`

**Description:** Delete banner

**Response:**
```json
{
  "status": "success",
  "message": "Banner deleted successfully",
  "data": null
}
```

### GET `/api/admin/banners/categories`

**Description:** Get all banner categories

**Response:**
```json
{
  "status": "success",
  "message": "Categories fetched successfully",
  "data": [
    {
      "id": 1,
      "name": "News",
      "banners_count": 15
    },
    {
      "id": 2,
      "name": "Promotions",
      "banners_count": 23
    }
  ]
}
```

### POST `/api/admin/banners/categories`

**Description:** Create new banner category

**Request Body:**
```json
{
  "name": "Events"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Category created successfully",
  "data": {
    "id": 5,
    "name": "Events",
    "created_at": "2025-10-26T15:35:00.000000Z"
  }
}
```

---

## 📝 9. Questionnaire Management

### GET `/api/admin/questionnaire-management`

**Description:** Get all questionnaire categories

**Response:**
```json
{
  "status": "success",
  "message": "Questionnaires fetched successfully",
  "data": [
    {
      "id": 1,
      "title": "Face",
      "icon": "😊",
      "color": "#992C55",
      "description": "Facial features questionnaire",
      "order": 1,
      "is_active": true,
      "questions_count": 5,
      "created_at": "2025-10-24T04:37:14.000000Z"
    }
  ]
}
```

### GET `/api/admin/questionnaire-management/{id}`

**Description:** Get single questionnaire with questions

**Response:**
```json
{
  "status": "success",
  "message": "Questionnaire details fetched successfully",
  "data": {
    "id": 1,
    "title": "Face",
    "icon": "😊",
    "color": "#992C55",
    "description": "Facial features questionnaire",
    "order": 1,
    "is_active": true,
    "questions": [
      {
        "id": 1,
        "type": "checkbox",
        "label": "Select features to retouch",
        "options": ["Eyes", "Nose", "Lips", "Skin"],
        "state_key": "face_features",
        "order": 1,
        "is_required": true
      }
    ]
  }
}
```

### POST `/api/admin/questionnaire-management`

**Description:** Create new questionnaire category

**Request Body:**
```json
{
  "title": "Hair",
  "icon": "💇",
  "color": "#FF5733",
  "description": "Hair styling questionnaire",
  "order": 4,
  "is_active": true
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Questionnaire created successfully",
  "data": {
    "id": 4,
    "title": "Hair",
    "icon": "💇",
    "color": "#FF5733",
    "created_at": "2025-10-26T15:40:00.000000Z"
  }
}
```

### PUT `/api/admin/questionnaire-management/{id}`

**Description:** Update questionnaire category

**Request Body:**
```json
{
  "title": "Face (Updated)",
  "description": "Updated description",
  "order": 2
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Questionnaire updated successfully",
  "data": {
    "id": 1,
    "title": "Face (Updated)",
    "description": "Updated description",
    "updated_at": "2025-10-26T15:45:00.000000Z"
  }
}
```

### DELETE `/api/admin/questionnaire-management/{id}`

**Description:** Delete questionnaire category

**Response:**
```json
{
  "status": "success",
  "message": "Questionnaire deleted successfully",
  "data": null
}
```

### POST `/api/admin/questionnaire-management/{id}/toggle-status`

**Description:** Activate/Deactivate questionnaire

**Response:**
```json
{
  "status": "success",
  "message": "Questionnaire status updated successfully",
  "data": {
    "id": 1,
    "is_active": false
  }
}
```

### POST `/api/admin/questionnaire-management/{id}/questions`

**Description:** Add question to questionnaire

**Request Body:**
```json
{
  "type": "checkbox",
  "label": "What areas need retouching?",
  "options": ["Eyes", "Nose", "Lips"],
  "state_key": "retouch_areas",
  "order": 1,
  "is_required": true
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Question added successfully",
  "data": {
    "id": 25,
    "questionnaire_id": 1,
    "type": "checkbox",
    "label": "What areas need retouching?",
    "created_at": "2025-10-26T15:50:00.000000Z"
  }
}
```

### PUT `/api/admin/questionnaire-management/questions/{questionId}`

**Description:** Update question

**Request Body:**
```json
{
  "label": "Updated question label",
  "options": ["Option 1", "Option 2", "Option 3"],
  "is_required": false
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Question updated successfully",
  "data": {
    "id": 25,
    "label": "Updated question label",
    "updated_at": "2025-10-26T15:55:00.000000Z"
  }
}
```

### DELETE `/api/admin/questionnaire-management/questions/{questionId}`

**Description:** Delete question

**Response:**
```json
{
  "status": "success",
  "message": "Question deleted successfully",
  "data": null
}
```

### POST `/api/admin/questionnaire-management/{id}/reorder-questions`

**Description:** Reorder questions

**Request Body:**
```json
{
  "question_orders": [
    {"id": 1, "order": 2},
    {"id": 2, "order": 1},
    {"id": 3, "order": 3}
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

### GET `/api/admin/questionnaire-management/meta/question-types`

**Description:** Get available question types

**Response:**
```json
{
  "status": "success",
  "message": "Question types fetched successfully",
  "data": {
    "types": [
      "text",
      "textarea",
      "checkbox",
      "radio",
      "select",
      "multiselect",
      "image",
      "slider",
      "bodymap"
    ]
  }
}
```

---

## 🔧 Error Handling

All endpoints follow a consistent error response format:

```json
{
  "status": "error",
  "message": "Error description here",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### Common HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## 📱 Frontend Integration Examples

### React Native / Expo Example

```javascript
// api/adminService.js
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'https://your-api-domain.com/api/admin';

// Create axios instance
const adminApi = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add auth token to requests
adminApi.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Handle 401 responses
adminApi.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      await AsyncStorage.removeItem('auth_token');
      // Navigate to login screen
    }
    return Promise.reject(error);
  }
);

// Dashboard
export const getDashboard = () => adminApi.get('/dashboard');

// Users
export const getUsers = (params) => adminApi.get('/users', { params });
export const getUser = (id) => adminApi.get(`/users/${id}`);
export const createUser = (data) => adminApi.post('/users', data);
export const updateUser = (id, data) => adminApi.put(`/users/${id}`, data);
export const deleteUser = (id) => adminApi.delete(`/users/${id}`);
export const toggleBlockUser = (id) => adminApi.post(`/users/${id}/toggle-block`);

// Chats
export const getChats = (params) => adminApi.get('/chats', { params });
export const getChat = (id) => adminApi.get(`/chats/${id}`);
export const deleteChat = (id) => adminApi.delete(`/chats/${id}`);

// Orders
export const getOrders = (params) => adminApi.get('/orders', { params });
export const getOrder = (id) => adminApi.get(`/orders/${id}`);
export const updateOrder = (id, data) => adminApi.put(`/orders/${id}`, data);
export const updateOrderStatus = (id, status) => 
  adminApi.post(`/orders/${id}/update-status`, { status });

// Notifications
export const sendNotification = (data) => {
  const formData = new FormData();
  formData.append('subject', data.subject);
  formData.append('message', data.message);
  formData.append('target', data.target);
  
  if (data.image) {
    formData.append('image', {
      uri: data.image.uri,
      type: 'image/jpeg',
      name: 'notification.jpg',
    });
  }
  
  if (data.user_ids) {
    data.user_ids.forEach(id => formData.append('user_ids[]', id));
  }
  
  return adminApi.post('/notifications/send', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
};

export default adminApi;
```

### Usage in Components

```javascript
// screens/admin/DashboardScreen.js
import React, { useEffect, useState } from 'react';
import { View, Text, ActivityIndicator } from 'react-native';
import { getDashboard } from '../../api/adminService';

export default function DashboardScreen() {
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    loadDashboard();
  }, []);

  const loadDashboard = async () => {
    try {
      setLoading(true);
      const response = await getDashboard();
      setStats(response.data.data.stats);
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load dashboard');
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <ActivityIndicator />;
  if (error) return <Text>{error}</Text>;

  return (
    <View>
      <Text>Total Users: {stats.total_users}</Text>
      <Text>Amount Generated: ${stats.amount_generated}</Text>
      <Text>Active Orders: {stats.active_orders}</Text>
      <Text>Completed Orders: {stats.completed_orders}</Text>
    </View>
  );
}
```

---

## 🎨 UI/UX Best Practices

### Loading States
Always show loading indicators when fetching data:
```javascript
{loading ? <ActivityIndicator /> : <DataComponent data={data} />}
```

### Error Handling
Display user-friendly error messages:
```javascript
{error && <ErrorMessage message={error} />}
```

### Pagination
Implement infinite scroll or load more buttons:
```javascript
<FlatList
  data={items}
  onEndReached={loadMore}
  onEndReachedThreshold={0.5}
  ListFooterComponent={loading ? <ActivityIndicator /> : null}
/>
```

### Pull to Refresh
Add pull-to-refresh functionality:
```javascript
<FlatList
  data={items}
  refreshing={refreshing}
  onRefresh={refresh}
/>
```

---

## 🔍 Testing

### Test User Accounts
- **Admin:** admin@example.com / password123
- **Support:** support@example.com / password123
- **Regular User:** user@example.com / password123

### API Testing Tools
- **Postman:** Import the collection from `/postman_collection.json`
- **Insomnia:** Import from `/insomnia_workspace.json`

---

## 📞 Support

For issues or questions:
- Backend Documentation: `/ADMIN_API_DOCUMENTATION.md`
- Questionnaire API: `/ADMIN_QUESTIONNAIRE_API.md`
- Online Status: `/ONLINE_STATUS_IMPLEMENTATION.md`

---

**Last Updated:** October 26, 2025
**API Version:** 1.0
**Base URL:** `https://your-api-domain.com/api`

