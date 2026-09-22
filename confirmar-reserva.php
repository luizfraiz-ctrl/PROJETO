<?php

session_start();

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_POST["vaga_id"]) || !isset($_POST["veiculo_id"])) {
    die("Dados da reserva não recebidos.");
}

$usuario_id = $_SESSION["usuario_id"];
$vaga_id = $_POST["vaga_id"];
$veiculo_id = $_POST["veiculo_id"];

/* Verifica se o veículo pertence ao usuário */
$sql = "SELECT id FROM veiculos
        WHERE id = '$veiculo_id'
        AND usuario_id = '$usuario_id'";

$resultado = mysqli_query($conn, $sql);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    die("Veículo inválido.");
}

/* Verifica se a vaga está livre */
$sql = "SELECT * FROM vagas
        WHERE id = '$vaga_id'
        AND status = 'livre'";

$resultado = mysqli_query($conn, $sql);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    die("Essa vaga não está disponível.");
}

/* Cria a reserva */
$sql = "INSERT INTO reservas
        (usuario_id, veiculo_id, vaga_id, status)
        VALUES
        ('$usuario_id', '$veiculo_id', '$vaga_id', 'ativa')";

if (!mysqli_query($conn, $sql)) {
    die("Erro ao criar reserva: " . mysqli_error($conn));
}

/* Marca a vaga como ocupada */
$sql = "UPDATE vagas
        SET status = 'ocupada'
        WHERE id = '$vaga_id'";

if (!mysqli_query($conn, $sql)) {
    die("Erro ao ocupar vaga: " . mysqli_error($conn));
}

mysqli_close($conn);

header("Location: minhas-reservas.php");
exit;

?>