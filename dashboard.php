<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$nome = $_SESSION["usuario_nome"];

// Conta os veículos do usuário
$sql = "SELECT COUNT(*) AS total
        FROM veiculos
        WHERE usuario_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$dados = mysqli_fetch_assoc($resultado);

$total_veiculos = $dados["total"];

mysqli_stmt_close($stmt);


// Conta as reservas ativas
$sql = "SELECT COUNT(*) AS total
        FROM reservas
        WHERE usuario_id = ?
        AND status = 'ativa'";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$dados = mysqli_fetch_assoc($resultado);

$total_reservas = $dados["total"];

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Park Point</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>


<header class="navbar">

    <div class="navbar-content">

        <a href="dashboard.php" class="logo">

            <span class="logo-icon">P</span>

            Park Point

        </a>


        <nav class="nav-menu">

            <a href="dashboard.php">
                Início
            </a>

            <a href="vagas.php">
                Vagas
            </a>

            <a href="minhas-reservas.php">
                Minhas Reservas
            </a>

            <a href="historico.php">
                Histórico
            </a>

            <a href="logout.php" class="btn-login">
                Sair
            </a>

        </nav>

    </div>

</header>


<main class="dashboard">


    <div class="dashboard-header">

        <div>

            <h1>
                Olá, <?= htmlspecialchars($nome) ?>! 👋
            </h1>

            <p>
                Bem-vindo ao seu painel do Park Point.
            </p>

        </div>

    </div>


    <div class="cards">


        <!-- MEUS VEÍCULOS -->

        <a href="meus-veiculos.php"
           class="card">

            <div class="card-icon">
                🚗
            </div>

            <h2>
                Meus Veículos
            </h2>

            <p>
                Gerencie seus veículos cadastrados.
            </p>

            <strong>
                <?= $total_veiculos ?> veículo(s)
            </strong>

        </a>


        <!-- VAGAS -->

        <a href="vagas.php"
           class="card">

            <div class="card-icon">
                🅿️
            </div>

            <h2>
                Consultar Vagas
            </h2>

            <p>
                Veja as vagas disponíveis e ocupadas.
            </p>

        </a>


        <!-- RESERVAR -->

        <a href="reservar.php"
           class="card">

            <div class="card-icon">
                📅
            </div>

            <h2>
                Reservar Vaga
            </h2>

            <p>
                Escolha uma vaga e faça sua reserva.
            </p>

        </a>


        <!-- MINHAS RESERVAS -->

        <a href="minhas-reservas.php"
           class="card">

            <div class="card-icon">
                🎫
            </div>

            <h2>
                Minhas Reservas
            </h2>

            <p>
                Consulte suas reservas atuais.
            </p>

            <strong>
                <?= $total_reservas ?> ativa(s)
            </strong>

        </a>


        <!-- HISTÓRICO -->

        <a href="historico.php"
           class="card">

            <div class="card-icon">
                📋
            </div>

            <h2>
                Histórico
            </h2>

            <p>
                Consulte o histórico das suas reservas.
            </p>

        </a>


    </div>


</main>


<footer>

    <p>
        © 2026 Park Point - Sistema de Gestão de Estacionamento
    </p>

</footer>


</body>

</html>

<?php

mysqli_close($conn);

?>

