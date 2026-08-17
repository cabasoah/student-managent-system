# Student Management System

A comprehensive web-based school management platform built with **Laravel 9** that enables streamlined administration of academic operations, student records, assessments, and real-time communication.

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [Project Structure](#project-structure)
- [Key Modules](#key-modules)
- [Database Setup](#database-setup)
- [Testing](#testing)
- [Development](#development)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)

## Features

### Academic Management
- **School Sessions & Semesters**: Manage academic calendar and organizational units
- **Classes & Sections**: Organize students into classes and sections
- **Courses & Syllabi**: Create and manage course curriculum
- **Academic Settings**: Configure school-wide academic parameters

### User Management
- **Students**: Complete student profiles with academic tracking
- **Teachers/Lecturers**: Teacher account management with invitations
- **Parent Information**: Track student guardians and family information
- **User Roles & Permissions**: Granular role-based access control (RBAC)

### Assessment & Grading
- **Examinations**: Create and manage exam schedules
- **Quizzes**: Interactive quiz system with question management
- **Assignments**: Assignment creation and submission tracking
- **Marks & Grading**: Record and calculate student marks
- **Grade Rules**: Configurable grading systems and promotion rules
- **Results Export**: Generate and export student result reports

### Attendance & Tracking
- **Attendance Management**: Track daily student attendance
- **Academic Analytics**: Monitor student academic performance
- **Student Progress**: Real-time academic progress tracking

### Communication
- **Real-time Chat**: Classroom-based chat system with WebSocket support
- **Notices**: School announcements and notices board
- **Invitations**: Invite teachers and manage invitations
- **Real-time Notifications**: Push notifications via Pusher

### Additional Features
- **Routines**: Class and exam routines
- **Excel Import/Export**: Bulk operations for data management
- **Responsive UI**: Bootstrap 4 frontend
- **API Support**: RESTful API endpoints for integrations

## Tech Stack

### Backend
- **Framework**: Laravel 9.x
- **Language**: PHP 8.0+
- **Database**: MySQL 5.7+
- **Real-time**: Pusher & Laravel Echo
- **Broadcasting**: WebSocket support

### Frontend
- **Framework**: Bootstrap 4.6
- **Build Tool**: Laravel Mix
- **Package Manager**: npm
- **JS Libraries**: jQuery, Axios, Lodash, Popper.js

### DevOps
- **Containerization**: Docker & Docker Compose
- **Web Server**: Nginx
- **PHP**: PHP 8.2-FPM

### Key Packages
- `maatwebsite/excel` - Excel import/export
- `spatie/laravel-permission` - Role & permission management
- `barryvdh/laravel-dompdf` - PDF generation
- `pusher/pusher-php-server` - Real-time notifications
- `laravel-echo` - Real-time event broadcasting
- `stevebauman/purify` - HTML sanitization

## Prerequisites

### Local Development
- PHP 8.0 or higher
- Composer
- Node.js (14+) and npm
- MySQL 5.7 or higher

### Docker Setup
- Docker
- Docker Compose

## Installation

### Option 1: Docker Setup (Recommended)

#### 1. Clone the Repository
```bash
git clone <repository-url>
cd student-management-system
```

#### 2. Set Up Environment
```bash
cp .env.example .env
```

#### 3. Build and Start Containers
```bash
docker-compose up -d
```

#### 4. Install Dependencies
```bash
# PHP Dependencies
docker exec app composer install

# Node Dependencies
docker exec app npm install
```

#### 5. Generate Application Key
```bash
docker exec app php artisan key:generate
```

#### 6. Run Migrations & Seeders
```bash
docker exec app php artisan migrate
docker exec app php artisan db:seed
```

#### 7. Build Frontend Assets
```bash
docker exec app npm run production
```

### Option 2: Local Development Setup

#### 1. Clone the Repository
```bash
git clone <repository-url>
cd student-management-system
```

#### 2. Install Dependencies
```bash
# PHP Dependencies
composer install

# Node Dependencies
npm install
```

#### 3. Environment Configuration
```bash
cp .env.example .env
```

#### 4. Generate Application Key
```bash
php artisan key:generate
```

#### 5. Create Database
```bash
mysql -u root -p
CREATE DATABASE unifiedtransform;
EXIT;
```

#### 6. Run Migrations
```bash
php artisan migrate
```

#### 7. Seed Database (Optional)
```bash
php artisan db:seed
```

#### 8. Build Frontend Assets
```bash
npm run production
```

## Configuration

### Environment Variables

Edit `.env` file with your configuration:

### Key Configuration Files
- `config/app.php` - Application settings
- `config/database.php` - Database connections
- `config/permission.php` - Permission settings
- `config/broadcasting.php` - Broadcast driver configuration

## Running the Application

### Docker Setup

```bash
# Start services
docker-compose up -d

# View logs
docker-compose logs -f app

# Stop services
docker-compose down
```

**Access Application**: http://localhost:8080

### Local Development

```bash
# Start development server
php artisan serve

# Watch frontend assets
npm run watch
```

**Access Application**: http://localhost:8000

## Project Structure

```
├── app/
│   ├── Console/          # Artisan commands
│   ├── Events/           # Event classes
│   ├── Exceptions/       # Custom exceptions
│   ├── Exports/          # Excel export classes
│   ├── Helpers/          # Helper functions
│   ├── Http/
│   │   ├── Controllers/  # Route controllers
│   │   ├── Middleware/   # HTTP middleware
│   │   ├── Requests/     # Form requests
│   │   └── Kernel.php    # HTTP kernel
│   ├── Imports/          # Excel import classes
│   ├── Interfaces/       # Interface contracts
│   ├── Models/           # Eloquent models
│   ├── Providers/        # Service providers
│   ├── Repositories/     # Repository pattern
│   └── Traits/           # Reusable traits
├── bootstrap/            # Bootstrap files
├── config/               # Configuration files
├── database/
│   ├── factories/        # Model factories
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── public/               # Public assets
├── resources/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript
│   ├── lang/             # Language files
│   ├── sass/             # SASS files
│   └── views/            # Blade templates
├── routes/               # Route definitions
├── storage/              # Logs and cache
├── tests/                # Test suites
└── vendor/               # Composer dependencies
```

## Key Modules

### Academic Module
- **Controllers**: AcademicSettingController, SchoolClassController, CourseController, SemesterController
- **Models**: AcademicSetting, SchoolClass, Course, Semester
- **Features**: Course management, class organization, academic calendar

### Student Module
- **Controllers**: StudentQuizController, StudentAcademicInfoController, StudentParentInfoController
- **Models**: Student, StudentAcademicInfo, StudentParentInfo
- **Features**: Student profiles, academic records, parent information

### Assessment Module
- **Controllers**: QuizController, ExamController, MarkController, QuestionController
- **Models**: Quiz, Exam, Mark, Question
- **Features**: Quiz creation, exam management, grade calculation

### Teacher Module
- **Controllers**: AssignedTeacherController, LecturerInviteController
- **Models**: AssignedTeacher, Teacher
- **Features**: Teacher assignment, lecturer invitations

### Communication Module
- **Controllers**: ChatController, NoticeController, EventController
- **Models**: ClassChatMessage (Events)
- **Features**: Real-time chat, notices, announcements

### Attendance Module
- **Controllers**: AttendanceController
- **Features**: Daily attendance tracking

## Database Setup

### Migrations

Run all pending migrations:
```bash
php artisan migrate
```

Rollback migrations:
```bash
php artisan migrate:rollback
```

Reset database:
```bash
php artisan migrate:refresh
```

### Seeders

Run seeders to populate sample data:
```bash
php artisan db:seed
```

Run specific seeder:
```bash
php artisan db:seed --class=UserSeeder
```

## Testing

Run the test suite:
```bash
# Using Docker
docker exec app php ./vendor/bin/phpunit

# Local
./vendor/bin/phpunit
```

Generate coverage report:
```bash
./vendor/bin/phpunit --coverage-html coverage
```

## Development

### Frontend Development

Watch for changes and rebuild:
```bash
npm run watch
```

Hot module reloading:
```bash
npm run hot
```

Build for production:
```bash
npm run production
```

### Artisan Commands

Generate common scaffolding:
```bash
# Generate a new model with migration
php artisan make:model Student -m

# Generate a controller
php artisan make:controller StudentController

# Generate a migration
php artisan make:migration create_students_table

# Generate a seeder
php artisan make:seeder StudentSeeder
```

### Debugging

Enable Laravel Debugbar (development only):
- Already configured in dev dependencies
- Access debugbar at the bottom-right of the page in development

Clear cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

## Deployment

### Pre-Deployment Checklist
1. Set `APP_DEBUG=false` in `.env`
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Run `npm run production` for optimized assets
5. Set appropriate file permissions
6. Configure database backups
7. Set up monitoring and logging

### Production Deployment
```bash
# SSH into server
ssh user@server

# Clone repository
git clone <repository-url>
cd student-management-system

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production
npm run production

# Setup environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --force

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data .

# Start queue worker (if using queues)
php artisan queue:work &
```

### Using Docker in Production
```bash
# Build production image
docker build -t app:latest .

# Run with environment variables
docker run -d \
  --name student-app \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  app:latest
```

## API Endpoints

The application provides RESTful API endpoints. Key endpoint groups:

- `/api/students` - Student management
- `/api/courses` - Course operations
- `/api/examinations` - Exam management
- `/api/assignments` - Assignment operations
- `/api/marks` - Grade management
- `/api/attendance` - Attendance tracking

See `routes/api.php` for complete endpoint documentation.

## Contributing

1. Create a feature branch (`git checkout -b feature/AmazingFeature`)
2. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
3. Push to the branch (`git push origin feature/AmazingFeature`)
4. Open a Pull Request

Please ensure all tests pass and follow the project's coding standards.

## Code Standards

The project follows PSR-12 PHP coding standards. Run code analysis:
```bash
# Using Docker
docker exec app vendor/bin/php-cs-fixer fix --dry-run

# Local
vendor/bin/php-cs-fixer fix --dry-run
```

## Troubleshooting

### Common Issues

**Database Connection Error**
```bash
# Verify database credentials in .env
# Check if MySQL is running
# Ensure database exists
mysql -u root -p -e "SHOW DATABASES;"
```

**Permission Denied Errors**
```bash
chmod -R 775 storage bootstrap/cache
chown -R $USER:$USER .
```

**Composer Issues**
```bash
composer update
composer dump-autoload
```

**Frontend Assets Not Loading**
```bash
npm run production
php artisan config:clear
```

**Real-time Features Not Working**
- Verify Pusher credentials in `.env`
- Check BROADCAST_DRIVER is set to `pusher`
- Verify WebSocket connection in browser console

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, please:
1. Check existing issues and documentation
2. Open a new GitHub issue with detailed description
3. Contact the development team

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

---

**Last Updated**: 2026-08-17
**Laravel Version**: 9.x
**PHP Version**: 8.0+
