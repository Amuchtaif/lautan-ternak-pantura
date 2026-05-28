<?php

class OrderController {

    /**
     * Helper method to perform safe redirects inside the MVC structure
     */
    private function redirect($path) {
        header("Location: /lautan-ternak-pantura/" . ltrim($path, '/'));
        exit;
    }

    /**
     * Redirect customer checkout flow to SalesController
     */
    public function checkout($livestockId = null) {
        $this->redirect("sales/checkout/" . ($livestockId ? $livestockId : ''));
    }

    /**
     * Redirect customer order list to SalesController
     */
    public function orders() {
        $this->redirect("sales/my_orders");
    }

    /**
     * Redirect customer order detail to SalesController
     */
    public function order_detail($id = null) {
        $this->redirect("sales/order_detail/" . ($id ? $id : ''));
    }

    /**
     * Redirect admin transactions list view to SalesController, preserving search and query parameters
     */
    public function transactions() {
        $query = !empty($_GET) ? '?' . http_build_query($_GET) : '';
        $this->redirect("sales/index" . $query);
    }

    /**
     * Redirect admin transaction details view to SalesController
     */
    public function transaction_detail($id = null) {
        $this->redirect("sales/detail/" . ($id ? $id : ''));
    }
}
