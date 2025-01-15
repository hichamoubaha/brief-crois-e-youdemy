<?php
require_once '../config/config.php';
require_once '../classes/Admin.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$admin = new Admin($db);

$error = '';
$success = '';

// Handle category addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        $stmt = $db->prepare("INSERT INTO categories (name) VALUES (:name)");
        if ($stmt->execute(['name' => $category_name])) {
            $success = 'Category added successfully!';
        } else {
            $error = 'Failed to add category.';
        }
    } else {
        $error = 'Category name cannot be empty.';
    }
}

// Handle category deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
    if ($stmt->execute(['id' => $delete_id])) {
        $success = 'Category deleted successfully!';
    } else {
        $error = 'Failed to delete category.';
    }
}

// Get all categories
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Youdemy</title>
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
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Manage Categories</h1>

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

        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6 mb -8">
            <h2 class="text-xl font-semibold mb-4">Add New Category</h2>
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="category_name">
                        Category Name
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           id="category_name" type="text" name="category_name" required>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Add Category
                </button>
            </form>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <h2 class="text-xl font-semibold p-6 border-b">Existing Categories</h2>
            <div class="p-6">
                <ul class="space-y-2">
                    <?php foreach ($categories as $category): ?>
                        <li class="flex justify-between items-center">
                            <span><?php echo htmlspecialchars($category['name']); ?></span>
                            <a href="?delete_id=<?php echo $category['id']; ?>" class="text-red-500 hover:text-red-700">Delete</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>

