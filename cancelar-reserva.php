<?php

session_start();

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    die("Reserva não informada.");
}

$usuario_id = $_SESSION["usuario_id"];
$reserva_id = $_GET["id"];

/* Busca a reserva */
$sql = "SELECT vaga_id
        FROM reservas
        WHERE id = '$reserva_id'
        AND usuario_id = '$usuario_id'
        AND status = 'ativa'";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Erro ao buscar reserva: " . mysqli_error($conn));
}

if (mysqli_num_rows($resultado) == 0) {
    die("Reserva não encontrada ou já cancelada.");
}

$reserva = mysqli_fetch_assoc($resultado);

$vaga_id = $reserva["vaga_id"];

/* Cancela a reserva */
$sql = "UPDATE reservas
        SET status = 'cancelada'
        WHERE id = '$reserva_id'
        AND usuario_id = '$usuario_id'";

if (!mysqli_query($conn, $sql)) {
    die("Erro ao cancelar reserva: " . mysqli_error($conn));
}

/* Libera a vaga */
$sql = "UPDATE vagas
        SET status = 'livre'
        WHERE id = '$vaga_id'";

if (!mysqli_query($conn, $sql)) {
    die("Erro ao liberar vaga: " . mysqli_error($conn));
}

mysqli_close($conn);

header("Location: minhas-reservas.php");
exit;

?>