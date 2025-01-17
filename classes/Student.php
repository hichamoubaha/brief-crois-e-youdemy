<?php
require_once 'User.php';

class Student extends User {
    public function __construct($db) {
        parent::__construct($db);
        $this->role = 'student';
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function enrollCourse($course_id) {
        try {
            // First check if the course exists and is approved
            $query = "SELECT id FROM courses WHERE id = :course_id AND is_approved = TRUE";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":course_id", $course_id);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return false; // Course doesn't exist or isn't approved
            }

            // Check if already enrolled
            $query = "SELECT id FROM enrollments WHERE student_id = :student_id AND course_id = :course_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":student_id", $this->id);
            $stmt->bindParam(":course_id", $course_id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return false; // Already enrolled
            }

            // Enroll in course
            $query = "INSERT INTO enrollments (student_id, course_id) VALUES (:student_id, :course_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":student_id", $this->id);
            $stmt->bindParam(":course_id", $course_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in enrollCourse: " . $e->getMessage());
            return false;
        }
    }

    public function getEnrolledCourses() {
        try {
            $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
                     FROM enrollments e 
                     JOIN courses c ON e.course_id = c.id 
                     LEFT JOIN users u ON c.teacher_id = u.id 
                     LEFT JOIN categories cat ON c.category_id = cat.id 
                     WHERE e.student_id = :student_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":student_id", $this->id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getEnrolledCourses: " . $e->getMessage());
            return [];
        }
    }

    public function getDashboardInfo() {
        $enrolledCourses = $this->getEnrolledCourses();
        return [
            'enrolled_courses_count' => count($enrolledCourses),
            'recent_courses' => array_slice($enrolledCourses, 0, 5)
        ];
    }
}