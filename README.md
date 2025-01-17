# Youdemy Platform

## Project Description
The Youdemy platform aims to revolutionize online learning by providing an interactive and personalized system for students and teachers.

## Features

### Front Office

#### Visitor
- Access to the course catalog with pagination.
- Search for courses using keywords.
- Create an account with a role selection (Student or Teacher).

#### Student
- View the course catalog.
- Search and view course details (description, content, teacher, etc.).
- Enroll in a course after authentication.
- Access a "My Courses" section showing enrolled courses.

#### Teacher
- Add new courses with details such as:
  - Title, description, content (video or document), tags, and category.
- Manage courses:
  - Modify, delete, and view enrollments.
- Access a "Statistics" section with information like:
  - Number of students enrolled, total courses, etc.

### Back Office

#### Administrator
- Validate teacher accounts.
- Manage users:
  - Activate, suspend, or delete accounts.
- Manage content:
  - Courses, categories, and tags.
  - Bulk insert tags for efficiency.
- Access global statistics:
  - Total number of courses.
  - Distribution by category.
  - Course with the highest number of students.
  - Top 3 teachers.

### Cross-Functional Features
- Courses can have multiple tags (many-to-many relationship).
- Polymorphism applied in methods such as adding and displaying courses.
- Authentication and authorization system to secure sensitive routes.
- Access control to ensure users only access features relevant to their roles.

## Technical Requirements

- Adherence to OOP principles:
  - Encapsulation, inheritance, polymorphism.
- Relational database with relationships:
  - One-to-many and many-to-many.
- Use PHP sessions for managing connected users.
- User data validation to ensure security.

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/hichamoubaha/brief-crois-e-youdemy.git
   ```
2. Navigate to the project directory:
   ```bash
   cd youdemy-platform
   ```
3. Set up the database:
   - Import the provided SQL file into your MySQL database.
   - Update the database configuration in `config/database.php`.

4. Start the PHP server:
   ```bash
   php -S localhost:8000
   ```

5. Open the application in your browser:
   ```
   http://localhost:8000
   ```


