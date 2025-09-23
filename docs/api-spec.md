# CognitPro AI Prompt Marketplace - API Specification

## 1. Introduction

This document provides a comprehensive specification for the RESTful API of the AI Prompt Marketplace. It details all endpoints, request/response formats, authentication mechanisms, and error handling for the platform.

## 2. API Overview

### 2.1 Base URL
```
https://api.cognitpro.com/v1
```

### 2.2 Authentication
- Bearer token authentication using Laravel Sanctum
- Tokens generated on successful login
- Tokens must be included in the Authorization header for protected endpoints

### 2.3 Rate Limiting
- 60 requests per minute for authenticated users
- 30 requests per minute for unauthenticated users
- Exceeding limits returns 429 Too Many Requests

### 2.4 Response Format
All responses are in JSON format:
```json
{
  "success": true,
  "data": {},
  "message": "Success message (optional)"
}
```

Error responses:
```json
{
  "success": false,
  "message": "Error description",
  "errors": {} // Validation errors (optional)
}
```

## 3. Authentication Endpoints

### 3.1 Register User
**POST** `/auth/register`

#### Request
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "buyer" // or "seller"
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "buyer",
      "created_at": "2023-01-01T00:00:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz"
  },
  "message": "Registration successful"
}
```

#### Status Codes
- `201 Created` - Registration successful
- `422 Unprocessable Entity` - Validation errors

### 3.2 Login User
**POST** `/auth/login`

#### Request
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "buyer",
      "created_at": "2023-01-01T00:00:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz"
  },
  "message": "Login successful"
}
```

#### Status Codes
- `200 OK` - Login successful
- `401 Unauthorized` - Invalid credentials
- `422 Unprocessable Entity` - Validation errors

### 3.3 Logout User
**POST** `/auth/logout`

#### Headers
```
Authorization: Bearer <token>
```

#### Response
```json
{
  "success": true,
  "message": "Logout successful"
}
```

#### Status Codes
- `200 OK` - Logout successful
- `401 Unauthorized` - Invalid token

### 3.4 Get Authenticated User
**GET** `/auth/user`

#### Headers
```
Authorization: Bearer <token>
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "buyer",
    "email_verified_at": null,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  }
}
```

#### Status Codes
- `200 OK` - User data retrieved
- `401 Unauthorized` - Invalid token

## 4. User Endpoints

### 4.1 Get User Profile
**GET** `/users/{id}`

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "role": "seller",
    "bio": "AI prompt engineer with 5 years of experience",
    "location": "San Francisco, CA",
    "website": "https://johndoe.com",
    "rating": 4.8,
    "review_count": 124,
    "prompt_count": 42,
    "member_since": "2023-01-01T00:00:00.000000Z"
  }
}
```

#### Status Codes
- `200 OK` - Profile retrieved
- `404 Not Found` - User not found

### 4.2 Update User Profile
**PUT** `/users/{id}`

#### Headers
```
Authorization: Bearer <token>
```

#### Request
```json
{
  "name": "John Doe Updated",
  "bio": "Senior AI prompt engineer",
  "location": "New York, NY",
  "website": "https://johnupdated.com"
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe Updated",
    "role": "seller",
    "bio": "Senior AI prompt engineer",
    "location": "New York, NY",
    "website": "https://johnupdated.com",
    "rating": 4.8,
    "review_count": 124,
    "prompt_count": 42
  },
  "message": "Profile updated successfully"
}
```

#### Status Codes
- `200 OK` - Profile updated
- `401 Unauthorized` - Not authorized
- `403 Forbidden` - Not allowed to update this profile
- `422 Unprocessable Entity` - Validation errors

### 4.3 Get User's Prompts
**GET** `/users/{id}/prompts`

#### Query Parameters
- `page` (integer, optional) - Page number
- `per_page` (integer, optional, default: 15) - Items per page

#### Response
```json
{
  "success": true,
  "data": {
    "prompts": [
      {
        "id": 1,
        "title": "Creative Writing Prompt",
        "description": "A prompt for generating creative stories",
        "excerpt": "Generate a short story about...",
        "price": 9.99,
        "price_type": "fixed",
        "category": {
          "id": 1,
          "name": "Creative Writing"
        },
        "rating": 4.7,
        "review_count": 23,
        "purchase_count": 142,
        "created_at": "2023-01-15T00:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 15,
      "total": 42
    }
  }
}
```

#### Status Codes
- `200 OK` - Prompts retrieved
- `404 Not Found` - User not found

## 5. Prompt Endpoints

### 5.1 List Prompts
**GET** `/prompts`

#### Query Parameters
- `search` (string, optional) - Search term
- `category` (integer, optional) - Category ID
- `tag` (string, optional) - Tag slug
- `min_price` (number, optional) - Minimum price
- `max_price` (number, optional) - Maximum price
- `price_type` (string, optional) - "fixed", "pay_what_you_want", or "free"
- `sort` (string, optional) - "newest", "popular", "rating", "price_low", "price_high"
- `page` (integer, optional) - Page number
- `per_page` (integer, optional, default: 15) - Items per page

#### Response
```json
{
  "success": true,
  "data": {
    "prompts": [
      {
        "id": 1,
        "uuid": "a1b2c3d4-e5f6-7890-g1h2-i3j4k5l6m7n8",
        "title": "Creative Writing Prompt",
        "description": "A prompt for generating creative stories",
        "excerpt": "Generate a short story about...",
        "price": 9.99,
        "price_type": "fixed",
        "category": {
          "id": 1,
          "name": "Creative Writing"
        },
        "tags": [
          {
            "id": 1,
            "name": "storytelling"
          },
          {
            "id": 2,
            "name": "fiction"
          }
        ],
        "seller": {
          "id": 2,
          "name": "Jane Smith"
        },
        "rating": 4.7,
        "review_count": 23,
        "purchase_count": 142,
        "is_purchased": false,
        "created_at": "2023-01-15T00:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 10,
      "per_page": 15,
      "total": 150
    }
  }
}
```

#### Status Codes
- `200 OK` - Prompts retrieved

### 5.2 Get Prompt
**GET** `/prompts/{id}`

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "uuid": "a1b2c3d4-e5f6-7890-g1h2-i3j4k5l6m7n8",
    "title": "Creative Writing Prompt",
    "description": "A prompt for generating creative stories",
    "excerpt": "Generate a short story about...",
    "content": "This is the full prompt content visible only to purchasers...",
    "price": 9.99,
    "price_type": "fixed",
    "category": {
      "id": 1,
      "name": "Creative Writing",
      "description": "Prompts for creative writing"
    },
    "tags": [
      {
        "id": 1,
        "name": "storytelling"
      },
      {
        "id": 2,
        "name": "fiction"
      }
    ],
    "seller": {
      "id": 2,
      "name": "Jane Smith",
      "rating": 4.8,
      "review_count": 124
    },
    "rating": 4.7,
    "review_count": 23,
    "purchase_count": 142,
    "is_purchased": false,
    "created_at": "2023-01-15T00:00:00.000000Z",
    "updated_at": "2023-01-20T00:00:00.000000Z"
  }
}
```

#### Status Codes
- `200 OK` - Prompt retrieved
- `404 Not Found` - Prompt not found

### 5.3 Create Prompt
**POST** `/prompts`

#### Headers
```
Authorization: Bearer <token>
```

#### Request
```json
{
  "title": "New Creative Prompt",
  "description": "Description of the prompt",
  "content": "Full prompt content",
  "excerpt": "Short excerpt for preview",
  "category_id": 1,
  "price_type": "fixed",
  "price": 14.99,
  "tags": ["tag1", "tag2"]
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 2,
    "uuid": "b2c3d4e5-f6g7-8901-h2i3-j4k5l6m7n8o9",
    "title": "New Creative Prompt",
    "description": "Description of the prompt",
    "excerpt": "Short excerpt for preview",
    "content": "Full prompt content",
    "price": 14.99,
    "price_type": "fixed",
    "status": "draft",
    "category": {
      "id": 1,
      "name": "Creative Writing"
    },
    "tags": [
      {
        "id": 3,
        "name": "tag1"
      },
      {
        "id": 4,
        "name": "tag2"
      }
    ],
    "seller": {
      "id": 1,
      "name": "John Doe"
    },
    "created_at": "2023-02-01T00:00:00.000000Z"
  },
  "message": "Prompt created successfully"
}
```

#### Status Codes
- `201 Created` - Prompt created
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized to create prompts
- `422 Unprocessable Entity` - Validation errors

### 5.4 Update Prompt
**PUT** `/prompts/{id}`

#### Headers
```
Authorization: Bearer <token>
```

#### Request
```json
{
  "title": "Updated Prompt Title",
  "description": "Updated description",
  "content": "Updated content",
  "excerpt": "Updated excerpt",
  "category_id": 2,
  "price_type": "pay_what_you_want",
  "price": 7.99,
  "tags": ["updated-tag1", "updated-tag2"]
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 2,
    "uuid": "b2c3d4e5-f6g7-8901-h2i3-j4k5l6m7n8o9",
    "title": "Updated Prompt Title",
    "description": "Updated description",
    "excerpt": "Updated excerpt",
    "content": "Updated content",
    "price": 7.99,
    "price_type": "pay_what_you_want",
    "status": "draft",
    "category": {
      "id": 2,
      "name": "Business Writing"
    },
    "tags": [
      {
        "id": 5,
        "name": "updated-tag1"
      },
      {
        "id": 6,
        "name": "updated-tag2"
      }
    ],
    "seller": {
      "id": 1,
      "name": "John Doe"
    },
    "updated_at": "2023-02-02T00:00:00.000000Z"
  },
  "message": "Prompt updated successfully"
}
```

#### Status Codes
- `200 OK` - Prompt updated
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized to update this prompt
- `404 Not Found` - Prompt not found
- `422 Unprocessable Entity` - Validation errors

### 5.5 Delete Prompt
**DELETE** `/prompts/{id}`

#### Headers
```
Authorization: Bearer <token>
```

#### Response
```json
{
  "success": true,
  "message": "Prompt deleted successfully"
}
```

#### Status Codes
- `200 OK` - Prompt deleted
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized to delete this prompt
- `404 Not Found` - Prompt not found

### 5.6 Publish Prompt
**POST** `/prompts/{id}/publish`

#### Headers
```
Authorization: Bearer <token>
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 2,
    "status": "published",
    "published_at": "2023-02-02T10:00:00.000000Z"
  },
  "message": "Prompt published successfully"
}
```

#### Status Codes
- `200 OK` - Prompt published
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized to publish this prompt
- `404 Not Found` - Prompt not found

### 5.7 Get Prompt Content
**GET** `/prompts/{id}/content`

### 5.8 Get Prompt Version History
**GET** `/prompts/{id}/versions`

#### Response
```json
{
  "success": true,
  "data": {
    "versions": [
      {
        "id": 3,
        "version_number": 3,
        "title": "Updated Creative Writing Prompt",
        "description": "An improved prompt for generating creative stories",
        "release_notes": "Added more examples and improved structure",
        "created_by": {
          "id": 2,
          "name": "Jane Smith"
        },
        "created_at": "2023-02-05T10:00:00.000000Z"
      },
      {
        "id": 2,
        "version_number": 2,
        "title": "Creative Writing Prompt v2",
        "description": "A prompt for generating creative stories",
        "release_notes": "Fixed typos and improved clarity",
        "created_by": {
          "id": 2,
          "name": "Jane Smith"
        },
        "created_at": "2023-01-25T14:30:00.000000Z"
      },
      {
        "id": 1,
        "version_number": 1,
        "title": "Creative Writing Prompt",
        "description": "A prompt for generating creative stories",
        "release_notes": "Initial version",
        "created_by": {
          "id": 2,
          "name": "Jane Smith"
        },
        "created_at": "2023-01-15T00:00:00.000000Z"
      }
    ]
  }
}
```

#### Status Codes
- `200 OK` - Version history retrieved
- `404 Not Found` - Prompt not found

### 5.9 Get Specific Prompt Version
**GET** `/prompts/{id}/versions/{version_id}`

#### Response
```json
{
  "success": true,
  "data": {
    "id": 2,
    "version_number": 2,
    "title": "Creative Writing Prompt v2",
    "description": "A prompt for generating creative stories",
    "content": "This is the full prompt content for version 2...",
    "excerpt": "Generate a short story about...",
    "price": 9.99,
    "price_type": "fixed",
    "category": {
      "id": 1,
      "name": "Creative Writing"
    },
    "release_notes": "Fixed typos and improved clarity",
    "created_by": {
      "id": 2,
      "name": "Jane Smith"
    },
    "created_at": "2023-01-25T14:30:00.000000Z"
  }
}
```

#### Status Codes
- `200 OK` - Version retrieved
- `404 Not Found` - Prompt or version not found

### 5.10 Create New Prompt Version
**POST** `/prompts/{id}/versions`

#### Headers
```
Authorization: Bearer <token>
```

#### Request
```json
{
  "title": "Updated Creative Writing Prompt",
  "description": "An improved prompt for generating creative stories",
  "content": "This is the updated full prompt content...",
  "excerpt": "Generate an amazing short story about...",
  "category_id": 1,
  "price_type": "fixed",
  "price": 12.99,
  "release_notes": "Major improvements with new examples"
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 3,
    "version_number": 3,
    "title": "Updated Creative Writing Prompt",
    "description": "An improved prompt for generating creative stories",
    "content": "This is the updated full prompt content...",
    "excerpt": "Generate an amazing short story about...",
    "price": 12.99,
    "price_type": "fixed",
    "status": "published",
    "category": {
      "id": 1,
      "name": "Creative Writing"
    },
    "release_notes": "Major improvements with new examples",
    "created_by": {
      "id": 2,
      "name": "Jane Smith"
    },
    "created_at": "2023-02-05T10:00:00.000000Z"
  },
  "message": "New prompt version created successfully"
}
```

#### Status Codes
- `201 Created` - New version created
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized to update this prompt
- `404 Not Found` - Prompt not found
- `422 Unprocessable Entity` - Validation errors

### 5.11 Rollback to Previous Version
**POST** `/prompts/{id}/rollback/{version_id}`

#### Headers
```
Authorization: Bearer <token>
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "version_number": 4,
    "title": "Creative Writing Prompt",
    "description": "A prompt for generating creative stories",
    "content": "This is the full prompt content from version 1...",
    "excerpt": "Generate a short story about...",
    "price": 9.99,
    "price_type": "fixed",
    "status": "published",
    "category": {
      "id": 1,
      "name": "Creative Writing"
    },
    "release_notes": "Rollback to version 1",
    "created_by": {
      "id": 2,
      "name": "Jane Smith"
    },
    "created_at": "2023-02-05T11:00:00.000000Z"
  },
  "message": "Prompt rolled back to version 1 successfully"
}
```

#### Status Codes
- `201 Created` - Rollback completed
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized to update this prompt
- `404 Not Found` - Prompt or version not found

#### Headers
```
Authorization: Bearer <token>
```

#### Response
```json
{
  "success": true,
  "data": {
    "content": "This is the full prompt content only visible to purchasers...",
    "is_purchased": true
  }
}
```

#### Status Codes
- `200 OK` - Content retrieved
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not purchased and not free
- `404 Not Found` - Prompt not found

## 6. Category Endpoints

### 6.1 List Categories
**GET** `/categories`

#### Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Creative Writing",
      "slug": "creative-writing",
      "description": "Prompts for creative writing",
      "prompt_count": 124,
      "icon": "pen-tool",
      "color": "#FF6B6B"
    },
    {
      "id": 2,
      "name": "Business",
      "slug": "business",
      "description": "Prompts for business applications",
      "prompt_count": 87,
      "icon": "briefcase",
      "color": "#4ECDC4"
    }
  ]
}
```

#### Status Codes
- `200 OK` - Categories retrieved

### 6.2 Get Category
**GET** `/categories/{id}`

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Creative Writing",
    "slug": "creative-writing",
    "description": "Prompts for creative writing",
    "parent_id": null,
    "prompt_count": 124,
    "icon": "pen-tool",
    "color": "#FF6B6B",
    "subcategories": [
      {
        "id": 3,
        "name": "Fiction",
        "slug": "fiction",
        "prompt_count": 67
      }
    ]
  }
}
```

#### Status Codes
- `200 OK` - Category retrieved
- `404 Not Found` - Category not found

### 6.3 Get Prompts by Category
**GET** `/categories/{id}/prompts`

#### Query Parameters
- `page` (integer, optional) - Page number
- `per_page` (integer, optional, default: 15) - Items per page

#### Response
```json
{
  "success": true,
  "data": {
    "category": {
      "id": 1,
      "name": "Creative Writing",
      "description": "Prompts for creative writing"
    },
    "prompts": [
      {
        "id": 1,
        "title": "Creative Writing Prompt",
        "description": "A prompt for generating creative stories",
        "excerpt": "Generate a short story about...",
        "price": 9.99,
        "price_type": "fixed",
        "seller": {
          "id": 2,
          "name": "Jane Smith"
        },
        "rating": 4.7,
        "review_count": 23,
        "purchase_count": 142,
        "created_at": "2023-01-15T00:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 75
    }
  }
}
```

#### Status Codes
- `200 OK` - Prompts retrieved
- `404 Not Found` - Category not found

## 7. Tag Endpoints

### 7.1 List Tags
**GET** `/tags`

#### Query Parameters
- `search` (string, optional) - Search term
- `page` (integer, optional) - Page number
- `per_page` (integer, optional, default: 15) - Items per page

#### Response
```json
{
  "success": true,
  "data": {
    "tags": [
      {
        "id": 1,
        "name": "storytelling",
        "slug": "storytelling",
        "prompt_count": 42
      },
      {
        "id": 2,
        "name": "fiction",
        "slug": "fiction",
        "prompt_count": 38
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 15,
      "total": 42
    }
  }
}
```

#### Status Codes
- `200 OK` - Tags retrieved

### 7.2 Get Tag
**GET** `/tags/{id}`

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "storytelling",
    "slug": "storytelling",
    "description": "Prompts related to storytelling techniques",
    "prompt_count": 42
  }
}
```

#### Status Codes
- `200 OK` - Tag retrieved
- `404 Not Found` - Tag not found

### 7.3 Get Prompts by Tag
**GET** `/tags/{id}/prompts`

#### Query Parameters
- `page` (integer, optional) - Page number
- `per_page` (integer, optional, default: 15) - Items per page

#### Response
```json
{
  "success": true,
  "data": {
    "tag": {
      "id": 1,
      "name": "storytelling",
      "description": "Prompts related to storytelling techniques"
    },
    "prompts": [
      {
        "id": 1,
        "title": "Creative Writing Prompt",
        "description": "A prompt for generating creative stories",
        "excerpt": "Generate a short story about...",
        "price": 9.99,
        "price_type": "fixed",
        "category": {
          "id": 1,
          "name": "Creative Writing"
        },
        "seller": {
          "id": 2,
          "name": "Jane Smith"
        },
        "rating": 4.7,
        "review_count": 23,
        "purchase_count": 142,
        "created_at": "2023-01-15T00:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 2,
      "per_page": 15,
      "total": 25
    }
  }
}
```

#### Status Codes
- `200 OK` - Prompts retrieved
- `404 Not Found` - Tag not found

## 8. Purchase Endpoints

### 8.1 Create Purchase
**POST** `/purchases`

#### Headers
```
Authorization: Bearer <token>
```

#### Request
```json
{
  "prompt_id": 1,
  "price_paid": 9.99,
  "payment_gateway": "stripe"
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "uuid": "c3d4e5f6-g7h8-9012-i3j4-k5l6m7n8o9p0",
    "prompt_id": 1,
    "price_paid": 9.99,
    "payment_gateway": "stripe",
    "transaction_id": "txn_1234567890",
    "purchased_at": "2023-02-02T10:30:00.000000Z"
  },
  "message": "Purchase successful"
}
```

#### Status Codes
- `201 Created` - Purchase created
- `401 Unauthorized` - Not authenticated
- `404 Not Found` - Prompt not found
- `422 Unprocessable Entity` - Validation errors

### 8.2 List User Purchases
**GET** `/purchases`

#### Headers
```
Authorization: Bearer <token>
```

#### Query Parameters
- `page` (integer, optional) - Page number
- `per_page` (integer, optional, default: 15) - Items per page

#### Response
```json
{
  "success": true,
  "data": {
    "purchases": [
      {
        "id": 1,
        "uuid": "c3d4e5f6-g7h8-9012-i3j4-k5l6m7n8o9p0",
        "prompt": {
          "id": 1,
          "title": "Creative Writing Prompt",
          "seller": {
            "id": 2,
            "name": "Jane Smith"
          }
        },
        "price_paid": 9.99,
        "payment_gateway": "stripe",
        "purchased_at": "2023-02-02T10:30:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 5
    }
  }
}
```

#### Status Codes
- `200 OK` - Purchases retrieved
- `401 Unauthorized` - Not authenticated

### 8.3 Get Purchase
**GET** `/purchases/{id}`

#### Headers
```
Authorization: Bearer <token>
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "uuid": "c3d4e5f6-g7h8-9012-i3j4-k5l6m7n8o9p0",
    "prompt": {
      "id": 1,
      "title": "Creative Writing Prompt",
      "description": "A prompt for generating creative stories",
      "content": "This is the full prompt content...",
      "seller": {
        "id": 2,
        "name": "Jane Smith"
      }
    },
    "price_paid": 9.99,
    "payment_gateway": "stripe",
    "transaction_id": "txn_1234567890",
    "purchased_at": "2023-02-02T10:30:00.000000Z"
  }
}
```

#### Status Codes
- `200 OK` - Purchase retrieved
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized to view this purchase
- `404 Not Found` - Purchase not found

## 9. Review Endpoints

### 9.1 Create Review
**POST** `/reviews`

#### Headers
```
Authorization: Bearer <token>
```

#### Request
```json
{
  "prompt_id": 1,
  "rating": 5,
  "title": "Excellent Prompt!",
  "review_text": "This prompt helped me generate amazing content..."
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "uuid": "d4e5f6g7-h8i9-0123-j4k5-l6m7n8o9p0q1",
    "prompt_id": 1,
    "rating": 5,
    "title": "Excellent Prompt!",
    "review_text": "This prompt helped me generate amazing content...",
    "verified_purchase": true,
    "created_at": "2023-02-03T14:30:00.000000Z"
  },
  "message": "Review submitted successfully"
}
```

#### Status Codes
- `201 Created` - Review created
- `401 Unauthorized` - Not authenticated
- `404 Not Found` - Prompt not found
- `422 Unprocessable Entity` - Validation errors

### 9.2 Get Prompt Reviews
**GET** `/prompts/{id}/reviews`

#### Query Parameters
- `page` (integer, optional) - Page number
- `per_page` (integer, optional, default: 15) - Items per page

#### Response
```json
{
  "success": true,
  "data": {
    "reviews": [
      {
        "id": 1,
        "uuid": "d4e5f6g7-h8i9-0123-j4k5-l6m7n8o9p0q1",
        "user": {
          "id": 1,
          "name": "John Doe"
        },
        "rating": 5,
        "title": "Excellent Prompt!",
        "review_text": "This prompt helped me generate amazing content...",
        "verified_purchase": true,
        "helpful_count": 12,
        "created_at": "2023-02-03T14:30:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 8
    }
  }
}
```

#### Status Codes
- `200 OK` - Reviews retrieved
- `404 Not Found` - Prompt not found

### 9.3 Update Review
**PUT** `/reviews/{id}`

#### Headers
```
Authorization: Bearer <token>
```

#### Request
```json
{
  "rating": 4,
  "title": "Very Good Prompt",
  "review_text": "This prompt was very helpful for my project..."
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "uuid": "d4e5f6g7-h8i9-0123-j4k5-l6m7n8o9p0q1",
    "prompt_id": 1,
    "rating": 4,
    "title": "Very Good Prompt",
    "review_text": "This prompt was very helpful for my project...",
    "verified_purchase": true,
    "updated_at": "2023-02-04T09:15:00.000000Z"
  },
  "message": "Review updated successfully"
}
```

#### Status Codes
- `200 OK` - Review updated
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized to update this review
- `404 Not Found` - Review not found
- `422 Unprocessable Entity` - Validation errors

### 9.4 Delete Review
**DELETE** `/reviews/{id}`

#### Headers
```
Authorization: Bearer <token>
```

#### Response
```json
{
  "success": true,
  "message": "Review deleted successfully"
}
```

#### Status Codes
- `200 OK` - Review deleted
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized to delete this review
- `404 Not Found` - Review not found

## 10. Payment Endpoints

### 10.1 Process Payment
**POST** `/payments`

#### Headers
```
Authorization: Bearer <token>
```

#### Request
```json
{
  "amount": 9.99,
  "currency": "USD",
  "payment_gateway": "stripe",
  "metadata": {
    "description": "Purchase of Creative Writing Prompt"
  }
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "uuid": "e5f6g7h8-i9j0-1234-k5l6-m7n8o9p0q1r2",
    "amount": 9.99,
    "currency": "USD",
    "payment_gateway": "stripe",
    "status": "pending",
    "transaction_id": "pi_1234567890",
    "client_secret": "pi_1234567890_secret_abcdefghijklmnop"
  }
}
```

#### Status Codes
- `201 Created` - Payment initiated
- `401 Unauthorized` - Not authenticated
- `422 Unprocessable Entity` - Validation errors

### 10.2 Get Payment Status
**GET** `/payments/{id}`

#### Headers
```
Authorization: Bearer <token>
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "uuid": "e5f6g7h8-i9j0-1234-k5l6-m7n8o9p0q1r2",
    "amount": 9.99,
    "currency": "USD",
    "payment_gateway": "stripe",
    "status": "completed",
    "transaction_id": "pi_1234567890",
    "processed_at": "2023-02-02T10:32:00.000000Z"
  }
}
```

#### Status Codes
- `200 OK` - Payment status retrieved
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized to view this payment
- `404 Not Found` - Payment not found

## 11. Payout Endpoints

### 11.1 Get User Payouts
**GET** `/payouts`

#### Headers
```
Authorization: Bearer <token>
```

#### Query Parameters
- `page` (integer, optional) - Page number
- `per_page` (integer, optional, default: 15) - Items per page

#### Response
```json
{
  "success": true,
  "data": {
    "payouts": [
      {
        "id": 1,
        "uuid": "f6g7h8i9-j0k1-2345-l6m7-n8o9p0q1r2s3",
        "amount": 79.92,
        "currency": "USD",
        "status": "processed",
        "payoneer_transaction_id": "txn_9876543210",
        "scheduled_for": "2023-02-15",
        "processed_at": "2023-02-15T10:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 3
    }
  }
}
```

#### Status Codes
- `200 OK` - Payouts retrieved
- `401 Unauthorized` - Not authenticated

## 12. Admin Endpoints

### 12.1 List Users
**GET** `/admin/users`

#### Headers
```
Authorization: Bearer <token>
```

#### Query Parameters
- `role` (string, optional) - Filter by role
- `search` (string, optional) - Search term
- `page` (integer, optional) - Page number
- `per_page` (integer, optional, default: 15) - Items per page

#### Response
```json
{
  "success": true,
  "data": {
    "users": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "seller",
        "email_verified_at": "2023-01-01T00:00:00.000000Z",
        "created_at": "2023-01-01T00:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 75
    }
  }
}
```

#### Status Codes
- `200 OK` - Users retrieved
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized (admin only)

### 12.2 Update User Role
**PUT** `/admin/users/{id}/role`

#### Headers
```
Authorization: Bearer <token>
```

#### Request
```json
{
  "role": "admin"
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "admin",
    "updated_at": "2023-02-05T14:30:00.000000Z"
  },
  "message": "User role updated successfully"
}
```

#### Status Codes
- `200 OK` - User role updated
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized (admin only)
- `404 Not Found` - User not found
- `422 Unprocessable Entity` - Validation errors

### 12.3 List Flagged Content
**GET** `/admin/flagged-content`

#### Headers
```
Authorization: Bearer <token>
```

#### Response
```json
{
  "success": true,
  "data": {
    "flagged_prompts": [
      {
        "id": 5,
        "title": "Flagged Prompt",
        "description": "This prompt was flagged by users",
        "flag_count": 3,
        "created_at": "2023-01-20T00:00:00.000000Z"
      }
    ],
    "flagged_reviews": [
      {
        "id": 2,
        "review_text": "Inappropriate review content",
        "flag_count": 2,
        "created_at": "2023-02-01T00:00:00.000000Z"
      }
    ]
  }
}
```

#### Status Codes
- `200 OK` - Flagged content retrieved
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized (admin only)

### 12.4 Update Platform Settings
**PUT** `/admin/settings`

#### Headers
```
Authorization: Bearer <token>
```

#### Request
```json
{
  "commission_percentage": 20,
  "commission_flat_fee": 0.50,
  "minimum_payout_threshold": 25.00
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "commission_percentage": 20,
    "commission_flat_fee": 0.50,
    "minimum_payout_threshold": 25.00
  },
  "message": "Platform settings updated successfully"
}
```

#### Status Codes
- `200 OK` - Settings updated
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized (admin only)
- `422 Unprocessable Entity` - Validation errors

## 13. Error Responses

### 13.1 Common HTTP Status Codes
- `200 OK` - Successful request
- `201 Created` - Resource created successfully
- `400 Bad Request` - Invalid request
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Access denied
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation errors
- `429 Too Many Requests` - Rate limit exceeded
- `500 Internal Server Error` - Server error

### 13.2 Validation Error Format
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "name": [
      "The name field is required."
    ],
    "email": [
      "The email field is required.",
      "The email must be a valid email address."
    ]
  }
}
```

### 13.3 General Error Format
```json
{
  "success": false,
  "message": "Error description"
}
```

## 14. Webhooks

### 14.1 Payment Webhooks
Endpoints to receive payment status updates from payment gateways.

#### Stripe Webhook
**POST** `/webhooks/stripe`

#### Paddle Webhook
**POST** `/webhooks/paddle`

#### PayPal Webhook
**POST** `/webhooks/paypal`

### 14.2 Payout Webhooks
Endpoints to receive payout status updates from Payoneer.

#### Payoneer Webhook
**POST** `/webhooks/payoneer`

---

*This API specification document provides a comprehensive overview of all endpoints and their specifications for the AI Prompt Marketplace. It will be updated as new features are added or modified.*