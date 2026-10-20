
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

/* Busca os estacionamentos disponíveis */
if ($perfil === "admin") {
    $stmt = $pdo->prepare("
        SELECT id, nome, endereco, valor_hora
        FROM estabelecimentos
        WHERE usuario_id = ?
        ORDER BY nome
    ");
    $stmt->execute([$usuario_id]);
} else {
    $stmt = $pdo->query("
        SELECT id, nome, endereco, valor_hora
        FROM estabelecimentos
        ORDER BY nome
    ");
}

$estabelecimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$estabelecimento = null;
$estabelecimento_id = 0;
$vagas = [];

if ($estabelecimentos) {
    $estabelecimento_id = isset($_GET["estabelecimento"])
        ? (int) $_GET["estabelecimento"]
        : (int) $estabelecimentos[0]["id"];

    foreach ($estabelecimentos as $est) {
        if ((int) $est["id"] === $estabelecimento_id) {
            $estabelecimento = $est;
            break;
        }
    }

    if (!$estabelecimento) {
        $estabelecimento = $estabelecimentos[0];
        $estabelecimento_id = (int) $estabelecimento["id"];
    }

    $stmt = $pdo->prepare("
        SELECT id, numero, status
        FROM vagas
        WHERE estabelecimento_id = ?
        ORDER BY numero
    ");
    $stmt->execute([$estabelecimento_id]);
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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
    <title>Vagas | Park Point</title>

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
            --verde: #16a34a;
            --vermelho: #dc2626;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: var(--fundo);
            color: var(--texto);
        }

        nav {
            min-height: 70px;
            background: var(--navy);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
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
            gap: 8px;
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
            margin-bottom: 28px;
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
            margin: 22px 0;
        }

        .atalho {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 15px;
            border: 1px solid var(--borda);
            border-radius: 10px;
            background: white;
            color: var(--texto);
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            transition: .2s;
        }

        .atalho:hover {
            border-color: var(--azul);
            color: var(--azul-escuro);
            transform: translateY(-2px);
        }

        .atalho.atual {
            border-color: var(--azul);
            background: #eaf8ff;
            color: #007bb8;
        }

        .cartao {
            background: white;
            border: 1px solid var(--borda);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(16, 24, 39, .04);
        }

        .estabelecimentos {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .estabelecimento {
            padding: 12px 16px;
            background: white;
            border: 1px solid var(--borda);
            border-radius: 10px;
            color: var(--texto);
            text-decoration: none;
            font-weight: bold;
            transition: .2s;
        }

        .estabelecimento:hover {
            border-color: var(--azul);
            color: var(--azul-escuro);
        }

        .estabelecimento.ativo {
            background: var(--azul);
            border-color: var(--azul);
            color: white;
        }

        .detalhes h2 {
            margin: 0 0 14px;
        }

        .detalhes p {
            margin: 9px 0;
            color: var(--cinza);
            line-height: 1.5;
        }

        .legenda {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
        }

        .legenda-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--cinza);
            font-size: 14px;
        }

        .bolinha {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .bolinha.verde {
            background: var(--verde);
        }

        .bolinha.vermelha {
            background: var(--vermelho);
        }

        .estacionamento {
            background: var(--navy);
            padding: 28px;
            border-radius: 16px;
        }

        .entrada {
            color: white;
            text-align: center;
            font-weight: bold;
            padding: 13px;
            margin-bottom: 25px;
            border: 1px dashed #718096;
            border-radius: 8px;
            letter-spacing: 1px;
        }

        .vagas {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .vaga {
            min-height: 115px;
            padding: 12px;
            border-radius: 12px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            font-weight: bold;
        }

        a.vaga {
            text-decoration: none;
        }

        .vaga.livre {
            background: var(--verde);
            transition: transform .2s, background .2s;
        }

        .vaga.livre:hover {
            background: #15803d;
            transform: translateY(-3px);
        }

        .vaga.ocupada {
            background: var(--vermelho);
            cursor: not-allowed;
        }

        .numero {
            font-size: 28px;
        }

        .status {
            margin-top: 9px;
            font-size: 11px;
            line-height: 1.4;
        }

        .mensagem {
            padding: 30px 15px;
            text-align: center;
            color: white;
            line-height: 1.6;
        }

        .sem-estacionamento {
            text-align: center;
            padding: 45px 20px;
        }

        .sem-estacionamento p {
            color: var(--cinza);
            line-height: 1.6;
        }

        .botao {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 9px;
            background: var(--azul);
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: .2s;
        }

        .botao:hover {
            background: var(--azul-escuro);
        }

        footer {
            text-align: center;
            padding: 22px;
            color: var(--cinza);
            font-size: 13px;
        }

        @media (max-width: 750px) {
            nav {
                padding: 16px 4%;
            }

            .menu {
                width: 100%;
            }

            .menu a {
                padding: 9px;
            }

            .vagas {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .estacionamento {
                padding: 18px;
            }

            .cabecalho h1 {
                font-size: 25px;
            }

            .cartao {
                padding: 18px;
            }
        }

        @media (max-width: 380px) {
            .vagas {
                gap: 10px;
            }

            .vaga {
                min-height: 100px;
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
        <a href="vagas.php" class="ativo">Vagas</a>

        <?php if ($perfil === "admin"): ?>
            <a href="funcionario.php">Funcionário</a>
            <a href="cadastro-estacionamento.php">Estacionamentos</a>
        <?php elseif ($perfil === "funcionario"): ?>
            <a href="minhas-reservas.php">Reservas</a>
        <?php else: ?>
            <a href="meus-veiculos.php">Meus veículos</a>
            <a href="minhas-reservas.php">Minhas reservas</a>
            <a href="historico.php">Histórico</a>
        <?php endif; ?>

        <a href="logout.php">Sair</a>
    </div>
</nav>

<main class="container">
    <header class="cabecalho">
        <h1>Encontre sua vaga</h1>
        <p>Escolha um estacionamento e veja quais vagas estão disponíveis.</p>
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

    <?php if (!$estabelecimentos): ?>

        <section class="cartao sem-estacionamento">
            <h2>Nenhum estacionamento cadastrado</h2>
            <p>
                Ainda não há estacionamentos disponíveis para mostrar.
                <?php if ($perfil === "admin"): ?>
                    Cadastre um estacionamento para começar.
                <?php endif; ?>
            </p>

            <?php if ($perfil === "admin"): ?>
                <a href="cadastro-estacionamento.php" class="botao">
                    Cadastrar estacionamento
                </a>
            <?php endif; ?>
        </section>

    <?php else: ?>

        <section class="cartao">
            <h2>Escolha um estacionamento</h2>

            <div class="estabelecimentos">
                <?php foreach ($estabelecimentos as $est): ?>
                    <a
                        href="vagas.php?estabelecimento=<?= (int) $est["id"] ?>"
                        class="estabelecimento <?= ((int) $est["id"] === $estabelecimento_id) ? "ativo" : "" ?>"
                    >
                        <?= h($est["nome"]) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="cartao detalhes">
            <h2><?= h($estabelecimento["nome"]) ?></h2>

            <p>📍 <?= h($estabelecimento["endereco"]) ?></p>

            <p>
                💰 R$ <?= number_format(
                    (float) $estabelecimento["valor_hora"],
                    2,
                    ",",
                    "."
                ) ?> por hora
            </p>
        </section>

        <div class="legenda">
            <div class="legenda-item">
                <span class="bolinha verde"></span>
                Vaga livre
            </div>

            <div class="legenda-item">
                <span class="bolinha vermelha"></span>
                Vaga ocupada
            </div>
        </div>

        <section class="estacionamento">
            <div class="entrada">ENTRADA / SAÍDA</div>

            <?php if (!$vagas): ?>
                <div class="mensagem">
                    Nenhuma vaga cadastrada neste estacionamento.
                </div>
            <?php else: ?>
                <div class="vagas">
                    <?php foreach ($vagas as $vaga): ?>
                        <?php if ($vaga["status"] === "livre"): ?>
                            <a
                                href="reservar.php?id=<?= (int) $vaga["id"] ?>"
                                class="vaga livre"
                                aria-label="Reservar vaga <?= h($vaga["numero"]) ?>"
                            >
                                <span class="numero"><?= h($vaga["numero"]) ?></span>
                                <span class="status">CLIQUE PARA RESERVAR</span>
                            </a>
                        <?php else: ?>
                            <div
                                class="vaga ocupada"
                                aria-label="Vaga <?= h($vaga["numero"]) ?> ocupada"
                            >
                                <span class="numero"><?= h($vaga["numero"]) ?></span>
                                <span class="status">OCUPADA</span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    <?php endif; ?>
</main>

<footer>
    Park Point © <?= date("Y") ?> — Sistema de gerenciamento de estacionamento
</footer>

</body>
</html>
 