<?php
require_once 'User.php';

class Teacher extends User {
    public function __construct($db, $id = null, $username = null, $email = null, $password = null, $is_active = true, $is_verified = false) {
        parent::__construct($db, $id, $username, $email, $password, 'teacher', $is_active, $is_verified);
    }

    public function setId($id) {
        $this->id = $id;
    }
    public function getMyCourses() {
        $query = "SELECT c.*, COUNT(e.id) as student_count, cat.name as category_name 
                 FROM courses c 
                 LEFT JOIN enrollments e ON c.id = e.course_id 
                 LEFT JOIN categories cat ON c.category_id = cat.id 
                 WHERE c.teacher_id = :teacher_id 
                 GROUP BY c.id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":teacher_id", $this->id);
        if (!$stmt->execute()) {
            error_log("Error executing getMyCourses query: " . implode(", ", $stmt->errorInfo()));
            return [];
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function createCourse($title, $description, $content, $video_url, $category_id, $tags) {
        try {
            $this->db->beginTransaction();

            // Insert the course into the courses table
            $query = "INSERT INTO courses (title, description, content, video_url, teacher_id, category_id, is_approved) 
                     VALUES (:title, :description, :content, :video_url, :teacher_id, :category_id, FALSE)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":content", $content);
            $stmt->bindParam(":video_url", $video_url);
            $stmt->bindParam(":teacher_id", $this->id);
            $stmt->bindParam(":category_id", $category_id);
            $stmt->execute();

            // Get the ID of the newly created course
            $course_id = $this->db->lastInsertId();

            // Insert tags into the course_tags table
            if (!empty($tags)) {
                foreach ($tags as $tag_id) {
                    $query = "INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(":course_id", $course_id);
                    $stmt->bindParam(":tag_id", $tag_id);
                    $stmt->execute();
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating course: " . $e->getMessage());
            return false;
        }
    }
    public function updateCourse($course_id, $title, $description, $content, $video_url, $category_id, $tags) {
        try {
            $this->db->beginTransaction();

            // Update the course in the courses table
            $query = "UPDATE courses 
                     SET title = :title, description = :description, content = :content, video_url = :video_url, category_id = :category_id 
                     WHERE id = :course_id AND teacher_id = :teacher_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":content", $content);
            $stmt->bindParam(":video_url", $video_url);
            $stmt->bindParam(":category_id", $category_id);
            $stmt->bindParam(":course_id", $course_id);
            $stmt->bindParam(":teacher_id", $this->id);
            $stmt->execute();

            // Delete existing tags for the course
            $query = "DELETE FROM course_tags WHERE course_id = :course_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":course_id", $course_id);
            $stmt->execute();

            // Insert new tags into the course_tags table
            if (!empty($tags)) {
                foreach ($tags as $tag_id) {
                    $query = "INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(":course_id", $course_id);
                    $stmt->bindParam(":tag_id", $tag_id);
                    $stmt->execute();
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating course: " . $e->getMessage());
            return false;
        }
    }
    public function deleteCourse($course_id) {
        try {
            $this->db->beginTransaction();

            // Delete course tags
            $stmt = $this->db->prepare("DELETE FROM course_tags WHERE course_id = :course_id");
            $stmt->execute(['course_id' => $course_id]);

            // Delete enrollments
            $stmt = $this->db->prepare("DELETE FROM enrollments WHERE course_id = :course_id");
            $stmt->execute(['course_id' => $course_id]);

            // Delete the course
            $stmt = $this->db->prepare("DELETE FROM courses WHERE id = :course_id AND teacher_id = :teacher_id");
            $stmt->execute(['course_id' => $course_id, 'teacher_id' => $this->id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting course: " . $e->getMessage());
            return false;
        }
    }
    public function getCourseStudents($course_id) {
        $query = "SELECT u.username, u.email, e.enrolled_at 
                 FROM enrollments e 
                 JOIN users u ON e.student_id = u.id 
                 JOIN courses c ON e.course_id = c.id 
                 WHERE c.id = :course_id AND c.teacher_id = :teacher_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":course_id", $course_id);
        $stmt->bindParam(":teacher_id", $this->id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getDashboardInfo() {
        $courses = $this->getMyCourses();
        return [
            'courses_count' => count($courses),
            'recent_courses' => array_slice($courses, 0, 5),
            'total_students' => array_sum(array_column($courses, 'student_count'))
        ];
    }
}