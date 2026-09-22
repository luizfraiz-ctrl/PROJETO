```php
<?php

session_start();

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT id, numero, status FROM vagas ORDER BY numero";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Erro ao buscar vagas: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Vagas - Park Point</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<header class="navbar">

    <div class="navbar-content">

        <a href="dashboard.php" class="logo">

            <div class="logo-icon">P</div>

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


<main class="vagas-page">

    <div class="vagas-header">

        <h1>Encontre sua vaga</h1>

        <p>
            Escolha uma vaga disponível para realizar sua reserva.
        </p>

    </div>


    <div class="vagas-grid">

        <?php

        while ($vaga = mysqli_fetch_assoc($resultado)) {

            $numero = $vaga["numero"];
            $status = $vaga["status"];
            $id = $vaga["id"];

            ?>

            <div class="vaga <?php echo ($status == "livre") ? "livre" : "ocupada"; ?>">

                <div class="vaga-numero">

                    <?php echo $numero; ?>

                </div>

                <h2>

                    Vaga <?php echo $numero; ?>

                </h2>


                <?php

                if ($status == "livre") {

                    ?>

                    <p class="status-livre">
                        ● LIVRE
                    </p>

                    <a
                        href="reservar.php?id=<?php echo $id; ?>"
                        class="btn-primary"
                    >
                        Reservar vaga
                    </a>

                    <?php

                } else {

                    ?>

                    <p class="status-ocupada">
                        ● OCUPADA
                    </p>

                    <?php

                }

                ?>

            </div>

            <?php

        }

        ?>

    </div>

</main>


<footer>

    <p>
        © 2026 Park Point — Sistema de Estacionamento
    </p>

</footer>


</body>

</html>

<?php

mysqli_close($conn);

?>
```
