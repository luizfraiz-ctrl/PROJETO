<?php

session_start();

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cadastro-veiculo.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

$placa = strtoupper(trim($_POST["placa"]));
$modelo = trim($_POST["modelo"]);
$cor = trim($_POST["cor"]);

if (empty($placa) || empty($modelo)) {
    die("Preencha placa e modelo.");
}

$sql = "INSERT INTO veiculos
        (usuario_id, placa, modelo, cor)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Erro ao preparar cadastro: " . $conn->error);
}

$stmt->bind_param(
    "isss",
    $usuario_id,
    $placa,
    $modelo,
    $cor
);

if ($stmt->execute()) {

    echo "<h2>Veículo cadastrado com sucesso!</h2>";
    echo "<a href='dashboard.php'>Voltar para o painel</a>";

} else {

    echo "Erro ao cadastrar veículo: " . $stmt->error;
}

$stmt->close();
$conn->close();

?>