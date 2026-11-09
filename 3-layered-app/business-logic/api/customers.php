<?php
/**
 * ============================================================================
 * LAYER 2: BUSINESS LOGIC LAYER — REST API Endpoint (api/customers.php)
 * Tugas : Menerima request HTTP (GET, POST, PUT, DELETE) dari Presentation
 *         Layer (Frontend), memprosesnya melalui CustomerService, dan
 *         mengirimkan hasil kembali dalam format JSON.
 * ============================================================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/CustomerService.php';

try {
    $service = new CustomerService();
    $method  = $_SERVER['REQUEST_METHOD'];

    // Membaca data JSON yang dikirim oleh Frontend (untuk POST dan PUT)
    $rawBody = file_get_contents('php://input');
    $input   = json_decode($rawBody, true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($method === 'GET') {
        // Jika ada parameter ?id=... ambil 1 pelanggan, jika tidak ambil semua / cari (?q=...)
        if ($id > 0) {
            $hasil = $service->getCustomerById($id);
        } else {
            $keyword = $_GET['q'] ?? '';
            $hasil   = $service->listCustomers($keyword);
        }
    } elseif ($method === 'POST') {
        // Tambah pelanggan baru
        $hasil = $service->createCustomer($input);
    } elseif ($method === 'PUT') {
        // Edit pelanggan berdasarkan ID
        $hasil = $service->updateCustomer($id, $input);
    } elseif ($method === 'DELETE') {
        // Hapus pelanggan berdasarkan ID
        $hasil = $service->deleteCustomer($id);
    } else {
        $hasil = [
            'status_code' => 405,
            'success'     => false,
            'message'     => 'Method HTTP tidak didukung.',
        ];
    }

    // Kirim status HTTP dan output JSON ke Presentation Layer
    http_response_code($hasil['status_code'] ?? 200);
    echo json_encode($hasil, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status_code' => 500,
        'success'     => false,
        'message'     => $e->getMessage(),
        'errors'      => [$e->getMessage()],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
