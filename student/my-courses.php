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
                <div class="flex items-center space-x-4">
                    <a href="../pages/courses.php" class="text-blue-500 hover:text-blue-700">Browse More Courses</a>
                    <span class="text-gray-400">|</span>
                    <div class="relative">
                        <button id="categoryDropdown" class="text-blue-500 hover:text-blue-700">
                            Filter by Category
                        </button>
                        <div id="categoryMenu" class="hidden absolute right-0 mt-2 py-2 w-48 bg-white rounded-md shadow-xl z-20">
                            <?php
                            $categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($categories as $category) {
                                echo "<a href='../pages/courses.php?category={$category['id']}' class='block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100'>" . htmlspecialchars($category['name']) . "</a>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">My Enrolled Courses</h1>

        <?php if (empty($courses)): ?>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <p class="text-gray-600 mb-4">You haven't enrolled in any courses yet.</p>
                <a href="../pages/courses.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Browse Available Courses
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($courses as $course): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($course['title']); ?></h2>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($course['description'], 0, 150)) . '...'; ?></p>
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-sm text-gray-500">By <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                                    <span class="text-sm text-gray-500 block">Category: <?php echo htmlspecialchars($course['category_name']); ?></span>
                                </div>
                                <a href="../pages/course.php?id=<?php echo $course['id']; ?>" 
                                   class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    Continue Learning
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

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

