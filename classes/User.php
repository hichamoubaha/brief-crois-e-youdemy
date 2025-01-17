<?php
abstract class User {
  protected $db;
  protected $id;
  protected $username;
  protected $email;
  protected $role;
  protected $is_active;
  protected $is_verified;

  public function __construct($db) {
      $this->db = $db;
  }

  public function login($email, $password) {
      $query = "SELECT * FROM users WHERE email = :email AND is_active = 1";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(":email", $email);
      $stmt->execute();

      if($stmt->rowCount() == 1) {
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          if(password_verify($password, $row['password'])) {
              session_start();
              $_SESSION['user_id'] = $row['id'];
              $_SESSION['role'] = $row['role'];
              return true;
          }
      }
      return false;
  }

  public static function register($db, $username, $email, $password, $role) {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $query = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
      $stmt = $db->prepare($query);
      
      $stmt->bindParam(":username", $username);
      $stmt->bindParam(":email", $email);
      $stmt->bindParam(":password", $hashed_password);
      $stmt->bindParam(":role", $role);

      return $stmt->execute();
  }

  public function logout() {
      session_start();
      session_destroy();
  }

  // Add getters and setters for protected properties
  public function getId() {
      return $this->id;
  }

  public function getUsername() {
      return $this->username;
  }

  public function getEmail() {
      return $this->email;
  }

  public function getRole() {
      return $this->role;
  }

  public function isActive() {
      return $this->is_active;
  }

  public function isVerified() {
      return $this->is_verified;
  }

  // Add abstract methods that child classes must implement
  abstract public function getDashboardInfo();
}
?>

