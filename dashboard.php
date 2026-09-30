<?php

session_start();

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];
$nome = $_SESSION["usuario_nome"] ?? "Usuário";
$perfil = $_SESSION["perfil"] ?? "cliente";


/* =========================
   CONTAR VEÍCULOS
========================= */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM veiculos
    WHERE usuario_id = ?
");

$stmt->execute([$usuario_id]);

$totalVeiculos = (int) $stmt->fetchColumn();


/* =========================
   CONTAR ESTACIONAMENTO ATIVO
========================= */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM estacionamentos e
    INNER JOIN veiculos v
        ON v.id = e.veiculo_id
    WHERE v.usuario_id = ?
    AND e.saida IS NULL
");

$stmt->execute([$usuario_id]);

$totalAtivos = (int) $stmt->fetchColumn();


/* =========================
   BUSCAR ESTACIONAMENTO ATIVO
========================= */

$stmt = $pdo->prepare("
    SELECT
        e.id,
        e.entrada,
        v.numero AS numero_vaga,
        ve.placa,
        ve.modelo,
        est.nome AS estacionamento
    FROM estacionamentos e

    INNER JOIN vagas v
        ON v.id = e.vaga_id

    INNER JOIN veiculos ve
        ON ve.id = e.veiculo_id

    LEFT JOIN estabelecimentos est
        ON est.id = v.estabelecimento_id

    WHERE ve.usuario_id = ?
    AND e.saida IS NULL

    ORDER BY e.id DESC

    LIMIT 1
");

$stmt->execute([$usuario_id]);

$ativo = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Park Point - Dashboard</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f7fb;
    color: #172033;
}


/* NAVBAR */

nav {
    background: #101827;
    min-height: 70px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 15px 40px;
}

.logo {
    color: white;
    text-decoration: none;

    font-size: 24px;
    font-weight: bold;
}

.logo-p {
    color: #00aaff;
}

.menu {
    display: flex;
    align-items: center;
    gap: 22px;

    flex-wrap: wrap;
}

.menu a {
    color: white;
    text-decoration: none;

    font-size: 14px;

    transition: .2s;
}

.menu a:hover {
    color: #00aaff;
}

.menu .sair {
    color: #ff7070;
}


/* CONTAINER */

.container {
    max-width: 1100px;

    margin: 45px auto;

    padding: 0 20px;
}


/* TITULO */

.bem-vindo h1 {
    margin: 0 0 8px;
}

.bem-vindo p {
    color: #667085;

    margin-bottom: 35px;
}


/* CARDS */

.cards {
    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 20px;

    margin-bottom: 30px;
}

.card {
    background: white;

    padding: 25px;

    border-radius: 16px;

    box-shadow: 0 5px 20px rgba(0,0,0,.06);
}

.card span {
    color: #667085;

    font-size: 14px;
}

.card strong {
    display: block;

    font-size: 35px;

    color: #00aaff;

    margin-top: 10px;
}


/* ESTACIONAMENTO ATIVO */

.ativo {
    background: white;

    border-radius: 16px;

    padding: 30px;

    margin-bottom: 30px;

    box-shadow: 0 5px 20px rgba(0,0,0,.06);
}

.ativo h2 {
    margin-top: 0;
}

.informacoes {
    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 15px;

    margin-top: 20px;
}

.info {
    background: #f4f7fb;

    padding: 15px;

    border-radius: 10px;
}

.info small {
    display: block;

    color: #667085;

    margin-bottom: 5px;
}

.info strong {
    font-size: 16px;
}


/* ACESSO */

.acesso {
    background: white;

    border-radius: 16px;

    padding: 30px;

    box-shadow: 0 5px 20px rgba(0,0,0,.06);
}

.acesso h2 {
    margin-top: 0;
}

.botoes {
    display: flex;

    flex-wrap: wrap;

    gap: 15px;
}

.botao {
    display: inline-block;

    background: #00aaff;

    color: white;

    text-decoration: none;

    padding: 14px 20px;

    border-radius: 10px;

    font-weight: bold;

    transition: .2s;
}

.botao:hover {
    background: #008dcc;

    transform: translateY(-2px);
}

.botao.escuro {
    background: #172033;
}

.botao.escuro:hover {
    background: #26344d;
}


/* RESPONSIVO */

@media (max-width: 800px) {

    nav {
        flex-direction: column;

        gap: 15px;
    }

    .menu {
        justify-content: center;
    }

}


@media (max-width: 600px) {

    .cards {
        grid-template-columns: 1fr;
    }

    .informacoes {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<!-- NAVBAR -->

<nav>

```
<a href="dashboard.php" class="logo">

    <span class="logo-p">P</span>ARK POINT

</a>


<div class="menu">

    <a href="dashboard.php">
        Início
    </a>

    <a href="vagas.php">
        Vagas
    </a>

    <a href="meus-veiculos.php">
        Meus veículos
    </a>

    <a href="minhas-reservas.php">
        Minhas reservas
    </a>

    <a href="historico.php">
        Histórico
    </a>

    <?php if ($perfil === "admin"): ?>

        <a href="admin.php">
            Administração
        </a>

    <?php endif; ?>

    <?php if ($perfil === "funcionario"): ?>

        <a href="funcionario.php">
            Funcionário
        </a>

    <?php endif; ?>

    <a href="logout.php" class="sair">
        Sair
    </a>

</div>
```

</nav>

<!-- CONTEÚDO -->

<div class="container">

```
<!-- BEM-VINDO -->

<div class="bem-vindo">

    <h1>

        Olá, <?= htmlspecialchars($nome) ?>! 👋

    </h1>

    <p>

        Bem-vindo ao seu painel do Park Point.

    </p>

</div>


<!-- CARDS -->

<div class="cards">


    <div class="card">

        <span>
            Meus veículos
        </span>

        <strong>

            <?= $totalVeiculos ?>

        </strong>

    </div>


    <div class="card">

        <span>
            Estacionamento ativo
        </span>

        <strong>

            <?= $totalAtivos ?>

        </strong>

    </div>


</div>


<!-- ESTACIONAMENTO ATIVO -->

<?php if ($ativo): ?>

    <div class="ativo">

        <h2>
            🚗 Estacionamento atual
        </h2>


        <div class="informacoes">


            <div class="info">

                <small>
                    Veículo
                </small>

                <strong>

                    <?= htmlspecialchars($ativo["placa"]) ?>

                    <?php if (!empty($ativo["modelo"])): ?>

                        -
                        <?= htmlspecialchars($ativo["modelo"]) ?>

                    <?php endif; ?>

                </strong>

            </div>


            <div class="info">

                <small>
                    Vaga
                </small>

                <strong>

                    <?= htmlspecialchars($ativo["numero_vaga"]) ?>

                </strong>

            </div>


            <div class="info">

                <small>
                    Estacionamento
                </small>

                <strong>

                    <?= htmlspecialchars(
                        $ativo["estacionamento"] ?? "Park Point"
                    ) ?>

                </strong>

            </div>


            <div class="info">

                <small>
                    Entrada
                </small>

                <strong>

                    <?= date(
                        "d/m/Y H:i",
                        strtotime($ativo["entrada"])
                    ) ?>

                </strong>

            </div>


        </div>

    </div>

<?php endif; ?>


<!-- ACESSO RÁPIDO -->

<div class="acesso">

    <h2>
        Acesso rápido
    </h2>


    <div class="botoes">


        <a href="vagas.php" class="botao">

            🅿️ Encontrar vaga

        </a>


        <a
            href="meus-veiculos.php"
            class="botao escuro"
        >

            🚗 Meus veículos

        </a>


        <a
            href="minhas-reservas.php"
            class="botao escuro"
        >

            📋 Minhas reservas

        </a>


        <a
            href="historico.php"
            class="botao escuro"
        >

            🕐 Histórico

        </a>


    </div>

</div>
```

</div>

</body>

</html>
