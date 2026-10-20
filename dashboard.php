```php
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

function h($valor) {
    return htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
}

/* Total de veículos do usuário */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM veiculos
    WHERE usuario_id = ?
");
$stmt->execute([$usuario_id]);
$totalVeiculos = (int) $stmt->fetchColumn();

/* Estacionamentos ativos dos veículos do usuário */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM estacionamentos e
    INNER JOIN veiculos v ON v.id = e.veiculo_id
    WHERE v.usuario_id = ?
      AND e.saida IS NULL
");
$stmt->execute([$usuario_id]);
$totalAtivos = (int) $stmt->fetchColumn();

/* Estacionamento ativo mais recente */
$stmt = $pdo->prepare("
    SELECT
        e.id,
        e.entrada,
        v.placa,
        v.modelo,
        v.cor,
        vg.numero AS vaga_numero
    FROM estacionamentos e
    INNER JOIN veiculos v ON v.id = e.veiculo_id
    INNER JOIN vagas vg ON vg.id = e.vaga_id
    WHERE v.usuario_id = ?
      AND e.saida IS NULL
    ORDER BY e.entrada DESC
    LIMIT 5
");
$stmt->execute([$usuario_id]);
$estacionamentosAtivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Total de veículos disponíveis para consulta */
$stmt = $pdo->prepare("
    SELECT id, placa, modelo, cor
    FROM veiculos
    WHERE usuario_id = ?
    ORDER BY id DESC
    LIMIT 5
");
$stmt->execute([$usuario_id]);
$veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do cliente | Park Point</title>

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

        nav {
            min-height: 72px;
            padding: 16px 5%;
            background: #101827;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .logo {
            color: #fff;
            text-decoration: none;
            font-size: 23px;
            font-weight: bold;
            white-space: nowrap;
        }

        .logo-p {
            color: #00aaff;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .menu a {
            color: #e5e7eb;
            text-decoration: none;
            font-size: 14px;
            transition: .2s;
        }

        .menu a:hover,
        .menu a.ativo {
            color: #00aaff;
        }

        .menu a.sair {
            color: #ff8585;
        }

        .container {
            max-width: 1150px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .cabecalho {
            margin-bottom: 28px;
        }

        .cabecalho h1 {
            margin: 0 0 8px;
        }

        .cabecalho p {
            margin: 0;
            color: #667085;
            line-height: 1.6;
        }

        .paineis {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }

        .painel-link {
            display: block;
            padding: 21px;
            background: #fff;
            color: #172033;
            text-decoration: none;
            border: 1px solid #e5eaf1;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .04);
            transition: .2s;
        }

        .painel-link:hover {
            border-color: #00aaff;
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(0, 170, 255, .12);
        }

        .painel-link.atual {
            border: 2px solid #00aaff;
            background: #f0faff;
        }

        .painel-icone {
            display: block;
            font-size: 26px;
            margin-bottom: 12px;
        }

        .painel-link strong {
            display: block;
            margin-bottom: 8px;
        }

        .painel-link p {
            margin: 0;
            color: #667085;
            font-size: 13px;
            line-height: 1.5;
        }

        .marcador {
            display: inline-block;
            margin-top: 12px;
            padding: 5px 9px;
            border-radius: 20px;
            background: #dff5ff;
            color: #0077a8;
            font-size: 11px;
            font-weight: bold;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat {
            padding: 23px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .05);
        }

        .stat span {
            color: #667085;
            font-size: 14px;
        }

        .stat strong {
            display: block;
            margin-top: 10px;
            color: #009fe3;
            font-size: 31px;
        }

        .secao {
            padding: 25px;
            margin-bottom: 25px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .05);
        }

        .secao h2 {
            margin-top: 0;
            font-size: 20px;
        }

        .botoes {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .botao {
            display: inline-block;
            padding: 12px 17px;
            border-radius: 9px;
            background: #00aaff;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
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
            background: #30415d;
        }

        .item {
            padding: 17px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item strong {
            display: block;
            margin-bottom: 6px;
        }

        .item p {
            margin: 4px 0;
            color: #667085;
            font-size: 14px;
            line-height: 1.5;
        }

        .vazio {
            padding: 18px 0;
            color: #667085;
            line-height: 1.6;
        }

        footer {
            padding: 20px;
            text-align: center;
            color: #667085;
            font-size: 13px;
        }

        @media (max-width: 850px) {
            nav {
                flex-direction: column;
                align-items: flex-start;
            }

            .paineis {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 550px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .container {
                margin-top: 25px;
            }

            .secao {
                padding: 19px;
            }
        }
    </style>
</head>

<body>

<nav>
    <a href="dashboard.php" class="logo">
        <span class="logo-p">P</span>ARK POINT
    </a>

    <div class="menu">
        <a href="dashboard.php" class="ativo">Painel do cliente</a>
        <a href="vagas.php">Vagas</a>
        <a href="meus-veiculos.php">Meus veículos</a>
        <a href="minhas-reservas.php">Minhas reservas</a>
        <a href="historico.php">Histórico</a>

        <?php if ($perfil === "admin"): ?>
            <a href="admin.php">Administração</a>
            <a href="funcionario.php">Painel do funcionário</a>
        <?php elseif ($perfil === "funcionario"): ?>
            <a href="funcionario.php">Painel do funcionário</a>
        <?php endif; ?>

        <a href="logout.php" class="sair">Sair</a>
    </div>
</nav>

<main class="container">

    <header class="cabecalho">
        <h1>Olá, <?= h($nome) ?>!</h1>
        <p>Bem-vindo ao seu painel do Park Point. Acesse as opções abaixo para gerenciar seus veículos e estacionamentos.</p>
    </header>

    <h2>Trocar de painel</h2>

    <div class="paineis">

        <a href="dashboard.php" class="painel-link atual">
            <span class="painel-icone">👤</span>
            <strong>Painel do cliente</strong>
            <p>Consulte seus veículos, vagas e estacionamentos ativos.</p>
            <span class="marcador">Você está aqui</span>
        </a>

        <?php if ($perfil === "admin"): ?>
            <a href="admin.php" class="painel-link">
                <span class="painel-icone">⚙️</span>
                <strong>Painel administrativo</strong>
                <p>Consulte os dados gerais e os estacionamentos cadastrados.</p>
            </a>
        <?php endif; ?>

        <?php if ($perfil === "admin" || $perfil === "funcionario"): ?>
            <a href="funcionario.php" class="painel-link">
                <span class="painel-icone">🅿️</span>
                <strong>Painel do funcionário</strong>
                <p>Acompanhe as vagas e registre as saídas dos veículos.</p>
            </a>
        <?php endif; ?>

    </div>

    <div class="stats">
        <div class="stat">
            <span>Meus veículos</span>
            <strong><?= $totalVeiculos ?></strong>
        </div>

        <div class="stat">
            <span>Estacionamentos ativos</span>
            <strong><?= $totalAtivos ?></strong>
        </div>
    </div>

    <section class="secao">
        <h2>Acesso rápido</h2>

        <div class="botoes">
            <a href="vagas.php" class="botao">Consultar vagas</a>
            <a href="meus-veiculos.php" class="botao escuro">Meus veículos</a>
            <a href="minhas-reservas.php" class="botao escuro">Minhas reservas</a>
            <a href="historico.php" class="botao escuro">Histórico</a>
        </div>
    </section>

    <section class="secao">
        <h2>Estacionamentos ativos</h2>

        <?php if (!$estacionamentosAtivos): ?>

            <div class="vazio">
                <p>Você não possui veículos estacionados neste momento.</p>
                <a href="vagas.php" class="botao">Consultar vagas disponíveis</a>
            </div>

        <?php else: ?>

            <?php foreach ($estacionamentosAtivos as $est): ?>
                <div class="item">
                    <strong>
                        <?= h($est["modelo"]) ?> — <?= h($est["placa"]) ?>
                    </strong>

                    <p>Vaga: <?= h($est["vaga_numero"]) ?></p>
                    <p>Cor: <?= h($est["cor"]) ?></p>
                    <p>
                        Entrada:
                        <?php
                        if (!empty($est["entrada"])) {
                            echo h(date(
                                "d/m/Y H:i",
                                strtotime($est["entrada"])
                            ));
                        } else {
                            echo "Não informada";
                        }
                        ?>
                    </p>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </section>

    <section class="secao">
        <h2>Meus veículos</h2>

        <?php if (!$veiculos): ?>

            <div class="vazio">
                <p>Você ainda não cadastrou veículos.</p>
                <a href="cadastro-veiculo.php" class="botao">Cadastrar veículo</a>
            </div>

        <?php else: ?>

            <?php foreach ($veiculos as $veiculo): ?>
                <div class="item">
                    <strong>
                        <?= h($veiculo["modelo"]) ?> — <?= h($veiculo["placa"]) ?>
                    </strong>
                    <p>Cor: <?= h($veiculo["cor"]) ?></p>
                </div>
            <?php endforeach; ?>

            <div class="botoes">
                <a href="meus-veiculos.php" class="botao">
                    Ver todos os veículos
                </a>
            </div>

        <?php endif; ?>
    </section>

</main>

<footer>
    Park Point — Sistema de gerenciamento de estacionamento
</footer>

</body>
</html>
```