<?php
class Student {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function enrollCourse($student_id, $course_id) {
        // Check if already enrolled
        $query = "SELECT id FROM enrollments WHERE student_id = :student_id AND course_id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":course_id", $course_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return false;
        }

        // Enroll in course
        $query = "INSERT INTO enrollments (student_id, course_id) VALUES (:student_id, :course_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":course_id", $course_id);
        return $stmt->execute();
    }

    public function getEnrolledCourses($student_id) {
        $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
                 FROM enrollments e 
                 JOIN courses c ON e.course_id = c.id 
                 LEFT JOIN users u ON c.teacher_id = u.id 
                 LEFT JOIN categories cat ON c.category_id = cat.id 
                 WHERE e.student_id = :student_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

