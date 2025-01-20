<?php
abstract class User {
    protected $db;
    protected $id;
    protected $username;
    protected $email;
    protected $password;
    protected $role;
    protected $is_active;
    protected $is_verified;

    public function __construct($db, $id = null, $username = null, $email = null, $password = null, $role = null, $is_active = true, $is_verified = false) {
        $this->db = $db;
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->is_active = $is_active;
        $this->is_verified = $is_verified;
    }

    public function __toString() {
        return $this->username;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function isActive() { return $this->is_active; }
    public function isVerified() { return $this->is_verified; }

    // Password hashing method
    protected function setPasswordHash($password) {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    // Save user to the database
    public function save() {
        try {
            if ($this->id) {
                // Update user
                $stmt = $this->db->prepare("UPDATE users SET username = :username, email = :email, role = :role, is_active = :is_active, is_verified = :is_verified WHERE id = :id");
                $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            } else {
                // Insert new user
                $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role, is_active, is_verified) VALUES (:username, :email, :password, :role, :is_active, :is_verified)");
                $stmt->bindParam(':password', $this->password, PDO::PARAM_STR);
            }
            $stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindParam(':role', $this->role, PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $this->is_active, PDO::PARAM_BOOL);
            $stmt->bindParam(':is_verified', $this->is_verified, PDO::PARAM_BOOL);
            $stmt->execute();

            if (!$this->id) {
                $this->id = $this->db->lastInsertId();
            }
            return $this->id;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while saving the user.");
        }
    }

    // Search user by name
    public function searchUserByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username LIKE :name");
        $stmt->bindValue(':name', '%' . $name . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user by ID
    public static function getUserById($db, $id) {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Static method to search user by email
    public static function findByEmail($db, $email) {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Method to register a new user (signup)
    public static function register($db, $username, $email, $password, $role) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long");
        }

        $username = htmlspecialchars($username);

        if (self::findByEmail($db, $email)) {
            throw new Exception("Email is already registered");
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->execute();

        return $db->lastInsertId();
    }

    // Method to login (signin)
    public static function login($db, $email, $password) {
        $user = self::findByEmail($db, $email);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Invalid email or password");
        }

        return $user;
    }

    // Method to change the user's password
    public function changePassword($newPassword) {
        $this->setPasswordHash($newPassword);
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindParam(':password', $this->password, PDO::PARAM_STR);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Abstract method that child classes must implement
    abstract public function getDashboardInfo();
}
?>

