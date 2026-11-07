# Authentication and Authorization Flow

## 1. Overview

The Online Quiz Platform API uses Laravel Sanctum for token-based authentication.

API base URL:

`http://127.0.0.1:8000/api/v1`

Protected endpoints require authentication.

## 2. User Roles

| Role | Description |
|---|---|
| Admin | Administrative access |
| Teacher | Manages own quizzes and grades essay answers |
| Student | Participates in published quizzes |

## 3. Registration

Endpoint:

`POST /api/v1/register`

Public registration creates a student account. Users cannot select the Admin or Teacher role through public registration.

## 4. Login

Endpoint:

`POST /api/v1/login`

After successful authentication, the API returns a Sanctum access token.

## 5. Protected Requests

Protected routes use Laravel's `auth:sanctum` middleware.

Required headers:

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

## 6. Current User

Endpoint:

`GET /api/v1/me`

Returns the authenticated user's profile information:

- ID
- Name
- Email
- Role
- Avatar URL

## 7. Logout

Endpoint:

`POST /api/v1/logout`

Revokes the current access token.

## 8. Avatar Upload

Endpoint:

`POST /api/v1/me/avatar`

The endpoint accepts an image using `multipart/form-data`.

Supported formats:

- JPG
- JPEG
- PNG
- WEBP

Maximum file size: 2048 KB.

The avatar is stored on Laravel's public storage disk.

## 9. Authorization Rules

### Admin
- Administrative access to platform resources.
- Can manage categories.
- Can access platform quizzes and related results.
- Can grade essay answers.

### Teacher
- Can create quizzes.
- Can manage own quizzes.
- Can manage questions and options belonging to own quizzes.
- Can view results of own quizzes.
- Can grade essay answers belonging to own quizzes.

### Student
- Can view published quizzes.
- Can participate in quizzes.
- Can submit answers to own active attempts.
- Can view personal results and answer history.
- Cannot manage categories, quizzes, questions, or options.

## 10. HTTP Status Codes

| Status | Meaning |
|---|---|
| 200 | Successful request |
| 201 | Resource created |
| 204 | Successful request without response body |
| 401 | Unauthenticated |
| 403 | Forbidden |
| 404 | Resource not found |
| 422 | Validation error |

## 11. Security

- Passwords are hashed before storage.
- Public registration assigns the student role.
- Protected endpoints require authentication.
- Resource ownership is checked for teachers and students.
- Students cannot submit awarded points directly.
- Essay grading is restricted to authorized users.
- Correct answers are excluded from API resources.
- Critical submission and grading operations use database transactions.

## 12. Authentication Flow

1. User registers or logs in.
2. API validates credentials.
3. Sanctum issues an access token after successful login.
4. Client sends the token with protected requests.
5. Laravel authenticates the user.
6. Controllers and Form Requests enforce authorization.
7. API returns the requested resource or an appropriate error.
8. Logout revokes the current access token.
