<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] !== "funcionario") {
    header("Location: dashboard.php");
    exit;
}

/* =========================
   ESTATÍSTICAS
========================= */

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
   ESTACIONAMENTOS ATIVOS
========================= */

$stmt = $pdo->query("
    SELECT
        e.id,
        e.entrada,
        v.numero AS vaga,
        ve.placa,
        ve.modelo,
        ve.cor,
        u.nome AS usuario
    FROM estacionamentos e
    INNER JOIN vagas v ON e.vaga_id = v.id
    INNER JOIN veiculos ve ON e.veiculo_id = ve.id
    INNER JOIN usuarios u ON ve.usuario_id = u.id
    WHERE e.saida IS NULL
    ORDER BY e.entrada ASC
");

$estacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   VEÍCULOS CADASTRADOS
========================= */

$stmt = $pdo->query("
    SELECT
        ve.id,
        ve.placa,
        ve.modelo,
        ve.cor,
        u.nome AS usuario
    FROM veiculos ve
    INNER JOIN usuarios u ON ve.usuario_id = u.id
    ORDER BY ve.modelo ASC
");

$veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   VAGAS
========================= */

$todasVagas = $pdo->query("
    SELECT id, numero, status
    FROM vagas
    ORDER BY numero
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

```
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Funcionário - Park Point</title>

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
        min-height: 72px;
        background: #07111f;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 40px;
        color: white;
    }

    .logo {
        color: white;
        text-decoration: none;
        font-size: 25px;
        font-weight: bold;
    }

    .logo-p {
        color: #00aaff;
    }

    .nav-links {
        display: flex;
        align-items: center;
        gap: 20px;
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
        background: #c62828;
        color: white !important;
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

    /* ESTATÍSTICAS */

    .estatisticas {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .estatistica {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        border-left: 5px solid #008cff;
    }

    .estatistica h3 {
        color: #687386;
        font-size: 15px;
        margin-bottom: 10px;
    }

    .numero {
        font-size: 34px;
        font-weight: bold;
    }

    /* SEÇÃO */

    .secao {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
    }

    .secao h2 {
        font-size: 22px;
        margin-bottom: 20px;
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
        font-size: 13px;
        font-weight: bold;
    }

    .vaga-livre {
        background: #dff5e3;
        color: #218838;
    }

    .vaga-ocupada {
        background: #ffe0e0;
        color: #c62828;
    }

    /* TABELA */

    .tabela-container {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    th {
        background: #f3f6fa;
        text-align: left;
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

    /* BOTÕES */

    .acoes {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .botao {
        display: inline-block;
        padding: 13px 18px;
        border-radius: 9px;
        background: #008cff;
        color: white;
        text-decoration: none;
        font-weight: bold;
    }

    .botao:hover {
        background: #0074d4;
    }

    .botao-saida {
        background: #e53935;
    }

    .botao-saida:hover {
        background: #c62828;
    }

    /* RESPONSIVO */

    @media (max-width: 900px) {

        .vagas {
            grid-template-columns: repeat(5, 1fr);
        }

        nav {
            padding: 0 20px;
        }

    }

    @media (max-width: 600px) {

        nav {
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
<a href="funcionario.php" class="logo">
    <span class="logo-p">P</span>ARK POINT
</a>

<div class="nav-links">

    <a href="funcionario.php">Painel</a>

    <a href="vagas.php">Vagas</a>

    <a href="logout.php" class="logout">Sair</a>

</div>
```

</nav>

<div class="container">

```
<div class="topo">

    <h1>Painel do Funcionário</h1>

    <p>
        Olá, <?= htmlspecialchars($_SESSION["usuario_nome"]) ?>.
        Aqui você pode acompanhar o estacionamento.
    </p>

</div>


<!-- ESTATÍSTICAS -->

<div class="estatisticas">

    <div class="estatistica">

        <h3>Vagas livres</h3>

        <div class="numero">
            <?= $vagasLivres ?>
        </div>

    </div>

    <div class="estatistica">

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

        <?php foreach ($todasVagas as $vaga): ?>

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


<!-- VEÍCULOS ESTACIONADOS -->

<div class="secao">

    <h2>Veículos estacionados</h2>

    <?php if (count($estacionados) === 0): ?>

        <p style="color:#687386;">
            Nenhum veículo está estacionado no momento.
        </p>

    <?php else: ?>

        <div class="tabela-container">

            <table>

                <thead>

                    <tr>

                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Placa</th>
                        <th>Cor</th>
                        <th>Vaga</th>
                        <th>Entrada</th>
                        <th>Ação</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($estacionados as $item): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($item["usuario"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($item["modelo"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($item["placa"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($item["cor"]) ?>
                            </td>

                            <td>
                                Vaga <?= htmlspecialchars($item["vaga"]) ?>
                            </td>

                            <td>
                                <?= date(
                                    "d/m/Y H:i",
                                    strtotime($item["entrada"])
                                ) ?>
                            </td>

                            <td>

                                <a
                                    href="cancelar-reserva.php?id=<?= $item["id"] ?>"
                                    class="botao botao-saida"
                                    onclick="return confirm('Deseja registrar a saída deste veículo?');"
                                >
                                    Registrar saída
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>


<!-- VEÍCULOS CADASTRADOS -->

<div class="secao">

    <h2>Veículos cadastrados</h2>

    <div class="tabela-container">

        <table>

            <thead>

                <tr>

                    <th>Cliente</th>
                    <th>Modelo</th>
                    <th>Placa</th>
                    <th>Cor</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($veiculos as $veiculo): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($veiculo["usuario"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($veiculo["modelo"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($veiculo["placa"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($veiculo["cor"]) ?>
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

        <a href="historico.php" class="botao">
            Ver histórico
        </a>

    </div>

</div>
```

</div>

</body>

</html>
