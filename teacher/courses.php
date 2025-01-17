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
$debug = $teacher->debug();
error_log("Teacher debug info: " . print_r($debug, true));

$courses = $teacher->getMyCourses();
error_log("Courses fetched: " . print_r($courses, true));
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
          </div>
      </div>
  </nav>

  <div class="max-w-6xl mx-auto px-4">
      <?php
      if (isset($_SESSION['success_message'])) {
          echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">';
          echo '<span class="block sm:inline">' . $_SESSION['success_message'] . '</span>';
          echo '</div>';
          unset($_SESSION['success_message']);
      }

      if (isset($_SESSION['error_message'])) {
          echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">';
          echo '<span class="block sm:inline">' . $_SESSION['error_message'] . '</span>';
          echo '</div>';
          unset($_SESSION['error_message']);
      }
      ?>
      <div class="flex justify-between items-center mb-8">
          <h1 class="text-3xl font-bold text-gray-800">My Courses</h1>
          <a href="create-course.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
              Create New Course
          </a>
      </div>

      <?php if (empty($courses)): ?>
          <div class="bg-white rounded-lg shadow-md p-6">
              <p class="text-gray-600">You haven't created any courses yet.</p>
              <p class="text-gray-500 mt-2">Debug info: Teacher ID = <?php echo $debug['id']; ?></p>
          </div>
      <?php else: ?>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <?php foreach($courses as $course): ?>
                  <div class="bg-white rounded-lg shadow-md overflow-hidden">
                      <div class="p-6">
                          <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($course['title']); ?></h2>
                          <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                          <div class="flex justify-between items-center">
                              <span class="text-sm text-gray-500"><?php echo $course['student_count']; ?> students</span>
                              <span class="text-sm text-gray-500"><?php echo htmlspecialchars($course['category_name']); ?></span>
                          </div>
                          <div class="mt-4 flex justify-between items-center">
                              <a href="edit-course.php?id=<?php echo $course['id']; ?>" 
                                 class="text-blue-500 hover:text-blue-700">Edit</a>
                              <a href="course-students.php?id=<?php echo $course['id']; ?>" 
                                 class="text-green-500 hover:text-green-700">Students</a>
                              <button onclick="confirmDelete(<?php echo $course['id']; ?>)" 
                                      class="text-red-500 hover:text-red-700">Delete</button>
                          </div>
                      </div>
                  </div>
              <?php endforeach; ?>
          </div>
      <?php endif; ?>
  </div>
  <script>
      function confirmDelete(courseId) {
          if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
              window.location.href = 'delete-course.php?id=' + courseId;
          }
      }
  </script>
</body>
</html>

