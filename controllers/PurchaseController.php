<?php
require_once 'models/Purchase.php';
require_once 'models/Livestock.php';

class PurchaseController {

    private function dbConnect() {
        require 'config/database.php';
        return $conn;
    }

    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: /lautan-ternak-pantura/views/auth/login");
            exit;
        }
    }

    // List all store purchase/stock logs
    public function index() {
        $this->checkAdmin();
        $db = $this->dbConnect();

        $purchaseModel = new Purchase($db);
        $purchasesList = $purchaseModel->getAll();

        $livestockModel = new Livestock($db);
        $livestockList = $livestockModel->getAll();

        require 'views/admin/purchases.php';
    }

    // Form to create purchase and execute database flows
    public function create() {
        $this->checkAdmin();
        $db = $this->dbConnect();
        
        $errorMsg = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $purchaseType = $_POST['purchase_type'] ?? 'new';

            if ($purchaseType === 'existing') {
                $livestockId = intval($_POST['livestock_id'] ?? 0);
                $qty = intval($_POST['qty'] ?? 1);
                $notes = trim($_POST['notes'] ?? '');

                if ($livestockId <= 0 || $qty <= 0) {
                    $errorMsg = "Hewan terdaftar harus dipilih dan kuantitas minimal 1.";
                } else {
                    $db->beginTransaction();
                    try {
                        $purchaseModel = new Purchase($db);
                        $livestockModel = new Livestock($db);

                        $livestock = $livestockModel->getById($livestockId);
                        if (!$livestock) {
                            throw new Exception("Hewan tidak ditemukan.");
                        }

                        $purchaseData = [
                            'livestock_name' => $livestock['name'],
                            'breed' => $livestock['breed'],
                            'weight' => $livestock['weight'],
                            'purchase_price' => $livestock['purchase_price'],
                            'selling_price' => $livestock['price'],
                            'qty' => $qty,
                            'notes' => $notes ?: "Penambahan stok hewan yang sudah ada: " . $livestock['name'],
                            'created_by' => $_SESSION['user_id']
                        ];

                        $logged = $purchaseModel->create($purchaseData);
                        if (!$logged) {
                            throw new Exception("Gagal mencatat riwayat pembelian.");
                        }

                        $updated = $livestockModel->increaseStock($livestockId, $qty);
                        if (!$updated) {
                            throw new Exception("Gagal memperbarui stok hewan di database.");
                        }

                        $db->commit();
                        $_SESSION['success_msg'] = "Pembelian stok hewan terdaftar berhasil dicatat!";
                        header("Location: /lautan-ternak-pantura/purchase/index?success=purchase");
                        exit;

                    } catch (Exception $e) {
                        $db->rollBack();
                        $errorMsg = "Error: " . $e->getMessage();
                    }
                }
            } else {
                $name = trim($_POST['livestock_name'] ?? '');
                $breed = trim($_POST['breed'] ?? '');
                $weight = floatval($_POST['weight'] ?? 0);
                $purchasePrice = floatval($_POST['purchase_price'] ?? 0);
                $sellingPrice = floatval($_POST['selling_price'] ?? 0);
                $qty = intval($_POST['qty'] ?? 1);
                $notes = trim($_POST['notes'] ?? '');
                $gender = $_POST['gender'] ?? 'male';
                $age = intval($_POST['age'] ?? 12);
                $description = trim($_POST['description'] ?? '');

                if ($sellingPrice < $purchasePrice) {
                    $errorMsg = "Harga jual tidak boleh lebih kecil dari harga beli.";
                } elseif (empty($name) || empty($breed) || $weight <= 0 || $purchasePrice <= 0 || $sellingPrice <= 0 || $qty <= 0) {
                    $errorMsg = "Semua field wajib diisi dengan nilai yang valid.";
                } else {
                    $db->beginTransaction();
                    try {
                        $purchaseModel = new Purchase($db);
                        $livestockModel = new Livestock($db);

                        // 1. Record purchase log
                        $purchaseData = [
                            'livestock_name' => $name,
                            'breed' => $breed,
                            'weight' => $weight,
                            'purchase_price' => $purchasePrice,
                            'selling_price' => $sellingPrice,
                            'qty' => $qty,
                            'notes' => $notes,
                            'created_by' => $_SESSION['user_id']
                        ];

                        $logged = $purchaseModel->create($purchaseData);
                        if (!$logged) {
                            throw new Exception("Gagal mencatat riwayat pembelian.");
                        }

                        // 2. Create new inventory stock (livestock entry)
                        $code = 'LTP-Q' . date('ymd') . rand(10, 99);

                        // Handle image upload
                        $image = 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80';
                        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                            $uploadDir = 'assets/uploads/livestock/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0777, true);
                            }
                            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                            $filename = 'livestock_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                            $uploadPath = $uploadDir . $filename;
                            
                            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                                $image = '/lautan-ternak-pantura/assets/uploads/livestock/' . $filename;
                            }
                        }

                        $livestockData = [
                            'code' => $code,
                            'name' => $name,
                            'category' => 'qurban',
                            'breed' => $breed,
                            'age' => $age,
                            'weight' => $weight,
                            'gender' => $gender,
                            'price' => $sellingPrice,
                            'purchase_price' => $purchasePrice,
                            'stock' => $qty,
                            'status' => 'available',
                            'image' => $image,
                            'description' => $description ?: "Hewan baru dari pembelian stok. Breed: $breed."
                        ];

                        $created = $livestockModel->create($livestockData);
                        if (!$created) {
                            throw new Exception("Gagal menambah stok hewan ke inventori.");
                        }

                        $db->commit();
                        $_SESSION['success_msg'] = "Pembelian stok baru berhasil dicatat!";
                        header("Location: /lautan-ternak-pantura/purchase/index?success=purchase");
                        exit;

                    } catch (Exception $e) {
                        $db->rollBack();
                        $errorMsg = "Error: " . $e->getMessage();
                    }
                }
            }
        }

        $livestockModel = new Livestock($db);
        $livestockList = $livestockModel->getAll();
        require 'views/admin/purchase_create.php';
    }

    // Edit purchase record
    public function edit() {
        $this->checkAdmin();
        $db = $this->dbConnect();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['livestock_name'] ?? '');
            $breed = trim($_POST['breed'] ?? '');
            $weight = floatval($_POST['weight'] ?? 0);
            $purchasePrice = floatval($_POST['purchase_price'] ?? 0);
            $sellingPrice = floatval($_POST['selling_price'] ?? 0);
            $qty = intval($_POST['qty'] ?? 1);
            $notes = trim($_POST['notes'] ?? '');

            if ($sellingPrice < $purchasePrice) {
                $_SESSION['error'] = "Harga jual tidak boleh lebih kecil dari harga beli.";
            } elseif (empty($name) || empty($breed) || $weight <= 0 || $purchasePrice <= 0 || $sellingPrice <= 0 || $qty <= 0) {
                $_SESSION['error'] = "Semua field wajib diisi dengan nilai yang valid.";
            } else {
                $purchaseModel = new Purchase($db);
                $data = [
                    'livestock_name' => $name,
                    'breed' => $breed,
                    'weight' => $weight,
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $sellingPrice,
                    'qty' => $qty,
                    'notes' => $notes
                ];
                
                if ($purchaseModel->update($id, $data)) {
                    $_SESSION['success_msg'] = "Data pembelian berhasil diperbarui.";
                } else {
                    $_SESSION['error'] = "Gagal memperbarui data pembelian.";
                }
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
            if ($purchaseModel->delete($id)) {
                $_SESSION['success_msg'] = "Data pembelian berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus data pembelian.";
            }
        }
        header("Location: /lautan-ternak-pantura/purchase/index");
        exit;
    }
}
