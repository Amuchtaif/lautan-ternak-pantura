<?php

class CashTransaction {
    private $conn;
    private $table = 'cash_transactions';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Centralized ledger recording function.
     * Must be run inside an active database transaction.
     */
    public function record($accountId, $type, $refType, $refId, $description, $cashIn, $cashOut, $userId, $date = null) {
        $cashIn = floatval($cashIn);
        $cashOut = floatval($cashOut);
        
        // 1. Lock cash account row for update (skip FOR UPDATE in SQLite test isolation)
        $query = "SELECT current_balance FROM cash_accounts WHERE id = ?";
        if ($this->conn->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
            $query .= " FOR UPDATE";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$accountId]);
        $currentBalance = $stmt->fetchColumn();
        
        if ($currentBalance === false) {
            throw new Exception("Rekening kas tidak ditemukan.");
        }
        
        $currentBalance = floatval($currentBalance);
        
        // 2. Calculate balance after transaction
        $balanceAfter = $currentBalance + $cashIn - $cashOut;
        
        // 3. Update cash account balance
        $updateStmt = $this->conn->prepare("UPDATE cash_accounts SET current_balance = ? WHERE id = ?");
        $updateStmt->execute([$balanceAfter, $accountId]);
        
        // 4. Record ledger transaction
        $query = "INSERT INTO " . $this->table . " 
            (cash_account_id, transaction_type, reference_type, reference_id, transaction_date, description, cash_in, cash_out, balance_after, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insertStmt = $this->conn->prepare($query);
        $dateVal = $date ?: date('Y-m-d H:i:s');
        
        $insertStmt->execute([
            $accountId,
            $type,       // 'MODAL_INVESTOR', 'OPERASIONAL', 'PEMBELIAN_HEWAN', 'PENJUALAN_HEWAN'
            $refType,
            $refId,
            $dateVal,
            $description,
            $cashIn,
            $cashOut,
            $balanceAfter,
            $userId
        ]);
        
        return $this->conn->lastInsertId();
    }

    /**
     * Get cash transactions ledger history with filters
     */
    public function getLedger($search = '', $type = '', $accountId = '', $startDate = '', $endDate = '') {
        $query = "SELECT t.*, a.name as account_name, u.name as creator_name
                 FROM " . $this->table . " t
                 JOIN cash_accounts a ON t.cash_account_id = a.id
                 JOIN users u ON t.created_by = u.id
                 WHERE 1=1";
        $params = [];

        if ($search) {
            $query .= " AND (t.description LIKE ? OR a.name LIKE ?)";
            $searchVal = "%$search%";
            $params[] = $searchVal;
            $params[] = $searchVal;
        }

        if ($type) {
            $query .= " AND t.transaction_type = ?";
            $params[] = $type;
        }

        if ($accountId) {
            $query .= " AND t.cash_account_id = ?";
            $params[] = $accountId;
        }

        if ($startDate) {
            $query .= " AND DATE(t.transaction_date) >= DATE(?)";
            $params[] = $startDate;
        }

        if ($endDate) {
            $query .= " AND DATE(t.transaction_date) <= DATE(?)";
            $params[] = $endDate;
        }

        $query .= " ORDER BY t.transaction_date DESC, t.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
