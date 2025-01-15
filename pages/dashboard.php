<?php
require_once '../config/config.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$course = new Course($db);

$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <div>
                        <a href="../index.php" class="flex items-center py-4 px-2">
                            <span class="font-semibold text-gray-500 text-lg">Youdemy</span>
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="logout.php" class="py-2 px-4 bg-red-500 text-white rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Dashboard</h1>

        <?php if($role === 'admin'): ?>
            <!-- Admin Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">User Management</h2>
                    <a href="../admin/users.php" class="text-blue-500 hover:text-blue-700">Manage Users →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Course Management</h2>
                    <a href="../admin/courses.php" class="text-blue-500 hover:text-blue-700">Manage Courses →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Category Management</h2>
                    <a href="../admin/categories.php" class="text-blue-500 hover:text-blue-700">Manage Categories →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Tags Management</h2>
                    <a href="../admin/tags.php" class="text-blue-500 hover:text-blue-700">Manage Tags →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Statistics Management</h2>
                    <a href="../admin/statistics.php" class="text-blue-500 hover:text-blue-700">View Statistics →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Course Approval</h2>
                    <a href="../admin/approve-courses.php" class="text-blue-500 hover:text-blue-700">Approve Courses →</a>
                </div>
            </div>

        <?php elseif($role === 'teacher'): ?>
            <!-- Teacher Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">My Courses</h2>
                    <a href="../teacher/courses.php" class="text-blue-500 hover:text-blue-700">View My Courses →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Create New Course</h2>
                    <a href="../teacher/create-course.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create Course</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Course Statistics</h2>
                    <a href="../teacher/statistics.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">View Statistics</a>
                </div>
            </div>

        <?php else: ?>
            <!-- Student Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">My Courses</h2>
                    <a href="../student/my-courses.php" class="text-blue-500 hover:text-blue-700">View My Courses →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">more courses</h2>
                    <a href="courses.php" class="text-blue-500 hover:text-blue-700">Discover →</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

