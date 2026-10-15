```php
<?php
session_start();
require_once "conexao.php";

// Verifica o login
if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

// Dados da sessão
$usuario_id = (int) $_SESSION["usuario_id"];
$nome = $_SESSION["usuario_nome"] ?? "Usuário";
$perfil = $_SESSION["perfil"] ?? "cliente";

// Escapa os valores exibidos na página
function h($valor): string
{
    return htmlspecialchars(
        (string) ($valor ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

// Valores padrão
$totalVeiculos = 0;
$totalAtivos = 0;
$ativo = false;
$erro = "";

try {
    // Total de veículos do usuário
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM veiculos
        WHERE usuario_id = ?
    ");
    $stmt->execute([$usuario_id]);
    $totalVeiculos = (int) $stmt->fetchColumn();

    // Total de estacionamentos ainda ativos
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM estacionamentos e
        INNER JOIN veiculos v ON v.id = e.veiculo_id
        WHERE v.usuario_id = ?
          AND e.saida IS NULL
    ");
    $stmt->execute([$usuario_id]);
    $totalAtivos = (int) $stmt->fetchColumn();

    // Informações do estacionamento ativo mais recente
    $stmt = $pdo->prepare("
        SELECT
            e.id,
            e.entrada,
            v.numero AS numero_vaga,
            ve.placa,
            ve.modelo,
            est.nome AS estacionamento
        FROM estacionamentos e
        INNER JOIN vagas v ON v.id = e.vaga_id
        INNER JOIN veiculos ve ON ve.id = e.veiculo_id
        LEFT JOIN estabelecimentos est
            ON est.id = v.estabelecimento_id
        WHERE ve.usuario_id = ?
          AND e.saida IS NULL
        ORDER BY e.id DESC
        LIMIT 1
    ");
    $stmt->execute([$usuario_id]);
    $ativo = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    error_log("Erro no dashboard do Park Point: " . $e->getMessage());
    $erro = "Não foi possível carregar todas as informações. Atualize a página.";
}

// Formata a data de entrada
$dataEntrada = "";

if ($ativo && !empty($ativo["entrada"])) {
    $timestamp = strtotime($ativo["entrada"]);

    if ($timestamp !== false) {
        $dataEntrada = date("d/m/Y H:i", $timestamp);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Meu painel | Park Point</title>

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

        a {
            -webkit-tap-highlight-color: transparent;
        }

        /* MENU SUPERIOR */

        .navbar {
            background: #ffffff;
            min-height: 76px;
            padding: 15px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #172554;
            text-decoration: none;
            font-size: 23px;
            font-weight: 800;
            white-space: nowrap;
        }

        .logo-p {
            width: 42px;
            height: 42px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border-radius: 12px;
            background: #2563eb;
            color: #ffffff;
            font-size: 25px;
        }

        .logo-point {
            color: #2563eb;
        }

        .menu {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px;
        }

        .menu a {
            display: inline-block;
            padding: 10px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s, color 0.2s;
        }

        .menu a:hover,
        .menu a.atual {
            color: #2563eb;
            background: #eff6ff;
        }

        .menu a.sair {
            background: #fff1f2;
            color: #be123c;
        }

        .menu a.sair:hover {
            background: #ffe4e6;
            color: #9f1239;
        }

        /* CONTEÚDO */

        .container {
            width: 90%;
            max-width: 1160px;
            margin: 38px auto;
        }

        .bem-vindo {
            margin-bottom: 28px;
        }

        .bem-vindo h1 {
            margin: 0 0 10px;
            color: #172554;
            font-size: 30px;
            line-height: 1.3;
        }

        .bem-vindo p {
            margin: 0;
            color: #64748b;
            line-height: 1.6;
        }

        .alerta {
            padding: 14px 16px;
            margin-bottom: 22px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            border-radius: 10px;
        }

        /* INDICADORES */

        .cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 26px;
        }

        .card {
            display: block;
            padding: 25px;
            background: #ffffff;
            border: 1px solid #edf0f5;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
        }

        .card-label {
            display: block;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .card-numero {
            display: block;
            color: #2563eb;
            font-size: 34px;
            font-weight: 800;
        }

        .card-ajuda {
            display: block;
            margin-top: 10px;
            color: #64748b;
            font-size: 13px;
        }

        /* SEÇÕES */

        .secao {
            padding: 26px;
            margin-bottom: 26px;
            background: #ffffff;
            border: 1px solid #edf0f5;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
        }

        .secao-titulo {
            margin: 0 0 8px;
            color: #172554;
            font-size: 21px;
        }

        .secao-descricao {
            margin: 0 0 20px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        /* ESTACIONAMENTO ATIVO */

        .informacoes {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .info {
            min-width: 0;
            padding: 16px;
            background: #f8fafc;
            border: 1px solid #e8edf4;
            border-radius: 10px;
            overflow-wrap: anywhere;
        }

        .info small {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 13px;
        }

        .info strong {
            color: #172033;
            font-size: 15px;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-size: 12px;
            font-weight: 700;
        }

        /* ACESSO RÁPIDO */

        .botoes {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .botao {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 76px;
            padding: 17px;
            border: 1px solid #e5eaf2;
            border-radius: 12px;
            background: #ffffff;
            color: #172033;
            text-decoration: none;
            transition: border-color 0.2s, background 0.2s, transform 0.2s;
        }

        .botao:hover {
            background: #f8fbff;
            border-color: #93c5fd;
            transform: translateY(-2px);
        }

        .botao-conteudo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .botao-icone {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border-radius: 10px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 20px;
        }

        .botao-texto strong {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .botao-texto small {
            display: block;
            color: #64748b;
            font-size: 12px;
            line-height: 1.4;
        }

        .seta {
            color: #94a3b8;
            font-size: 20px;
        }

        .vazio {
            padding: 20px;
            border-radius: 10px;
            background: #f8fafc;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        footer {
            padding: 25px 15px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }

        /* CELULAR E TABLET */

        @media (max-width: 850px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .menu {
                justify-content: flex-start;
                width: 100%;
            }
        }

        @media (max-width: 600px) {
            .container {
                width: 92%;
                margin: 26px auto;
            }

            .bem-vindo h1 {
                font-size: 25px;
            }

            .cards,
            .informacoes,
            .botoes {
                grid-template-columns: 1fr;
            }

            .secao,
            .card {
                padding: 20px;
            }

            .menu {
                gap: 4px;
            }

            .menu a {
                padding: 9px 10px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a href="dashboard.php" class="logo">
        <span class="logo-p">P</span>
        <span>Park <span class="logo-point">Point</span></span>
    </a>

    <nav class="menu" aria-label="Menu principal">
        <a href="dashboard.php" class="atual">Início</a>
        <a href="vagas.php">Vagas</a>
        <a href="meus-veiculos.php">Meus veículos</a>
        <a href="minhas-reservas.php">Minhas reservas</a>
        <a href="historico.php">Histórico</a>

        <?php if ($perfil === "admin"): ?>
            <a href="admin.php">Administração</a>
            <a href="funcionario.php">Painel funcionário</a>
        <?php elseif ($perfil === "funcionario"): ?>
            <a href="funcionario.php">Painel funcionário</a>
        <?php endif; ?>

        <a href="logout.php" class="sair">Sair</a>
    </nav>
</header>

<main class="container">

    <section class="bem-vindo">
        <h1>Olá, <?= h($nome) ?>! 👋</h1>
        <p>Bem-vindo ao seu painel do Park Point. Confira seus veículos e estacionamentos.</p>
    </section>

    <?php if ($erro !== ""): ?>
        <div class="alerta"><?= h($erro) ?></div>
    <?php endif; ?>

    <section class="cards">
        <a href="meus-veiculos.php" class="card">
            <span class="card-label">Meus veículos</span>
            <strong class="card-numero"><?= $totalVeiculos ?></strong>
            <span class="card-ajuda">Consultar e gerenciar veículos →</span>
        </a>

        <a href="minhas-reservas.php" class="card">
            <span class="card-label">Estacionamentos ativos</span>
            <strong class="card-numero"><?= $totalAtivos ?></strong>
            <span class="card-ajuda">Consultar suas movimentações →</span>
        </a>
    </section>

    <section class="secao">
        <h2 class="secao-titulo">🚗 Estacionamento atual</h2>
        <p class="secao-descricao">
            Aqui aparece o estacionamento mais recente que ainda não possui saída registrada.
        </p>

        <?php if ($ativo): ?>
            <div style="margin-bottom: 18px;">
                <span class="status">Estacionamento ativo</span>
            </div>

            <div class="informacoes">
                <div class="info">
                    <small>Veículo</small>
                    <strong>
                        <?= h($ativo["placa"]) ?>
                        <?php if (!empty($ativo["modelo"])): ?>
                            — <?= h($ativo["modelo"]) ?>
                        <?php endif; ?>
                    </strong>
                </div>

                <div class="info">
                    <small>Vaga</small>
                    <strong><?= h($ativo["numero_vaga"]) ?></strong>
                </div>

                <div class="info">
                    <small>Estacionamento</small>
                    <strong>
                        <?= h($ativo["estacionamento"] ?: "Park Point") ?>
                    </strong>
                </div>

                <div class="info">
                    <small>Data e hora de entrada</small>
                    <strong>
                        <?= h($dataEntrada ?: "Não informada") ?>
                    </strong>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <a href="minhas-reservas.php" class="botao" style="display: inline-flex;">
                    Consultar minhas movimentações →
                </a>
            </div>

        <?php else: ?>
            <div class="vazio">
                Você não possui um estacionamento ativo no momento.
                <br>
                Quando houver uma entrada registrada para um dos seus veículos,
                as informações aparecerão aqui.
            </div>

            <div style="margin-top: 18px;">
                <a href="vagas.php" class="botao" style="display: inline-flex;">
                    Encontrar uma vaga →
                </a>
            </div>
        <?php endif; ?>
    </section>

    <section class="secao">
        <h2 class="secao-titulo">Acesso rápido</h2>
        <p class="secao-descricao">
            Escolha para onde deseja ir.
        </p>

        <div class="botoes">
            <a href="vagas.php" class="botao">
                <span class="botao-conteudo">
                    <span class="botao-icone">🅿️</span>
                    <span class="botao-texto">
                        <strong>Encontrar uma vaga</strong>
                        <small>Consultar as vagas disponíveis</small>
                    </span>
                </span>
                <span class="seta">›</span>
            </a>

            <a href="meus-veiculos.php" class="botao">
                <span class="botao-conteudo">
                    <span class="botao-icone">🚗</span>
                    <span class="botao-texto">
                        <strong>Meus veículos</strong>
                        <small>Cadastrar, editar ou consultar</small>
                    </span>
                </span>
                <span class="seta">›</span>
            </a>

            <a href="minhas-reservas.php" class="botao">
                <span class="botao-conteudo">
                    <span class="botao-icone">📋</span>
                    <span class="botao-texto">
                        <strong>Minhas reservas</strong>
                        <small>Consultar seus registros ativos</small>
                    </span>
                </span>
                <span class="seta">›</span>
            </a>

            <a href="historico.php" class="botao">
                <span class="botao-conteudo">
                    <span class="botao-icone">🕒</span>
                    <span class="botao-texto">
                        <strong>Histórico</strong>
                        <small>Consultar movimentações anteriores</small>
                    </span>
                </span>
                <span class="seta">›</span>
            </a>
        </div>
    </section>

</main>

<footer>
    Park Point &copy; <?= date("Y") ?> — Sistema de gerenciamento de estacionamento
</footer>

</body>
</html>
```