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
$nome = $_SESSION["usuario_nome"] ?? "Usuário";

$historico = [];
$erro = null;

function h($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}

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
        INNER JOIN vagas v ON e.vaga_id = v.id
        INNER JOIN veiculos ve ON e.veiculo_id = ve.id
        WHERE ve.usuario_id = ?
          AND e.saida IS NOT NULL
        ORDER BY e.saida DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $erro = "Não foi possível carregar o histórico. Tente novamente.";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico | Park Point</title>

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
            background: #111827;
            width: 100%;
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
            padding: 30px;
        }

        .container {
            max-width: 1200px;
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
            padding: 32px;
            margin-bottom: 25px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.10);
        }

        .cabecalho h1 {
            font-size: 29px;
            margin-bottom: 9px;
        }

        .cabecalho p {
            color: #d7e0ec;
            font-size: 14px;
            line-height: 1.6;
        }

        .resumo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .resumo h2 {
            font-size: 20px;
            color: #172033;
        }

        .contador {
            background: #e0f2fe;
            color: #0369a1;
            padding: 8px 13px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 800;
        }

        .erro {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .vazio {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 50px 25px;
            text-align: center;
            box-shadow: 0 5px 16px rgba(15, 23, 42, 0.04);
        }

        .vazio-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .vazio h2 {
            font-size: 21px;
            margin-bottom: 9px;
        }

        .vazio p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 22px;
        }

        .botao {
            display: inline-block;
            background: #0284c7;
            color: #fff;
            padding: 12px 18px;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 700;
            transition: 0.2s;
        }

        .botao:hover {
            background: #0369a1;
            transform: translateY(-2px);
        }

        .lista {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .registro {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 23px;
            box-shadow: 0 5px 16px rgba(15, 23, 42, 0.04);
            transition: 0.2s;
        }

        .registro:hover {
            transform: translateY(-3px);
            border-color: #bae6fd;
            box-shadow: 0 12px 25px rgba(15, 23, 42, 0.08);
        }

        .registro-topo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
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
            background: #e0f2fe;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
        }

        .vaga-texto small,
        .dado small,
        .veiculo-info small {
            display: block;
            color: #64748b;
            font-size: 11px;
            margin-bottom: 5px;
        }

        .vaga-texto strong {
            color: #111827;
            font-size: 19px;
        }

        .status {
            background: #dcfce7;
            color: #166534;
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
        }

        .dados {
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 17px 0;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .dado strong {
            display: block;
            color: #334155;
            font-size: 13px;
            line-height: 1.5;
        }

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
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .veiculo-info strong {
            display: block;
            color: #334155;
            font-size: 14px;
        }

        .tempo {
            margin-top: 18px;
            padding: 12px 14px;
            background: #f1f5f9;
            border-radius: 10px;
            color: #64748b;
            font-size: 12px;
        }

        .tempo strong {
            color: #0369a1;
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

            .lista {
                grid-template-columns: 1fr;
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
                padding: 25px 21px;
            }

            .cabecalho h1 {
                font-size: 25px;
            }

            .registro {
                padding: 19px;
            }

            .dados {
                grid-template-columns: 1fr;
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
            <a href="vagas.php">Vagas</a>
            <a href="meus-veiculos.php">Meus veículos</a>
            <a href="minhas-reservas.php">Minhas reservas</a>
            <a href="historico.php" class="ativo">Histórico</a>
            <a href="logout.php" class="sair">Sair</a>
        </nav>
    </div>
</header>

<main class="main">
    <div class="container">

        <a href="dashboard.php" class="voltar">← Voltar ao painel</a>

        <section class="cabecalho">
            <h1>Histórico de estacionamentos 📋</h1>
            <p>
                Olá, <?= h($nome) ?>! Consulte seus estacionamentos finalizados,
                os horários de entrada e saída e os veículos utilizados.
            </p>
        </section>

        <?php if ($erro): ?>

            <div class="erro"><?= h($erro) ?></div>

        <?php elseif (empty($historico)): ?>

            <section class="vazio">
                <div class="vazio-icon">📋</div>
                <h2>Seu histórico está vazio</h2>
                <p>
                    Quando você finalizar um estacionamento, os registros
                    aparecerão aqui.
                </p>
                <a href="vagas.php" class="botao">Encontrar uma vaga</a>
            </section>

        <?php else: ?>

            <div class="resumo">
                <h2>Estacionamentos anteriores</h2>
                <span class="contador">
                    <?= count($historico) ?>
                    <?= count($historico) === 1 ? "registro" : "registros" ?>
                </span>
            </div>

            <section class="lista">
                <?php foreach ($historico as $registro): ?>

                    <?php
                    $inicio = strtotime($registro["entrada"]);
                    $fim = strtotime($registro["saida"]);

                    $entrada = $inicio ? date("d/m/Y H:i", $inicio) : "Não informado";
                    $saida = $fim ? date("d/m/Y H:i", $fim) : "Não informado";
                    $tempo = "";

                    if ($inicio && $fim && $fim >= $inicio) {
                        $segundos = $fim - $inicio;
                        $horas = intdiv($segundos, 3600);
                        $minutos = intdiv($segundos % 3600, 60);

                        if ($horas > 0) {
                            $tempo .= $horas . ($horas === 1 ? " hora " : " horas ");
                        }

                        $tempo .= $minutos . " min";
                    }
                    ?>

                    <article class="registro">
                        <div class="registro-topo">
                            <div class="vaga">
                                <div class="vaga-icon">🅿️</div>
                                <div class="vaga-texto">
                                    <small>Vaga utilizada</small>
                                    <strong>Nº <?= h($registro["vaga"]) ?></strong>
                                </div>
                            </div>

                            <span class="status">FINALIZADA</span>
                        </div>

                        <div class="dados">
                            <div class="dado">
                                <small>Entrada</small>
                                <strong><?= h($entrada) ?></strong>
                            </div>

                            <div class="dado">
                                <small>Saída</small>
                                <strong><?= h($saida) ?></strong>
                            </div>
                        </div>

                        <div class="veiculo">
                            <div class="veiculo-icon">🚗</div>
                            <div class="veiculo-info">
                                <small>Veículo utilizado</small>
                                <strong>
                                    <?= h($registro["modelo"]) ?> — <?= h($registro["placa"]) ?>
                                </strong>
                                <?php if (!empty($registro["cor"])): ?>
                                    <small>Cor: <?= h($registro["cor"]) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($tempo !== ""): ?>
                            <div class="tempo">
                                ⏱ Tempo estacionado: <strong><?= h($tempo) ?></strong>
                            </div>
                        <?php endif; ?>
                    </article>

                <?php endforeach; ?>
            </section>

        <?php endif; ?>
    </div>
</main>

<footer>
    © <?= date("Y") ?> Park Point — Sistema de gerenciamento de estacionamento.
</footer>

</body>
</html>
```