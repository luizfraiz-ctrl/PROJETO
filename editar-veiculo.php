<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: meus-veiculos.php");
    exit;
}


// Busca o veículo do usuário
$sql = "SELECT *
        FROM veiculos
        WHERE id = ?
        AND usuario_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $id,
    $usuario_id
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$veiculo = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);


if (!$veiculo) {

    die("Veículo não encontrado.");

}


// Atualização
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $placa = strtoupper(
        trim($_POST["placa"] ?? "")
    );

    $modelo = trim(
        $_POST["modelo"] ?? ""
    );

    $cor = trim(
        $_POST["cor"] ?? ""
    );


    if (
        $placa === "" ||
        $modelo === "" ||
        $cor === ""
    ) {

        $erro = "Preencha todos os campos.";

    } else {

        $sql = "UPDATE veiculos
                SET placa = ?,
                    modelo = ?,
                    cor = ?
                WHERE id = ?
                AND usuario_id = ?";

        $stmt = mysqli_prepare(
            $conn,
            $sql
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssii",
            $placa,
            $modelo,
            $cor,
            $id,
            $usuario_id
        );


        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            header(
                "Location: meus-veiculos.php?sucesso=1"
            );

            exit;

        } else {

            $erro = "Erro ao atualizar o veículo.";

        }

        mysqli_stmt_close($stmt);

    }

}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Editar veículo - Park Point
    </title>

    <link
        rel="stylesheet"
        href="style.css"
    >

</head>


<body>


<header class="navbar">

    <div class="navbar-content">

        <a
            href="dashboard.php"
            class="logo"
        >

            <span class="logo-icon">
                P
            </span>

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

            <a
                href="logout.php"
                class="btn-login"
            >
                Sair
            </a>

        </nav>

    </div>

</header>


<main class="form-page">


    <div class="form-box">

        <h1>
            Editar veículo
        </h1>

        <p>
            Altere os dados do seu veículo.
        </p>


        <?php if (isset($erro)): ?>

            <div class="alert alert-error">

                <?= htmlspecialchars($erro) ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <label for="placa">
                Placa
            </label>

            <input
                type="text"
                id="placa"
                name="placa"
                maxlength="10"
                value="<?= htmlspecialchars($veiculo["placa"]) ?>"
                required
            >


            <label for="modelo">
                Modelo
            </label>

            <input
                type="text"
                id="modelo"
                name="modelo"
                maxlength="100"
                value="<?= htmlspecialchars($veiculo["modelo"]) ?>"
                required
            >


            <label for="cor">
                Cor
            </label>

            <input
                type="text"
                id="cor"
                name="cor"
                maxlength="50"
                value="<?= htmlspecialchars($veiculo["cor"]) ?>"
                required
            >


            <button
                type="submit"
                class="btn-primary"
            >

                Salvar alterações

            </button>


            <a
                href="meus-veiculos.php"
                class="btn-secondary"
            >

                Cancelar

            </a>


        </form>

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