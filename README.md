# Online Quiz Platform

A RESTful Online Quiz Platform built with Laravel 13, PHP, MySQL, and Laravel Sanctum.

## Features

- Token-based authentication using Laravel Sanctum
- Role-based access control: Admin, Teacher, Student
- Category management
- Quiz creation and management
- Question and option management
- Multiple-choice and true/false questions
- Short-answer and essay questions
- Timed quiz attempts
- Automatic grading
- Manual essay grading
- Quiz results and leaderboard
- Student answer history
- User profile and avatar upload
- RESTful API with versioning
- Request validation and database transactions

## Technology Stack

| Technology | Purpose |
|---|---|
| PHP 8.3+ | Backend language |
| Laravel 13 | Backend framework |
| Laravel Sanctum | API authentication |
| MySQL 8 | Database |
| Eloquent ORM | Database operations |
| PHPUnit | Automated testing |

## User Roles

### Admin
- Administrative access to platform resources.
- Manage categories and access platform quizzes and results.

### Teacher
- Create and manage own quizzes.
- Manage questions and options.
- View results of own quizzes.
- Grade essay answers.

### Student
- View published quizzes.
- Participate in quizzes.
- Submit answers.
- View personal results and history.
- Manage personal profile and avatar.

## Requirements

- PHP 8.3 or higher
- Composer
- MySQL 8
- Git

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/MortazaAhmadi313/OnlineQuizPlatform.git
cd OnlineQuizPlatform
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Update the database settings in `.env`:

```env
APP_NAME="Online Quiz Platform"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=online_quiz_platform
DB_USERNAME=root
DB_PASSWORD=
```

Create the `online_quiz_platform` database in MySQL before running migrations.

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Configure public storage

```bash
php artisan storage:link
```

### 6. Start the server

```bash
php artisan serve
```

API base URL:

`http://127.0.0.1:8000/api/v1`

## API Overview

All API endpoints use the `/api/v1` prefix.

### Public Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/hello` | Welcome message |
| GET | `/greet/{name}` | Greeting |
| GET | `/ping` | API health check |
| POST | `/register` | Register a student |
| POST | `/login` | User login |

### Protected Endpoints

| Resource | Endpoint |
|---|---|
| Authentication | `/logout`, `/me`, `/me/avatar` |
| Categories | `/categories` |
| Quizzes | `/quizzes` |
| Questions | `/questions` |
| Options | `/options` |
| Attempts | `/attempts` |
| Answers | `/answers` |
| Leaderboards | `/leaderboards` |
| Users | `/users` |

Additional endpoints:

- `POST /attempts/{attempt}/submit`
- `PATCH /answers/{answer}/grade`

## Authentication

The API uses Laravel Sanctum personal access tokens.

Include the access token in protected requests:

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

## Testing

Run automated tests:

```bash
php artisan test
```

## Documentation

- [Authentication and Authorization Flow](docs/auth-flow.md)

## Project Status

Backend development milestone: `v1.0-backend`

## Author

**Mortaza Ahmadi**

Computer Science Student
Parwan University

GitHub: [MortazaAhmadi313](https://github.com/MortazaAhmadi313)

## License

Developed as an academic university project.
