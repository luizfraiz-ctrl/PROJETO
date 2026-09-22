```php
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

    <!-- NAVBAR -->

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


    <!-- FORMULÁRIO -->

    <main class="form-page">

        <div class="form-box">

            <div class="card-icon">
                🚗
            </div>

            <h1>
                Cadastrar veículo
            </h1>

            <p>
                Adicione seu veículo para poder realizar reservas no Park Point.
            </p>


            <form action="cadastrar-veiculo.php" method="POST">


                <label>
                    Placa
                </label>

                <input
                    type="text"
                    name="placa"
                    maxlength="10"
                    placeholder="Ex.: ABC1D23"
                    required
                >


                <label>
                    Modelo
                </label>

                <input
                    type="text"
                    name="modelo"
                    placeholder="Ex.: Honda Civic"
                    required
                >


                <label>
                    Cor
                </label>

                <input
                    type="text"
                    name="cor"
                    placeholder="Ex.: Preto"
                >


                <button type="submit">
                    Cadastrar veículo
                </button>

            </form>


            <p style="text-align:center; margin-top:20px;">

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
```
