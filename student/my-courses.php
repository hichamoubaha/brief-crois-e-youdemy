<?php
require_once '../config/config.php';
require_once '../classes/Student.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../pages/login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$student = new Student($db);
$student->setId($_SESSION['user_id']); // Set the student ID

$courses = $student->getEnrolledCourses();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Youdemy</title>
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
                    <a href="../pages/courses.php" class="text-blue-500 hover:text-blue-700 flex items-center space-x-2">
                        <i class="fas fa-search"></i>
                        <span>Browse More Courses</span>
                    </a>
                    <span class="text-gray-400">|</span>
                    <div class="relative">
                        <button id="categoryDropdown" class="text-blue-500 hover:text-blue-700 flex items-center space-x-2">
                            <i class="fas fa-filter"></i>
                            <span>Filter by Category</span>
                        </button>
                        <div id="categoryMenu" class="hidden absolute right-0 mt-2 py-2 w-48 bg-gray-800 rounded-md shadow-xl z-20">
                            <?php
                            $categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($categories as $category) {
                                echo "<a href='../pages/courses.php?category={$category['id']}' class='block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700'>" . htmlspecialchars($category['name']) . "</a>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- My Enrolled Courses Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8 gradient-text">My Enrolled Courses</h1>

        <?php if (empty($courses)): ?>
            <div class="dashboard-card p-6 rounded-lg text-center">
                <p class="text-gray-300 mb-4">You haven't enrolled in any courses yet.</p>
                <a href="../pages/courses.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 justify-center">
                    <i class="fas fa-search"></i>
                    <span>Browse Available Courses</span>
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($courses as $course): ?>
                    <div class="dashboard-card p-6 rounded-lg">
                    <img src="../cour.jpeg" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                        <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($course['title']); ?></h2>
                        <p class="text-gray-400 mb-4"><?php echo htmlspecialchars(substr($course['description'], 0, 150)) . '...'; ?></p>
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-sm text-gray-400">By <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                                <span class="text-sm text-gray-400 block">Category: <?php echo htmlspecialchars($course['category_name']); ?></span>
                            </div>
                            <a href="../pages/course.php?id=<?php echo $course['id']; ?>" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                                <i class="fas fa-play"></i>
                                <span>Continue Learning</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
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

    <script>
        const categoryDropdown = document.getElementById('categoryDropdown');
        const categoryMenu = document.getElementById('categoryMenu');

        categoryDropdown.addEventListener('click', () => {
            categoryMenu.classList.toggle('hidden');
        });

        window.addEventListener('click', (e) => {
            if (!categoryDropdown.contains(e.target)) {
                categoryMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>