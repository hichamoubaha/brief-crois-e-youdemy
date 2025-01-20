<?php
require_once '../config/config.php';
require_once '../classes/Course.php';

session_start();

$database = Database::getInstance();
$db = $database->connect();
$course = new Course($db);

$keyword = $_GET['q'] ?? '';
$courses = $course->searchCourses($keyword);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Youdemy</title>
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
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="py-2 px-4 text-gray-500 hover:text-gray-700">Login</a>
                        <a href="register.php" class="py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600">Register</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="py-2 px-4 text-gray-500 hover:text-gray-700">Dashboard</a>
                        <a href="logout.php" class="py-2 px-4 bg-red-500 text-white rounded hover:bg-red-600">Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Search Results</h1>
            <p class="text-gray-600 mt-2">Showing results for: "<?php echo htmlspecialchars($keyword); ?>"</p>
        </div>

        <form action="search.php" method="GET" class="mb-8">
            <div class="flex gap-4">
                <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>" 
                       class="flex-1 shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       placeholder="Search courses...">
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    Search
                </button>
            </div>
        </form>

        <?php if (empty($courses)): ?>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <p class="text-gray-600">No courses found matching your search.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($courses as $course): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($course['title']); ?></h2>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">By <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                                <a href="course.php?id=<?php echo $course['id']; ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">View Course</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

