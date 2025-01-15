<?php
class Teacher {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getMyCourses($teacher_id) {
        $query = "SELECT c.*, COUNT(e.id) as student_count 
                 FROM courses c 
                 LEFT JOIN enrollments e ON c.id = e.course_id 
                 WHERE c.teacher_id = :teacher_id 
                 GROUP BY c.id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":teacher_id", $teacher_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createCourse($title, $description, $content, $video_url, $teacher_id, $category_id, $tags) {
        try {
            $this->db->beginTransaction();

            // Insert course
            $query = "INSERT INTO courses (title, description, content, video_url, teacher_id, category_id, is_approved) 
                     VALUES (:title, :description, :content, :video_url, :teacher_id, :category_id, FALSE)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":content", $content);
            $stmt->bindParam(":video_url", $video_url);
            $stmt->bindParam(":teacher_id", $teacher_id);
            $stmt->bindParam(":category_id", $category_id);
            $stmt->execute();

            $course_id = $this->db->lastInsertId();

            // Add tags
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
            return false;
        }
    }

    public function updateCourse($course_id, $title, $description, $content, $video_url, $category_id, $tags) {
        try {
            $this->db->beginTransaction();

            // Update course
            $query = "UPDATE courses 
                     SET title = :title, description = :description, content = :content, video_url = :video_url, category_id = :category_id 
                     WHERE id = :course_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":content", $content);
            $stmt->bindParam(":video_url", $video_url);
            $stmt->bindParam(":category_id", $category_id);
            $stmt->bindParam(":course_id", $course_id);
            $stmt->execute();

            // Remove old tags
            $query = "DELETE FROM course_tags WHERE course_id = :course_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":course_id", $course_id);
            $stmt->execute();

            // Add new tags
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
            $stmt = $this->db->prepare("DELETE FROM courses WHERE id = :course_id");
            $stmt->execute(['course_id' => $course_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getCourseStudents($course_id, $teacher_id) {
        $query = "SELECT u.username, u.email, e.enrolled_at 
                 FROM enrollments e 
                 JOIN users u ON e.student_id = u.id 
                 JOIN courses c ON e.course_id = c.id 
                 WHERE c.id = :course_id AND c.teacher_id = :teacher_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":course_id", $course_id);
        $stmt->bindParam(":teacher_id", $teacher_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

