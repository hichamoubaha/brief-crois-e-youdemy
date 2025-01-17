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
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg mb-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <a href="../index.php" class="flex items-center py-4 px-2">
                        <span class="font-semibold text-gray-500 text-lg">← Back to Courses</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4">
        <?php if($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
                
                <div class="flex items-center text-gray-600 mb-4">
                    <span class="mr-4">By <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                    <span>Category: <?php echo htmlspecialchars($course['category_name']); ?></span>
                </div>

                <div class="prose max-w-none mb-8">
                    <h2 class="text-xl font-semibold mb-2">Description</h2>
                    <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                </div>

                <?php if ($is_logged_in): ?>
                    <?php if (!empty($tags)): ?>
                        <div class="flex flex-wrap gap-2 mb-6">
                            <?php foreach ($tags as $tag): ?>
                                <span class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">
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
                        <div class="bg-gray-50 p-4 rounded">
                            <?php echo nl2br(htmlspecialchars($course['content'])); ?>
                        </div>
                    </div>

                    <?php if ($_SESSION['role'] === 'student'): ?>
                        <form method="POST" action="">
                            <button type="submit" name="enroll" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                                Enroll in Course
                            </button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-8" role="alert">
                        <p class="font-bold">Course Content Hidden</p>
                        <p>Please <a href="login.php" class="underline">log in</a> or <a href="register.php" class="underline">register</a> to view the full course content, including videos and enrollment options.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

