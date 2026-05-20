<?php
class Livestock {
    private $conn;
    private $table = 'livestock';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($category = '', $search = '') {
        $query = "SELECT * FROM " . $this->table . " WHERE 1=1";
        $params = [];

        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }

        if ($search) {
            $query .= " AND (name LIKE ? OR breed LIKE ? OR code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $query .= " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailable($category = '', $search = '') {
        $query = "SELECT * FROM " . $this->table . " WHERE status = 'available' AND stock > 0";
        $params = [];

        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }

        if ($search) {
            $query .= " AND (name LIKE ? OR breed LIKE ? OR code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $query .= " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCode($code) {
        $query = "SELECT * FROM " . $this->table . " WHERE code = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
            (code, name, category, breed, age, weight, gender, price, purchase_price, stock, status, image, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['code'],
            $data['name'],
            $data['category'],
            $data['breed'],
            $data['age'],
            $data['weight'],
            $data['gender'],
            $data['price'],
            $data['purchase_price'] ?? 0,
            $data['stock'],
            $data['status'] ?? 'available',
            $data['image'] ?? null,
            $data['description'] ?? null
        ]);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET 
            code = ?, 
            name = ?, 
            category = ?, 
            breed = ?, 
            age = ?, 
            weight = ?, 
            gender = ?, 
            price = ?, 
            purchase_price = ?, 
            stock = ?, 
            status = ?, 
            image = ?, 
            description = ? 
            WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['code'],
            $data['name'],
            $data['category'],
            $data['breed'],
            $data['age'],
            $data['weight'],
            $data['gender'],
            $data['price'],
            $data['purchase_price'] ?? 0,
            $data['stock'],
            $data['status'],
            $data['image'] ?? null,
            $data['description'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function reduceStock($id, $qty) {
        // Fetch current stock
        $livestock = $this->getById($id);
        if (!$livestock) return false;

        $newStock = $livestock['stock'] - $qty;
        if ($newStock < 0) return false;

        $status = $newStock == 0 ? 'sold' : 'available';

        $query = "UPDATE " . $this->table . " SET stock = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$newStock, $status, $id]);
    }

    public function increaseStock($id, $qty) {
        $livestock = $this->getById($id);
        if (!$livestock) return false;

        $newStock = $livestock['stock'] + $qty;
        $status = 'available';

        $query = "UPDATE " . $this->table . " SET stock = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$newStock, $status, $id]);
    }

    public function verifyPurchaseStock($id, $qty) {
        $livestock = $this->getById($id);
        if (!$livestock) {
            throw new Exception("Hewan tidak ditemukan.");
        }
        if ($livestock['status'] !== 'available' || $livestock['stock'] < $qty) {
            throw new Exception("Stok tidak mencukupi atau sudah terjual.");
        }
        return true;
    }
}
