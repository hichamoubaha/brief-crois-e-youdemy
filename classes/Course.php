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