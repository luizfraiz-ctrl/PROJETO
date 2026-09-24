<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];

$reservas = [];

try {

    $sql = "
        SELECT
            e.id,
            e.entrada,
            e.saida,
            v.numero AS vaga,
            ve.placa,
            ve.modelo,
            ve.cor
        FROM estacionamentos e
        INNER JOIN vagas v
            ON e.vaga_id = v.id
        INNER JOIN veiculos ve
            ON e.veiculo_id = ve.id
        WHERE ve.usuario_id = ?
        ORDER BY
            CASE
                WHEN e.saida IS NULL THEN 0
                ELSE 1
            END,
            e.id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);

    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $erro = "Não foi possível carregar suas reservas.";
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

```
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Minhas Reservas - Park Point</title>

<style>

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f3f6fa;
        color: #172033;
        min-height: 100vh;
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
        width: 100%;
        padding: 30px;
    }

    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }


    /* VOLTAR */

    .voltar {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        color: #475569;
        font-size: 14px;
        font-weight: 700;

        padding: 8px 12px 8px 7px;
        margin-bottom: 18px;

        border-radius: 9px;

        transition: 0.2s;
    }

    .voltar:hover {
        background: #e2e8f0;
        color: #111827;
        transform: translateX(-3px);
    }

    .seta {
        font-size: 22px;
    }


    /* CABEÇALHO */

    .cabecalho {
        background: linear-gradient(
            135deg,
            #111827,
            #1e3a5f
        );

        color: white;

        border-radius: 20px;

        padding: 32px 35px;

        margin-bottom: 25px;

        box-shadow:
            0 10px 25px rgba(15, 23, 42, 0.12);
    }

    .cabecalho h1 {
        font-size: 30px;
        margin-bottom: 8px;
    }

    .cabecalho p {
        color: #d7e0ec;
        font-size: 14px;
        line-height: 1.5;
    }


    /* ERRO */

    .erro {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #be123c;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
    }


    /* SEM RESERVAS */

    .vazio {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 55px 25px;
        text-align: center;
        box-shadow:
            0 5px 16px rgba(15, 23, 42, 0.05);
    }

    .vazio-icon {
        font-size: 55px;
        margin-bottom: 18px;
    }

    .vazio h2 {
        font-size: 22px;
        margin-bottom: 8px;
        color: #111827;
    }

    .vazio p {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 22px;
    }

    .botao-vagas {
        display: inline-block;
        background: #2563eb;
        color: white;
        padding: 12px 20px;
        border-radius: 9px;
        font-size: 14px;
        font-weight: bold;
        transition: 0.2s;
    }

    .botao-vagas:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
    }


    /* LISTA */

    .lista {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }

    .reserva {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 24px;

        box-shadow:
            0 5px 16px rgba(15, 23, 42, 0.05);

        transition: 0.2s;
    }

    .reserva:hover {
        transform: translateY(-3px);

        box-shadow:
            0 12px 27px rgba(15, 23, 42, 0.09);
    }


    /* TOPO DO CARD */

    .reserva-topo {
        display: flex;
        align-items: center;
        justify-content: space-between;

        margin-bottom: 22px;
    }

    .vaga {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .vaga-icon {
        width: 48px;
        height: 48px;

        border-radius: 12px;

        background: #eff6ff;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 23px;
    }

    .vaga-texto small {
        display: block;
        color: #64748b;
        font-size: 12px;
        margin-bottom: 3px;
    }

    .vaga-texto strong {
        color: #111827;
        font-size: 20px;
    }


    /* STATUS */

    .status {
        padding: 7px 11px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
    }

    .status-ativa {
        background: #dcfce7;
        color: #15803d;
    }

    .status-finalizada {
        background: #f1f5f9;
        color: #64748b;
    }


    /* DADOS */

    .dados {
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;

        padding: 18px 0;

        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 17px;
    }

    .dado small {
        display: block;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .dado strong {
        display: block;
        color: #334155;
        font-size: 13px;
    }


    /* VEÍCULO */

    .veiculo {
        display: flex;
        align-items: center;
        gap: 12px;

        margin-top: 18px;
    }

    .veiculo-icon {
        width: 42px;
        height: 42px;

        border-radius: 10px;

        background: #f1f5f9;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 20px;
    }

    .veiculo-info small {
        display: block;
        color: #94a3b8;
        font-size: 11px;
        margin-bottom: 3px;
    }

    .veiculo-info strong {
        display: block;
        color: #334155;
        font-size: 14px;
    }


    /* BOTÃO CANCELAR */

    .cancelar {
        display: block;
        width: 100%;

        margin-top: 20px;

        padding: 12px;

        border: 1px solid #fecaca;
        border-radius: 9px;

        background: #fff1f2;
        color: #dc2626;

        text-align: center;

        font-size: 13px;
        font-weight: 700;

        transition: 0.2s;
    }

    .cancelar:hover {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
    }


    /* RESPONSIVO */

    @media (max-width: 900px) {

        .lista {
            grid-template-columns: 1fr;
        }

    }


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
            padding: 18px 15px 35px;
        }

        .cabecalho {
            padding: 27px 23px;
        }

        .cabecalho h1 {
            font-size: 25px;
        }

    }


    @media (max-width: 480px) {

        .reserva-topo {
            align-items: flex-start;
            gap: 10px;
        }

        .dados {
            grid-template-columns: 1fr;
        }

        .reserva {
            padding: 20px;
        }

    }

</style>
```

</head>

<body>

<header class="navbar">

```
<div class="navbar-content">

    <a href="dashboard.php" class="logo">
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

        <a href="logout.php" class="sair">
            Sair
        </a>

    </nav>

</div>
```

</header>

<main class="main">

```
<div class="container">


    <a href="dashboard.php" class="voltar">

        <span class="seta">
            ←
        </span>

        Voltar

    </a>


    <section class="cabecalho">

        <h1>
            Minhas Reservas 📅
        </h1>

        <p>
            Consulte suas reservas, veja os veículos utilizados
            e acompanhe seus estacionamentos.
        </p>

    </section>


    <?php if (isset($erro)): ?>

        <div class="erro">
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php elseif (empty($reservas)): ?>

        <div class="vazio">

            <div class="vazio-icon">
                🅿️
            </div>

            <h2>
                Nenhuma reserva encontrada
            </h2>

            <p>
                Você ainda não possui reservas no Park Point.
            </p>

            <a href="vagas.php" class="botao-vagas">
                Encontrar uma vaga
            </a>

        </div>

    <?php else: ?>

        <section class="lista">

            <?php foreach ($reservas as $reserva): ?>

                <?php
                    $ativa = empty($reserva["saida"]);

                    $entrada = date(
                        "d/m/Y H:i",
                        strtotime($reserva["entrada"])
                    );

                    $saida = !empty($reserva["saida"])
                        ? date(
                            "d/m/Y H:i",
                            strtotime($reserva["saida"])
                        )
                        : null;
                ?>

                <article class="reserva">


                    <div class="reserva-topo">

                        <div class="vaga">

                            <div class="vaga-icon">
                                🅿️
                            </div>

                            <div class="vaga-texto">

                                <small>
                                    Vaga
                                </small>

                                <strong>
                                    Nº <?= htmlspecialchars($reserva["vaga"]) ?>
                                </strong>

                            </div>

                        </div>


                        <?php if ($ativa): ?>

                            <span class="status status-ativa">
                                ATIVA
                            </span>

                        <?php else: ?>

                            <span class="status status-finalizada">
                                FINALIZADA
                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="dados">

                        <div class="dado">

                            <small>
                                Entrada
                            </small>

                            <strong>
                                <?= htmlspecialchars($entrada) ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <small>
                                Saída
                            </small>

                            <strong>

                                <?php if ($saida): ?>

                                    <?= htmlspecialchars($saida) ?>

                                <?php else: ?>

                                    Em andamento

                                <?php endif; ?>

                            </strong>

                        </div>

                    </div>


                    <div class="veiculo">

                        <div class="veiculo-icon">
                            🚗
                        </div>

                        <div class="veiculo-info">

                            <small>
                                Veículo
                            </small>

                            <strong>
                                <?= htmlspecialchars($reserva["modelo"]) ?>
                                -
                                <?= htmlspecialchars($reserva["placa"]) ?>
                            </strong>

                        </div>

                    </div>


                    <?php if ($ativa): ?>

                        <a
                            href="cancelar-reserva.php?id=<?= (int) $reserva["id"] ?>"
                            class="cancelar"
                            onclick="return confirm('Deseja realmente cancelar esta reserva?')"
                        >
                            Cancelar reserva
                        </a>

                    <?php endif; ?>


                </article>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>


</div>
```

</main>

</body>

</html>
