<?php
class Livestock {
    private $conn;
    private $table = 'livestock';

    public function __construct($db) {
        $this->conn = $db;
    }

    private function getSelectFields() {
        return "*, livestock_code AS code, breed AS name, selling_price AS price, image AS image_url";
    }

    public function getAll($category = '', $search = '') {
        $query = "SELECT " . $this->getSelectFields() . " FROM " . $this->table . " WHERE 1=1";
        $params = [];

        if ($search) {
            $query .= " AND (breed LIKE ? OR livestock_code LIKE ? OR peternak_name LIKE ?)";
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
        $query = "SELECT " . $this->getSelectFields() . " FROM " . $this->table . " WHERE status = 'available' AND stock > 0";
        $params = [];

        if ($search) {
            $query .= " AND (breed LIKE ? OR livestock_code LIKE ? OR peternak_name LIKE ?)";
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
        $query = "SELECT " . $this->getSelectFields() . " FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCode($code) {
        $query = "SELECT " . $this->getSelectFields() . " FROM " . $this->table . " WHERE livestock_code = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function validate($data) {
        if (empty($data['peternak_name'])) {
            throw new Exception("Nama peternak harus diisi.");
        }
        if (empty($data['breed'])) {
            throw new Exception("Jenis / Ras hewan harus diisi.");
        }
        if (!isset($data['purchase_price']) || $data['purchase_price'] < 0) {
            throw new Exception("Harga beli tidak valid.");
        }
        if (!isset($data['selling_price']) || $data['selling_price'] < 0) {
            throw new Exception("Harga jual tidak valid.");
        }
        if ($data['selling_price'] < $data['purchase_price']) {
            throw new Exception("Harga jual tidak boleh lebih rendah dari harga beli.");
        }
        if (!isset($data['stock']) || $data['stock'] < 0) {
            throw new Exception("Stok tidak boleh kurang dari 0.");
        }
        if (!isset($data['age']) || $data['age'] <= 0) {
            throw new Exception("Umur tidak valid.");
        }
        if (!isset($data['weight']) || $data['weight'] <= 0) {
            throw new Exception("Berat tidak valid.");
        }
    }

    public function create($data) {
        $this->validate($data);
        
        $stock = intval($data['stock']);
        $status = $data['status'] ?? 'available';
        if ($stock === 0) {
            $status = 'sold';
        }

        $query = "INSERT INTO " . $this->table . " 
            (livestock_code, peternak_name, breed, age, weight, gender, purchase_price, selling_price, stock, status, image, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['livestock_code'] ?? $data['code'],
            $data['peternak_name'],
            $data['breed'],
            $data['age'],
            $data['weight'],
            $data['gender'],
            $data['purchase_price'],
            $data['selling_price'],
            $stock,
            $status,
            $data['image'] ?? null,
            $data['description'] ?? null
        ]);
    }

    public function update($id, $data) {
        $this->validate($data);

        $stock = intval($data['stock']);
        $status = $data['status'] ?? 'available';
        if ($stock === 0) {
            $status = 'sold';
        } elseif ($status === 'sold' && $stock > 0) {
            $status = 'available';
        }

        $query = "UPDATE " . $this->table . " SET 
            livestock_code = ?, 
            peternak_name = ?, 
            breed = ?, 
            age = ?, 
            weight = ?, 
            gender = ?, 
            purchase_price = ?, 
            selling_price = ?, 
            stock = ?, 
            status = ?, 
            image = ?, 
            description = ? 
            WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['livestock_code'] ?? $data['code'],
            $data['peternak_name'],
            $data['breed'],
            $data['age'],
            $data['weight'],
            $data['gender'],
            $data['purchase_price'],
            $data['selling_price'],
            $stock,
            $status,
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
        $livestock = $this->getById($id);
        if (!$livestock) return false;

        $newStock = $livestock['stock'] - $qty;
        if ($newStock < 0) return false;

        $status = ($newStock === 0) ? 'sold' : $livestock['status'];

        $query = "UPDATE " . $this->table . " SET stock = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$newStock, $status, $id]);
    }

    public function increaseStock($id, $qty) {
        $livestock = $this->getById($id);
        if (!$livestock) return false;

        $newStock = $livestock['stock'] + $qty;
        $status = $livestock['status'];
        if ($status === 'sold' && $newStock > 0) {
            $status = 'available';
        }

        $query = "UPDATE " . $this->table . " SET stock = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$newStock, $status, $id]);
    }

    public function verifyPurchaseStock($id, $qty) {
        $livestock = $this->getById($id);
        if (!$livestock) {
            throw new Exception("Hewan tidak ditemukan.");
        }
        if ($livestock['status'] === 'inactive') {
            throw new Exception("Hewan tidak aktif.");
        }
        if ($livestock['stock'] < $qty) {
            throw new Exception("Stok tidak mencukupi. Tersedia: " . $livestock['stock']);
        }
        return true;
    }
}
