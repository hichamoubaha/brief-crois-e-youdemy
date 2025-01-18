<?php
class Course {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($title, $description, $content, $teacher_id, $category_id) {
        $query = "INSERT INTO courses (title, description, content, teacher_id, category_id) 
                 VALUES (:title, :description, :content, :teacher_id, :category_id)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":content", $content);
        $stmt->bindParam(":teacher_id", $teacher_id);
        $stmt->bindParam(":category_id", $category_id);

        return $stmt->execute();
    }

    public function getAllCourses($page = 1, $limit = 6) { 
        $offset = ($page - 1) * $limit;
        $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
                 FROM courses c 
                 LEFT JOIN users u ON c.teacher_id = u.id 
                 LEFT JOIN categories cat ON c.category_id = cat.id 
                 WHERE c.is_approved = TRUE
                 ORDER BY c.created_at DESC
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchCourses($keyword) {
        $keyword = "%$keyword%";
        $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
                 FROM courses c 
                 LEFT JOIN users u ON c.teacher_id = u.id 
                 LEFT JOIN categories cat ON c.category_id = cat.id 
                 WHERE (c.title LIKE :keyword OR c.description LIKE :keyword) AND c.is_approved = TRUE
                 ORDER BY c.created_at DESC
                 LIMIT 6"; 
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCoursesByCategory($category_id, $page = 1, $limit = 6) { 
        $offset = ($page - 1) * $limit;
        $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
                 FROM courses c 
                 LEFT JOIN users u ON c.teacher_id = u.id 
                 LEFT JOIN categories cat ON c.category_id = cat.id 
                 WHERE c.category_id = :category_id AND c.is_approved = TRUE
                 ORDER BY c.created_at DESC
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

