<?php

session_start();

require_once "conexao.php";

/* VERIFICAR LOGIN */

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];


/* VERIFICAR VAGA */

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: vagas.php");
    exit;
}

$vaga_id = (int) $_GET["id"];


/* BUSCAR VAGA */

$sql_vaga = "SELECT id, numero, status
             FROM vagas
             WHERE id = $vaga_id";

$resultado_vaga = mysqli_query($conn, $sql_vaga);

if (!$resultado_vaga) {
    die("Erro ao buscar vaga: " . mysqli_error($conn));
}

if (mysqli_num_rows($resultado_vaga) == 0) {
    die("Vaga não encontrada.");
}

$vaga = mysqli_fetch_assoc($resultado_vaga);


/* VERIFICAR SE ESTÁ LIVRE */

if ($vaga["status"] != "livre") {
    die("Essa vaga não está disponível.");
}


/* BUSCAR VEÍCULOS DO USUÁRIO */

$sql_veiculos = "SELECT id, placa, modelo, cor
                 FROM veiculos
                 WHERE usuario_id = $usuario_id
                 ORDER BY id DESC";

$resultado_veiculos = mysqli_query($conn, $sql_veiculos);

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


        <?php if (mysqli_num_rows($resultado_veiculos) > 0): ?>


            <form action="confirmar-reserva.php" method="POST">


                <input
                    type="hidden"
                    name="vaga_id"
                    value="<?php echo $vaga["id"]; ?>"
                >


                <label for="veiculo_id">
                    Escolha seu veículo
                </label>


                <select
                    name="veiculo_id"
                    id="veiculo_id"
                    required
                >

                    <option value="">
                        Selecione um veículo
                    </option>


                    <?php while ($veiculo = mysqli_fetch_assoc($resultado_veiculos)): ?>

                        <option value="<?php echo $veiculo["id"]; ?>">

                            <?php echo htmlspecialchars($veiculo["modelo"]); ?>

                            -

                            <?php echo htmlspecialchars($veiculo["placa"]); ?>

                            <?php if (!empty($veiculo["cor"])): ?>

                                -
                                <?php echo htmlspecialchars($veiculo["cor"]); ?>

                            <?php endif; ?>

                        </option>

                    <?php endwhile; ?>


                </select>


                <button type="submit">
                    Confirmar reserva
                </button>


            </form>


        <?php else: ?>


            <div class="card">

                <div class="card-icon">
                    🚗
                </div>

                <h3>
                    Nenhum veículo cadastrado
                </h3>

                <p>
                    Você precisa cadastrar um veículo antes de reservar uma vaga.
                </p>

                <br>

                <a
                    href="cadastro-veiculo.php"
                    class="btn-primary"
                >
                    Cadastrar veículo
                </a>

            </div>


        <?php endif; ?>


        <p style="text-align:center; margin-top:25px;">

            <a
                href="vagas.php"
                style="color:#2563eb; font-weight:600;"
            >
                ← Voltar para vagas
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

<?php

mysqli_close($conn);

?>