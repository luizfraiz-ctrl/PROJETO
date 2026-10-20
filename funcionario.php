```php
<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

// Confere o perfil atual no banco para evitar usar um perfil antigo da sessão.
try {
    $stmt = $pdo->prepare("SELECT nome, perfil FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->execute([(int) $_SESSION["usuario_id"]]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        session_unset();
        session_destroy();
        header("Location: entrar.php");
        exit;
    }

    $perfil = (string) $usuario["perfil"];
    $_SESSION["perfil"] = $perfil;
    $_SESSION["usuario_nome"] = $usuario["nome"];

} catch (Throwable $e) {
    error_log("Erro ao validar usuário do painel: " . $e->getMessage());
    http_response_code(500);
    exit("Não foi possível validar seu acesso. Tente novamente.");
}

if (!in_array($perfil, ["funcionario", "admin"], true)) {
    header("Location: dashboard.php");
    exit;
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function h($valor): string
{
    return htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
}

$nome = $_SESSION["usuario_nome"] ?? "Funcionário";
$erro = "";
$totalVagas = 0;
$vagasLivres = 0;
$vagasOcupadas = 0;
$vagas = [];
$veiculosEstacionados = [];

try {
    $stmt = $pdo->query("
        SELECT
            COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN status = 'livre' THEN 1 ELSE 0 END), 0) AS livres,
            COALESCE(SUM(CASE WHEN status = 'ocupada' THEN 1 ELSE 0 END), 0) AS ocupadas
        FROM vagas
    ");

    $estatisticas = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalVagas = (int) ($estatisticas["total"] ?? 0);
    $vagasLivres = (int) ($estatisticas["livres"] ?? 0);
    $vagasOcupadas = (int) ($estatisticas["ocupadas"] ?? 0);

    $stmt = $pdo->query("
        SELECT
            e.id,
            e.entrada,
            v.numero AS vaga,
            ve.placa,
            ve.modelo,
            ve.cor
        FROM estacionamentos e
        INNER JOIN vagas v ON v.id = e.vaga_id
        INNER JOIN veiculos ve ON ve.id = e.veiculo_id
        WHERE e.saida IS NULL
        ORDER BY e.entrada DESC
    ");

    $veiculosEstacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("
        SELECT id, numero, status
        FROM vagas
        ORDER BY numero
    ");

    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    error_log("Erro no painel do funcionário: " . $e->getMessage());
    $erro = "Não foi possível carregar os dados. Confira a conexão e as tabelas do banco.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do funcionário | Park Point</title>

    <style>
        * { box-sizing: border-box; }

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

        .logo-p { color: #00aaff; }

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
        .menu a.ativo { color: #00aaff; }

        .menu a.sair { color: #ff8585; }
        .menu a.sair:hover { color: #ff3b3b; }

        .container {
            max-width: 1150px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .cabecalho { margin-bottom: 28px; }

        .cabecalho h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .cabecalho p {
            margin: 0;
            color: #667085;
            line-height: 1.6;
        }

        .identificacao {
            display: inline-block;
            margin-top: 14px;
            padding: 7px 11px;
            border-radius: 20px;
            background: #dff5ff;
            color: #0077a8;
            font-size: 12px;
            font-weight: bold;
        }

        .secao-titulo {
            margin: 30px 0 15px;
            font-size: 20px;
        }

        .paineis {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
            box-shadow: 0 4px 16px rgba(0,0,0,.04);
            transition: .2s;
        }

        .painel-link:hover {
            border-color: #00aaff;
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(0,170,255,.12);
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

        .estatisticas {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .cartao {
            padding: 23px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,.05);
        }

        .cartao p {
            color: #667085;
            font-size: 14px;
            margin: 0;
        }

        .cartao h2 {
            color: #009fe3;
            font-size: 31px;
            margin: 10px 0 0;
        }

        .cartao.livres h2 { color: #15803d; }
        .cartao.ocupadas h2 { color: #b91c1c; }

        .secao {
            padding: 25px;
            margin-bottom: 25px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,.05);
        }

        .secao h2 {
            margin-top: 0;
            font-size: 20px;
        }

        .grade-vagas {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
        }

        .vaga {
            text-align: center;
            padding: 16px 8px;
            border-radius: 10px;
            border: 1px solid;
        }

        .vaga strong {
            display: block;
            margin-bottom: 8px;
        }

        .vaga span {
            font-size: 12px;
            font-weight: bold;
        }

        .vaga.livre {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #15803d;
        }

        .vaga.ocupada {
            background: #fef2f2;
            border-color: #fecaca;
            color: #b91c1c;
        }

        .tabela-container { overflow-x: auto; }

        table {
            width: 100%;
            min-width: 700px;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 14px;
        }

        th {
            color: #667085;
            background: #f8fafc;
            font-size: 12px;
            text-transform: uppercase;
        }

        .btn-saida {
            padding: 11px 14px;
            border: none;
            border-radius: 9px;
            background: #00aaff;
            color: #fff;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            white-space: nowrap;
            transition: .2s;
        }

        .btn-saida:hover { background: #008dcc; }

        .vazio {
            padding: 22px 0;
            text-align: center;
            color: #667085;
            line-height: 1.6;
        }

        .alerta {
            padding: 14px;
            margin-bottom: 22px;
            border-radius: 10px;
            background: #fff7ed;
            color: #9a3412;
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

        footer {
            padding: 22px;
            text-align: center;
            color: #667085;
            font-size: 13px;
        }

        @media (max-width: 850px) {
            nav {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 650px) {
            .estatisticas { grid-template-columns: 1fr; }
            .container { margin-top: 25px; }
            .secao { padding: 19px; }
            .cabecalho h1 { font-size: 25px; }
        }
    </style>
</head>
<body>

<nav>
    <a href="funcionario.php" class="logo">
        <span class="logo-p">P</span>ARK POINT
    </a>

    <div class="menu">
        <a href="funcionario.php" class="ativo">Painel do funcionário</a>
        <a href="vagas.php">Vagas</a>

        <?php if ($perfil === "admin"): ?>
            <a href="admin.php">Administração</a>
        <?php endif; ?>

        <a href="logout.php" class="sair">Sair</a>
    </div>
</nav>

<main class="container">
    <header class="cabecalho">
        <h1>Painel do funcionário</h1>
        <p>Olá, <?= h($nome) ?>! Gerencie as vagas e acompanhe entradas e saídas dos veículos.</p>
        <span class="identificacao">
            Acesso: <?= $perfil === "admin" ? "Administrador" : "Funcionário" ?>
        </span>
    </header>

    <h2 class="secao-titulo">Acesso rápido</h2>

    <div class="paineis">
        <a href="funcionario.php" class="painel-link atual">
            <span class="painel-icone">🅿️</span>
            <strong>Painel do funcionário</strong>
            <p>Consulte as vagas e registre a saída dos veículos.</p>
            <span class="marcador">Você está aqui</span>
        </a>

        <a href="vagas.php" class="painel-link">
            <span class="painel-icone">🚗</span>
            <strong>Consultar vagas</strong>
            <p>Veja a situação atual das vagas do estacionamento.</p>
        </a>

        <?php if ($perfil === "admin"): ?>
            <a href="admin.php" class="painel-link">
                <span class="painel-icone">⚙️</span>
                <strong>Painel administrativo</strong>
                <p>Acesse as ferramentas de administração.</p>
            </a>
        <?php endif; ?>
    </div>

    <?php if ($erro !== ""): ?>
        <div class="alerta"><?= h($erro) ?></div>
    <?php endif; ?>

    <section class="estatisticas">
        <div class="cartao">
            <p>Total de vagas</p>
            <h2><?= $totalVagas ?></h2>
        </div>

        <div class="cartao livres">
            <p>Vagas livres</p>
            <h2><?= $vagasLivres ?></h2>
        </div>

        <div class="cartao ocupadas">
            <p>Vagas ocupadas</p>
            <h2><?= $vagasOcupadas ?></h2>
        </div>
    </section>

    <section class="secao">
        <h2>Mapa de vagas</h2>

        <?php if (empty($vagas)): ?>
            <p class="vazio">Nenhuma vaga cadastrada.</p>
        <?php else: ?>
            <div class="grade-vagas">
                <?php foreach ($vagas as $vaga): ?>
                    <?php $ocupada = $vaga["status"] === "ocupada"; ?>
                    <div class="vaga <?= $ocupada ? "ocupada" : "livre" ?>">
                        <strong>Vaga <?= h($vaga["numero"]) ?></strong>
                        <span><?= $ocupada ? "OCUPADA" : "LIVRE" ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="secao">
        <h2>Veículos estacionados</h2>

        <?php if (empty($veiculosEstacionados)): ?>
            <p class="vazio">Não há veículos estacionados no momento.</p>
        <?php else: ?>
            <div class="tabela-container">
                <table>
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th>Modelo</th>
                            <th>Cor</th>
                            <th>Vaga</th>
                            <th>Entrada</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($veiculosEstacionados as $veiculo): ?>
                            <tr>
                                <td><?= h($veiculo["placa"]) ?></td>
                                <td><?= h($veiculo["modelo"]) ?></td>
                                <td><?= h($veiculo["cor"]) ?></td>
                                <td><?= h($veiculo["vaga"]) ?></td>
                                <td>
                                    <?php
                                    $timestamp = strtotime($veiculo["entrada"] ?? "");
                                    echo $timestamp
                                        ? date("d/m/Y H:i", $timestamp)
                                        : h($veiculo["entrada"]);
                                    ?>
                                </td>
                                <td>
                                    <form method="POST"
                                          action="registrar-saida.php"
                                          onsubmit="return confirm('Deseja registrar a saída deste veículo?');">
                                        <input type="hidden" name="id" value="<?= (int) $veiculo["id"] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION["csrf_token"]) ?>">
                                        <button class="btn-saida" type="submit">Registrar saída</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<footer>
    Park Point &copy; <?= date("Y") ?> — Sistema de gerenciamento de estacionamento
</footer>

</body>
</html>
```