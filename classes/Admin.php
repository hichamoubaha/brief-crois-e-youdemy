<?php
require_once 'User.php';

class Admin extends User {
    public function __construct($db, $id = null, $username = null, $email = null, $password = null, $is_active = true, $is_verified = true) {
        parent::__construct($db, $id, $username, $email, $password, 'admin', $is_active, $is_verified);
    }

    public function getAllUsers() {
        $query = "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleUserStatus($user_id) {
        $query = "UPDATE users SET is_active = NOT is_active WHERE id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }

    public function verifyTeacher($user_id) {
        $query = "UPDATE users SET is_verified = 1 WHERE id = :user_id AND role = 'teacher'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }

    public function deleteUser($user_id) {
        try {
            $this->db->beginTransaction();

            // Delete enrollments
            $stmt = $this->db->prepare("DELETE FROM enrollments WHERE student_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            // Delete course tags for courses created by this user
            $stmt = $this->db->prepare("
                DELETE ct FROM course_tags ct 
                INNER JOIN courses c ON ct.course_id = c.id 
                WHERE c.teacher_id = :user_id
            ");
            $stmt->execute(['user_id' => $user_id]);

            // Delete courses created by this user
            $stmt = $this->db->prepare("DELETE FROM courses WHERE teacher_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            // Delete the user
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :user_id AND role != 'admin'");
            $stmt->execute(['user_id' => $user_id]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    public function getDashboardInfo() {
        $query = "SELECT 
                    (SELECT COUNT(*) FROM users WHERE role = 'student') as total_students,
                    (SELECT COUNT(*) FROM users WHERE role = 'teacher') as total_teachers,
                    (SELECT COUNT(*) FROM courses) as total_courses";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUnapprovedCourses() {
        $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
                FROM courses c 
                LEFT JOIN users u ON c.teacher_id = u.id 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                WHERE c.is_approved = FALSE
                ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approveCourse($course_id) {
        $query = "UPDATE courses SET is_approved = TRUE WHERE id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":course_id", $course_id);
        return $stmt->execute();
    }

    public function getStatistics() {
        $stats = [];

        // Total courses
        $query = "SELECT COUNT(*) as total_courses FROM courses";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_courses'];

        // Most popular course
        $query = "SELECT c.id, c.title, COUNT(e.id) as enrollments 
                FROM courses c 
                LEFT JOIN enrollments e ON c.id = e.course_id 
                GROUP BY c.id 
                ORDER BY enrollments DESC 
                LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['most_popular_course'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Courses by category
        $query = "SELECT cat.name, COUNT(c.id) as count 
                FROM categories cat 
                LEFT JOIN courses c ON cat.id = c.category_id 
                GROUP BY cat.id";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['courses_by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top teachers
        $query = "SELECT u.id, u.username, COUNT(c.id) as course_count 
                FROM users u 
                LEFT JOIN courses c ON u.id = c.teacher_id 
                WHERE u.role = 'teacher' 
                GROUP BY u.id 
                ORDER BY course_count DESC 
                LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['top_teachers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }
}

