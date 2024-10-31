<?php
session_start();
include 'config.php';

if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false]);
exit();