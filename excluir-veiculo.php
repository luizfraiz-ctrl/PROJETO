<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$mensagem = "";
$tipo = "erro";

if ($id <= 0) {

    $mensagem = "Veículo inválido.";

} else {

    try {

        // Confirma que o veículo pertence ao usuário logado
        $stmt = $pdo->prepare("
            SELECT id, placa, modelo, cor
            FROM veiculos
            WHERE id = ?
              AND usuario_id = ?
        ");

        $stmt->execute([
            $id,
            $usuario_id
        ]);

        $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$veiculo) {

            $mensagem = "Veículo não encontrado.";

        } else {

            // Verifica se o veículo possui registros de estacionamento
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM estacionamentos
                WHERE veiculo_id = ?
            ");

            $stmt->execute([$id]);

            $possuiHistorico = (int) $stmt->fetchColumn();


            if ($possuiHistorico > 0) {

                $mensagem = "Este veículo possui histórico de estacionamento e não pode ser excluído.";

            } else {

                $stmt = $pdo->prepare("
                    DELETE FROM veiculos
                    WHERE id = ?
                      AND usuario_id = ?
                ");

                $stmt->execute([
                    $id,
                    $usuario_id
                ]);

                $tipo = "sucesso";

                $mensagem = "Veículo excluído com sucesso.";

            }
        }

    } catch (PDOException $e) {

        $mensagem = "Não foi possível excluir este veículo.";

    }

}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

```
<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Excluir veículo - Park Point
</title>


<style>

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }


    body {
        font-family: Arial, Helvetica, sans-serif;

        min-height: 100vh;

        background: #f3f6fa;

        color: #172033;

        display: flex;
        flex-direction: column;
    }


    a {
        text-decoration: none;
    }


    /* NAVBAR */

    .navbar {
        width: 100%;

        min-height: 68px;

        background: #111827;
    }


    .navbar-content {
        min-height: 68px;

        padding: 0 32px;

        display: flex;

        align-items: center;

        justify-content: space-between;
    }


    .logo {
        color: white;

        font-size: 24px;

        font-weight: 800;
    }


    .logo span {
        color: #60a5fa;
    }


    .nav {
        display: flex;

        align-items: center;

        gap: 5px;
    }


    .nav a {
        color: #dbe4ef;

        font-size: 14px;

        font-weight: 600;

        padding: 10px 13px;

        border-radius: 8px;

        transition: 0.2s;
    }


    .nav a:hover {
        background: #273449;

        color: white;
    }


    .nav .sair {
        background: #dc2626;

        color: white;

        margin-left: 5px;
    }


    .nav .sair:hover {
        background: #b91c1c;
    }


    /* CONTEÚDO */

    .main {
        flex: 1;

        width: 100%;

        padding: 40px 20px;

        display: flex;

        align-items: center;

        justify-content: center;
    }


    .container {
        width: 100%;

        max-width: 600px;
    }


    /* CARD */

    .card {
        background: white;

        border: 1px solid #e2e8f0;

        border-radius: 20px;

        padding: 40px;

        text-align: center;

        box-shadow:
            0 10px 30px rgba(15, 23, 42, 0.08);
    }


    /* ÍCONE */

    .icone {
        width: 70px;
        height: 70px;

        margin: 0 auto 22px;

        border-radius: 18px;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 32px;
    }


    .icone.erro {
        background: #fff1f2;
    }


    .icone.sucesso {
        background: #f0fdf4;
    }


    /* TEXTO */

    h1 {
        color: #111827;

        font-size: 27px;

        margin-bottom: 10px;
    }


    .mensagem {
        color: #64748b;

        font-size: 14px;

        line-height: 1.6;

        margin-bottom: 25px;
    }


    /* INFORMAÇÕES DO VEÍCULO */

    .veiculo {
        background: #f8fafc;

        border: 1px solid #e2e8f0;

        border-radius: 12px;

        padding: 16px;

        margin-bottom: 25px;
    }


    .veiculo strong {
        display: block;

        color: #334155;

        font-size: 16px;

        margin-bottom: 5px;
    }


    .veiculo span {
        color: #64748b;

        font-size: 13px;
    }


    /* BOTÃO */

    .botao {
        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-height: 46px;

        padding: 0 22px;

        border-radius: 10px;

        background: #2563eb;

        color: white;

        font-size: 14px;

        font-weight: 700;

        transition: 0.2s;
    }


    .botao:hover {
        background: #1d4ed8;

        transform: translateY(-2px);
    }


    /* RESPONSIVO */

    @media (max-width: 700px) {

        .navbar-content {
            padding: 14px 15px;

            flex-direction: column;

            gap: 12px;
        }


        .nav {
            flex-wrap: wrap;

            justify-content: center;
        }


        .main {
            padding: 25px 15px;
        }


        .card {
            padding: 30px 20px;
        }

    }

</style>
```

</head>

<body>

<header class="navbar">

```
<div class="navbar-content">


    <a
        href="dashboard.php"
        class="logo"
    >
        Park <span>Point</span>
    </a>


    <nav class="nav">

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
            class="sair"
        >
            Sair
        </a>

    </nav>


</div>
```

</header>

<main class="main">

```
<div class="container">


    <section class="card">


        <?php if ($tipo === "sucesso"): ?>


            <div class="icone sucesso">
                ✅
            </div>


            <h1>
                Veículo excluído!
            </h1>


            <p class="mensagem">
                <?= htmlspecialchars($mensagem) ?>
            </p>


        <?php else: ?>


            <div class="icone erro">
                ⚠️
            </div>


            <h1>
                Não foi possível excluir
            </h1>


            <p class="mensagem">
                <?= htmlspecialchars($mensagem) ?>
            </p>


            <?php if (isset($veiculo) && $veiculo): ?>

                <div class="veiculo">

                    <strong>
                        <?= htmlspecialchars($veiculo["modelo"]) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars($veiculo["placa"]) ?>
                        •
                        <?= htmlspecialchars($veiculo["cor"]) ?>
                    </span>

                </div>

            <?php endif; ?>


        <?php endif; ?>


        <a
            href="meus-veiculos.php"
            class="botao"
        >
            Voltar para meus veículos
        </a>


    </section>


</div>
```

</main>

</body>

</html>
