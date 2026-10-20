
<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];
$perfil = $_SESSION["perfil"] ?? "cliente";

function h($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

$stmt = $pdo->prepare("
    SELECT id, placa, modelo, cor
    FROM veiculos
    WHERE usuario_id = ?
    ORDER BY id DESC
");
$stmt->execute([$usuario_id]);
$veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($perfil === "admin") {
    $painel_url = "admin.php";
    $painel_nome = "Painel administrativo";
} elseif ($perfil === "funcionario") {
    $painel_url = "funcionario.php";
    $painel_nome = "Painel do funcionário";
} else {
    $painel_url = "dashboard.php";
    $painel_nome = "Painel do cliente";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus veículos | Park Point</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --azul: #00aaff;
            --azul-escuro: #008bd1;
            --navy: #101827;
            --fundo: #f4f7fb;
            --texto: #172033;
            --cinza: #667085;
            --borda: #e5eaf1;
            --vermelho: #dc2626;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--fundo);
            color: var(--texto);
        }

        nav {
            min-height: 70px;
            background: var(--navy);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 15px 5%;
            flex-wrap: wrap;
        }

        .logo {
            color: white;
            text-decoration: none;
            font-size: 23px;
            font-weight: 800;
            letter-spacing: .5px;
        }

        .logo-p {
            color: var(--azul);
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .menu a {
            color: #e5eaf1;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
            transition: .2s;
        }

        .menu a:hover,
        .menu a.ativo {
            background: rgba(0, 170, 255, .14);
            color: var(--azul);
        }

        .container {
            width: min(1100px, 92%);
            margin: 35px auto 50px;
        }

        .cabecalho {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .cabecalho h1 {
            margin: 0 0 10px;
            font-size: 30px;
        }

        .cabecalho p {
            margin: 0;
            color: var(--cinza);
            line-height: 1.6;
        }

        .painel-seletor {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 28px;
        }

        .atalho {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 15px;
            border: 1px solid var(--borda);
            border-radius: 10px;
            background: white;
            color: var(--texto);
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: .2s;
        }

        .atalho:hover {
            border-color: var(--azul);
            color: var(--azul-escuro);
            transform: translateY(-2px);
        }

        .atalho.atual {
            background: #eaf8ff;
            border-color: var(--azul);
            color: #007bb8;
        }

        .botao {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            border: none;
            border-radius: 10px;
            background: var(--azul);
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: .2s;
        }

        .botao:hover {
            background: var(--azul-escuro);
            transform: translateY(-2px);
        }

        .resumo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
            background: white;
            border: 1px solid var(--borda);
            border-radius: 14px;
            padding: 18px 22px;
            margin-bottom: 22px;
        }

        .resumo span {
            color: var(--cinza);
            font-size: 14px;
        }

        .resumo strong {
            font-size: 22px;
            color: var(--azul-escuro);
        }

        .veiculos-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .veiculo-card {
            background: white;
            border: 1px solid var(--borda);
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 4px 15px rgba(16, 24, 39, .04);
            transition: .2s;
            min-width: 0;
        }

        .veiculo-card:hover {
            transform: translateY(-3px);
            border-color: #b7e8ff;
            box-shadow: 0 10px 24px rgba(16, 24, 39, .08);
        }

        .veiculo-topo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 18px;
        }

        .veiculo-icone {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: #eaf8ff;
            border-radius: 12px;
            font-size: 25px;
        }

        .etiqueta {
            padding: 6px 9px;
            border-radius: 20px;
            background: #eaf8ff;
            color: #007bb8;
            font-size: 10px;
            font-weight: bold;
        }

        .veiculo-modelo {
            margin: 0 0 12px;
            font-size: 20px;
            overflow-wrap: anywhere;
        }

        .veiculo-placa {
            display: inline-block;
            margin-bottom: 18px;
            padding: 8px 11px;
            border: 2px solid #d5dce6;
            border-radius: 6px;
            background: #f8fafc;
            color: var(--navy);
            font-weight: bold;
            letter-spacing: 1px;
        }

        .veiculo-info {
            border-top: 1px solid var(--borda);
            padding-top: 15px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .info-linha {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 14px;
        }

        .info-linha span:first-child {
            color: var(--cinza);
        }

        .info-linha span:last-child {
            color: var(--texto);
            font-weight: 600;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .acoes {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .acao {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 8px;
            border-radius: 9px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            transition: .2s;
        }

        .editar {
            background: #eaf8ff;
            color: #007bb8;
        }

        .editar:hover {
            background: #d6f1ff;
        }

        .excluir {
            background: #fef2f2;
            color: var(--vermelho);
        }

        .excluir:hover {
            background: #fee2e2;
        }

        .vazio {
            background: white;
            border: 1px dashed #cbd5e1;
            border-radius: 16px;
            padding: 50px 22px;
            text-align: center;
        }

        .vazio-icone {
            font-size: 45px;
            margin-bottom: 14px;
        }

        .vazio h2 {
            margin: 0 0 10px;
        }

        .vazio p {
            max-width: 450px;
            margin: 0 auto 22px;
            color: var(--cinza);
            line-height: 1.6;
        }

        footer {
            text-align: center;
            padding: 22px;
            color: var(--cinza);
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .veiculos-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            nav {
                padding: 16px 4%;
            }

            .menu {
                width: 100%;
            }

            .menu a {
                padding: 9px;
            }

            .container {
                margin-top: 25px;
            }

            .cabecalho h1 {
                font-size: 26px;
            }

            .cabecalho .botao {
                width: 100%;
            }

            .veiculos-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<nav>
    <a href="<?= h($painel_url) ?>" class="logo">
        <span class="logo-p">P</span>ARK POINT
    </a>

    <div class="menu">
        <a href="<?= h($painel_url) ?>"><?= h($painel_nome) ?></a>
        <a href="vagas.php">Vagas</a>

        <?php if ($perfil === "admin"): ?>
            <a href="funcionario.php">Funcionário</a>
            <a href="cadastro-estacionamento.php">Estacionamentos</a>
        <?php elseif ($perfil === "funcionario"): ?>
            <a href="minhas-reservas.php">Reservas</a>
        <?php else: ?>
            <a href="meus-veiculos.php" class="ativo">Meus veículos</a>
            <a href="minhas-reservas.php">Reservas</a>
            <a href="historico.php">Histórico</a>
        <?php endif; ?>

        <a href="logout.php">Sair</a>
    </div>
</nav>

<main class="container">

    <header class="cabecalho">
        <div>
            <h1>Meus veículos 🚗</h1>
            <p>Gerencie os veículos cadastrados na sua conta.</p>
        </div>

        <a href="cadastrar-veiculo.php" class="botao">
            + Adicionar veículo
        </a>
    </header>

    <div class="painel-seletor">
        <a href="<?= h($painel_url) ?>" class="atalho">
            ← Voltar ao painel
        </a>

        <?php if ($perfil === "admin"): ?>
            <a href="funcionario.php" class="atalho">Painel do funcionário</a>
            <a href="dashboard.php" class="atalho">Painel do cliente</a>
        <?php elseif ($perfil === "funcionario"): ?>
            <a href="admin.php" class="atalho">Painel administrativo</a>
            <a href="dashboard.php" class="atalho">Painel do cliente</a>
        <?php endif; ?>
    </div>

    <section class="resumo">
        <span>Veículos cadastrados na sua conta</span>
        <strong><?= count($veiculos) ?></strong>
    </section>

    <?php if ($veiculos): ?>

        <div class="veiculos-grid">
            <?php foreach ($veiculos as $veiculo): ?>
                <article class="veiculo-card">

                    <div class="veiculo-topo">
                        <div class="veiculo-icone">🚗</div>
                        <span class="etiqueta">CADASTRADO</span>
                    </div>

                    <h2 class="veiculo-modelo">
                        <?= h($veiculo["modelo"]) ?>
                    </h2>

                    <div class="veiculo-placa">
                        <?= h($veiculo["placa"]) ?>
                    </div>

                    <div class="veiculo-info">
                        <div class="info-linha">
                            <span>Modelo</span>
                            <span><?= h($veiculo["modelo"]) ?></span>
                        </div>

                        <div class="info-linha">
                            <span>Cor</span>
                            <span><?= h($veiculo["cor"]) ?></span>
                        </div>
                    </div>

                    <div class="acoes">
                        <a
                            href="editar-veiculos.php?id=<?= (int) $veiculo["id"] ?>"
                            class="acao editar"
                        >✏️ Editar</a>

                        <a
                            href="excluir-veiculo.php?id=<?= (int) $veiculo["id"] ?>"
                            class="acao excluir"
                            onclick="return confirm('Tem certeza que deseja excluir este veículo?');"
                        >🗑️ Excluir</a>
                    </div>

                </article>
            <?php endforeach; ?>
        </div>

    <?php else: ?>

        <section class="vazio">
            <div class="vazio-icone">🚗</div>
            <h2>Nenhum veículo cadastrado</h2>
            <p>
                Cadastre seu primeiro veículo para poder realizar
                reservas no Park Point.
            </p>
            <a href="cadastrar-veiculo.php" class="botao">
                + Cadastrar veículo
            </a>
        </section>

    <?php endif; ?>

</main>

<footer>
    Park Point © <?= date("Y") ?> — Sistema de gerenciamento de estacionamento
</footer>

</body>
</html>
