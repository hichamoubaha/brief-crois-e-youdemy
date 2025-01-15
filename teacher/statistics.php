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

// Get total courses count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM courses WHERE teacher_id = :teacher_id");
$stmt->execute(['teacher_id' => $_SESSION['user_id']]);
$total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total students count across all courses
$stmt = $db->prepare("
    SELECT COUNT(DISTINCT e.student_id) as total 
    FROM courses c 
    LEFT JOIN enrollments e ON c.id = e.course_id 
    WHERE c.teacher_id = :teacher_id
");
$stmt->execute(['teacher_id' => $_SESSION['user_id']]);
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get most popular course
$stmt = $db->prepare("
    SELECT c.title, COUNT(e.id) as enrollments 
    FROM courses c 
    LEFT JOIN enrollments e ON c.id = e.course_id 
    WHERE c.teacher_id = :teacher_id 
    GROUP BY c.id 
    ORDER BY enrollments DESC 
    LIMIT 1
");
$stmt->execute(['teacher_id' => $_SESSION['user_id']]);
$most_popular = $stmt->fetch(PDO::FETCH_ASSOC);

// Get courses by category
$stmt = $db->prepare("
    SELECT cat.name, COUNT(c.id) as count 
    FROM categories cat 
    LEFT JOIN courses c ON cat.id = c.category_id 
    WHERE c.teacher_id = :teacher_id 
    GROUP BY cat.id
");
$stmt->execute(['teacher_id' => $_SESSION['user_id']]);
$courses_by_category = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Statistics - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg mb-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <a href="courses.php" class="flex items-center py-4 px-2">
                        <span class="font-semibold text-gray-500 text-lg">← Back to My Courses</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Course Statistics</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Total Courses -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Total Courses</h2>
                <p class="text-3xl font-bold text-blue-600"><?php echo $total_courses; ?></p>
            </div>

            <!-- Total Students -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Total Students</h2>
                <p class="text-3xl font-bold text-green-600"><?php echo $total_students; ?></p>
            </div>

            <!-- Most Popular Course -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Most Popular Course</h2>
                <?php if ($most_popular): ?>
                    <p class="text-lg font-medium"><?php echo htmlspecialchars($most_popular['title']); ?></p>
                    <p class="text-gray-600"><?php echo $most_popular['enrollments']; ?> enrollments</p>
                <?php else: ?>
                    <p class="text-gray-600">No enrollments yet</p>
                <?php endif; ?>
            </div>

            <!-- Courses by Category -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Courses by Category</h2>
                <div class="space-y-2">
                    <?php foreach ($courses_by_category as $category): ?>
                        <div class="flex justify-between">
                            <span><?php echo htmlspecialchars($category['name']); ?></span>
                            <span class="font-medium"><?php echo $category['count']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

