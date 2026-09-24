<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

/* =========================
   ESTATÍSTICAS
========================= */

$totalUsuarios = $pdo->query("
    SELECT COUNT(*) 
    FROM usuarios
")->fetchColumn();

$totalVeiculos = $pdo->query("
    SELECT COUNT(*) 
    FROM veiculos
")->fetchColumn();

$vagasLivres = $pdo->query("
    SELECT COUNT(*)
    FROM vagas
    WHERE status = 'livre'
")->fetchColumn();

$vagasOcupadas = $pdo->query("
    SELECT COUNT(*)
    FROM vagas
    WHERE status = 'ocupada'
")->fetchColumn();

/* =========================
   RESERVAS ATIVAS
========================= */

$stmt = $pdo->query("
    SELECT
        e.id,
        e.entrada,
        v.numero AS vaga,
        ve.placa,
        ve.modelo,
        u.nome AS usuario
    FROM estacionamentos e
    INNER JOIN vagas v ON e.vaga_id = v.id
    INNER JOIN veiculos ve ON e.veiculo_id = ve.id
    INNER JOIN usuarios u ON ve.usuario_id = u.id
    WHERE e.saida IS NULL
    ORDER BY e.entrada DESC
");

$reservasAtivas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   USUÁRIOS
========================= */

$stmt = $pdo->query("
    SELECT id, nome, email, perfil, criado_em
    FROM usuarios
    ORDER BY id DESC
");

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

```
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Administrador - Park Point</title>

<style>

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body {
        background: #f3f6fa;
        color: #172033;
    }

    /* NAVBAR */

    nav {
        height: 72px;
        background: #07111f;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 40px;
        color: white;
    }

    .logo {
        text-decoration: none;
        color: white;
        font-size: 25px;
        font-weight: bold;
    }

    .logo-p {
        color: #00aaff;
    }

    .nav-links {
        display: flex;
        gap: 22px;
        align-items: center;
    }

    .nav-links a {
        color: white;
        text-decoration: none;
        font-size: 14px;
    }

    .nav-links a:hover {
        color: #00aaff;
    }

    .logout {
        background: #e53935;
        padding: 10px 16px;
        border-radius: 8px;
    }

    .logout:hover {
        color: white !important;
        background: #c62828;
    }

    /* CONTAINER */

    .container {
        max-width: 1250px;
        margin: auto;
        padding: 35px 25px 60px;
    }

    .topo {
        margin-bottom: 30px;
    }

    .topo h1 {
        font-size: 32px;
        margin-bottom: 8px;
    }

    .topo p {
        color: #687386;
    }

    /* CARDS */

    .estatisticas {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 35px;
    }

    .card-estatistica {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        border-left: 5px solid #008cff;
    }

    .card-estatistica h3 {
        color: #687386;
        font-size: 15px;
        margin-bottom: 12px;
    }

    .numero {
        font-size: 34px;
        font-weight: bold;
    }

    /* SEÇÕES */

    .secao {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
    }

    .secao h2 {
        margin-bottom: 20px;
        font-size: 22px;
    }

    /* TABELAS */

    .tabela-container {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 650px;
    }

    th {
        text-align: left;
        background: #f3f6fa;
        padding: 13px;
        font-size: 14px;
    }

    td {
        padding: 14px 13px;
        border-bottom: 1px solid #edf0f4;
        font-size: 14px;
    }

    tr:hover td {
        background: #fafcff;
    }

    /* PERFIS */

    .perfil {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }

    .perfil-admin {
        background: #e8eaf6;
        color: #3949ab;
    }

    .perfil-cliente {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .perfil-funcionario {
        background: #fff3e0;
        color: #ef6c00;
    }

    /* VAGAS */

    .vagas {
        display: grid;
        grid-template-columns: repeat(10, 1fr);
        gap: 10px;
    }

    .vaga {
        padding: 15px 5px;
        text-align: center;
        border-radius: 9px;
        font-weight: bold;
        font-size: 13px;
    }

    .vaga-livre {
        background: #dff5e3;
        color: #218838;
    }

    .vaga-ocupada {
        background: #ffe0e0;
        color: #c62828;
    }

    /* BOTÕES */

    .acoes {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .botao {
        display: inline-block;
        text-decoration: none;
        background: #008cff;
        color: white;
        padding: 12px 18px;
        border-radius: 9px;
        font-weight: bold;
    }

    .botao:hover {
        background: #0074d4;
    }

    .botao-secundario {
        background: #172033;
    }

    .botao-secundario:hover {
        background: #0b1422;
    }

    /* RESPONSIVO */

    @media (max-width: 900px) {

        .estatisticas {
            grid-template-columns: repeat(2, 1fr);
        }

        .vagas {
            grid-template-columns: repeat(5, 1fr);
        }

        nav {
            padding: 0 20px;
        }

        .nav-links {
            gap: 10px;
        }

    }

    @media (max-width: 600px) {

        nav {
            height: auto;
            padding: 18px;
            flex-direction: column;
            gap: 15px;
        }

        .nav-links {
            flex-wrap: wrap;
            justify-content: center;
        }

        .estatisticas {
            grid-template-columns: 1fr;
        }

        .vagas {
            grid-template-columns: repeat(2, 1fr);
        }

        .container {
            padding: 25px 15px;
        }

    }

</style>
```

</head>

<body>

<nav>

```
<a href="admin.php" class="logo">
    <span class="logo-p">P</span>ARK POINT
</a>

<div class="nav-links">

    <a href="admin.php">Painel</a>

    <a href="dashboard.php">Área do cliente</a>

    <a href="vagas.php">Vagas</a>

    <a href="logout.php" class="logout">Sair</a>

</div>
```

</nav>

<div class="container">

```
<div class="topo">

    <h1>Painel Administrativo</h1>

    <p>
        Bem-vindo, <?= htmlspecialchars($_SESSION["usuario_nome"]) ?>.
        Aqui você pode acompanhar o sistema Park Point.
    </p>

</div>


<!-- ESTATÍSTICAS -->

<div class="estatisticas">

    <div class="card-estatistica">

        <h3>Usuários cadastrados</h3>

        <div class="numero">
            <?= $totalUsuarios ?>
        </div>

    </div>


    <div class="card-estatistica">

        <h3>Veículos cadastrados</h3>

        <div class="numero">
            <?= $totalVeiculos ?>
        </div>

    </div>


    <div class="card-estatistica">

        <h3>Vagas livres</h3>

        <div class="numero">
            <?= $vagasLivres ?>
        </div>

    </div>


    <div class="card-estatistica">

        <h3>Vagas ocupadas</h3>

        <div class="numero">
            <?= $vagasOcupadas ?>
        </div>

    </div>

</div>


<!-- VAGAS -->

<div class="secao">

    <h2>Status das vagas</h2>

    <div class="vagas">

        <?php

        $todasVagas = $pdo->query("
            SELECT numero, status
            FROM vagas
            ORDER BY numero
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($todasVagas as $vaga):

        ?>

            <div class="vaga
                <?= $vaga["status"] === "livre"
                    ? "vaga-livre"
                    : "vaga-ocupada" ?>">

                Vaga <?= htmlspecialchars($vaga["numero"]) ?>

                <br>

                <?= ucfirst($vaga["status"]) ?>

            </div>

        <?php endforeach; ?>

    </div>

</div>


<!-- RESERVAS ATIVAS -->

<div class="secao">

    <h2>Estacionamentos ativos</h2>

    <?php if (count($reservasAtivas) === 0): ?>

        <p style="color:#687386;">
            Não existem estacionamentos ativos no momento.
        </p>

    <?php else: ?>

        <div class="tabela-container">

            <table>

                <thead>

                    <tr>

                        <th>Usuário</th>
                        <th>Veículo</th>
                        <th>Placa</th>
                        <th>Vaga</th>
                        <th>Entrada</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($reservasAtivas as $reserva): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($reserva["usuario"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($reserva["modelo"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($reserva["placa"]) ?>
                            </td>

                            <td>
                                Vaga <?= htmlspecialchars($reserva["vaga"]) ?>
                            </td>

                            <td>
                                <?= date(
                                    "d/m/Y H:i",
                                    strtotime($reserva["entrada"])
                                ) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>


<!-- USUÁRIOS -->

<div class="secao">

    <h2>Usuários do sistema</h2>

    <div class="tabela-container">

        <table>

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Cadastro</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($usuarios as $usuario): ?>

                    <tr>

                        <td>
                            <?= $usuario["id"] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($usuario["nome"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($usuario["email"]) ?>
                        </td>

                        <td>

                            <?php

                            $classePerfil = "perfil-" . $usuario["perfil"];

                            ?>

                            <span class="perfil <?= $classePerfil ?>">

                                <?= ucfirst($usuario["perfil"]) ?>

                            </span>

                        </td>

                        <td>
                            <?= date(
                                "d/m/Y",
                                strtotime($usuario["criado_em"])
                            ) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- ACESSOS -->

<div class="secao">

    <h2>Acessos rápidos</h2>

    <div class="acoes">

        <a href="vagas.php" class="botao">
            Ver vagas
        </a>

        <a href="historico.php" class="botao botao-secundario">
            Ver histórico
        </a>

        <a href="meus-veiculos.php" class="botao botao-secundario">
            Ver veículos
        </a>

    </div>

</div>
```

</div>

</body>

</html>
