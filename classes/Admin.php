<?php
class Admin {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllCourses() {
        $query = "SELECT c.*, cat.name as category_name 
                  FROM courses c 
                  LEFT JOIN categories cat ON c.category_id = cat.id 
                  ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

            // Delete user's enrollments
            $stmt = $this->db->prepare("DELETE FROM enrollments WHERE student_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            // Delete user's courses (if they're a teacher)
            $stmt = $this->db->prepare("DELETE FROM courses WHERE teacher_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            // Delete the user
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getStatistics() {
        $stats = [];
        
        // Total courses
        $query = "SELECT COUNT(*) as total FROM courses";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Courses by category
        $query = "SELECT c.name, COUNT(co.id) as count 
                 FROM categories c 
                 LEFT JOIN courses co ON c.id = co.category_id 
                 GROUP BY c.id";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['courses_by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Most popular course
        $query = "SELECT c.title, COUNT(e.id) as enrollments 
                 FROM courses c 
                 LEFT JOIN enrollments e ON c.id = e.course_id 
                 GROUP BY c.id 
                 ORDER BY enrollments DESC 
                 LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['most_popular_course'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Top 3 teachers
        $query = "SELECT u.username, COUNT(c.id) as course_count 
                 FROM users u 
                 LEFT JOIN courses c ON u.id = c.teacher_id 
                 WHERE u.role = 'teacher' 
                 GROUP BY u.id 
                 ORDER BY course_count DESC 
                 LIMIT 3";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['top_teachers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    public function getUnapprovedCourses() {
        $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
                  FROM courses c 
                  JOIN users u ON c.teacher_id = u.id 
                  JOIN categories cat ON c.category_id = cat.id 
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
}
?>

