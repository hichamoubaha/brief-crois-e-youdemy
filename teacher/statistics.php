<?php
require_once '../config/config.php';
require_once '../classes/Teacher.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$database = Database::getInstance();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-text {
            background: linear-gradient(45deg, #3b82f6, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dashboard-card {
            background: linear-gradient(145deg, rgba(31, 41, 55, 0.9), rgba(17, 24, 39, 0.9));
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100">
    <!-- Navigation -->
    <nav class="w-full z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="../index.php" class="flex items-center space-x-3">
                        <i class="fas fa-graduation-cap text-3xl text-blue-500"></i>
                        <span class="font-bold text-2xl gradient-text">Youdemy</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Course Statistics Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8 gradient-text">Course Statistics</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Total Courses -->
            <div class="dashboard-card p-6 rounded-lg">
                <div class="flex items-center space-x-4 mb-4">
                    <i class="fas fa-book text-3xl text-blue-500"></i>
                    <h2 class="text-xl font-semibold">Total Courses</h2>
                </div>
                <p class="text-3xl font-bold text-blue-500"><?php echo $total_courses; ?></p>
            </div>

            <!-- Total Students -->
            <div class="dashboard-card p-6 rounded-lg">
                <div class="flex items-center space-x-4 mb-4">
                    <i class="fas fa-users text-3xl text-green-500"></i>
                    <h2 class="text-xl font-semibold">Total Students</h2>
                </div>
                <p class="text-3xl font-bold text-green-500"><?php echo $total_students; ?></p>
            </div>

            <!-- Most Popular Course -->
            <div class="dashboard-card p-6 rounded-lg">
                <div class="flex items-center space-x-4 mb-4">
                    <i class="fas fa-star text-3xl text-yellow-500"></i>
                    <h2 class="text-xl font-semibold">Most Popular Course</h2>
                </div>
                <?php if ($most_popular): ?>
                    <p class="text-lg font-medium"><?php echo htmlspecialchars($most_popular['title']); ?></p>
                    <p class="text-gray-400"><?php echo $most_popular['enrollments']; ?> enrollments</p>
                <?php else: ?>
                    <p class="text-gray-400">No enrollments yet</p>
                <?php endif; ?>
            </div>

            <!-- Courses by Category -->
            <div class="dashboard-card p-6 rounded-lg">
                <div class="flex items-center space-x-4 mb-4">
                    <i class="fas fa-tags text-3xl text-purple-500"></i>
                    <h2 class="text-xl font-semibold">Courses by Category</h2>
                </div>
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

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 py-12 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- About Section -->
                <div>
                    <h3 class="text-lg font-bold mb-4 gradient-text">About Youdemy</h3>
                    <p class="text-sm text-gray-400">
                        Youdemy is an online learning platform dedicated to providing high-quality courses from industry experts. Transform your future with our comprehensive learning resources.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-bold mb-4 gradient-text">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="../index.php" class="text-gray-400 hover:text-white transition duration-200">Home</a></li>
                        <li><a href="#courses" class="text-gray-400 hover:text-white transition duration-200">Courses</a></li>
                        <li><a href="#categories" class="text-gray-400 hover:text-white transition duration-200">Categories</a></li>
                        <li><a href="login.php" class="text-gray-400 hover:text-white transition duration-200">Login</a></li>
                        <li><a href="register.php" class="text-gray-400 hover:text-white transition duration-200">Register</a></li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div>
                    <h3 class="text-lg font-bold mb-4 gradient-text">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition duration-200">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-200">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-200">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-200">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-200">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-sm text-gray-400">
                    &copy; <?php echo date("Y"); ?> Youdemy. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>