<?php
require_once '../config/config.php';
require_once '../classes/User.php';

session_start();

$database = new Database();
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
            if (User ::register($db, $username, $email, $password, $role)) {
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h1 class="text-2xl font-bold mb-6 text-center">Create Account</h1>
            
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

            <form id="registrationForm" method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                        Username
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           id="username" type="text" name="username" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           id="email" type="email" name="email" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                           id="password" type="password" name="password" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="role">
                        Role
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="role" name="role" required>
                        <option value="">Select a role</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
                            type="submit">
                        Register
                    </button>
                </div>
            </form>
            <p class="text-center mt-4">
                Already have an account? <a href="login.php" class="text-blue-500 hover:text-blue-700">Login</a>
            </p>
        </div>
    </div>

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