<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastrar veículo - Park Point</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>


<header class="navbar">

    <div class="navbar-content">

        <a href="dashboard.php" class="logo">

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


<main class="form-page">


    <div class="form-box">


        <div
            class="card-icon"
            style="margin-left:auto; margin-right:auto;"
        >
            🚗
        </div>


        <h1>
            Cadastrar veículo
        </h1>


        <p>
            Adicione um veículo à sua conta do Park Point.
        </p>


        <form action="cadastrar-veiculo.php" method="POST">


            <label for="placa">
                Placa
            </label>

            <input
                type="text"
                id="placa"
                name="placa"
                placeholder="Ex: ABC1D23"
                maxlength="10"
                required
            >


            <label for="modelo">
                Modelo
            </label>

            <input
                type="text"
                id="modelo"
                name="modelo"
                placeholder="Ex: Honda Civic"
                maxlength="100"
                required
            >


            <label for="cor">
                Cor
            </label>

            <input
                type="text"
                id="cor"
                name="cor"
                placeholder="Ex: Preto"
                maxlength="50"
            >


            <button type="submit">
                Cadastrar veículo
            </button>


        </form>


        <p style="margin-top:25px;">

            <a
                href="dashboard.php"
                style="color:#2563eb; font-weight:600;"
            >
                ← Voltar para o painel
            </a>

        </p>


    </div>


</main>


<footer>

    <p>
        © 2026 Park Point — Sistema de Estacionamento
    </p>

</footer>


</body>

</html>
