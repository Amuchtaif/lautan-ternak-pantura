<?php
require_once 'models/Order.php';
require_once 'models/Payment.php';
require_once 'models/Livestock.php';

class OrderController {

    private function dbConnect() {
        require 'config/database.php';
        return $conn;
    }

    private function checkAuth($role = 'customer') {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
            header("Location: /lautan-ternak-pantura/views/auth/login");
            exit;
        }
    }

    // Customer: Checkout view and page rendering
    public function checkout($livestockId = null) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!$livestockId) {
            header("Location: /lautan-ternak-pantura/marketplace");
            exit;
        }

        $db = $this->dbConnect();
        $userData = null;
        
        if (isset($_SESSION['user_id'])) {
            $userStmt = $db->prepare("SELECT name, phone, address, email FROM users WHERE id = ?");
            $userStmt->execute([$_SESSION['user_id']]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
        }

        $livestockModel = new Livestock($db);
        $livestock = $livestockModel->getById($livestockId);

        if (!$livestock || $livestock['status'] !== 'available' || $livestock['stock'] <= 0) {
            header("Location: /lautan-ternak-pantura/marketplace");
            exit;
        }

        require 'views/customer/checkout.php';
    }

    // Customer: Order list view
    public function orders() {
        $this->checkAuth('customer');
        $db = $this->dbConnect();
        
        $orderModel = new Order($db);
        $ordersList = $orderModel->getByCustomerId($_SESSION['user_id']);

        require 'views/customer/orders.php';
    }

    // Customer: Order detail view
    public function order_detail($id = null) {
        $this->checkAuth('customer');
        if (!$id) {
            header("Location: /lautan-ternak-pantura/order/orders");
            exit;
        }

        $db = $this->dbConnect();
        $orderModel = new Order($db);
        $order = $orderModel->getById($id);

        if (!$order || $order['customer_id'] != $_SESSION['user_id']) {
            header("Location: /lautan-ternak-pantura/order/orders");
            exit;
        }

        $paymentModel = new Payment($db);
        $payment = $paymentModel->getByOrderId($id);

        require 'views/customer/order_detail.php';
    }

    // Admin: List all transactions
    public function transactions() {
        $this->checkAuth('admin');
        $db = $this->dbConnect();
        
        $orderModel = new Order($db);
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        $transactions = $orderModel->getAll($status, $search);

        require 'views/admin/transactions.php';
    }

    // Admin: View transaction detail and handle verification actions
    public function transaction_detail($id = null) {
        $this->checkAuth('admin');
        if (!$id) {
            header("Location: /lautan-ternak-pantura/order/transactions");
            exit;
        }

        $db = $this->dbConnect();
        $orderModel = new Order($db);
        $order = $orderModel->getById($id);

        if (!$order) {
            header("Location: /lautan-ternak-pantura/order/transactions");
            exit;
        }

        $paymentModel = new Payment($db);
        $payment = $paymentModel->getByOrderId($id);

        // Handle Status updates or Verification action
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            
            if ($action === 'verify' && $payment) {
                $status = $_POST['status'] === 'verified' ? 'verified' : 'rejected';
                
                $db->beginTransaction();
                try {
                    $paymentModel->verify($payment['id'], $_SESSION['user_id'], $status);
                    
                    $orderStatus = $status === 'verified' ? 'paid' : 'waiting_payment';
                    $orderModel->updateStatus($id, $orderStatus);
                    
                    $db->commit();
                    header("Location: /lautan-ternak-pantura/order/transaction_detail/$id?success=verified");
                    exit;
                } catch (Exception $e) {
                    $db->rollBack();
                    $error = $e->getMessage();
                }
            } elseif ($action === 'update_order_status') {
                $status = $_POST['order_status'];
                
                $oldStatus = $order['status'];
                $livestockId = $order['livestock_id'];

                $db->beginTransaction();
                try {
                    $orderModel->updateStatus($id, $status);
                    
                    // Adjust stock based on status transition
                    if ($oldStatus !== 'cancelled' && $status === 'cancelled') {
                        // Restore stock
                        $stmtL = $db->prepare("SELECT stock FROM livestock WHERE id = ?");
                        $stmtL->execute([$livestockId]);
                        $live = $stmtL->fetch(PDO::FETCH_ASSOC);
                        if ($live) {
                            $newStock = $live['stock'] + 1;
                            $stmtUpL = $db->prepare("UPDATE livestock SET stock = ?, status = 'available' WHERE id = ?");
                            $stmtUpL->execute([$newStock, $livestockId]);
                        }
                    } elseif ($oldStatus === 'cancelled' && $status !== 'cancelled') {
                        // Reduce stock
                        $stmtL = $db->prepare("SELECT stock FROM livestock WHERE id = ?");
                        $stmtL->execute([$livestockId]);
                        $live = $stmtL->fetch(PDO::FETCH_ASSOC);
                        if ($live) {
                            $newStock = max(0, $live['stock'] - 1);
                            $newStatus = $newStock == 0 ? 'sold' : 'available';
                            $stmtUpL = $db->prepare("UPDATE livestock SET stock = ?, status = ? WHERE id = ?");
                            $stmtUpL->execute([$newStock, $newStatus, $livestockId]);
                        }
                    } else {
                        // Ensure status matches current stock
                        $stmtL = $db->prepare("SELECT stock FROM livestock WHERE id = ?");
                        $stmtL->execute([$livestockId]);
                        $live = $stmtL->fetch(PDO::FETCH_ASSOC);
                        if ($live) {
                            $newStatus = $live['stock'] == 0 ? 'sold' : 'available';
                            $stmtUpL = $db->prepare("UPDATE livestock SET status = ? WHERE id = ?");
                            $stmtUpL->execute([$newStatus, $livestockId]);
                        }
                    }
                    
                    $db->commit();
                    header("Location: /lautan-ternak-pantura/order/transaction_detail/$id?success=updated");
                    exit;
                } catch (Exception $e) {
                    $db->rollBack();
                    $error = $e->getMessage();
                }
            }
        }

        require 'views/admin/transaction_detail.php';
    }
}
