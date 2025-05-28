<?php
session_start();

$dsn = 'mysql:host=localhost;dbname=u68691;charset=utf8';
$username = 'u68691';
$password = '9388506';
try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

$action = $_GET['action'] ?? 'login';
$errors = [];
$values = $_POST;

if ($action === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}
