<?php
// CONEXAO VIA PDO
$dsn = 'mysql:host=localhost;dbname=ecoleta;charset=utf8';
$username = 'root';
$password = '';
 
try {
    $conexao = new PDO($dsn, $username, $password);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
 