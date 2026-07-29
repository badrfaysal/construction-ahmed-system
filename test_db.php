<?php
echo "Testing MySQL connection...\n";

// Test 1: Try 127.0.0.1
echo "Test 1 - 127.0.0.1: ";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '', [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "OK\n";
    $pdo = null;
} catch (Exception $e) {
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Test 2: Try localhost
echo "Test 2 - localhost: ";
try {
    $pdo = new PDO('mysql:host=localhost;port=3306', 'root', '', [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "OK\n";
    $pdo = null;
} catch (Exception $e) {
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Test 3: Try ::1 (IPv6)
echo "Test 3 - ::1: ";
try {
    $pdo = new PDO('mysql:host=::1;port=3306', 'root', '', [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "OK\n";
    $pdo = null;
} catch (Exception $e) {
    echo "FAIL - " . $e->getMessage() . "\n";
}
