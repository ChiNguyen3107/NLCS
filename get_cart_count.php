<?php
session_start();

$count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        if (is_numeric($quantity)) {
            $count += (int)$quantity;
        }
    }
}

echo $count;
?>