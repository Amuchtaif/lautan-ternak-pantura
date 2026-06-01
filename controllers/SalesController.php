<?php
require_once 'models/Sale.php';
require_once 'models/SalePayment.php';
require_once 'models/Livestock.php';

class SalesController {

    private function dbConnect() {
        global $conn;
        if (!isset($conn)) {
            require 'config/database.php';
        }
        return $conn;
    }

    private function checkAuth($role = null) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id'])) {
            header("Location: /lautan-ternak-pantura/auth/login?redirect=" . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        if ($role && $_SESSION['role'] !== $role) {
            header("Location: /lautan-ternak-pantura/auth/login?redirect=" . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    // [ADMIN] Lists all transactions
    public function index() {
        $this->checkAuth('admin');
        $db = $this->dbConnect();

        $search = $_GET['search'] ?? '';
        $payment_status = $_GET['payment_status'] ?? '';
        $sale_status = $_GET['sale_status'] ?? '';

        $saleModel = new Sale($db);
        $salesList = $saleModel->getAll($search, $payment_status, $sale_status);

        // Fetch available livestock list for creation modal
        $livestockModel = new Livestock($db);
        $livestockList = $livestockModel->getAvailable();

        // Fetch registered customers list for selection dropdown
        $customerStmt = $db->query("SELECT id, name, full_name, phone, address FROM users WHERE role = 'customer' ORDER BY name ASC");
        $customerList = $customerStmt ? $customerStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        foreach ($customerList as &$cust) {
            if (empty($cust['full_name'])) {
                $cust['full_name'] = $cust['name'] ?: '';
            }
            if (empty($cust['name'])) {
                $cust['name'] = $cust['full_name'] ?: '';
            }
        }

        require 'views/admin/sales.php';
    }

    // [ADMIN] Render manual order form / handle post creation
    public function create() {
        $this->checkAuth('admin');
        $db = $this->dbConnect();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customerName = trim($_POST['customer_name'] ?? '');
            $customerPhone = trim($_POST['customer_phone'] ?? '');
            $livestockId = intval($_POST['livestock_id'] ?? 0);
            $qty = intval($_POST['qty'] ?? 1);
            $paymentType = $_POST['payment_type'] ?? 'lunas';
            $paymentAmount = floatval($_POST['payment_amount'] ?? 0);
            $paymentMethod = trim($_POST['payment_method'] ?? 'Tunai / Cash');
            $notes = trim($_POST['notes'] ?? '');

            try {
                // Optional Payment Proof Upload
                $paymentProof = null;
                if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['payment_proof']['tmp_name'];
                    $fileName = $_FILES['payment_proof']['name'];
                    $fileSize = $_FILES['payment_proof']['size'];
                    
                    // Max 2MB
                    if ($fileSize > 2 * 1024 * 1024) {
                        throw new Exception("Bukti pembayaran terlalu besar. Maksimal 2MB.");
                    }

                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    if (!in_array($fileExtension, $allowedExtensions)) {
                        throw new Exception("Format bukti pembayaran tidak diperbolehkan. Gunakan JPG, JPEG, PNG, WEBP, atau GIF.");
                    }

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $fileTmpPath);
                    finfo_close($finfo);
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    if (!in_array($mimeType, $allowedMimes)) {
                        throw new Exception("Tipe file bukti pembayaran tidak valid.");
                    }

                    $uploadDir = 'storage/uploads/receipts/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $newFileName = 'proof_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                        $paymentProof = '/lautan-ternak-pantura/' . $uploadPath;
                    } else {
                        throw new Exception("Gagal mengunggah bukti pembayaran.");
                    }
                }

                $invoiceCode = 'LTP-INV-' . time() . '-' . rand(100, 999);
                $saleModel = new Sale($db);

                $data = [
                    'invoice_code' => $invoiceCode,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'livestock_id' => $livestockId,
                    'qty' => $qty,
                    'payment_type' => $paymentType,
                    'payment_amount' => $paymentAmount,
                    'payment_method' => $paymentMethod,
                    'payment_proof' => $paymentProof,
                    'payment_note' => 'Pembayaran awal dicatat oleh Admin',
                    'initial_payment_status' => 'verified', // Admin records are verified instantly
                    'sale_status' => 'processing',
                    'notes' => $notes,
                    'created_by' => $_SESSION['user_id']
                ];

                $saleModel->create($data);

                $_SESSION['success_msg'] = "Transaksi penjualan berhasil dicatat!";
                header("Location: /lautan-ternak-pantura/sales/index");
                exit;

            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: /lautan-ternak-pantura/sales/index");
                exit;
            }
        }

        // GET requests fallback to the index list
        header("Location: /lautan-ternak-pantura/sales/index");
        exit;
    }

    // [ADMIN] Transaction detail invoice & verification ledger
    public function detail($id = null) {
        $this->checkAuth('admin');
        if (!$id) {
            header("Location: /lautan-ternak-pantura/sales/index");
            exit;
        }

        $db = $this->dbConnect();
        $saleModel = new Sale($db);
        $sale = $saleModel->getById($id);

        if (!$sale) {
            header("Location: /lautan-ternak-pantura/sales/index");
            exit;
        }

        $paymentModel = new SalePayment($db);
        $payments = $paymentModel->getBySaleId($id);
        
        $totalPaid = $saleModel->getTotalPaid($id);
        $remaining = $saleModel->getRemainingBalance($id);

        require 'views/admin/sales_detail.php';
    }

    // [CUSTOMER] Checkout page from catalog
    public function checkout($livestockId = null) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (isset($_SESSION['user_id']) && $_SESSION['role'] !== 'customer') {
            header("Location: /lautan-ternak-pantura/auth/login");
            exit;
        }
        if (!$livestockId) {
            header("Location: /lautan-ternak-pantura/marketplace");
            exit;
        }

        $db = $this->dbConnect();
        
        // Load customer data if logged in
        $userData = null;
        if (isset($_SESSION['user_id'])) {
            $userStmt = $db->prepare("SELECT full_name, name, phone, address, email FROM users WHERE id = ?");
            $userStmt->execute([$_SESSION['user_id']]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
        }

        $livestockModel = new Livestock($db);
        $livestock = $livestockModel->getById($livestockId);

        if (!$livestock || $livestock['status'] !== 'available' || $livestock['stock'] <= 0) {
            $_SESSION['error'] = "Hewan tidak tersedia untuk dibeli.";
            header("Location: /lautan-ternak-pantura/marketplace");
            exit;
        }

        require 'views/customer/checkout.php';
    }

    // [CUSTOMER] Process checkout post
    public function processCheckout() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (isset($_SESSION['user_id']) && $_SESSION['role'] !== 'customer') {
            header("Location: /lautan-ternak-pantura/auth/login");
            exit;
        }
        $db = $this->dbConnect();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /lautan-ternak-pantura/marketplace");
            exit;
        }

        $livestockId = intval($_POST['livestock_id'] ?? 0);
        $qty = intval($_POST['qty'] ?? 1);
        $paymentType = $_POST['payment_type'] ?? 'lunas';
        $paymentAmount = floatval($_POST['payment_amount'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');

        // If guest checkout, create/retrieve guest user and set session
        if (!isset($_SESSION['user_id'])) {
            if (empty($customerName) || empty($customerPhone)) {
                $_SESSION['error'] = "Nama dan nomor WhatsApp wajib diisi.";
                header("Location: /lautan-ternak-pantura/sales/checkout/" . $livestockId);
                exit;
            }

            $guestEmail = 'guest_' . preg_replace('/[^0-9]/', '', $customerPhone) . '@guest.com';
            if (empty($guestEmail) || $guestEmail === 'guest_@guest.com') {
                $guestEmail = 'guest_' . bin2hex(random_bytes(4)) . '@guest.com';
            }

            $userStmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $userStmt->execute([$guestEmail]);
            $guestUser = $userStmt->fetch(PDO::FETCH_ASSOC);

            if ($guestUser) {
                $customerId = $guestUser['id'];
                $updateStmt = $db->prepare("UPDATE users SET name = ?, full_name = ?, phone = ?, address = ? WHERE id = ?");
                $updateStmt->execute([$customerName, $customerName, $customerPhone, $notes, $customerId]);
            } else {
                $defaultPasswordHash = password_hash('password123', PASSWORD_BCRYPT);
                $insertStmt = $db->prepare("INSERT INTO users (name, full_name, email, password, role, phone, address, status) VALUES (?, ?, ?, ?, 'customer', ?, ?, 'active')");
                $insertStmt->execute([$customerName, $customerName, $guestEmail, $defaultPasswordHash, $customerPhone, $notes]);
                $customerId = $db->lastInsertId();
            }

            $_SESSION['user_id'] = $customerId;
            $_SESSION['role'] = 'customer';
            $_SESSION['full_name'] = $customerName;
            $_SESSION['name'] = $customerName;
            $_SESSION['email'] = $guestEmail;
            $_SESSION['is_login'] = true;
        }

        try {
            $livestockModel = new Livestock($db);
            $livestock = $livestockModel->getById($livestockId);
            if (!$livestock) {
                throw new Exception("Hewan tidak ditemukan.");
            }

            $totalPrice = $livestock['selling_price'] * $qty;
            
            // Check dynamic amount
            if ($paymentType === 'lunas') {
                $paymentAmount = $totalPrice;
            }

            $invoiceCode = 'LTP-INV-' . time() . '-' . rand(100, 999);
            
            // Secure photo upload for checkout payment proof
            $paymentProof = null;
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['payment_proof']['tmp_name'];
                $fileName = $_FILES['payment_proof']['name'];
                $fileSize = $_FILES['payment_proof']['size'];
                
                // Max 2MB
                if ($fileSize > 2 * 1024 * 1024) {
                    throw new Exception("Bukti transfer terlalu besar. Maksimal 2MB.");
                }

                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new Exception("Format bukti transfer tidak diperbolehkan. Gunakan JPG, JPEG, PNG, WEBP, atau GIF.");
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $fileTmpPath);
                finfo_close($finfo);
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                if (!in_array($mimeType, $allowedMimes)) {
                    throw new Exception("Tipe bukti transfer tidak valid.");
                }

                $uploadDir = 'storage/uploads/receipts/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $newFileName = 'proof_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                $uploadPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                    $paymentProof = '/lautan-ternak-pantura/' . $uploadPath;
                } else {
                    throw new Exception("Gagal mengunggah bukti transfer.");
                }
            }

            $saleModel = new Sale($db);

            $data = [
                'invoice_code' => $invoiceCode,
                'customer_name' => $_SESSION['full_name'] ?? $_SESSION['name'],
                'customer_phone' => $_POST['customer_phone'] ?? '',
                'livestock_id' => $livestockId,
                'qty' => $qty,
                'payment_type' => $paymentType,
                'payment_amount' => $paymentAmount,
                'payment_method' => 'Transfer Bank Manual',
                'payment_note' => $paymentType === 'dp' ? 'Pembayaran Uang Muka (DP)' : 'Pelunasan Transaksi',
                'payment_proof' => $paymentProof,
                'initial_payment_status' => 'pending', // Awaiting admin verification
                'sale_status' => 'pending',
                'notes' => $notes,
                'created_by' => $_SESSION['user_id']
            ];

            $saleId = $saleModel->create($data);

            $_SESSION['success_msg'] = "Pemesanan berhasil diajukan! Silakan tunggu verifikasi admin.";
            header("Location: /lautan-ternak-pantura/sales/order_detail/" . $saleId);
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: /lautan-ternak-pantura/sales/checkout/" . $livestockId);
            exit;
        }
    }

    // [CUSTOMER] Customer order history list
    public function my_orders() {
        $this->checkAuth('customer');
        $db = $this->dbConnect();

        $saleModel = new Sale($db);
        $ordersList = $saleModel->getAll('', '', '', $_SESSION['user_id']);

        require 'views/customer/my_orders.php';
    }

    // [CUSTOMER] Customer invoice details & payment uploader
    public function order_detail($id = null) {
        $this->checkAuth('customer');
        if (!$id) {
            header("Location: /lautan-ternak-pantura/sales/my_orders");
            exit;
        }

        $db = $this->dbConnect();
        $saleModel = new Sale($db);
        $sale = $saleModel->getById($id);

        if (!$sale || $sale['created_by'] != $_SESSION['user_id']) {
            header("Location: /lautan-ternak-pantura/sales/my_orders");
            exit;
        }

        $paymentModel = new SalePayment($db);
        $payments = $paymentModel->getBySaleId($id);

        $totalPaid = $saleModel->getTotalPaid($id);
        $remaining = $saleModel->getRemainingBalance($id);

        require 'views/customer/order_detail.php';
    }

    // [CUSTOMER / ADMIN] Records an installment payment
    public function record_payment() {
        $this->checkAuth();
        $db = $this->dbConnect();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /lautan-ternak-pantura/");
            exit;
        }

        $saleId = intval($_POST['sale_id'] ?? 0);
        $amount = floatval($_POST['payment_amount'] ?? 0);
        $method = trim($_POST['payment_method'] ?? 'Transfer Bank Manual');
        $notes = trim($_POST['payment_note'] ?? '');

        try {
            if ($saleId <= 0) {
                throw new Exception("ID Penjualan tidak valid.");
            }
            if ($amount <= 0) {
                throw new Exception("Jumlah cicilan pembayaran harus lebih dari 0.");
            }

            $saleModel = new Sale($db);
            $sale = $saleModel->getById($saleId);
            if (!$sale) {
                throw new Exception("Data transaksi penjualan tidak ditemukan.");
            }

            // Gating: make sure customer owns the sale or user is admin
            if ($_SESSION['role'] !== 'admin' && $sale['created_by'] != $_SESSION['user_id']) {
                throw new Exception("Akses tidak sah.");
            }

            // Check if there is an outstanding balance
            $remaining = $saleModel->getRemainingBalance($saleId);
            if ($amount > $remaining) {
                throw new Exception("Jumlah cicilan Rp " . number_format($amount, 0, ',', '.') . " melebihi sisa tagihan Rp " . number_format($remaining, 0, ',', '.') . ".");
            }

            // Secure Photo Upload for Installment Proof
            $paymentProof = null;
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['payment_proof']['tmp_name'];
                $fileName = $_FILES['payment_proof']['name'];
                $fileSize = $_FILES['payment_proof']['size'];
                
                // Max 2MB
                if ($fileSize > 2 * 1024 * 1024) {
                    throw new Exception("Bukti transfer terlalu besar. Maksimal 2MB.");
                }

                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new Exception("Format bukti transfer tidak diperbolehkan. Gunakan JPG, JPEG, PNG, WEBP, atau GIF.");
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $fileTmpPath);
                finfo_close($finfo);
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                if (!in_array($mimeType, $allowedMimes)) {
                    throw new Exception("Tipe file bukti transfer tidak valid.");
                }

                $uploadDir = 'storage/uploads/receipts/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $newFileName = 'proof_inst_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                $uploadPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                    $paymentProof = '/lautan-ternak-pantura/' . $uploadPath;
                } else {
                    throw new Exception("Gagal mengunggah bukti transfer.");
                }
            }

            $paymentCode = 'PAY-' . time() . '-' . rand(100, 999);
            $paymentModel = new SalePayment($db);

            $data = [
                'sale_id' => $saleId,
                'payment_code' => $paymentCode,
                'payment_method' => $method,
                'payment_amount' => $amount,
                'payment_note' => $notes ?: 'Pembayaran Cicilan Lanjutan',
                'payment_proof' => $paymentProof,
                'payment_status' => ($_SESSION['role'] === 'admin') ? 'verified' : 'pending',
                'created_by' => $_SESSION['user_id']
            ];

            $paymentModel->create($data);

            $_SESSION['success_msg'] = "Bukti cicilan pembayaran berhasil diunggah!";

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        // Redirect back appropriately
        if ($_SESSION['role'] === 'admin') {
            header("Location: /lautan-ternak-pantura/sales/detail/" . $saleId);
        } else {
            header("Location: /lautan-ternak-pantura/sales/order_detail/" . $saleId);
        }
        exit;
    }

    // [ADMIN] Verify an installment payment receipt
    public function verifyPayment() {
        $this->checkAuth('admin');
        $db = $this->dbConnect();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /lautan-ternak-pantura/sales/index");
            exit;
        }

        $paymentId = intval($_POST['payment_id'] ?? 0);
        $status = $_POST['status'] === 'verified' ? 'verified' : 'rejected';

        try {
            $paymentModel = new SalePayment($db);
            $payment = $paymentModel->getById($paymentId);
            if (!$payment) {
                throw new Exception("Pembayaran tidak ditemukan.");
            }

            $paymentModel->updateStatus($paymentId, $status);
            $_SESSION['success_msg'] = "Status pembayaran berhasil diperbarui menjadi: " . strtoupper($status);
            header("Location: /lautan-ternak-pantura/sales/detail/" . $payment['sale_id']);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: /lautan-ternak-pantura/sales/index");
            exit;
        }
    }

    // [ADMIN] Update general delivery status
    public function updateStatus() {
        $this->checkAuth('admin');
        $db = $this->dbConnect();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /lautan-ternak-pantura/sales/index");
            exit;
        }

        $saleId = intval($_POST['sale_id'] ?? 0);
        $status = $_POST['sale_status'] ?? 'pending';

        try {
            $saleModel = new Sale($db);
            $sale = $saleModel->getById($saleId);
            if (!$sale) {
                throw new Exception("Transaksi penjualan tidak ditemukan.");
            }

            $saleModel->updateStatus($saleId, ['sale_status' => $status]);
            $_SESSION['success_msg'] = "Status pesanan berhasil diperbarui!";
            header("Location: /lautan-ternak-pantura/sales/detail/" . $saleId);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: /lautan-ternak-pantura/sales/detail/" . $saleId);
            exit;
        }
    }

    // [ADMIN] Delete order entirely
    public function delete() {
        $this->checkAuth('admin');
        $db = $this->dbConnect();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $saleModel = new Sale($db);
            try {
                if ($saleModel->delete($id)) {
                    $_SESSION['success_msg'] = "Transaksi penjualan berhasil dibatalkan dan dihapus. Inventori hewan dikembalikan.";
                } else {
                    throw new Exception("Gagal menghapus transaksi.");
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        header("Location: /lautan-ternak-pantura/sales/index");
        exit;
    }
}
