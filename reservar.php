```php
<?php

session_start();

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    die("Vaga não informada.");
}

$usuario_id = $_SESSION["usuario_id"];
$vaga_id = $_GET["id"];


/* BUSCAR VAGA */

$sql = "SELECT * FROM vagas
        WHERE id = '$vaga_id'
        AND status = 'livre'";

$resultado_vaga = mysqli_query($conn, $sql);

if (!$resultado_vaga) {
    die("Erro ao buscar vaga: " . mysqli_error($conn));
}

if (mysqli_num_rows($resultado_vaga) == 0) {
    die("Essa vaga não está disponível.");
}

$vaga = mysqli_fetch_assoc($resultado_vaga);


/* BUSCAR VEÍCULOS */

$sql = "SELECT * FROM veiculos
        WHERE usuario_id = '$usuario_id'
        ORDER BY id DESC";

$resultado_veiculos = mysqli_query($conn, $sql);

if (!$resultado_veiculos) {
    die("Erro ao buscar veículos: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reservar vaga - Park Point</title>

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

            <a href="dashboard.php">Início</a>

            <a href="vagas.php">Vagas</a>

            <a href="minhas-reservas.php">Reservas</a>

            <a href="historico.php">Histórico</a>

            <a href="logout.php" class="btn-login">Sair</a>

        </nav>

    </div>

</header>



<main class="form-page">


    <div class="form-box">


        <div class="card-icon">
            🅿️
        </div>


        <h1>
            Reservar vaga
        </h1>


        <p>
            Você está reservando a
            <strong>Vaga <?php echo $vaga["numero"]; ?></strong>.
        </p>


        <?php

        echo "<p>Veículos encontrados: " . mysqli_num_rows($resultado_veiculos) . "</p>";

if (mysqli_num_rows($resultado_veiculos) > 0) 

            ?>

            <form action="confirmar-reserva.php" method="PO

```
