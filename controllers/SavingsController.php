<?php
require_once 'models/SavingsPlan.php';
require_once 'models/SavingsTransaction.php';
require_once 'models/Livestock.php';
require_once 'models/User.php';

class SavingsController {
    private function dbConnect() {
        global $conn;
        if (!isset($conn)) {
            require 'config/database.php';
        }
        return $conn;
    }

    private function requireRole($role) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
            header('Location: /lautan-ternak-pantura/auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    private function ensureCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public function index() {
        $this->requireRole('customer');
        header('Location: /lautan-ternak-pantura/customer/dashboard');
        exit;
    }

    public function create() {
        $this->requireRole('customer');
        $this->ensureCsrfToken();

        $db = $this->dbConnect();
        $livestockModel = new Livestock($db);
        $userModel = new User($db);
        $livestocks = $livestockModel->getAvailable('qurban');
        $selectedLivestock = null;
        $currentUser = $userModel->findArrayById((int)$_SESSION['user_id']);

        if (!empty($_GET['livestock_id'])) {
            $candidate = $livestockModel->getById((int)$_GET['livestock_id']);
            if ($candidate && $candidate['status'] === 'available' && (int)$candidate['stock'] > 0) {
                $selectedLivestock = $candidate;
            }
        }

        require 'views/customer/savings_create.php';
    }

    public function detail($id = null) {
        $this->requireRole('customer');
        $this->ensureCsrfToken();

        $db = $this->dbConnect();
        $planModel = new SavingsPlan($db);
        $transactionModel = new SavingsTransaction($db);

        $plan = $planModel->getCustomerPlan((int)$id, (int)$_SESSION['user_id']);
        if (!$plan) {
            header('Location: /lautan-ternak-pantura/customer/dashboard');
            exit;
        }

        $group = null;
        if (!empty($plan['group_id'])) {
            $stmtGroup = $db->prepare("
                SELECT g.*, 
                       (SELECT COUNT(*) FROM savings_plans WHERE group_id = g.id) AS member_count
                FROM savings_groups g
                WHERE g.id = ?
            ");
            $stmtGroup->execute([$plan['group_id']]);
            $group = $stmtGroup->fetch(PDO::FETCH_ASSOC);
        }

        $transactions = $transactionModel->getByPlan($plan['id']);
        $progress = $planModel->calculateProgress((float)$plan['current_amount'], (float)$plan['target_amount']);

        require 'views/customer/savings_detail.php';
    }

    public function management() {
        $this->requireRole('admin');
        $this->ensureCsrfToken();

        $db = $this->dbConnect();
        $planModel = new SavingsPlan($db);
        $transactionModel = new SavingsTransaction($db);

        $filters = [
            'status' => $_GET['status'] ?? '',
            'customer' => $_GET['customer'] ?? ''
        ];

        $plans = $planModel->getAll($filters);
        $stats = $planModel->getAdminStats();
        $pendingTransactions = $transactionModel->countPending();

        require 'views/admin/savings_management.php';
    }

    public function adminDetail($id = null) {
        $this->requireRole('admin');
        $this->ensureCsrfToken();

        $db = $this->dbConnect();
        $planModel = new SavingsPlan($db);
        $transactionModel = new SavingsTransaction($db);

        $plan = $planModel->getById((int)$id);
        if (!$plan) {
            http_response_code(404);
            echo 'Rencana tabungan tidak ditemukan.';
            return;
        }

        $transactions = $transactionModel->getByPlan($plan['id']);
        $progress = $planModel->calculateProgress((float)$plan['current_amount'], (float)$plan['target_amount']);

        require 'views/admin/savings_detail.php';
    }

    public function receipt($id = null) {
        $this->printReceipt($id);
    }

    public function printReceipt($id = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            header('Location: /lautan-ternak-pantura/auth/login');
            exit;
        }

        $db = $this->dbConnect();
        $transactionModel = new SavingsTransaction($db);
        $planModel = new SavingsPlan($db);

        $trx = $transactionModel->getById((int)$id);
        if (!$trx) {
            http_response_code(404);
            echo 'Transaksi tidak ditemukan.';
            return;
        }

        $plan = $planModel->getById($trx['savings_plan_id']);
        if (!$plan) {
            http_response_code(404);
            echo 'Rencana tabungan terkait tidak ditemukan.';
            return;
        }

        if ($_SESSION['role'] !== 'admin' && (int)$plan['customer_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(403);
            echo 'Akses ditolak.';
            return;
        }

        require 'views/print_receipt.php';
    }

    public function groups() {
        $this->requireRole('admin');
        $this->ensureCsrfToken();

        $db = $this->dbConnect();
        
        // Fetch groups
        $stmtGroups = $db->prepare("
            SELECT g.*, l.livestock_code, l.breed, l.selling_price,
                   (SELECT COUNT(*) FROM savings_plans WHERE group_id = g.id) AS member_count
            FROM savings_groups g
            LEFT JOIN livestock l ON g.livestock_id = l.id
            ORDER BY g.created_at DESC
        ");
        $stmtGroups->execute();
        $groups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

        // Fetch members for each group
        $groupMembers = [];
        $stmtMembers = $db->prepare("
            SELECT sp.id, sp.plan_code, sp.customer_id, u.name AS customer_name, u.phone AS customer_phone, sp.target_amount, sp.current_amount
            FROM savings_plans sp
            JOIN users u ON sp.customer_id = u.id
            WHERE sp.group_id = ?
        ");
        foreach ($groups as $group) {
            $stmtMembers->execute([$group['id']]);
            $groupMembers[$group['id']] = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fetch available cows (breed contains Sapi)
        $stmtCows = $db->prepare("
            SELECT id, livestock_code, breed, selling_price, stock, status
            FROM livestock
            WHERE breed LIKE '%Sapi%' AND status = 'available' AND stock > 0
        ");
        $stmtCows->execute();
        $availableCows = $stmtCows->fetchAll(PDO::FETCH_ASSOC);

        require 'views/admin/sapi_groups.php';
    }

    public function updateGroup() {
        $this->requireRole('admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /lautan-ternak-pantura/savings/groups');
            exit;
        }

        if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            header('Location: /lautan-ternak-pantura/savings/groups?error=csrf');
            exit;
        }

        $db = $this->dbConnect();
        $action = $_POST['action'] ?? '';
        $groupId = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);

        try {
            $db->beginTransaction();

            if ($action === 'assign_livestock') {
                $livestockId = filter_input(INPUT_POST, 'livestock_id', FILTER_VALIDATE_INT);
                
                // Get livestock details
                $stmtL = $db->prepare("SELECT * FROM livestock WHERE id = ?");
                $stmtL->execute([$livestockId]);
                $livestock = $stmtL->fetch(PDO::FETCH_ASSOC);

                if (!$livestock) {
                    throw new RuntimeException('Hewan qurban tidak ditemukan.');
                }

                // Update group
                $stmtUpdate = $db->prepare("UPDATE savings_groups SET livestock_id = ?, status = 'Hewan Dibeli' WHERE id = ?");
                $stmtUpdate->execute([$livestockId, $groupId]);

                // Fetch group code
                $stmtG = $db->prepare("SELECT group_code FROM savings_groups WHERE id = ?");
                $stmtG->execute([$groupId]);
                $groupCode = $stmtG->fetchColumn();

                // Notify all members
                $stmtMembers = $db->prepare("SELECT customer_id FROM savings_plans WHERE group_id = ?");
                $stmtMembers->execute([$groupId]);
                $members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);

                require_once 'models/SavingsPlan.php';
                $planModel = new SavingsPlan($db);
                foreach ($members as $member) {
                    $planModel->addNotification(
                        $member['customer_id'], 
                        "Hewan qurban untuk kelompok Anda ({$groupCode}) telah ditentukan: {$livestock['livestock_code']} - {$livestock['breed']}."
                    );
                }

            } elseif ($action === 'update_status') {
                $status = $_POST['status'] ?? '';
                if (!in_array($status, ['Menunggu Anggota', 'Penuh', 'Hewan Dibeli', 'Disembelih', 'Selesai'], true)) {
                    throw new RuntimeException('Status kelompok tidak valid.');
                }

                $stmtUpdate = $db->prepare("UPDATE savings_groups SET status = ? WHERE id = ?");
                $stmtUpdate->execute([$status, $groupId]);

                // Fetch group code
                $stmtG = $db->prepare("SELECT group_code FROM savings_groups WHERE id = ?");
                $stmtG->execute([$groupId]);
                $groupCode = $stmtG->fetchColumn();

                // Fetch members
                $stmtMembers = $db->prepare("SELECT id, customer_id FROM savings_plans WHERE group_id = ?");
                $stmtMembers->execute([$groupId]);
                $members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);

                require_once 'models/SavingsPlan.php';
                $planModel = new SavingsPlan($db);

                $msgMap = [
                    'Hewan Dibeli' => "Hewan qurban kelompok Anda ({$groupCode}) sudah dibeli dan siap untuk proses qurban.",
                    'Disembelih' => "Proses penyembelihan hewan qurban kelompok Anda ({$groupCode}) sedang/telah dilaksanakan.",
                    'Selesai' => "Seluruh proses qurban kelompok Anda ({$groupCode}) telah selesai. Terima kasih!"
                ];
                
                $message = $msgMap[$status] ?? "Status kelompok Anda ({$groupCode}) diubah menjadi {$status}.";

                foreach ($members as $member) {
                    $planModel->addNotification($member['customer_id'], $message);
                    
                    if ($status === 'Selesai') {
                        $stmtPlanUpdate = $db->prepare("UPDATE savings_plans SET status = 'Selesai' WHERE id = ?");
                        $stmtPlanUpdate->execute([$member['id']]);
                    }
                }

            } elseif ($action === 'move_member') {
                $planId = filter_input(INPUT_POST, 'plan_id', FILTER_VALIDATE_INT);
                $targetGroupId = filter_input(INPUT_POST, 'target_group_id', FILTER_VALIDATE_INT);

                // Check member count in target group
                $stmtCount = $db->prepare("SELECT COUNT(*) FROM savings_plans WHERE group_id = ?");
                $stmtCount->execute([$targetGroupId]);
                $count = (int)$stmtCount->fetchColumn();

                if ($count >= 7) {
                    throw new RuntimeException('Kelompok tujuan sudah penuh.');
                }

                // Update group_id
                $stmtMove = $db->prepare("UPDATE savings_plans SET group_id = ? WHERE id = ?");
                $stmtMove->execute([$targetGroupId, $planId]);

                // Fetch target group code
                $stmtTG = $db->prepare("SELECT group_code FROM savings_groups WHERE id = ?");
                $stmtTG->execute([$targetGroupId]);
                $targetGroupCode = $stmtTG->fetchColumn();

                // Fetch customer details
                $stmtPlan = $db->prepare("SELECT customer_id FROM savings_plans WHERE id = ?");
                $stmtPlan->execute([$planId]);
                $customerId = $stmtPlan->fetchColumn();

                require_once 'models/SavingsPlan.php';
                $planModel = new SavingsPlan($db);
                $planModel->addNotification($customerId, "Anda telah dipindahkan oleh admin ke kelompok qurban {$targetGroupCode}.");

                // Update statuses of both original and target groups if they changed fullness
                $groupsToCheck = [$groupId, $targetGroupId];
                foreach ($groupsToCheck as $gId) {
                    if (!$gId) continue;
                    
                    $stmtGC = $db->prepare("
                        SELECT status, (SELECT COUNT(*) FROM savings_plans WHERE group_id = ?) AS m_count 
                        FROM savings_groups WHERE id = ?
                    ");
                    $stmtGC->execute([$gId, $gId]);
                    $grpData = $stmtGC->fetch(PDO::FETCH_ASSOC);

                    if ($grpData) {
                        $newStatus = $grpData['status'];
                        if ($grpData['m_count'] >= 7 && $grpData['status'] === 'Menunggu Anggota') {
                            $newStatus = 'Penuh';
                        } elseif ($grpData['m_count'] < 7 && $grpData['status'] === 'Penuh') {
                            $newStatus = 'Menunggu Anggota';
                        }
                        
                        if ($newStatus !== $grpData['status']) {
                            $db->prepare("UPDATE savings_groups SET status = ? WHERE id = ?")->execute([$newStatus, $gId]);
                        }
                    }
                }
            }

            $db->commit();
            header('Location: /lautan-ternak-pantura/savings/groups?success=added');
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            header('Location: /lautan-ternak-pantura/savings/groups?error=' . urlencode($e->getMessage()));
        }
        exit;
    }
}
