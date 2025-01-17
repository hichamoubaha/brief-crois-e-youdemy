<?php
require_once '../config/config.php';
require_once '../classes/Course.php';
require_once '../classes/Student.php';

session_start();

$database = new Database();
$db = $database->connect();

if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$course_id = $_GET['id'];

// Get course details
$query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
         FROM courses c 
         LEFT JOIN users u ON c.teacher_id = u.id 
         LEFT JOIN categories cat ON c.category_id = cat.id 
         WHERE c.id = :course_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":course_id", $course_id);
$stmt->execute();
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: ../index.php');
    exit;
}

// Get course tags
$query = "SELECT t.name FROM tags t 
         JOIN course_tags ct ON t.id = ct.tag_id 
         WHERE ct.course_id = :course_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":course_id", $course_id);
$stmt->execute();
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle enrollment
$error = '';
$success = '';
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'student' && isset($_POST['enroll'])) {
    $student = new Student($db);
    $student->setId($_SESSION['user_id']);
    if ($student->enrollCourse($course_id)) {
        $success = 'Successfully enrolled in the course!';
    } else {
        $error = 'You are already enrolled in this course or the course is not available.';
    }
}

$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Youdemy</title>
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

    <!-- Course Details Content -->
    <div class="max-w-4xl mx-auto px-4 py-8">
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-card p-8 rounded-lg">
            <h1 class="text-3xl font-bold mb-4 gradient-text"><?php echo htmlspecialchars($course['title']); ?></h1>
            
            <div class="flex items-center text-gray-400 mb-4">
                <i class="fas fa-user-tie text-blue-500 mr-2"></i>
                <span class="mr-4">By <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                <i class="fas fa-tag text-blue-500 mr-2"></i>
                <span>Category: <?php echo htmlspecialchars($course['category_name']); ?></span>
            </div>

            <div class="prose max-w-none mb-8">
                <h2 class="text-xl font-semibold mb-2">Description</h2>
                <p class="text-gray-300"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
            </div>

            <?php if ($is_logged_in): ?>
                <?php if (!empty($tags)): ?>
                    <div class="flex flex-wrap gap-2 mb-6">
                        <?php foreach ($tags as $tag): ?>
                            <span class="bg-blue-600 text-white text-sm px-3 py-1 rounded-full">
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($course['video_url'])): ?>
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-2">Course Video</h2>
                        <div class="aspect-w-16 aspect-h-9">
                            <?php
                            $video_url = $course['video_url'];
                            $video_id = '';
                            
                            if (strpos($video_url, 'youtube.com') !== false) {
                                parse_str(parse_url($video_url, PHP_URL_QUERY), $params);
                                $video_id = $params['v'] ?? '';
                                $embed_url = "https://www.youtube.com/embed/{$video_id}";
                            } elseif (strpos($video_url, 'youtu.be') !== false) {
                                $video_id = basename(parse_url($video_url, PHP_URL_PATH));
                                $embed_url = "https://www.youtube.com/embed/{$video_id}";
                            } elseif (strpos($video_url, 'vimeo.com') !== false) {
                                $video_id = basename(parse_url($video_url, PHP_URL_PATH));
                                $embed_url = "https://player.vimeo.com/video/{$video_id}";
                            } else {
                                $embed_url = $video_url;
                            }
                            ?>
                            <iframe 
                                src="<?php echo htmlspecialchars($embed_url); ?>" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen 
                                class="w-full h-full"
                            ></iframe>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="prose max-w-none mb-8">
                    <h2 class="text-xl font-semibold mb-2">Course Content</h2>
                    <div class="bg-gray-800 p-4 rounded">
                        <?php echo nl2br(htmlspecialchars($course['content'])); ?>
                    </div>
                </div>

                <?php if ($_SESSION['role'] === 'student'): ?>
                    <form method="POST" action="">
                        <button type="submit" name="enroll" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>Enroll in Course</span>
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="bg-yellow-600 border-l-4 border-yellow-500 text-yellow-100 p-4 mb-8" role="alert">
                    <p class="font-bold">Course Content Hidden</p>
                    <p>Please <a href="login.php" class="underline">log in</a> or <a href="register.php" class="underline">register</a> to view the full course content, including videos and enrollment options.</p>
                </div>
            <?php endif; ?>
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