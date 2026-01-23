# API Documentation v1

## Base URL
```
http://your-domain.com/api/v1
```

## Authentication

All endpoints except `/auth/login` require authentication using Bearer tokens (Laravel Sanctum).

### Login
```http
POST /auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password",
  "device_name": "optional-device-name"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {...},
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

### Using the Token
Include the token in the Authorization header for all protected endpoints:
```http
Authorization: Bearer 1|abc123...
```

---

## Purchase Requests

### List Purchase Requests
```http
GET /purchase-requests?status=pending&department_id=1&per_page=20
```

**Query Parameters:**
- `status` - Filter by status (pending, approved, rejected, cancelled)
- `department_id` - Filter by department
- `from_date` - Start date (YYYY-MM-DD)
- `to_date` - End date (YYYY-MM-DD)
- `per_page` - Items per page (default: 15, max: 100)

### Create Purchase Request
```http
POST /purchase-requests
Content-Type: application/json

{
  "expected_date": "2026-02-15",
  "cost_reduce_idea": "Optional cost reduction idea",
  "to_department": "Purchasing",
  "items": [
    {
      "item_name": "Office Chair",
      "quantity": 10,
      "uom": "pcs",
      "purpose": "New workstation",
      "price": 150.00,
      "currency": "USD"
    }
  ]
}
```

### Get Purchase Request
```http
GET /purchase-requests/{id}
```

### Update Purchase Request
```http
PUT /purchase-requests/{id}
Content-Type: application/json

{
  "expected_date": "2026-03-01",
  "cost_reduce_idea": "Updated idea"
}
```

### Cancel Purchase Request
```http
DELETE /purchase-requests/{id}
```

### Approve Purchase Request
```http
POST /purchase-requests/{id}/approve
```

### Reject Purchase Request
```http
POST /purchase-requests/{id}/reject
Content-Type: application/json

{
  "reason": "Reason for rejection"
}
```

### Get Approval History
```http
GET /purchase-requests/{id}/history
```

---

## Discipline Evaluations

### List Evaluations
```http
GET /discipline/evaluations?department=001&month=1&year=2026
```

**Query Parameters:**
- `department` - Department code
- `month` - Month (1-12)
- `year` - Year
- `status` - Employee status (YAYASAN, KONTRAK, MAGANG)
- `is_locked` - Filter locked/unlocked (true/false)
- `per_page` - Items per page (default: 15, max: 100)

### Create Evaluation
```http
POST /discipline/evaluations
Content-Type: application/json

{
  "nik": "EMP001",
  "department": "001",
  "month": "2026-01-01",
  "kemampuan_kerja": "A",
  "kecerdasan_kerja": "A",
  "qualitas_kerja": "B",
  "disiplin_kerja": "A",
  "kepatuhan_kerja": "A",
  "lembur": "A",
  "efektifitas_kerja": "B",
  "relawan": "A",
  "integritas": "A",
  "alpha": 0,
  "telat": 1,
  "sakit": 0,
  "izin": 0
}
```

### Get Evaluation
```http
GET /discipline/evaluations/{id}
```

### Update Evaluation
```http
PUT /discipline/evaluations/{id}
Content-Type: application/json

{
  "qualitas_kerja": "A",
  "telat": 0
}
```

### Approve as Department Head
```http
POST /discipline/approve/dept-head
Content-Type: application/json

{
  "department": "001",
  "month": 1,
  "year": 2026,
  "lock_data": true
}
```

### Approve as General Manager
```http
POST /discipline/approve/gm
Content-Type: application/json

{
  "department": "001",
  "month": 1,
  "year": 2026
}
```

### Reject as Department Head
```http
POST /discipline/reject/dept-head
Content-Type: application/json

{
  "department": "001",
  "month": 1,
  "year": 2026,
  "remark": "Needs review"
}
```

### Export to Excel
```http
GET /discipline/export?month=1&year=2026&type=full
```

**Query Parameters:**
- `month` - Month (1-12, required)
- `year` - Year (optional, defaults to current year)
- `type` - Export type: `full` or `jpayroll` (optional, defaults to full)

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {...}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {...}
}
```

### Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

---

## HTTP Status Codes

- `200 OK` - Successful GET/PUT request
- `201 Created` - Successful POST request
- `400 Bad Request` - Invalid request data
- `401 Unauthorized` - Missing or invalid authentication token
- `403 Forbidden` - Authenticated but not authorized
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation failed
- `500 Internal Server Error` - Server error

---

## Rate Limiting

- **Limit:** 60 requests per minute per user/IP
- **Headers:** Rate limit info included in response headers
  - `X-RateLimit-Limit` - Requests allowed per minute
  - `X-RateLimit-Remaining` - Requests remaining
  - `Retry-After` - Seconds to wait if rate limited

---

## Notes

- All dates should be in `YYYY-MM-DD` format
- All date-times are returned in ISO 8601 format
- Pagination uses standard Laravel pagination format
- Scores (A/B/C/D) correspond to: A=4, B=3, C=2, D=1

---

**Version:** 1.0  
**Last Updated:** 2026-01-08
