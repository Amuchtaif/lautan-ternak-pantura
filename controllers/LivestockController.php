<?php
require_once 'models/Livestock.php';

class LivestockController {
    
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

    // Public / Customer Detail View
    public function detail($id = null) {
        if (!$id) {
            header("Location: /lautan-ternak-pantura/marketplace");
            exit;
        }

        $db = $this->dbConnect();
        
        $livestock = null;
        if (isset($db)) {
            $livestockModel = new Livestock($db);
            $livestock = $livestockModel->getById($id);
        }

        if (!$livestock) {
            header("Location: /lautan-ternak-pantura/marketplace");
            exit;
        }

        require 'views/livestock/detail.php';
    }

    // Admin Livestock Inventory Dashboard
    public function index() {
        $this->checkAdmin();
        $db = $this->dbConnect();

        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';

        $livestockModel = new Livestock($db);
        $livestockList = $livestockModel->getAll($category, $search);

        require 'views/admin/livestock.php';
    }

    // Admin form to record new livestock entry
    public function create() {
        $this->checkAdmin();
        $db = $this->dbConnect();
        
        $errorMsg = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim($_POST['livestock_code'] ?? '');
            $peternak = trim($_POST['peternak_name'] ?? '');
            $category = $_POST['category'] ?? 'qurban';
            $breed = trim($_POST['breed'] ?? '');
            $age = intval($_POST['age'] ?? 0);
            $weight = floatval($_POST['weight'] ?? 0);
            $gender = $_POST['gender'] ?? 'male';
            $purchasePrice = floatval($_POST['purchase_price'] ?? 0);
            $sellingPrice = floatval($_POST['selling_price'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $description = trim($_POST['description'] ?? '');

            // Handle secure photo upload
            $image = 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80'; // fallback
            
            try {
                if (empty($code)) {
                    $code = 'LTP-LIV-' . time() . rand(10, 99);
                }

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

                $livestockModel = new Livestock($db);

                $data = [
                    'livestock_code' => $code,
                    'peternak_name' => $peternak,
                    'category' => $category,
                    'breed' => $breed,
                    'age' => $age,
                    'weight' => $weight,
                    'gender' => $gender,
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $sellingPrice,
                    'stock' => $stock,
                    'status' => ($stock > 0) ? 'available' : 'sold',
                    'image' => $image,
                    'description' => $description
                ];

                if ($livestockModel->create($data)) {
                    $_SESSION['success_msg'] = "Hewan baru berhasil ditambahkan.";
                    header("Location: /lautan-ternak-pantura/livestock/index");
                    exit;
                } else {
                    throw new Exception("Gagal mencatat hewan baru di database.");
                }

            } catch (Exception $e) {
                $errorMsg = $e->getMessage();
            }
        }

        require 'views/admin/livestock_create.php';
    }

    // Admin edit livestock details
    public function edit($id = null) {
        $this->checkAdmin();
        if (!$id) {
            header("Location: /lautan-ternak-pantura/livestock/index");
            exit;
        }

        $db = $this->dbConnect();
        $livestockModel = new Livestock($db);
        $livestock = $livestockModel->getById($id);

        if (!$livestock) {
            header("Location: /lautan-ternak-pantura/livestock/index");
            exit;
        }

        $errorMsg = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim($_POST['livestock_code'] ?? '');
            $peternak = trim($_POST['peternak_name'] ?? '');
            $category = $_POST['category'] ?? 'qurban';
            $breed = trim($_POST['breed'] ?? '');
            $age = intval($_POST['age'] ?? 0);
            $weight = floatval($_POST['weight'] ?? 0);
            $gender = $_POST['gender'] ?? 'male';
            $purchasePrice = floatval($_POST['purchase_price'] ?? 0);
            $sellingPrice = floatval($_POST['selling_price'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $status = $_POST['status'] ?? 'available';
            $description = trim($_POST['description'] ?? '');

            $image = $livestock['image']; // retain old image by default

            try {
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

                $data = [
                    'livestock_code' => $code,
                    'peternak_name' => $peternak,
                    'category' => $category,
                    'breed' => $breed,
                    'age' => $age,
                    'weight' => $weight,
                    'gender' => $gender,
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $sellingPrice,
                    'stock' => $stock,
                    'status' => $status,
                    'image' => $image,
                    'description' => $description
                ];

                if ($livestockModel->update($id, $data)) {
                    $_SESSION['success_msg'] = "Data hewan berhasil diperbarui.";
                    header("Location: /lautan-ternak-pantura/livestock/index");
                    exit;
                } else {
                    throw new Exception("Gagal memperbarui data hewan di database.");
                }

            } catch (Exception $e) {
                $errorMsg = $e->getMessage();
            }
        }

        require 'views/admin/livestock_edit.php';
    }

    // Admin delete livestock
    public function delete() {
        $this->checkAdmin();
        $db = $this->dbConnect();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $livestockModel = new Livestock($db);
            
            if ($livestockModel->delete($id)) {
                $_SESSION['success_msg'] = "Hewan berhasil dihapus dari sistem.";
            } else {
                $_SESSION['error'] = "Gagal menghapus data hewan.";
            }
        }
        header("Location: /lautan-ternak-pantura/livestock/index");
        exit;
    }
}
