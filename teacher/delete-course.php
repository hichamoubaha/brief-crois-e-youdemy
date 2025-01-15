<?php
require_once '../config/config.php';
require_once '../classes/Teacher.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$teacher = new Teacher($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $course_id = $_GET['id'];
    
    // Verify that the course belongs to the current teacher
    $stmt = $db->prepare("SELECT id FROM courses WHERE id = :course_id AND teacher_id = :teacher_id");
    $stmt->execute([
        'course_id' => $course_id,
        'teacher_id' => $_SESSION['user_id']
    ]);
    
    if ($stmt->rowCount() > 0) {
        // Course belongs to the teacher, proceed with deletion
        if ($teacher->deleteCourse($course_id)) {
            $_SESSION['success_message'] = "Course deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete the course.";
        }
    } else {
        $_SESSION['error_message'] = "You don't have permission to delete this course.";
    }
}

// Redirect back to the courses page
header('Location: courses.php');
exit;

