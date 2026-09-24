<?php

$host = "localhost";
$dbname = "estacionamento_novo";
$user = "root";
$pass = "";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {

    die(
        "Erro na conexão com o banco: " .
        htmlspecialchars($e->getMessage())
    );

}

?>
