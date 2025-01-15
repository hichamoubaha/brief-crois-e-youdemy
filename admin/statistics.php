<?php
require_once '../config/config.php';
require_once '../classes/Admin.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$admin = new Admin($db);

$stats = $admin->getStatistics();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg mb-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <a href="../pages/dashboard.php" class="flex items-center py-4 px-2">
                        <span class="font-semibold text-gray-500 text-lg">← Back to Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Platform Statistics</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Total Courses -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Total Courses</h2>
                <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total_courses']; ?></p>
            </div>

            <!-- Most Popular Course -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Most Popular Course</h2>
                <?php if ($stats['most_popular_course']): ?>
                    <p class="text-lg font-medium"><?php echo htmlspecialchars($stats['most_popular_course']['title']); ?></p>
                    <p class="text-gray-600"><?php echo $stats['most_popular_course']['enrollments']; ?> enrollments</p>
                <?php else: ?>
                    <p class="text-gray-600">No enrollments yet</p>
                <?php endif; ?>
            </div>

            <!-- Courses by Category -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Courses by Category</h2>
                <div class="space-y-2">
                    <?php foreach ($stats['courses_by_category'] as $category): ?>
                        <div class="flex justify-between">
                            <span><?php echo htmlspecialchars($category['name']); ?></span>
                            <span class="font-medium"><?php echo $category['count']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Teachers -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Top Teachers</h2>
                <div class="space-y-4">
                    <?php foreach ($stats['top_teachers'] as $index => $teacher): ?>
                        <div class="flex items-center">
                            <span class="text-lg font-bold mr-4">#<?php echo $index + 1; ?></span>
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($teacher['username']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo $teacher['course_count']; ?> courses</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

