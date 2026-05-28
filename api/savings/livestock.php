<?php
require_once '../../config/database.php';
require_once '../../models/Livestock.php';

header('Content-Type: application/json');

try {
    if (!isset($conn)) {
        throw new RuntimeException('Koneksi database tidak tersedia.');
    }

    $livestockModel = new Livestock($conn);
    $rows = $livestockModel->getAvailable();
    $data = array_map(function ($item) {
        $price = (float)($item['price'] ?? $item['selling_price'] ?? 0);
        return [
            'id' => (int)$item['id'],
            'name' => $item['name'] ?? $item['breed'] ?? 'Hewan Qurban',
            'type' => $item['breed'] ?? $item['name'] ?? 'Hewan Qurban',
            'code' => $item['livestock_code'] ?? '',
            'price' => $price,
            'monthly_estimate' => round($price / 10),
            'image' => $item['image'] ?: '/lautan-ternak-pantura/assets/images/landing-page.jpg',
            'status' => $item['status'] ?? 'available'
        ];
    }, $rows);

    echo json_encode(['success' => true, 'data' => $data]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
