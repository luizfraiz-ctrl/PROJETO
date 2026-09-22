```php
<?php

session_start();

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$nome = $_SESSION["usuario_nome"];

$sql = "SELECT id, placa, modelo, cor
        FROM veiculos
        WHERE usuario_id = ?
        ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Erro ao buscar veículos: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Painel - Park Point</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

    <!-- NAVBAR -->

    <header class="navbar">

        <div class="navbar-content">

            <a href="index.php" class="logo">

                <div class="logo-icon">
                    P
                </div>

                Park <span>Point</span>

            </a>

            <nav class="nav-menu">

                <a href="dashboard.php">
                    Início
                </a>

                <a href="vagas.php">
                    Vagas
                </a>

                <a href="minhas-reservas.php">
                    Reservas
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


    <!-- DASHBOARD -->

    <main class="dashboard">


        <!-- BOAS-VINDAS -->

        <section class="dashboard-welcome">

            <h1>
                Olá, <?php echo htmlspecialchars($nome); ?>! 👋
            </h1>

            <p>
                Bem-vindo ao seu painel do Park Point.
                Gerencie seus veículos e encontre sua vaga.
            </p>

        </section>


        <!-- MENU -->

        <section class="dashboard-menu">

            <a href="vagas.php">
                🅿️
                <br><br>
                Encontrar vaga
            </a>

            <a href="cadastro-veiculo.php">
                🚗
                <br><br>
                Cadastrar veículo
            </a>

            <a href="minhas-reservas.php">
                📅
                <br><br>
                Minhas reservas
            </a>

            <a href="historico.php">
                📋
                <br><br>
                Histórico
            </a>

        </section>


        <!-- VEÍCULOS -->

        <section>

            <div class="section-title" style="text-align:left; margin-bottom:20px;">
```
