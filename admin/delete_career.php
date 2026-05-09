<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM careers WHERE id = $id");
    header("Location: manage_careers.php");
    exit;
}
