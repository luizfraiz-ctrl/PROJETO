```php
<?php
session_start();
require_once "conexao.php";

// Verifica o login
if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];
$perfil = $_SESSION["perfil"] ?? "cliente";
$nome = $_SESSION["usuario_nome"] ?? "Usuário";

// Token de segurança para cancelamento
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$reservas = [];
$erro = "";

function h($valor): string
{
    return htmlspecialchars(
        (string) ($valor ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
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
        ORDER BY
            CASE WHEN e.saida IS NULL THEN 0 ELSE 1 END,
            e.id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    error_log("Erro ao carregar reservas: " . $e->getMessage());
    $erro = "Não foi possível carregar suas movimentações. Tente novamente.";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas reservas | Park Point</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            color: #172033;
        }

        /* MENU */

        header {
            min-height: 76px;
            padding: 15px 5%;
            background: #ffffff;
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

        .logo-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #2563eb;
            color: #ffffff;
            font-size: 25px;
        }

        .logo-point {
            color: #2563eb;
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 6px;
        }

        nav a {
            padding: 10px 11px;
            border-radius: 8px;
            color: #475569;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: background .2s, color .2s;
        }

        nav a:hover,
        nav a.ativo {
            background: #eff6ff;
            color: #2563eb;
        }

        nav a.sair {
            background: #fff1f2;
            color: #be123c;
        }

        nav a.sair:hover {
            background: #ffe4e6;
        }

        /* CONTEÚDO */

        main {
            width: 90%;
            max-width: 1100px;
            margin: 32px auto 45px;
        }

        .voltar-area {
            margin-bottom: 24px;
        }

        .voltar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 16px;
            border: 1px solid #dbeafe;
            border-radius: 9px;
            background: #ffffff;
            color: #1d4ed8;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: background .2s, transform .2s;
        }

        .voltar:hover {
            background: #eff6ff;
            transform: translateY(-1px);
        }

        .cabecalho {
            margin-bottom: 26px;
        }

        .cabecalho h1 {
            margin-bottom: 9px;
            color: #172554;
            font-size: 30px;
        }

        .cabecalho p {
            color: #64748b;
            line-height: 1.6;
        }

        .resumo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 17px 20px;
            margin-bottom: 22px;
            border: 1px solid #e5eaf2;
            border-radius: 12px;
            background: #ffffff;
        }

        .resumo strong {
            color: #172554;
        }

        .resumo span {
            color: #64748b;
            font-size: 14px;
        }

        .mensagem {
            padding: 18px;
            margin-bottom: 20px;
            border-radius: 12px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            line-height: 1.6;
        }

        /* CARTÕES */

        .lista {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .reserva {
            min-width: 0;
            padding: 24px;
            border: 1px solid #e5eaf2;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
        }

        .reserva-topo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding-bottom: 17px;
            margin-bottom: 18px;
            border-bottom: 1px solid #edf0f5;
        }

        .reserva h2 {
            color: #172554;
            font-size: 19px;
        }

        .status {
            display: inline-block;
            padding: 7px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .status.ativa {
            background: #dcfce7;
            color: #166534;
        }

        .status.finalizada {
            background: #e5e7eb;
            color: #4b5563;
        }

        .detalhe {
            margin-bottom: 13px;
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .detalhe strong {
            color: #172033;
        }

        .cancelar {
            width: 100%;
            padding: 12px 15px;
            margin-top: 12px;
            border: 1px solid #fecaca;
            border-radius: 9px;
            background: #fff1f2;
            color: #be123c;
            font-family: inherit;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background .2s, color .2s;
        }

        .cancelar:hover {
            background: #dc2626;
            color: #ffffff;
        }

        .vazio {
            padding: 35px 24px;
            border: 1px solid #e5eaf2;
            border-radius: 14px;
            background: #ffffff;
            text-align: center;
        }

        .vazio h2 {
            margin-bottom: 10px;
            color: #172554;
        }

        .vazio p {
            max-width: 500px;
            margin: 0 auto 22px;
            color: #64748b;
            line-height: 1.6;
        }

        .botao {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border: none;
            border-radius: 9px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            font-family: inherit;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background .2s, transform .2s;
        }

        .botao:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        footer {
            padding: 25px 15px;
            color: #94a3b8;
            text-align: center;
            font-size: 13px;
        }

        @media (max-width: 850px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }

            nav {
                justify-content: flex-start;
                width: 100%;
            }

            .lista {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            header {
                padding: 16px 5%;
            }

            main {
                width: 92%;
                margin-top: 25px;
            }

            .cabecalho h1 {
                font-size: 25px;
            }

            .reserva {
                padding: 19px;
            }

            nav a {
                padding: 9px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

<header>
    <a href="dashboard.php" class="logo">
        <span class="logo-icon">P</span>
        <span>Park <span class="logo-point">Point</span></span>
    </a>

    <nav aria-label="Menu principal">
        <a href="dashboard.php">Painel do cliente</a>
        <a href="vagas.php">Vagas</a>
        <a href="meus-veiculos.php">Meus veículos</a>
        <a href="minhas-reservas.php" class="ativo">Minhas reservas</a>
        <a href="historico.php">Histórico</a>

        <?php if ($perfil === "admin"): ?>
            <a href="admin.php">Administração</a>
            <a href="funcionario.php">Painel do funcionário</a>
        <?php elseif ($perfil === "funcionario"): ?>
            <a href="funcionario.php">Painel do funcionário</a>
        <?php endif; ?>

        <a href="logout.php" class="sair">Sair</a>
    </nav>
</header>

<main>
    <div class="voltar-area">
        <?php if ($perfil === "admin"): ?>
            <a href="admin.php" class="voltar">← Voltar ao painel administrativo</a>
        <?php elseif ($perfil === "funcionario"): ?>
            <a href="funcionario.php" class="voltar">← Voltar ao painel do funcionário</a>
        <?php else: ?>
            <a href="dashboard.php" class="voltar">← Voltar ao meu painel</a>
        <?php endif; ?>
    </div>

    <section class="cabecalho">
        <h1>Minhas reservas</h1>
        <p>Olá, <?= h($nome) ?>! Acompanhe suas entradas, saídas e movimentações no estacionamento.</p>
    </section>

    <?php if ($erro !== ""): ?>
        <div class="mensagem">
            <?= h($erro) ?>
        </div>

    <?php elseif (empty($reservas)): ?>
        <div class="vazio">
            <h2>Nenhuma movimentação encontrada</h2>
            <p>Você ainda não possui registros de estacionamento vinculados aos seus veículos.</p>
            <a href="vagas.php" class="botao">Encontrar uma vaga</a>
        </div>

    <?php else: ?>
        <div class="resumo">
            <strong>Suas movimentações</strong>
            <span>
                <?= count($reservas) ?>
                <?= count($reservas) === 1 ? "registro encontrado" : "registros encontrados" ?>
            </span>
        </div>

        <div class="lista">
            <?php foreach ($reservas as $reserva): ?>
                <?php $ativa = empty($reserva["saida"]); ?>

                <article class="reserva">
                    <div class="reserva-topo">
                        <h2>Vaga <?= h($reserva["vaga"]) ?></h2>

                        <span class="status <?= $ativa ? "ativa" : "finalizada" ?>">
                            <?= $ativa ? "Ativa" : "Finalizada" ?>
                        </span>
                    </div>

                    <p class="detalhe">
                        <strong>Veículo:</strong>
                        <?= h($reserva["modelo"] ?: "Modelo não informado") ?>
                    </p>

                    <p class="detalhe">
                        <strong>Placa:</strong>
                        <?= h($reserva["placa"]) ?>
                    </p>

                    <p class="detalhe">
                        <strong>Cor:</strong>
                        <?= h($reserva["cor"] ?: "Não informada") ?>
                    </p>

                    <p class="detalhe">
                        <strong>Entrada:</strong>
                        <?php
                        $entradaTimestamp = strtotime($reserva["entrada"]);
                        echo $entradaTimestamp
                            ? h(date("d/m/Y H:i", $entradaTimestamp))
                            : h($reserva["entrada"]);
                        ?>
                    </p>

                    <p class="detalhe">
                        <strong>Saída:</strong>
                        <?php if ($ativa): ?>
                            Ainda não finalizada
                        <?php else: ?>
                            <?php
                            $saidaTimestamp = strtotime($reserva["saida"]);
                            echo $saidaTimestamp
                                ? h(date("d/m/Y H:i", $saidaTimestamp))
                                : h($reserva["saida"]);
                            ?>
                        <?php endif; ?>
                    </p>

                    <?php if ($ativa): ?>
                        <form
                            method="POST"
                            action="cancelar-reserva.php"
                            onsubmit="return confirm('Deseja realmente cancelar esta reserva?');"
                        >
                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $reserva["id"] ?>"
                            >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= h($_SESSION["csrf_token"]) ?>"
                            >

                            <button type="submit" class="cancelar">
                                Cancelar reserva
                            </button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<footer>
    Park Point &copy; <?= date("Y") ?> — Sistema de gerenciamento de estacionamento
</footer>

</body>
</html>
```