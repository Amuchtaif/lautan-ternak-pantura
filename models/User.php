<?php
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $phone;
    public $address;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            $this->phone = $row['phone'] ?? '';
            $this->address = $row['address'] ?? '';
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':role', $this->role);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateProfile($phone, $address) {
        // Refactor: Sanitize and clean inputs
        $cleanPhone = htmlspecialchars(strip_tags(trim($phone)));
        $cleanAddress = htmlspecialchars(strip_tags(trim($address)));

        $query = "UPDATE " . $this->table . " SET phone = :phone, address = :address WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':phone', $cleanPhone);
        $stmt->bindParam(':address', $cleanAddress);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            $this->phone = $cleanPhone;
            $this->address = $cleanAddress;
            return true;
        }
        return false;
    }
}
