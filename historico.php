<?php

session_start();

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

$sql = "SELECT
            reservas.id,
            reservas.data_reserva,
            reservas.status,
            vagas.numero AS vaga,
            veiculos.placa,
            veiculos.modelo,
            veiculos.cor
        FROM reservas
        INNER JOIN vagas
            ON reservas.vaga_id = vagas.id
        INNER JOIN veiculos
            ON reservas.veiculo_id = veiculos.id
        WHERE reservas.usuario_id = '$usuario_id'
        ORDER BY reservas.id DESC";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Erro ao buscar histórico: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Histórico - Park Point</title>

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


<main class="dashboard">


    <section class="dashboard-welcome">

        <h1>
            Histórico de reservas
        </h1>

        <p>
            Consulte todas as suas reservas realizadas no Park Point.
        </p>

    </section>


    <?php if (mysqli_num_rows($resultado) > 0): ?>


        <div class="cards">


            <?php while ($reserva = mysqli_fetch_assoc($resultado)): ?>


                <div class="card">


                    <div class="card-icon">
                        📋
                    </div>


                    <h3>
                        Vaga <?php echo $reserva["vaga"]; ?>
                    </h3>


                    <p>
                        <strong>Veículo:</strong>
                        <?php echo htmlspecialchars($reserva["modelo"]); ?>
                    </p>


                    <p>
                        <strong>Placa:</strong>
                        <?php echo htmlspecialchars($reserva["placa"]); ?>
                    </p>


                    <?php if (!empty($reserva["cor"])): ?>

                        <p>
                            <strong>Cor:</strong>
                            <?php echo htmlspecialchars($reserva["cor"]); ?>
                        </p>

                    <?php endif; ?>


                    <p>
                        <strong>Data:</strong>
                        <?php echo date("d/m/Y H:i", strtotime($reserva["data_reserva"])); ?>
                    </p>


                    <p>

                        <strong>Status:</strong>

                        <?php if ($reserva["status"] == "ativa"): ?>

                            <span style="color:#16a34a; font-weight:700;">
                                ● ATIVA
                            </span>

                        <?php else: ?>

                            <span style="color:#dc2626; font-weight:700;">
                                ● CANCELADA
                            </span>

                        <?php endif; ?>

                    </p>


                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <div
            class="card"
            style="text-align:center; max-width:600px; margin:auto;"
        >

            <div
                class="card-icon"
                style="margin-left:auto; margin-right:auto;"
            >
                📋
            </div>

            <h3>
                Histórico vazio
            </h3>

            <p>
                Você ainda não possui reservas registradas.
            </p>

            <br>

            <a href="vagas.php" class="btn-primary">
                Encontrar uma vaga
            </a>

        </div>


    <?php endif; ?>


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