<?php
require_once '../config/config.php';
require_once '../classes/Teacher.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../pages/login.php');
    exit;
}

$database = Database::getInstance();
$db = $database->connect();
$teacher = new Teacher($db);
$teacher->setId($_SESSION['user_id']);

$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    header('Location: courses.php');
    exit;
}

// Get course details
$stmt = $db->prepare("SELECT title FROM courses WHERE id = :course_id AND teacher_id = :teacher_id");
$stmt->execute([
    'course_id' => $course_id,
    'teacher_id' => $_SESSION['user_id']
]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: courses.php');
    exit;
}

// Get enrolled students
$students = $teacher->getCourseStudents($course_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Students - Youdemy</title>
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

    <!-- Course Students Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-2 gradient-text"><?php echo htmlspecialchars($course['title']); ?></h1>
        <p class="text-gray-400 mb-8">Enrolled Students</p>

        <?php if (empty($students)): ?>
            <div class="dashboard-card p-6 rounded-lg">
                <p class="text-gray-300">No students enrolled in this course yet.</p>
            </div>
        <?php else: ?>
            <div class="dashboard-card p-6 rounded-lg">
                <table class="min-w-full">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Enrolled Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($student['username']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($student['email']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y', strtotime($student['enrolled_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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