<?php
// config/db.php — Database Connection

$host     = 'localhost';
$dbname   = 'service_finder';
$username = 'root';
$password = '';   // Default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<h2 style='color:red;font-family:sans-serif;padding:20px;'>
        Database Connection Failed: " . $e->getMessage() . "
        <br><small>Make sure MySQL is running in XAMPP and you ran setup.sql</small>
    </h2>");
}
?>
