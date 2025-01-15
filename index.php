<?php
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/Course.php';

session_start();

$database = new Database();
$db = $database->connect();
$course = new Course($db);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get all categories
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get courses based on search, category, and pagination
if ($search) {
    $courses = $course->searchCourses($search);
} elseif ($category) {
    $courses = $course->getCoursesByCategory($category, $page);
} else {
    $courses = $course->getAllCourses($page);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Youdemy - Online Learning Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <div>
                        <a href="index.php" class="flex items-center py-4 px-2">
                            <span class="font-semibold text-gray-500 text-lg">Youdemy</span>
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="pages/login.php" class="py-2 px-4 text-gray-500 hover:text-gray-700">Login</a>
                        <a href="pages/register.php" class="py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600">Register</a>
                    <?php else: ?>
                        <a href="pages/dashboard.php" class="py-2 px-4 text-gray-500 hover:text-gray-700">Dashboard</a>
                        <a href="pages/logout.php" class="py-2 px-4 bg-red-500 text-white rounded hover:bg-red-600">Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Welcome to Youdemy</h1>
            <p class="text-gray-600 mt-2">Discover our courses and start learning today</p>
        </div>

        <!-- Search Form -->
        <form action="index.php" method="GET" class="mb-8">
            <div class="flex gap-4">
                <input type="text" name="search" placeholder="Search for courses..." value="<?php echo htmlspecialchars($search); ?>"
                       class="flex-grow shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Search
                </button>
            </div>
        </form>

        <!-- Category Buttons -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Categories</h2>
            <div class="flex flex-wrap gap-2">
                <a href="index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                    All Courses
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="index.php?category=<?php echo urlencode($cat['id']); ?>" 
                       class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded <?php echo $category == $cat['id'] ? 'bg-blue-500 text-white' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($courses)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-8" role="alert">
                <p>No courses found. Try a different search term or category.</p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($courses as $course): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($course['title']); ?></h2>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">By <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                            <a href="pages/course.php?id=<?php echo $course['id']; ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">View Course</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if (!$search && !$category): ?>
            <div class="mt-8 flex justify-center">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="mx-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Previous</a>
                <?php endif; ?>
                <a href="?page=<?php echo $page + 1; ?>" class="mx-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Next</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

