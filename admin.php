```php
<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];
$perfil = $_SESSION["perfil"] ?? "cliente";

if ($perfil !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$_SESSION["navegacao_admin"] = true;

function h($valor) {
    return htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
}

/* ESTATÍSTICAS */
$totalUsuarios = (int) $pdo->query(
    "SELECT COUNT(*) FROM usuarios"
)->fetchColumn();

$totalVeiculos = (int) $pdo->query(
    "SELECT COUNT(*) FROM veiculos"
)->fetchColumn();

$totalEstabelecimentos = (int) $pdo->query(
    "SELECT COUNT(*) FROM estabelecimentos"
)->fetchColumn();

$vagasLivres = (int) $pdo->query(
    "SELECT COUNT(*) FROM vagas WHERE status = 'livre'"
)->fetchColumn();

/* ESTACIONAMENTOS */
$stmt = $pdo->query("
    SELECT
        e.id,
        e.nome,
        e.endereco,
        e.telefone,
        e.valor_hora,
        e.total_vagas,
        COUNT(v.id) AS vagas_cadastradas,
        SUM(
            CASE WHEN v.status = 'livre' THEN 1 ELSE 0 END
        ) AS vagas_livres
    FROM estabelecimentos e
    LEFT JOIN vagas v ON v.estabelecimento_id = e.id
    GROUP BY
        e.id, e.nome, e.endereco, e.telefone,
        e.valor_hora, e.total_vagas
    ORDER BY e.id DESC
");

$estabelecimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração | Park Point</title>

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
            background: #101827;
            min-height: 72px;
            padding: 16px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .logo {
            color: white;
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
            margin-bottom: 25px;
        }

        .cabecalho h1 {
            margin: 0 0 8px;
        }

        .cabecalho p {
            color: #667085;
            margin: 0;
            line-height: 1.6;
        }

        .secao-titulo {
            margin: 30px 0 15px;
            font-size: 20px;
        }

        /* SELETOR DE PAINÉIS */
        .paineis {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }

        .painel-link {
            display: block;
            background: white;
            color: #172033;
            text-decoration: none;
            padding: 22px;
            border-radius: 14px;
            border: 1px solid #e5eaf1;
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
            font-size: 26px;
            display: block;
            margin-bottom: 12px;
        }

        .painel-link strong {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .painel-link p {
            margin: 0;
            color: #667085;
            font-size: 13px;
            line-height: 1.5;
        }

        .marcador-atual {
            display: inline-block;
            margin-top: 12px;
            padding: 5px 9px;
            border-radius: 20px;
            color: #0077a8;
            background: #dff5ff;
            font-size: 11px;
            font-weight: bold;
        }

        /* ESTATÍSTICAS */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }

        .stat {
            background: white;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .05);
        }

        .stat span {
            color: #667085;
            font-size: 14px;
        }

        .stat strong {
            display: block;
            color: #009fe3;
            font-size: 30px;
            margin-top: 10px;
        }

        .acoes,
        .secao {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .05);
        }

        .acoes h2,
        .secao h2 {
            margin-top: 0;
        }

        .botoes {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .botao {
            display: inline-block;
            padding: 12px 17px;
            border-radius: 9px;
            background: #00aaff;
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            border: none;
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

        .estabelecimento {
            border: 1px solid #e4e7ec;
            border-radius: 13px;
            padding: 22px;
            margin-top: 16px;
            background: #fafbfc;
        }

        .estabelecimento h3 {
            margin: 0 0 10px;
        }

        .endereco {
            color: #667085;
            margin-bottom: 18px;
            line-height: 1.5;
        }

        .dados {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .dado {
            background: white;
            border-radius: 9px;
            padding: 14px;
            border: 1px solid #e5e7eb;
            overflow-wrap: anywhere;
        }

        .dado span {
            display: block;
            color: #667085;
            font-size: 12px;
            margin-bottom: 7px;
        }

        .dado strong {
            font-size: 15px;
        }

        .vagas-status {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .status {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .status.livre {
            background: #dcfce7;
            color: #15803d;
        }

        .status.ocupada {
            background: #fee2e2;
            color: #b91c1c;
        }

        .vazio {
            text-align: center;
            padding: 35px 15px;
            color: #667085;
            line-height: 1.6;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #667085;
            font-size: 13px;
        }

        @media (max-width: 850px) {
            .paineis {
                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            nav {
                flex-direction: column;
                align-items: flex-start;
            }

            .menu {
                justify-content: flex-start;
            }
        }

        @media (max-width: 550px) {
            .stats,
            .dados {
                grid-template-columns: 1fr;
            }

            .container {
                margin-top: 25px;
            }

            .acoes,
            .secao {
                padding: 19px;
            }
        }
    </style>
</head>

<body>

<nav>
    <a href="admin.php" class="logo">
        <span class="logo-p">P</span>ARK POINT
    </a>

    <div class="menu">
        <a href="admin.php" class="ativo">Administração</a>
        <a href="funcionario.php">Painel do funcionário</a>
        <a href="dashboard.php">Painel do cliente</a>
        <a href="vagas.php">Vagas</a>
        <a href="cadastro-estacionamento.php">Novo estacionamento</a>
        <a href="logout.php" class="sair">Sair</a>
    </div>
</nav>

<main class="container">

    <header class="cabecalho">
        <h1>Área administrativa</h1>
        <p>
            Bem-vindo à administração do Park Point.
            Escolha abaixo qual área deseja acessar.
        </p>
    </header>

    <h2 class="secao-titulo">Navegação entre painéis</h2>

    <div class="paineis">

        <a href="admin.php" class="painel-link atual">
            <span class="painel-icone">⚙️</span>
            <strong>Painel administrativo</strong>
            <p>Consulte as estatísticas e acompanhe os estacionamentos cadastrados.</p>
            <span class="marcador-atual">Você está aqui</span>
        </a>

        <a href="funcionario.php" class="painel-link">
            <span class="painel-icone">🅿️</span>
            <strong>Painel do funcionário</strong>
            <p>Consulte as vagas, acompanhe veículos estacionados e registre saídas.</p>
        </a>

        <a href="dashboard.php" class="painel-link">
            <span class="painel-icone">👤</span>
            <strong>Painel do cliente</strong>
            <p>Acesse a área de veículos, vagas, reservas e histórico.</p>
        </a>

    </div>

    <h2 class="secao-titulo">Resumo do sistema</h2>

    <div class="stats">
        <div class="stat">
            <span>Usuários cadastrados</span>
            <strong><?= $totalUsuarios ?></strong>
        </div>

        <div class="stat">
            <span>Veículos cadastrados</span>
            <strong><?= $totalVeiculos ?></strong>
        </div>

        <div class="stat">
            <span>Estacionamentos</span>
            <strong><?= $totalEstabelecimentos ?></strong>
        </div>

        <div class="stat">
            <span>Vagas livres</span>
            <strong><?= $vagasLivres ?></strong>
        </div>
    </div>

    <section class="acoes">
        <h2>Ações rápidas</h2>

        <div class="botoes">
            <a href="cadastro-estacionamento.php" class="botao">
                + Novo estacionamento
            </a>

            <a href="vagas.php" class="botao escuro">
                Ver vagas
            </a>

            <a href="funcionario.php" class="botao escuro">
                Abrir painel do funcionário
            </a>

            <a href="dashboard.php" class="botao escuro">
                Abrir painel do cliente
            </a>
        </div>
    </section>

    <section class="secao">
        <h2>Estacionamentos cadastrados</h2>

        <?php if (!$estabelecimentos): ?>

            <div class="vazio">
                <p>Nenhum estacionamento foi cadastrado ainda.</p>

                <a href="cadastro-estacionamento.php" class="botao">
                    Cadastrar estacionamento
                </a>
            </div>

        <?php else: ?>

            <?php foreach ($estabelecimentos as $est): ?>

                <?php
                $totalVagasEst = (int) $est["vagas_cadastradas"];
                $livres = (int) ($est["vagas_livres"] ?? 0);
                $ocupadas = max(0, $totalVagasEst - $livres);
                ?>

                <article class="estabelecimento">
                    <h3><?= h($est["nome"]) ?></h3>

                    <div class="endereco">
                        📍 <?= h($est["endereco"]) ?>
                    </div>

                    <div class="dados">
                        <div class="dado">
                            <span>Vagas cadastradas</span>
                            <strong><?= $totalVagasEst ?></strong>
                        </div>

                        <div class="dado">
                            <span>Valor por hora</span>
                            <strong>
                                R$ <?= number_format(
                                    (float) $est["valor_hora"],
                                    2,
                                    ",",
                                    "."
                                ) ?>
                            </strong>
                        </div>

                        <div class="dado">
                            <span>Telefone</span>
                            <strong><?= h($est["telefone"]) ?></strong>
                        </div>
                    </div>

                    <div class="vagas-status">
                        <span class="status livre">
                            <?= $livres ?> vagas livres
                        </span>

                        <span class="status ocupada">
                            <?= $ocupadas ?> vagas ocupadas
                        </span>
                    </div>
                </article>

            <?php endforeach; ?>

        <?php endif; ?>
    </section>

</main>

<footer>
    Park Point — Sistema de gerenciamento de estacionamento
</footer>

</body>
</html>
```