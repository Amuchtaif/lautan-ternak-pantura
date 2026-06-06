<?php
class Order {
    /**
     * @var PDO
     */
    private $conn;
    private $table = 'orders';

    /**
     * @param PDO $db
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * @param array $data
     * @return int|string|false
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
            (order_code, customer_id, livestock_id, qty, livestock_price_snapshot, total_price, status, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([
            $data['order_code'],
            $data['customer_id'],
            $data['livestock_id'],
            $data['qty'],
            $data['livestock_price_snapshot'],
            $data['total_price'],
            $data['status'] ?? 'pending',
            $data['notes'] ?? null
        ]);

        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    private function parseGuestDetails($row) {
        if (!$row) return $row;
        
        $row['customer_phone'] = $row['customer_phone'] ?? '';
        $row['customer_address'] = $row['customer_address'] ?? '';
        
        if (!empty($row['notes'])) {
            if (preg_match('/Penerima:\s*(.*?)\r?\n/i', $row['notes'], $matches)) {
                $row['customer_name'] = trim($matches[1]);
            }
            if (preg_match('/WhatsApp:\s*(.*?)\r?\n/i', $row['notes'], $matches)) {
                $row['customer_phone'] = trim($matches[1]);
            }
            if (preg_match('/Alamat:\s*(.*?)\r?\n/i', $row['notes'], $matches)) {
                $row['customer_address'] = trim($matches[1]);
            }
            if (preg_match('/Catatan:\s*(.*)$/is', $row['notes'], $matches)) {
                $row['notes'] = trim($matches[1]);
            }
        }
        return $row;
    }

    /**
     * @param int|string $id
     * @return array|false
     */
    public function getById($id) {
        $query = "SELECT o.*, 
                 u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address,
                 l.breed as livestock_name, l.livestock_code as livestock_code, l.image as livestock_image, l.breed as livestock_breed, 'umum' as livestock_category, l.weight as livestock_weight
                 FROM " . $this->table . " o
                 JOIN users u ON o.customer_id = u.id
                 JOIN livestock l ON o.livestock_id = l.id
                 WHERE o.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this->parseGuestDetails($row);
    }

    public function getByCode($code) {
        $query = "SELECT o.*, 
                 u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address,
                 l.breed as livestock_name, l.livestock_code as livestock_code, l.image as livestock_image, l.breed as livestock_breed, 'umum' as livestock_category, l.weight as livestock_weight
                 FROM " . $this->table . " o
                 JOIN users u ON o.customer_id = u.id
                 JOIN livestock l ON o.livestock_id = l.id
                 WHERE o.order_code = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this->parseGuestDetails($row);
    }

    public function getByCustomerId($customerId) {
        $query = "SELECT o.*, l.breed as livestock_name, l.livestock_code as livestock_code, l.image as livestock_image
                 FROM " . $this->table . " o
                 JOIN livestock l ON o.livestock_id = l.id
                 WHERE o.customer_id = ?
                 ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll($status = '', $search = '') {
        $query = "SELECT o.*, u.name as customer_name, l.breed as livestock_name, l.livestock_code as livestock_code, p.payment_proof
                 FROM " . $this->table . " o
                 JOIN users u ON o.customer_id = u.id
                 JOIN livestock l ON o.livestock_id = l.id
                 LEFT JOIN payments p ON p.order_id = o.id
                 WHERE 1=1";
        $params = [];

        if ($status) {
            $query .= " AND o.status = ?";
            $params[] = $status;
        }

        if ($search) {
            $query .= " AND (o.order_code LIKE ? OR u.name LIKE ? OR l.breed LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $query .= " ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $id]);
    }

    public function generateOfflineSummary($customerName, $livestockName, $totalPrice) {
        $formattedPrice = number_format($totalPrice, 0, ',', '.');
        return "[Transaksi Offline] Pembeli: " . $customerName . " | Hewan: " . $livestockName . " | Total: Rp " . $formattedPrice;
    }
}
