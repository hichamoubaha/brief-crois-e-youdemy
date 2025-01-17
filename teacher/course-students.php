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

  <div class="max-w-6xl mx-auto px-4">
      <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($course['title']); ?></h1>
      <p class="text-gray-600 mb-8">Enrolled Students</p>

      <?php if (empty($students)): ?>
          <div class="bg-white p-6 rounded-lg shadow-md">
              <p class="text-gray-600">No students enrolled in this course yet.</p>
          </div>
      <?php else: ?>
          <div class="bg-white shadow-md rounded-lg overflow-hidden">
              <table class="min-w-full">
                  <thead class="bg-gray-50">
                      <tr>
                          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled Date</th>
                      </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
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
</body>
</html>

