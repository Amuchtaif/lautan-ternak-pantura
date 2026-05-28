<?php
class User {
    private $conn;
    private $table = 'users';
    private $columnCache = [];

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

    private function hasColumn($column) {
        if (array_key_exists($column, $this->columnCache)) {
            return $this->columnCache[$column];
        }

        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM " . $this->table . " LIKE ?");
            $stmt->execute([$column]);
            $this->columnCache[$column] = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $stmt = $this->conn->query("PRAGMA table_info(" . $this->table . ")");
            $columns = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $this->columnCache[$column] = in_array($column, array_column($columns, 'name'), true);
        }

        return $this->columnCache[$column];
    }

    private function nameColumn() {
        return $this->hasColumn('full_name') ? 'full_name' : 'name';
    }

    private function normalizeRow($row) {
        if (!$row) {
            return false;
        }

        if (!isset($row['full_name'])) {
            $row['full_name'] = $row['name'] ?? '';
        }
        if (!isset($row['name'])) {
            $row['name'] = $row['full_name'] ?? '';
        }
        if (!isset($row['status'])) {
            $row['status'] = 'active';
        }

        return $row;
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->id = $row['id'];
            $row = $this->normalizeRow($row);
            $this->name = $row['full_name'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            $this->phone = $row['phone'] ?? '';
            $this->address = $row['address'] ?? '';
            return true;
        }
        return false;
    }

    public function findArrayByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $this->normalizeRow($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function findArrayByLogin($login) {
        if ($this->hasColumn('username')) {
            $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE email = ? OR username = ? LIMIT 1");
            $stmt->execute([$login, $login]);
            return $this->normalizeRow($stmt->fetch(PDO::FETCH_ASSOC));
        }

        return $this->findArrayByEmail($login);
    }

    public function findArrayById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1");
        $stmt->execute([(int)$id]);
        return $this->normalizeRow($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM " . $this->table . " WHERE email = ?");
        $stmt->execute([$email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function usernameExists($username) {
        if (!$this->hasColumn('username')) {
            return false;
        }
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM " . $this->table . " WHERE username = ?");
        $stmt->execute([$username]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function createCustomer($data) {
        $columns = [];
        $values = [];

        if ($this->hasColumn('full_name')) {
            $columns[] = 'full_name';
            $values[] = $data['full_name'];
        }

        $columns[] = 'name';
        $values[] = $data['full_name'];

        array_push($columns, 'email', 'phone', 'password', 'role');
        array_push($values, $data['email'], $data['phone'], $data['password'], 'customer');

        if ($this->hasColumn('username')) {
            $columns[] = 'username';
            $values[] = $data['username'] ?? null;
        }

        $optional = [
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'province' => $data['province'] ?? null,
            'profile_photo' => $data['profile_photo'] ?? null,
            'status' => 'active'
        ];

        foreach ($optional as $column => $value) {
            if ($this->hasColumn($column)) {
                $columns[] = $column;
                $values[] = $value;
            }
        }

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $query = "INSERT INTO " . $this->table . " (" . implode(', ', $columns) . ") VALUES (" . $placeholders . ")";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($values);

        return $this->conn->lastInsertId();
    }

    public function updateLastLogin($id) {
        if (!$this->hasColumn('last_login')) {
            return true;
        }

        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function create() {
        $nameColumn = $this->nameColumn();
        $query = "INSERT INTO " . $this->table . " ({$nameColumn}, email, password, role) VALUES (:name, :email, :password, :role)";
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
