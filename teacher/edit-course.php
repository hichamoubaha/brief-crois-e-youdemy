<?php
require_once '../config/config.php';
require_once '../classes/Teacher.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
  header('Location: ../pages/login.php');
  exit;
}

$database = new Database();
$db = $database->connect();
$teacher = new Teacher($db);
$teacher->setId($_SESSION['user_id']);

$error = '';
$success = '';

// Get course ID from URL
$course_id = $_GET['id'] ?? null;
if (!$course_id) {
  header('Location: courses.php');
  exit;
}

// Get categories and tags for the form
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT * FROM tags ORDER BY name");
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current course data
$stmt = $db->prepare("SELECT * FROM courses WHERE id = :course_id AND teacher_id = :teacher_id");
$stmt->execute([
  'course_id' => $course_id,
  'teacher_id' => $_SESSION['user_id']
]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
  header('Location: courses.php');
  exit;
}

// Get current course tags
$stmt = $db->prepare("SELECT tag_id FROM course_tags WHERE course_id = :course_id");
$stmt->execute(['course_id' => $course_id]);
$current_tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'] ?? '';
  $description = $_POST['description'] ?? '';
  $content = $_POST['content'] ?? '';
  $video_url = $_POST['video_url'] ?? '';
  $category_id = $_POST['category_id'] ?? '';
  $selected_tags = $_POST['tags'] ?? [];

  if (empty($title) || empty($description) || empty($content) || empty($category_id)) {
      $error = 'All fields are required';
  } else {
      if ($teacher->updateCourse($course_id, $title, $description, $content, $video_url, $category_id, $selected_tags)) {
          $success = 'Course updated successfully!';
          // Refresh course data
          $stmt = $db->prepare("SELECT * FROM courses WHERE id = :course_id");
          $stmt->execute(['course_id' => $course_id]);
          $course = $stmt->fetch(PDO::FETCH_ASSOC);
      } else {
          $error = 'Failed to update course';
      }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Course - Youdemy</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <nav class="bg-white shadow-lg mb-8">
      <div class="max-w-6xl mx-auto px-4">
          <div class="flex justify-between">
              <div class="flex space-x-7">
                  <a href="courses.php" class="flex items-center py-4 px-2">
                      <span class="font-semibold text-gray-500 text-lg">← Back to My Courses</span>
                  </a>
              </div>
          </div>
      </div>
  </nav>

  <div class="max-w-4xl mx-auto px-4">
      <h1 class="text-3xl font-bold text-gray-800 mb-8">Edit Course</h1>

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

      <form method="POST" class="bg-white shadow-md rounded-lg p-6">
          <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                  Course Title
              </label>
              <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                     id="title" type="text" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required>
          </div>

          <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                  Description
              </label>
              <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="description" name="description" rows="3" required><?php echo htmlspecialchars($course['description']); ?></textarea>
          </div>

          <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="content">
                  Course Content
              </label>
              <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="content" name="content" rows="10" required><?php echo htmlspecialchars($course['content']); ?></textarea>
          </div>

          <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="video_url">
                  Video URL (YouTube, Vimeo, or direct video link)
              </label>
              <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                     id="video_url" type="url" name="video_url" value="<?php echo htmlspecialchars($course['video_url']); ?>" placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/...">
              <p class="text-sm text-gray-600 mt-1">Enter a YouTube, Vimeo, or direct video link.</p>
          </div>

          <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="category">
                  Category
              </label>
              <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                      id="category" name="category_id" required>
                  <option value="">Select a category</option>
                  <?php foreach ($categories as $category): ?>
                      <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $course['category_id'] ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($category['name']); ?>
                      </option>
                  <?php endforeach; ?>
              </select>
          </div>

          <div class="mb-6">
              <label class="block text-gray-700 text-sm font-bold mb-2">
                  Tags
              </label>
              <div class="grid grid-cols-2 gap-2">
                  <?php foreach ($tags as $tag): ?>
                      <label class="inline-flex items-center">
                          <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>"
                                 <?php echo in_array($tag['id'], $current_tags) ? 'checked' : ''; ?>
                                 class="form-checkbox h-4 w-4 text-blue-600">
                          <span class="ml-2"><?php echo htmlspecialchars($tag['name']); ?></span>
                      </label>
                  <?php endforeach; ?>
              </div>
          </div>

          <div class="flex items-center justify-between">
              <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                      type="submit">
                  Update Course
              </button>
          </div>
      </form>
  </div>
</body>
</html>

