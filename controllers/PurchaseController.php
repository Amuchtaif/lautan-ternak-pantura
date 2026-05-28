<?php
require_once 'models/Purchase.php';
require_once 'models/Livestock.php';

class PurchaseController {

    private function dbConnect() {
        global $conn;
        if (!isset($conn)) {
            require 'config/database.php';
        }
        return $conn;
    }

    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: /lautan-ternak-pantura/auth/login");
            exit;
        }
    }

    // List all purchases with filters
    public function index() {
        $this->checkAdmin();
        $db = $this->dbConnect();

        $search = $_GET['search'] ?? '';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';

        $purchaseModel = new Purchase($db);
        $purchasesList = $purchaseModel->getAll($search, $start_date, $end_date);

        // Fetch all purchase payments for ledger history
        $stmt = $db->query("SELECT * FROM purchase_payments ORDER BY payment_date DESC");
        $purchasePayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch registered livestock list for modal dropdown selection
        $livestockModel = new Livestock($db);
        $livestockList = $livestockModel->getAll();

        require 'views/admin/purchases.php';
    }

    // Record new purchase log
    public function create() {
        $this->checkAdmin();
        $db = $this->dbConnect();
        
        $errorMsg = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $purchaseType = $_POST['purchase_type'] ?? 'new';
            $qty = intval($_POST['qty'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            $purchasedAt = $_POST['purchased_at'] ?? '';

            try {
                if ($qty <= 0) {
                    throw new Exception("Jumlah pembelian harus minimal 1.");
                }

                $paymentType = $_POST['payment_type'] ?? 'lunas';
                if (!in_array($paymentType, ['dp', 'lunas'])) {
                    $paymentType = 'lunas';
                }

                $livestockModel = new Livestock($db);
                $livestockId = 0;
                $peternakName = '';
                $purchasePrice = 0;

                if ($purchaseType === 'new') {
                    // Create new livestock
                    $livestockName = trim($_POST['livestock_name'] ?? '');
                    $peternakName = trim($_POST['peternak_name'] ?? '');
                    $breed = trim($_POST['breed'] ?? ''); // kambing, sapi, domba
                    $gender = $_POST['gender'] ?? 'male';
                    $age = intval($_POST['age'] ?? 0);
                    $weight = floatval($_POST['weight'] ?? 0);
                    $rawPurchasePrice = $_POST['purchase_price'] ?? '';
                    $rawSellingPrice = $_POST['selling_price'] ?? '';
                    $description = trim($_POST['description'] ?? '');

                    // Clean formatting dots from price inputs
                    $purchasePrice = floatval(preg_replace('/\D/', '', $rawPurchasePrice));
                    $sellingPrice = floatval(preg_replace('/\D/', '', $rawSellingPrice));

                    if (empty($livestockName)) {
                        throw new Exception("Nama hewan harus diisi.");
                    }
                    if (empty($peternakName)) {
                        throw new Exception("Nama mitra peternak / supplier harus diisi.");
                    }
                    if (empty($breed)) {
                        throw new Exception("Kategori hewan harus diisi.");
                    }
                    if ($purchasePrice <= 0) {
                        throw new Exception("Harga beli harus lebih dari 0.");
                    }
                    if ($sellingPrice <= 0) {
                        throw new Exception("Harga jual harus lebih dari 0.");
                    }
                    if ($sellingPrice < $purchasePrice) {
                        throw new Exception("Harga jual tidak boleh lebih kecil dari harga beli.");
                    }

                    // Handle secure photo upload
                    $image = 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80'; // fallback
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['image']['tmp_name'];
                        $fileName = $_FILES['image']['name'];
                        $fileSize = $_FILES['image']['size'];

                        // Validate file size (max 2MB)
                        if ($fileSize > 2 * 1024 * 1024) {
                            throw new Exception("File foto terlalu besar. Maksimal 2MB.");
                        }

                        // Validate extension
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                        if (!in_array($fileExtension, $allowedExtensions)) {
                            throw new Exception("Format foto tidak diperbolehkan. Gunakan JPG, JPEG, PNG, WEBP, atau GIF.");
                        }

                        // Validate MIME Type
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $fileTmpPath);
                        finfo_close($finfo);
                        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                        if (!in_array($mimeType, $allowedMimes)) {
                            throw new Exception("Tipe file tidak valid. Harap unggah file gambar asli.");
                        }

                        $uploadDir = 'assets/uploads/livestock/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        $newFileName = 'livestock_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                        $uploadPath = $uploadDir . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                            $image = '/lautan-ternak-pantura/' . $uploadPath;
                        } else {
                            throw new Exception("Gagal menyimpan foto ke server.");
                        }
                    }

                    // Generate a new unique livestock code
                    $code = 'LTP-LIV-' . time() . rand(10, 99);

                    // Combine breed and name
                    $breedDbValue = ucfirst($breed) . ' ' . $livestockName;

                    $livestockData = [
                        'livestock_code' => $code,
                        'peternak_name' => $peternakName,
                        'breed' => $breedDbValue,
                        'age' => $age,
                        'weight' => $weight,
                        'gender' => $gender,
                        'purchase_price' => $purchasePrice,
                        'selling_price' => $sellingPrice,
                        'stock' => 0,
                        'status' => 'available',
                        'image' => $image,
                        'description' => $description
                    ];

                    if ($livestockModel->create($livestockData)) {
                        $livestockId = intval($db->lastInsertId());
                    } else {
                        throw new Exception("Gagal mencatat hewan baru di database.");
                    }
                } else {
                    // Existing livestock purchase
                    $livestockId = intval($_POST['livestock_id'] ?? 0);
                    if ($livestockId <= 0) {
                        throw new Exception("Hewan terdaftar harus dipilih.");
                    }

                    $livestock = $livestockModel->getById($livestockId);
                    if (!$livestock) {
                        throw new Exception("Hewan tidak ditemukan di database.");
                    }

                    $peternakName = $livestock['peternak_name'];
                    $purchasePrice = floatval($livestock['purchase_price']);
                }

                $rawAmountPaid = $_POST['amount_paid'] ?? '';
                $amountPaid = floatval(preg_replace('/\D/', '', $rawAmountPaid));

                if ($paymentType === 'dp') {
                    if ($amountPaid <= 0) {
                        throw new Exception("Pembayaran awal (DP) harus diisi dan lebih besar dari 0.");
                    }
                    $total_purchase = $qty * $purchasePrice;
                    if ($amountPaid >= $total_purchase) {
                        throw new Exception("Pembayaran DP tidak boleh melebihi atau sama dengan total harga beli. Gunakan tipe Lunas.");
                    }
                }

                $purchaseCode = 'LTP-PUR-' . time() . '-' . rand(10, 99);

                $purchaseModel = new Purchase($db);

                $purchaseData = [
                    'purchase_code' => $purchaseCode,
                    'livestock_id' => $livestockId,
                    'peternak_name' => $peternakName,
                    'qty' => $qty,
                    'purchase_price' => $purchasePrice,
                    'payment_type' => $paymentType,
                    'amount_paid' => $amountPaid,
                    'notes' => $notes,
                    'created_by' => $_SESSION['user_id'],
                    'purchased_at' => $purchasedAt ?: date('Y-m-d H:i:s')
                ];

                if ($purchaseModel->create($purchaseData)) {
                    $_SESSION['success_msg'] = "Pembelian stok hewan berhasil dicatat!";
                    header("Location: /lautan-ternak-pantura/purchase/index");
                    exit;
                } else {
                    throw new Exception("Gagal mencatat transaksi pembelian.");
                }

            } catch (Exception $e) {
                $errorMsg = $e->getMessage();
            }
        }

        $livestockModel = new Livestock($db);
        $livestockList = $livestockModel->getAll(); // Load all livestock for selection
        
        require 'views/admin/purchase_create.php';
    }

    // Edit purchase record
    public function edit() {
        $this->checkAdmin();
        $db = $this->dbConnect();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['livestock_name'] ?? '');
            $breed = trim($_POST['breed'] ?? ''); // kambing, sapi, domba
            $weight = floatval($_POST['weight'] ?? 0);
            $qty = intval($_POST['qty'] ?? 1);
            $notes = trim($_POST['notes'] ?? '');

            $rawPurchasePrice = $_POST['purchase_price'] ?? '';
            $rawSellingPrice = $_POST['selling_price'] ?? '';

            // Clean formatting dots from price inputs
            $purchasePrice = floatval(preg_replace('/\D/', '', $rawPurchasePrice));
            $sellingPrice = floatval(preg_replace('/\D/', '', $rawSellingPrice));

            try {
                if (empty($name)) {
                    throw new Exception("Nama hewan harus diisi.");
                }
                if (empty($breed)) {
                    throw new Exception("Kategori hewan harus diisi.");
                }
                if ($weight <= 0) {
                    throw new Exception("Berat hewan harus lebih dari 0.");
                }
                if ($qty <= 0) {
                    throw new Exception("Jumlah pembelian harus lebih dari 0.");
                }
                if ($purchasePrice <= 0) {
                    throw new Exception("Harga beli harus lebih dari 0.");
                }
                if ($sellingPrice <= 0) {
                    throw new Exception("Harga jual harus lebih dari 0.");
                }
                if ($sellingPrice < $purchasePrice) {
                    throw new Exception("Harga jual tidak boleh lebih kecil dari harga beli.");
                }

                $purchaseModel = new Purchase($db);
                $data = [
                    'livestock_name' => $name,
                    'breed' => $breed,
                    'weight' => $weight,
                    'qty' => $qty,
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $sellingPrice,
                    'notes' => $notes
                ];

                if ($purchaseModel->update($id, $data)) {
                    $_SESSION['success_msg'] = "Data pembelian dan stok inventori berhasil diperbarui.";
                } else {
                    throw new Exception("Gagal memperbarui data pembelian.");
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        header("Location: /lautan-ternak-pantura/purchase/index");
        exit;
    }

    // Delete purchase record
    public function delete() {
        $this->checkAdmin();
        $db = $this->dbConnect();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $purchaseModel = new Purchase($db);
            try {
                if ($purchaseModel->delete($id)) {
                    $_SESSION['success_msg'] = "Data pembelian berhasil dihapus dan stok hewan disesuaikan.";
                } else {
                    throw new Exception("Gagal menghapus data pembelian.");
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        header("Location: /lautan-ternak-pantura/purchase/index");
        exit;
    }

    // Record custom payment instalment for breeder purchases
    public function recordPayment() {
        $this->checkAdmin();
        $db = $this->dbConnect();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $purchaseId = intval($_POST['purchase_id'] ?? 0);
            $rawAmount = $_POST['payment_amount'] ?? '';
            $amount = floatval(preg_replace('/\D/', '', $rawAmount));
            $paymentDate = trim($_POST['payment_date'] ?? '');
            
            try {
                if ($purchaseId <= 0) {
                    throw new Exception("ID pembelian tidak valid.");
                }
                if ($amount <= 0) {
                    throw new Exception("Jumlah pembayaran harus lebih dari 0.");
                }
                
                $purchaseModel = new Purchase($db);
                if ($purchaseModel->recordPayment($purchaseId, $amount, $paymentDate)) {
                    $_SESSION['success_msg'] = "Pembayaran cicilan / pelunasan pembelian berhasil dicatat!";
                } else {
                    throw new Exception("Gagal mencatat pembayaran.");
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        header("Location: /lautan-ternak-pantura/purchase/index");
        exit;
    }
}
