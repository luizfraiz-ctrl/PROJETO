<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "estacionamento";

$conn = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conn) {
    die("Erro ao conectar ao banco de dados: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

?>