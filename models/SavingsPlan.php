<?php

class SavingsPlan {
    private $conn;
    private $table = 'savings_plans';
    private $columnCache = [];

    public function __construct($db) {
        $this->conn = $db;
    }

    public function generatePlanCode() {
        if (!$this->hasColumn($this->table, 'plan_code')) {
            return null;
        }

        do {
            $code = 'TQ-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE plan_code = ?");
            $stmt->execute([$code]);
        } while ((int)$stmt->fetchColumn() > 0);

        return $code;
    }

    public function calculateMonthlyTarget($targetAmount, $durationMonth) {
        if ($durationMonth <= 0) {
            throw new InvalidArgumentException('Durasi tabungan harus lebih dari 0 bulan.');
        }

        return round($targetAmount / $durationMonth, 2);
    }

    public function calculateProgress($currentAmount, $targetAmount) {
        if ($targetAmount <= 0) {
            return 0;
        }

        return min(100, round(($currentAmount / $targetAmount) * 100, 2));
    }

    private function hasColumn($table, $column) {
        $key = $table . '.' . $column;
        if (array_key_exists($key, $this->columnCache)) {
            return $this->columnCache[$key];
        }

        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
            $stmt->execute([$column]);
            $this->columnCache[$key] = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $stmt = $this->conn->query("PRAGMA table_info({$table})");
            $columns = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $this->columnCache[$key] = in_array($column, array_column($columns, 'name'), true);
        }

        return $this->columnCache[$key];
    }

    private function transactionsUseNewSchema() {
        return $this->hasColumn('savings_transactions', 'savings_plan_id');
    }

    private function planSelectFields() {
        $usesNewTransactions = $this->transactionsUseNewSchema();
        $txPlanColumn = $usesNewTransactions ? 'savings_plan_id' : 'plan_id';
        $txStatusColumn = $usesNewTransactions ? 'transaction_status' : 'status';
        $verifiedTotal = "(SELECT COALESCE(SUM(st.amount), 0) FROM savings_transactions st WHERE st.{$txPlanColumn} = sp.id AND st.{$txStatusColumn} = 'verified')";

        $planCode = $this->hasColumn($this->table, 'plan_code')
            ? 'sp.plan_code'
            : "CONCAT('TQ-OLD-', LPAD(sp.id, 6, '0'))";
        $livestockTarget = $this->hasColumn($this->table, 'livestock_target')
            ? 'sp.livestock_target'
            : "'Hewan Qurban'";
        $currentAmount = $this->hasColumn($this->table, 'current_amount')
            ? 'sp.current_amount'
            : $verifiedTotal;
        $monthlyTarget = $this->hasColumn($this->table, 'monthly_target')
            ? 'sp.monthly_target'
            : 'sp.monthly_installment';
        $durationMonth = $this->hasColumn($this->table, 'duration_month')
            ? 'sp.duration_month'
            : 'GREATEST(1, CEIL(sp.target_amount / NULLIF(sp.monthly_installment, 0)))';
        $startDate = $this->hasColumn($this->table, 'start_date')
            ? 'sp.start_date'
            : 'DATE(sp.created_at)';
        $targetDate = $this->hasColumn($this->table, 'target_date')
            ? 'sp.target_date'
            : "DATE_ADD(DATE(sp.created_at), INTERVAL GREATEST(1, CEIL(sp.target_amount / NULLIF(sp.monthly_installment, 0))) MONTH)";
        $notes = $this->hasColumn($this->table, 'notes') ? 'sp.notes' : 'NULL';
        $updatedAt = $this->hasColumn($this->table, 'updated_at') ? 'sp.updated_at' : 'sp.created_at';
        $programType = $this->hasColumn($this->table, 'program_type') ? 'sp.program_type' : "'kambing'";
        $groupId = $this->hasColumn($this->table, 'group_id') ? 'sp.group_id' : 'NULL';

        return "
            sp.id,
            sp.livestock_id,
            {$planCode} AS plan_code,
            sp.customer_id,
            {$livestockTarget} AS livestock_target,
            sp.target_amount,
            {$currentAmount} AS current_amount,
            {$monthlyTarget} AS monthly_target,
            {$durationMonth} AS duration_month,
            {$startDate} AS start_date,
            {$targetDate} AS target_date,
            sp.status,
            {$notes} AS notes,
            sp.created_at,
            {$updatedAt} AS updated_at,
            {$programType} AS program_type,
            {$groupId} AS group_id
        ";
    }

    public function create($data) {
        $duration = (int)$data['duration_month'];
        $targetAmount = (float)$data['target_amount'];
        $monthlyTarget = isset($data['monthly_target'])
            ? (float)$data['monthly_target']
            : $this->calculateMonthlyTarget($targetAmount, $duration);
        $startDate = $data['start_date'] ?? date('Y-m-d');
        $targetDate = $data['target_date'] ?? date('Y-m-d', strtotime($startDate . ' +' . $duration . ' months'));

        if (!$this->hasColumn($this->table, 'plan_code')) {
            $livestockId = $data['livestock_id'] ?? null;
            if ($this->hasColumn($this->table, 'livestock_id') && !$livestockId) {
                $stmtLivestock = $this->conn->query("SELECT id FROM livestock ORDER BY id ASC LIMIT 1");
                $livestockId = $stmtLivestock ? $stmtLivestock->fetchColumn() : null;
            }

            if ($this->hasColumn($this->table, 'livestock_id')) {
                $query = "INSERT INTO {$this->table} (customer_id, livestock_id, target_amount, monthly_installment, status) VALUES (?, ?, ?, ?, 'Aktif')";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$data['customer_id'], $livestockId ?: 0, $targetAmount, $monthlyTarget]);
            } else {
                $query = "INSERT INTO {$this->table} (customer_id, target_amount, monthly_installment, status) VALUES (?, ?, ?, 'Aktif')";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$data['customer_id'], $targetAmount, $monthlyTarget]);
            }

            return $this->conn->lastInsertId();
        }

        $columns = ['plan_code', 'customer_id', 'livestock_target', 'target_amount', 'current_amount', 'monthly_target', 'duration_month', 'start_date', 'target_date', 'status', 'notes'];
        $values = [$this->generatePlanCode(), $data['customer_id'], $data['livestock_target'], $targetAmount, 0, $monthlyTarget, $duration, $startDate, $targetDate, 'Aktif', $data['notes'] ?? null];

        if ($this->hasColumn($this->table, 'livestock_id')) {
            $columns[] = 'livestock_id';
            $values[] = $data['livestock_id'] ?? null;
        }
        if ($this->hasColumn($this->table, 'target_type')) {
            $columns[] = 'target_type';
            $values[] = $data['target_type'] ?? (!empty($data['livestock_id']) ? 'livestock' : 'manual');
        }
        if ($this->hasColumn($this->table, 'program_type')) {
            $columns[] = 'program_type';
            $values[] = $data['program_type'] ?? 'kambing';
        }
        if ($this->hasColumn($this->table, 'group_id')) {
            $columns[] = 'group_id';
            $values[] = $data['group_id'] ?? null;
        }

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $query = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($values);

        return $this->conn->lastInsertId();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("
            SELECT " . $this->planSelectFields() . ", u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
            FROM {$this->table} sp
            JOIN users u ON sp.customer_id = u.id
            WHERE sp.id = ?
        ");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCustomerPlan($id, $customerId) {
        $stmt = $this->conn->prepare("SELECT " . $this->planSelectFields() . " FROM {$this->table} sp WHERE sp.id = ? AND sp.customer_id = ?");
        $stmt->execute([(int)$id, (int)$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCustomer($customerId) {
        $stmt = $this->conn->prepare("SELECT " . $this->planSelectFields() . " FROM {$this->table} sp WHERE sp.customer_id = ? ORDER BY sp.created_at DESC");
        $stmt->execute([(int)$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll($filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'sp.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['customer'])) {
            $where[] = $this->hasColumn($this->table, 'plan_code')
                ? '(u.name LIKE ? OR u.email LIKE ? OR sp.plan_code LIKE ?)'
                : '(u.name LIKE ? OR u.email LIKE ? OR CAST(sp.id AS CHAR) LIKE ?)';
            $keyword = '%' . $filters['customer'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $txPlanColumn = $this->transactionsUseNewSchema() ? 'savings_plan_id' : 'plan_id';
        $txStatusColumn = $this->transactionsUseNewSchema() ? 'transaction_status' : 'status';

        $sql = "
            SELECT " . $this->planSelectFields() . ", u.name AS customer_name, u.email AS customer_email,
                (SELECT COUNT(*) FROM savings_transactions st WHERE st.{$txPlanColumn} = sp.id AND st.{$txStatusColumn} = 'pending') AS pending_transactions
            FROM {$this->table} sp
            JOIN users u ON sp.customer_id = u.id
        ";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY sp.created_at DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDashboardStats($customerId) {
        if (!$this->hasColumn($this->table, 'current_amount')) {
            $txPlanColumn = $this->transactionsUseNewSchema() ? 'savings_plan_id' : 'plan_id';
            $txStatusColumn = $this->transactionsUseNewSchema() ? 'transaction_status' : 'status';
            $stmt = $this->conn->prepare("
                SELECT
                    COUNT(CASE WHEN sp.status IN ('active', 'Aktif', 'Masuk Kelompok') THEN 1 END) AS active_plans,
                    COALESCE(SUM(CASE WHEN st.{$txStatusColumn} = 'verified' THEN st.amount ELSE 0 END), 0) AS total_saved,
                    COALESCE(SUM(DISTINCT sp.target_amount), 0) AS total_target,
                    NULL AS nearest_target_date
                FROM {$this->table} sp
                LEFT JOIN savings_transactions st ON st.{$txPlanColumn} = sp.id
                WHERE sp.customer_id = ?
            ");
            $stmt->execute([(int)$customerId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $stmt = $this->conn->prepare("
            SELECT
                COUNT(CASE WHEN status IN ('active', 'Aktif', 'Masuk Kelompok') THEN 1 END) AS active_plans,
                COALESCE(SUM(current_amount), 0) AS total_saved,
                COALESCE(SUM(target_amount), 0) AS total_target,
                MIN(CASE WHEN status IN ('active', 'Aktif', 'Masuk Kelompok') THEN target_date END) AS nearest_target_date
            FROM {$this->table}
            WHERE customer_id = ?
        ");
        $stmt->execute([(int)$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAdminStats() {
        if (!$this->hasColumn($this->table, 'current_amount')) {
            $txPlanColumn = $this->transactionsUseNewSchema() ? 'savings_plan_id' : 'plan_id';
            $txStatusColumn = $this->transactionsUseNewSchema() ? 'transaction_status' : 'status';
            $stmt = $this->conn->query("
                SELECT
                    COUNT(DISTINCT sp.customer_id) AS total_customers,
                    COALESCE(SUM(CASE WHEN st.{$txStatusColumn} = 'verified' THEN st.amount ELSE 0 END), 0) AS total_collected,
                    COUNT(DISTINCT CASE WHEN sp.status IN ('completed', 'Target Tercapai', 'Masuk Kelompok', 'Selesai') THEN sp.id END) AS completed_plans,
                    0 AS due_soon
                FROM {$this->table} sp
                LEFT JOIN savings_transactions st ON st.{$txPlanColumn} = sp.id
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $stmt = $this->conn->query("
            SELECT
                COUNT(DISTINCT customer_id) AS total_customers,
                COALESCE(SUM(current_amount), 0) AS total_collected,
                COUNT(CASE WHEN status IN ('completed', 'Target Tercapai', 'Masuk Kelompok', 'Selesai') THEN 1 END) AS completed_plans,
                COUNT(CASE WHEN status IN ('active', 'Aktif', 'Masuk Kelompok') AND target_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) AS due_soon
            FROM {$this->table}
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addNotification($userId, $message) {
        $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
        return $stmt->execute([(int)$userId, $message]);
    }

    public function assignToGroup($planId) {
        $plan = $this->getById($planId);
        if (!$plan || $plan['program_type'] !== 'sapi_patungan') {
            return false;
        }

        // 1. Send notification that target is reached
        $this->addNotification($plan['customer_id'], "Selamat! Target tabungan Anda untuk program " . $plan['livestock_target'] . " (" . $plan['plan_code'] . ") telah tercapai.");

        // 2. Find group that has status 'Menunggu Anggota' and has < 7 members
        $stmtGroup = $this->conn->prepare("
            SELECT g.*, (SELECT COUNT(*) FROM savings_plans WHERE group_id = g.id) AS member_count
            FROM savings_groups g
            WHERE g.status = 'Menunggu Anggota'
            HAVING member_count < 7
            ORDER BY g.created_at ASC
            LIMIT 1
        ");
        $stmtGroup->execute();
        $group = $stmtGroup->fetch(PDO::FETCH_ASSOC);

        if ($group) {
            $groupId = $group['id'];
            $groupCode = $group['group_code'];
            $newMemberCount = $group['member_count'] + 1;
        } else {
            // Create a new group
            $year = date('Y');
            $stmtSeq = $this->conn->prepare("SELECT COUNT(*) FROM savings_groups WHERE group_code LIKE ?");
            $stmtSeq->execute(["SQ-{$year}-%"]);
            $count = (int)$stmtSeq->fetchColumn() + 1;
            $groupCode = sprintf("SQ-%s-%03d", $year, $count);

            $stmtNewGroup = $this->conn->prepare("INSERT INTO savings_groups (group_code, status) VALUES (?, 'Menunggu Anggota')");
            $stmtNewGroup->execute([$groupCode]);
            $groupId = $this->conn->lastInsertId();
            $newMemberCount = 1;
        }

        // Assign participant to this group
        $stmtAssign = $this->conn->prepare("UPDATE savings_plans SET group_id = ?, status = 'Masuk Kelompok' WHERE id = ?");
        $stmtAssign->execute([$groupId, $planId]);

        // Send notification to this user
        $this->addNotification($plan['customer_id'], "Anda telah berhasil bergabung ke kelompok qurban " . $groupCode . ".");

        // If group is full (7 members), update group status to 'Penuh' and notify all members
        if ($newMemberCount >= 7) {
            $stmtUpdateGroup = $this->conn->prepare("UPDATE savings_groups SET status = 'Penuh' WHERE id = ?");
            $stmtUpdateGroup->execute([$groupId]);

            // Fetch all members of this group to send notifications
            $stmtMembers = $this->conn->prepare("SELECT customer_id FROM savings_plans WHERE group_id = ?");
            $stmtMembers->execute([$groupId]);
            $members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);

            foreach ($members as $member) {
                $this->addNotification($member['customer_id'], "Kelompok qurban Anda (" . $groupCode . ") sudah penuh! Menunggu proses penetapan hewan qurban oleh panitia.");
            }
        }

        return true;
    }

    public function applyVerifiedDeposit($planId, $amount) {
        $plan = $this->getById($planId);
        if (!$plan) {
            throw new RuntimeException('Rencana tabungan tidak ditemukan.');
        }

        $newAmount = (float)$plan['current_amount'] + (float)$amount;
        $targetReached = $newAmount >= (float)$plan['target_amount'];

        $newStatus = $plan['status'];
        if ($targetReached && in_array($plan['status'], ['Aktif', 'active', 'overdue'], true)) {
            $newStatus = 'Target Tercapai';
        }

        $stmt = $this->conn->prepare("UPDATE {$this->table} SET current_amount = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$newAmount, $newStatus, (int)$planId]);

        // Trigger auto grouping if target reached and program is sapi_patungan
        if ($targetReached && in_array($plan['status'], ['Aktif', 'active', 'overdue'], true)) {
            if ($plan['program_type'] === 'sapi_patungan') {
                $this->assignToGroup($planId);
            } else {
                $this->addNotification($plan['customer_id'], "Selamat! Target tabungan Anda untuk program " . $plan['livestock_target'] . " (" . $plan['plan_code'] . ") telah tercapai.");
            }
        }

        return true;
    }

    public function updateAdmin($id, $data) {
        $targetAmount = (float)$data['target_amount'];
        $duration = max(1, (int)$data['duration_month']);
        $monthlyTarget = isset($data['monthly_target'])
            ? (float)$data['monthly_target']
            : $this->calculateMonthlyTarget($targetAmount, $duration);

        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET livestock_target = ?, target_amount = ?, monthly_target = ?, duration_month = ?, target_date = ?, status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['livestock_target'],
            $targetAmount,
            $monthlyTarget,
            $duration,
            $data['target_date'],
            $data['status'],
            $data['notes'] ?? null,
            (int)$id
        ]);
    }

    public function cancel($id) {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}
