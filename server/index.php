<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/AmoCRMController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'price' => $_POST['price'] ?? 0,
        'time_spent' => isset($_POST['time_spent']) ? (bool)$_POST['time_spent'] : false
    ];

    try {
        var_dump($data);
        $controller = new AmoCRMController();
        $response = $controller->handleCreateDeal($data);

        header('Content-Type: application/json');
        if ($response['status'] === 'success') {
            echo json_encode(['message' => $response['message'], 'deal_id' => $response['deal_id']]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => $response['message'], 'error' => $response['error']]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Ошибка при обработке запроса', 'error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Метод не разрешен']);
}
