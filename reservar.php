<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];
$perfil = $_SESSION["perfil"] ?? "cliente";
$nome = $_SESSION["usuario_nome"] ?? "Usuário";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function h($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}

$erro = null;
$vaga = null;
$veiculos = [];
$estacionamentoAtivo = null;

if (!isset($_GET["id"]) || filter_var($_GET["id"], FILTER_VALIDATE_INT) === false || (int) $_GET["id"] < 1) {
    $erro = "A vaga informada é inválida.";
} else {
    $vaga_id = (int) $_GET["id"];

    try {
        // Busca os dados da vaga e do estabelecimento.
        $stmt = $pdo->prepare("
            SELECT
                v.id,
                v.numero,
                v.status,
                v.estabelecimento_id,
                e.nome AS estabelecimento_nome,
                e.endereco,
                e.valor_hora
            FROM vagas v
            INNER JOIN estabelecimentos e
                ON e.id = v.estabelecimento_id
            WHERE v.id = ?
            LIMIT 1
        ");

        $stmt->execute([$vaga_id]);
        $vaga = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vaga) {
            $erro = "Vaga não encontrada. Escolha outra vaga disponível.";
        } elseif ($vaga["status"] !== "livre") {
            $erro = "Esta vaga não está mais disponível. Escolha outra vaga.";
        } else {
            // Busca os veículos cadastrados pelo usuário.
            $stmt = $pdo->prepare("
                SELECT id, placa, modelo, cor
                FROM veiculos
                WHERE usuario_id = ?
                ORDER BY id DESC
            ");

            $stmt->execute([$usuario_id]);
            $veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Verifica se algum veículo do usuário está estacionado.
            $stmt = $pdo->prepare("
                SELECT
                    est.id,
                    est.vaga_id,
                    v.numero AS numero_vaga,
                    ve.placa,
                    ve.modelo
                FROM estacionamentos est
                INNER JOIN veiculos ve
                    ON ve.id = est.veiculo_id
                INNER JOIN vagas v
                    ON v.id = est.vaga_id
                WHERE ve.usuario_id = ?
                  AND est.saida IS NULL
                ORDER BY est.id DESC
                LIMIT 1
            ");

            $stmt->execute([$usuario_id]);
            $estacionamentoAtivo = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $erro = "Não foi possível carregar os dados. Tente novamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reservar vaga | Park Point</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            color: #172033;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
        }

        .navbar {
            width: 100%;
            background: #111827;
        }

        .navbar-content {
            max-width: 1400px;
            min-height: 72px;
            margin: auto;
            padding: 14px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .logo {
            color: #fff;
            font-size: 23px;
            font-weight: 800;
            white-space: nowrap;
        }

        .logo span {
            color: #00aaff;
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 5px;
        }

        .nav a {
            color: #dbe4ef;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            transition: 0.2s;
        }

        .nav a:hover,
        .nav a.ativo {
            background: #263449;
            color: #00aaff;
        }

        .nav a.sair {
            background: #dc2626;
            color: #fff;
        }

        .nav a.sair:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        .main {
            padding: 30px 20px 45px;
        }

        .container {
            max-width: 760px;
            margin: 0 auto;
        }

        .voltar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            font-size: 14px;
            font-weight: 700;
            padding: 9px 12px;
            margin-bottom: 18px;
            border-radius: 9px;
            transition: 0.2s;
        }

        .voltar:hover {
            background: #e2e8f0;
            color: #0284c7;
            transform: translateX(-3px);
        }

        .cabecalho {
            background: linear-gradient(135deg, #111827, #1e3a5f);
            color: #fff;
            border-radius: 18px;
            padding: 28px 30px;
            margin-bottom: 22px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.10);
        }

        .cabecalho h1 {
            font-size: 28px;
            margin-bottom: 9px;
        }

        .cabecalho p {
            color: #d7e0ec;
            font-size: 14px;
            line-height: 1.6;
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 5px 18px rgba(15, 23, 42, 0.05);
        }

        .info {
            background: #f1f7fc;
            border: 1px solid #dbeafe;
            border-radius: 13px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .info h2 {
            color: #172033;
            font-size: 17px;
            margin-bottom: 15px;
        }

        .info p {
            font-size: 14px;
            line-height: 1.6;
            margin: 9px 0;
            color: #475569;
            overflow-wrap: anywhere;
        }

        .info strong {
            color: #172033;
        }

        .preco {
            color: #0284c7 !important;
            font-weight: 800;
            font-size: 17px !important;
        }

        .campo {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 9px;
            color: #334155;
        }

        select {
            width: 100%;
            padding: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #fff;
            color: #172033;
            font-size: 15px;
            outline: none;
            transition: 0.2s;
        }

        select:focus {
            border-color: #00aaff;
            box-shadow: 0 0 0 3px rgba(0, 170, 255, 0.12);
        }

        .botao {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            border: none;
            border-radius: 10px;
            padding: 14px 18px;
            background: #0284c7;
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.2s;
        }

        .botao:hover {
            background: #0369a1;
            transform: translateY(-2px);
        }

        .mensagem {
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }

        .mensagem.erro {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #9f1239;
        }

        .mensagem.alerta {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .mensagem.sucesso {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .mensagem p {
            margin-top: 8px;
        }

        .link {
            display: inline-block;
            margin-top: 12px;
            color: #0284c7;
            font-weight: 800;
            font-size: 14px;
        }

        .link:hover {
            color: #0369a1;
            text-decoration: underline;
        }

        .rodape-card {
            border-top: 1px solid #e2e8f0;
            padding-top: 18px;
            margin-top: 20px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
        }

        footer {
            text-align: center;
            padding: 22px 15px;
            color: #94a3b8;
            font-size: 12px;
        }

        @media (max-width: 950px) {
            .navbar-content {
                flex-direction: column;
            }

            .nav {
                justify-content: center;
            }
        }

        @media (max-width: 600px) {
            .navbar-content {
                padding: 17px 14px;
            }

            .nav a {
                padding: 9px;
                font-size: 12px;
            }

            .main {
                padding: 20px 14px 30px;
            }

            .cabecalho {
                padding: 24px 20px;
            }

            .cabecalho h1 {
                font-size: 24px;
            }

            .card {
                padding: 20px;
            }

            .info {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <div class="navbar-content">
        <a href="dashboard.php" class="logo">
            <span>P</span>ARK POINT
        </a>

        <nav class="nav">
            <?php if ($perfil === "admin"): ?>
                <a href="admin.php">Admin</a>
            <?php endif; ?>

            <?php if ($perfil === "funcionario" || $perfil === "admin"): ?>
                <a href="funcionario.php">Funcionário</a>
            <?php endif; ?>

            <a href="dashboard.php">Início</a>
            <a href="vagas.php" class="ativo">Vagas</a>
            <a href="meus-veiculos.php">Meus veículos</a>
            <a href="minhas-reservas.php">Minhas reservas</a>
            <a href="historico.php">Histórico</a>
            <a href="logout.php" class="sair">Sair</a>
        </nav>
    </div>
</header>

<main class="main">
    <div class="container">

        <a href="vagas.php" class="voltar">← Voltar para as vagas</a>

        <section class="cabecalho">
            <h1>Reservar vaga 🅿️</h1>
            <p>
                Olá, <?= h($nome) ?>! Confira os dados do estacionamento
                e selecione o veículo que deseja utilizar.
            </p>
        </section>

        <?php if ($erro): ?>

            <div class="mensagem erro">
                <strong><?= h($erro) ?></strong>
                <p>Volte à lista para consultar as vagas disponíveis.</p>
                <a href="vagas.php" class="link">Ver vagas disponíveis →</a>
            </div>

        <?php else: ?>

            <section class="card">

                <div class="info">
                    <h2>Detalhes da vaga</h2>

                    <p>
                        <strong>Estacionamento:</strong>
                        <?= h($vaga["estabelecimento_nome"]) ?>
                    </p>

                    <p>
                        <strong>Endereço:</strong>
                        <?= h($vaga["endereco"]) ?>
                    </p>

                    <p>
                        <strong>Número da vaga:</strong>
                        <?= h($vaga["numero"]) ?>
                    </p>

                    <p class="preco">
                        R$ <?= number_format((float)$vaga["valor_hora"], 2, ",", ".") ?> por hora
                    </p>
                </div>

                <?php if ($estacionamentoAtivo): ?>

                    <div class="mensagem alerta">
                        <strong>Você já possui um veículo estacionado.</strong>

                        <p>
                            Veículo:
                            <?= h($estacionamentoAtivo["modelo"] ?: "Modelo não informado") ?>
                            — <?= h($estacionamentoAtivo["placa"]) ?>
                        </p>

                        <p>
                            Vaga atual: <?= h($estacionamentoAtivo["numero_vaga"]) ?>.
                            Finalize esse estacionamento antes de iniciar outro.
                        </p>

                        <a href="minhas-reservas.php" class="link">
                            Consultar meu estacionamento →
                        </a>
                    </div>

                <?php elseif (empty($veiculos)): ?>

                    <div class="mensagem alerta">
                        <strong>Você ainda não possui veículos cadastrados.</strong>

                        <p>
                            Cadastre um veículo para continuar com a reserva.
                        </p>

                        <a href="cadastro-veiculo.php" class="link">
                            Cadastrar veículo →
                        </a>
                    </div>

                <?php else: ?>

                    <form action="confirmar-reserva.php" method="POST">

                        <input
                            type="hidden"
                            name="vaga_id"
                            value="<?= (int)$vaga["id"] ?>"
                        >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= h($_SESSION["csrf_token"]) ?>"
                        >

                        <div class="campo">
                            <label for="veiculo_id">Escolha o veículo</label>

                            <select name="veiculo_id" id="veiculo_id" required>
                                <option value="">Selecione um veículo</option>

                                <?php foreach ($veiculos as $veiculo): ?>
                                    <option value="<?= (int)$veiculo["id"] ?>">
                                        <?= h($veiculo["placa"]) ?>
                                        <?= !empty($veiculo["modelo"]) ? " — " . h($veiculo["modelo"]) : "" ?>
                                        <?= !empty($veiculo["cor"]) ? " — " . h($veiculo["cor"]) : "" ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="botao">
                            Confirmar reserva
                        </button>

                        <div class="rodape-card">
                            Antes de confirmar, confira se selecionou o veículo correto.
                            A disponibilidade da vaga deverá ser validada novamente
                            ao processar a reserva.
                        </div>
                    </form>

                <?php endif; ?>

            </section>

        <?php endif; ?>

    </div>
</main>

<footer>
    © <?= date("Y") ?> Park Point — Sistema de gerenciamento de estacionamento.
</footer>

</body>
</html>