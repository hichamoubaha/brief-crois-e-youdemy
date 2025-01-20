<?php
require_once '../config/config.php';
require_once '../classes/User.php';

session_start();

$database = Database::getInstance();
$db = $database->connect();
// We'll use the static register method from the User class

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Server-side validation
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required';
    } elseif (!preg_match("/^[a-zA-Z]+$/", $username)) {
        $error = 'Username must contain only letters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        try {
            if (User::register($db, $username, $email, $password, $role)) {
                $success = 'Registration successful! Please login.';
            } else {
                $error = 'Registration failed';
            }
        } catch (PDOException $e) {
            $error = 'Email or username already exists';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .gradient-text {
            background: linear-gradient(45deg, #3b82f6, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="text-gray-300 hover:text-white flex items-center space-x-2">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                        <a href="register.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-user-plus"></i>
                            <span>Register</span>
                        </a>
                    <?php else: ?>
                        <a href="dashboard.php" class="text-gray-300 hover:text-white flex items-center space-x-2">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-gray-800 p-8 rounded-lg shadow-md w-96">
            <h1 class="text-2xl font-bold mb-6 text-center gradient-text">Create Account</h1>
            
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

            <form id="registrationForm" method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-bold mb-2" for="username">
                        Username
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           id="username" type="text" name="username" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           id="email" type="email" name="email" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           id="password" type="password" name="password" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-bold mb-2" for="role">
                        Role
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            id="role" name="role" required>
                        <option value="">Select a role</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full"
                            type="submit">
                        Register
                    </button>
                </div>
            </form>
            <p class="text-center mt-4 text-gray-300">
                Already have an account? <a href="login.php" class="text-blue-500 hover:text-blue-700">Login</a>
            </p>
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

    <script>
        document.getElementById('registrationForm').addEventListener('submit', function(event) {
            event.preventDefault();
            // Get form values
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Regex for username (only letters)
            const usernameRegex = /^[A-Za-z]+$/;
            // Regex for email (valid email format)
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // Validation checks
            if (!usernameRegex.test(username)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Username',
                    text: 'Username must contain only letters.',
                });
                return;
            }

            if (!emailRegex.test(email)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Email',
                    text: 'Please enter a valid email address.',
                });
                return;
            }

            if (password.length < 6) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Password',
                    text: 'Password must be at least 6 characters long.',
                });
                return;
            }

            // If all validations pass, submit the form
            this.submit();
        });
    </script>
</body>
</html>