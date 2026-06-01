<?php
require_once 'models/CashAccount.php';
require_once 'models/Investor.php';
require_once 'models/InvestorFund.php';
require_once 'models/OperationalCategory.php';
require_once 'models/OperationalExpense.php';
require_once 'models/CashTransaction.php';

class FinanceController {

    private function dbConnect() {
        global $conn;
        if (!isset($conn)) {
            require 'config/database.php';
        }
        return $conn;
    }

    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /lautan-ternak-pantura/auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    /**
     * Kas & Bank Submenu
     */
    public function cash() {
        $this->checkAdmin();
        $db = $this->dbConnect();
        $accountModel = new CashAccount($db);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            try {
                if ($action === 'add') {
                    $data = [
                        'name' => trim($_POST['name'] ?? ''),
                        'type' => $_POST['type'] ?? 'bank',
                        'account_number' => trim($_POST['account_number'] ?? ''),
                        'bank_name' => trim($_POST['bank_name'] ?? ''),
                        'opening_balance' => floatval(preg_replace('/\D/', '', $_POST['opening_balance'] ?? '0')),
                        'description' => trim($_POST['description'] ?? ''),
                        'status' => $_POST['status'] ?? 'active'
                    ];

                    if (empty($data['name'])) throw new Exception("Nama rekening kas wajib diisi.");

                    if ($accountModel->create($data)) {
                        $_SESSION['success_msg'] = "Rekening kas berhasil ditambahkan.";
                    } else {
                        throw new Exception("Gagal menambahkan rekening kas.");
                    }
                } elseif ($action === 'edit') {
                    $id = intval($_POST['id'] ?? 0);
                    $data = [
                        'name' => trim($_POST['name'] ?? ''),
                        'type' => $_POST['type'] ?? 'bank',
                        'account_number' => trim($_POST['account_number'] ?? ''),
                        'bank_name' => trim($_POST['bank_name'] ?? ''),
                        'status' => $_POST['status'] ?? 'active',
                        'description' => trim($_POST['description'] ?? '')
                    ];

                    if (empty($data['name'])) throw new Exception("Nama rekening kas wajib diisi.");

                    if ($accountModel->update($id, $data)) {
                        $_SESSION['success_msg'] = "Data rekening kas berhasil diperbarui.";
                    } else {
                        throw new Exception("Gagal memperbarui rekening kas.");
                    }
                } elseif ($action === 'delete') {
                    $id = intval($_POST['id'] ?? 0);
                    if ($accountModel->delete($id)) {
                        $_SESSION['success_msg'] = "Rekening kas berhasil dihapus.";
                    } else {
                        throw new Exception("Gagal menghapus rekening kas.");
                    }
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            header("Location: /lautan-ternak-pantura/finance/cash");
            exit;
        }

        $accounts = $accountModel->getAll();
        require 'views/admin/finance_cash.php';
    }

    /**
     * Modal Investor Submenu
     */
    public function investor() {
        $this->checkAdmin();
        $db = $this->dbConnect();
        
        $investorModel = new Investor($db);
        $fundModel = new InvestorFund($db);
        $accountModel = new CashAccount($db);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            try {
                if ($action === 'add_investor') {
                    $data = [
                        'name' => trim($_POST['name'] ?? ''),
                        'phone' => trim($_POST['phone'] ?? ''),
                        'address' => trim($_POST['address'] ?? '')
                    ];
                    if ($investorModel->create($data)) {
                        $_SESSION['success_msg'] = "Investor baru berhasil didaftarkan.";
                    }
                } elseif ($action === 'edit_investor') {
                    $id = intval($_POST['id'] ?? 0);
                    $data = [
                        'name' => trim($_POST['name'] ?? ''),
                        'phone' => trim($_POST['phone'] ?? ''),
                        'address' => trim($_POST['address'] ?? '')
                    ];
                    if ($investorModel->update($id, $data)) {
                        $_SESSION['success_msg'] = "Profil investor berhasil diperbarui.";
                    }
                } elseif ($action === 'delete_investor') {
                    $id = intval($_POST['id'] ?? 0);
                    if ($investorModel->delete($id)) {
                        $_SESSION['success_msg'] = "Data investor berhasil dihapus.";
                    }
                } elseif ($action === 'add_fund') {
                    $investorId = intval($_POST['investor_id'] ?? 0);
                    $accountId = intval($_POST['cash_account_id'] ?? 0);
                    $amount = floatval(preg_replace('/\D/', '', $_POST['amount'] ?? '0'));
                    $date = $_POST['date'] ?? date('Y-m-d');
                    $description = trim($_POST['description'] ?? '');

                    $investor = $investorModel->getById($investorId);
                    if (!$investor) throw new Exception("Investor tidak valid.");

                    // Upload Proof of Transfer
                    $proof = null;
                    if (isset($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['proof']['tmp_name'];
                        $fileName = $_FILES['proof']['name'];
                        $fileSize = $_FILES['proof']['size'];

                        if ($fileSize > 2 * 1024 * 1024) throw new Exception("Bukti transfer terlalu besar. Maksimal 2MB.");

                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                        if (!in_array($fileExtension, $allowedExtensions)) throw new Exception("Format foto tidak diperbolehkan.");

                        $uploadDir = 'assets/uploads/receipts/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                        $newFileName = 'invest_proof_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                        $uploadPath = $uploadDir . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                            $proof = '/lautan-ternak-pantura/' . $uploadPath;
                        } else {
                            throw new Exception("Gagal menyimpan bukti transfer.");
                        }
                    }

                    $data = [
                        'investor_id' => $investorId,
                        'investor_name' => $investor['name'],
                        'cash_account_id' => $accountId,
                        'date' => $date,
                        'amount' => $amount,
                        'proof' => $proof,
                        'description' => $description,
                        'status' => 'active',
                        'created_by' => $_SESSION['user_id']
                    ];

                    if ($fundModel->create($data)) {
                        $_SESSION['success_msg'] = "Setoran modal investor berhasil dicatat & kas bertambah.";
                    }
                } elseif ($action === 'delete_fund') {
                    $id = intval($_POST['id'] ?? 0);
                    if ($fundModel->delete($id, $_SESSION['user_id'])) {
                        $_SESSION['success_msg'] = "Catatan investasi dibatalkan dan saldo kas disesuaikan.";
                    }
                } elseif ($action === 'complete_fund') {
                    $id = intval($_POST['id'] ?? 0);
                    if ($fundModel->updateStatus($id, 'completed')) {
                        $_SESSION['success_msg'] = "Investasi modal berhasil diselesaikan.";
                    }
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            header("Location: /lautan-ternak-pantura/finance/investor");
            exit;
        }

        $investors = $investorModel->getAll();
        $funds = $fundModel->getAll();
        $accounts = $accountModel->getActiveAccounts();
        require 'views/admin/finance_investor.php';
    }

    /**
     * Dana Operasional Submenu
     */
    public function operasional() {
        $this->checkAdmin();
        $db = $this->dbConnect();

        $categoryModel = new OperationalCategory($db);
        $expenseModel = new OperationalExpense($db);
        $accountModel = new CashAccount($db);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            try {
                if ($action === 'add_category') {
                    $name = trim($_POST['name'] ?? '');
                    if ($categoryModel->create($name)) {
                        $_SESSION['success_msg'] = "Kategori pengeluaran baru berhasil didaftarkan.";
                    }
                } elseif ($action === 'add_expense') {
                    $categoryId = intval($_POST['category_id'] ?? 0);
                    $accountId = intval($_POST['cash_account_id'] ?? 0);
                    $amount = floatval(preg_replace('/\D/', '', $_POST['amount'] ?? '0'));
                    $date = $_POST['date'] ?? date('Y-m-d');
                    $description = trim($_POST['description'] ?? '');

                    // Fetch category details
                    $categoriesList = $categoryModel->getAll();
                    $categoryName = '';
                    foreach ($categoriesList as $cat) {
                        if ($cat['id'] == $categoryId) {
                            $categoryName = $cat['name'];
                            break;
                        }
                    }

                    // Handle Receipt Attachment Upload
                    $attachment = null;
                    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['attachment']['tmp_name'];
                        $fileName = $_FILES['attachment']['name'];
                        $fileSize = $_FILES['attachment']['size'];

                        if ($fileSize > 2 * 1024 * 1024) throw new Exception("Nota transaksi terlalu besar. Maksimal 2MB.");

                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'];
                        if (!in_array($fileExtension, $allowedExtensions)) throw new Exception("Format nota tidak didukung.");

                        $uploadDir = 'assets/uploads/expenses/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                        $newFileName = 'exp_proof_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                        $uploadPath = $uploadDir . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                            $attachment = '/lautan-ternak-pantura/' . $uploadPath;
                        } else {
                            throw new Exception("Gagal menyimpan nota transaksi.");
                        }
                    }

                    $data = [
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                        'cash_account_id' => $accountId,
                        'date' => $date,
                        'amount' => $amount,
                        'description' => $description,
                        'attachment' => $attachment,
                        'created_by' => $_SESSION['user_id']
                    ];

                    if ($expenseModel->create($data)) {
                        $_SESSION['success_msg'] = "Pengeluaran operasional berhasil dicatat & kas dikurangi.";
                    }
                } elseif ($action === 'delete_expense') {
                    $id = intval($_POST['id'] ?? 0);
                    if ($expenseModel->delete($id, $_SESSION['user_id'])) {
                        $_SESSION['success_msg'] = "Catatan pengeluaran berhasil dihapus & saldo kas disesuaikan.";
                    }
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            header("Location: /lautan-ternak-pantura/finance/operasional");
            exit;
        }

        // Filters
        $filterCategoryId = $_GET['category_id'] ?? null;
        $filterStartDate = $_GET['start_date'] ?? null;
        $filterEndDate = $_GET['end_date'] ?? null;

        $categories = $categoryModel->getAll();
        $expenses = $expenseModel->getAll($filterCategoryId, $filterStartDate, $filterEndDate);
        $accounts = $accountModel->getActiveAccounts();
        require 'views/admin/finance_operational.php';
    }

    /**
     * Buku Besar Arus Kas Submenu
     */
    public function arusKas() {
        $this->checkAdmin();
        $db = $this->dbConnect();

        $transactionModel = new CashTransaction($db);
        $accountModel = new CashAccount($db);

        // Filters
        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $accountId = $_GET['cash_account_id'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $transactions = $transactionModel->getLedger($search, $type, $accountId, $startDate, $endDate);
        $accounts = $accountModel->getAll();
        require 'views/admin/finance_cash_transactions.php';
    }
}
