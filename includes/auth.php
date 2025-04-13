<?php
session_start();
if (!isset($_SESSION['admin_giris'])) {
    header("Location: login.php");
    exit;
}
