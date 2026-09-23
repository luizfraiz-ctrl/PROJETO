<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

// Busca somente os veículos do usuário logado
$sql = "SELECT * FROM veiculos
        WHERE usuario_id = ?
        ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Meus Veículos - Park Point</title>

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
            <h1>Meus Veículos</h1>

            <p>
                Gerencie os veículos cadastrados na sua conta.
            </p>
        </div>

        <a href="cadastro-veiculo.php"
           class="btn-primary">

            + Cadastrar veículo

        </a>

    </div>


    <?php if (isset($_GET["sucesso"])): ?>

        <div class="alert alert-success">

            Veículo atualizado com sucesso!

        </div>

    <?php endif; ?>


    <?php if (isset($_GET["excluido"])): ?>

        <div class="alert alert-success">

            Veículo excluído com sucesso!

        </div>

    <?php endif; ?>


    <div class="cards">

        <?php if (mysqli_num_rows($resultado) > 0): ?>

            <?php while ($veiculo = mysqli_fetch_assoc($resultado)): ?>

                <div class="card">

                    <div class="card-icon">
                        🚗
                    </div>

                    <h2>
                        <?= htmlspecialchars($veiculo["modelo"]) ?>
                    </h2>

                    <p>
                        <strong>Placa:</strong>
                        <?= htmlspecialchars($veiculo["placa"]) ?>
                    </p>

                    <p>
                        <strong>Cor:</strong>
                        <?= htmlspecialchars($veiculo["cor"]) ?>
                    </p>


                    <div class="veiculo-acoes">

                        <a
                            href="editar-veiculo.php?id=<?= $veiculo["id"] ?>"
                            class="btn-secondary">

                            ✏️ Editar

                        </a>


                        <form
                            action="excluir-veiculo.php"
                            method="POST"
                            onsubmit="return confirm('Tem certeza que deseja excluir este veículo?');">

                            <input
                                type="hidden"
                                name="id"
                                value="<?= $veiculo["id"] ?>">

                            <button
                                type="submit"
                                class="btn-danger">

                                🗑️ Excluir

                            </button>

                        </form>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="card">

                <div class="card-icon">
                    🚗
                </div>

                <h2>
                    Nenhum veículo cadastrado
                </h2>

                <p>
                    Você ainda não possui veículos cadastrados.
                </p>

                <br>

                <a href="cadastro-veiculo.php"
                   class="btn-primary">

                    Cadastrar meu primeiro veículo

                </a>

            </div>

        <?php endif; ?>

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

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>