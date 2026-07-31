<?php
try {
    $db = new PDO('mysql:host=127.0.0.1', 'root', '');
    $db->exec('CREATE DATABASE IF NOT EXISTS badr1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
    echo 'DB Created successfully.';
} catch (Exception $e) {
    echo $e->getMessage();
}
