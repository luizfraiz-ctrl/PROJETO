
<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: vagas.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];
$perfil = $_SESSION["perfil"] ?? "cliente";
$nome = $_SESSION["usuario_nome"] ?? "Usuário";

function h($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}

function paginaMensagem($titulo, $mensagem, $tipo = "erro") {
    global $perfil, $nome;

    $sucesso = ($tipo === "sucesso");
    $corPrincipal = $sucesso ? "#16a34a" : "#dc2626";
    $corFundo = $sucesso ? "#f0fdf4" : "#fff1f2";
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= h($titulo) ?> | Park Point</title>

        <style>
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
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
                padding: 0 30px;
            }

            .navbar-content {
                max-width: 1400px;
                min-height: 72px;
                margin: auto;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
            }

            .logo {
                color: white;
                font-size: 23px;
                font-weight: 800;
            }

            .logo span {
                color: #00aaff;
            }

            .nav {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
            }

            .nav a {
                color: #dbe4ef;
                padding: 10px 12px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: bold;
                transition: .2s;
            }

            .nav a:hover {
                color: #00aaff;
                background: #263449;
            }

            .nav .sair {
                background: #dc2626;
                color: white;
            }

            .nav .sair:hover {
                background: #b91c1c;
            }

            .container {
                width: calc(100% - 30px);
                max-width: 650px;
                margin: 55px auto;
            }

            .voltar {
                display: inline-block;
                color: #475569;
                font-weight: bold;
                font-size: 14px;
                margin-bottom: 18px;
            }

            .voltar:hover {
                color: #0284c7;
            }

            .card {
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 18px;
                padding: 38px;
                text-align: center;
                box-shadow: 0 8px 25px rgba(15, 23, 42, .06);
            }

            .icone {
                width: 68px;
                height: 68px;
                margin: 0 auto 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: <?= $corFundo ?>;
                color: <?= $corPrincipal ?>;
                font-size: 32px;
                font-weight: bold;
            }

            h1 {
                font-size: 26px;
                margin-bottom: 12px;
            }

            .mensagem {
                color: #64748b;
                line-height: 1.7;
                font-size: 15px;
                overflow-wrap: anywhere;
            }

            .botoes {
                display: flex;
                gap: 12px;
                margin-top: 27px;
            }

            .botao {
                flex: 1;
                display: inline-block;
                padding: 13px 16px;
                border-radius: 9px;
                background: #0284c7;
                color: white;
                font-size: 14px;
                font-weight: bold;
                transition: .2s;
            }

            .botao:hover {
                background: #0369a1;
                transform: translateY(-2px);
            }

            .botao.secundario {
                background: #e2e8f0;
                color: #172033;
            }

            .botao.secundario:hover {
                background: #cbd5e1;
            }

            footer {
                text-align: center;
                color: #94a3b8;
                font-size: 12px;
                padding: 20px;
            }

            @media (max-width: 850px) {
                .navbar-content {
                    padding: 15px 0;
                    flex-direction: column;
                }

                .nav {
                    justify-content: center;
                }
            }

            @media (max-width: 500px) {
                .navbar {
                    padding: 0 14px;
                }

                .nav a {
                    font-size: 12px;
                    padding: 9px;
                }

                .card {
                    padding: 28px 20px;
                }

                h1 {
                    font-size: 23px;
                }

                .botoes {
                    flex-direction: column;
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
                    <a href="minhas-reservas.php">Minhas reservas</a>
                    <a href="historico.php">Histórico</a>
                    <a href="logout.php" class="sair">Sair</a>
                </nav>
            </div>
        </header>

        <main class="container">
            <a href="vagas.php" class="voltar">← Voltar para as vagas</a>

            <section class="card">
                <div class="icone"><?= $sucesso ? "✓" : "!" ?></div>

                <h1><?= h($titulo) ?></h1>

                <p class="mensagem">
                    Olá, <?= h($nome) ?>!<br>
                    <?= h($mensagem) ?>
                </p>

                <div class="botoes">
                    <a href="minhas-reservas.php" class="botao">
                        Meus estacionamentos
                    </a>

                    <a href="vagas.php" class="botao secundario">
                        Ver vagas
                    </a>
                </div>
            </section>
        </main>

        <footer>
            © <?= date("Y") ?> Park Point — Sistema de gerenciamento de estacionamento.
        </footer>
    </body>
    </html>
    <?php
    exit;
}

// Valida o token de segurança enviado pelo formulário.
$tokenSessao = $_SESSION["csrf_token"] ?? "";
$tokenFormulario = $_POST["csrf_token"] ?? "";

if (
    !is_string($tokenSessao) ||
    !is_string($tokenFormulario) ||
    $tokenSessao === "" ||
    !hash_equals($tokenSessao, $tokenFormulario)
) {
    paginaMensagem(
        "Sessão de segurança expirada",
        "Volte à lista de vagas e tente realizar a reserva novamente."
    );
}

$vaga_id = filter_var(
    $_POST["vaga_id"] ?? null,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

$veiculo_id = filter_var(
    $_POST["veiculo_id"] ?? null,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if ($vaga_id === false || $vaga_id === null ||
    $veiculo_id === false || $veiculo_id === null) {
    paginaMensagem(
        "Dados inválidos",
        "Não foi possível identificar a vaga ou o veículo selecionado."
    );
}

try {
    $pdo->beginTransaction();

    /*
     * 1. Bloqueia a linha do usuário.
     * Isso ajuda a impedir que duas solicitações simultâneas
     * iniciem estacionamentos para o mesmo usuário.
     */
    $stmt = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE id = ?
        FOR UPDATE
    ");
    $stmt->execute([$usuario_id]);

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->rollBack();

        paginaMensagem(
            "Usuário não encontrado",
            "Entre novamente na sua conta e tente outra vez."
        );
    }

    /*
     * 2. Confirma que o veículo pertence ao usuário conectado.
     */
    $stmt = $pdo->prepare("
        SELECT id, placa, modelo, cor
        FROM veiculos
        WHERE id = ?
          AND usuario_id = ?
        LIMIT 1
    ");
    $stmt->execute([$veiculo_id, $usuario_id]);
    $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$veiculo) {
        $pdo->rollBack();

        paginaMensagem(
            "Veículo não encontrado",
            "O veículo selecionado não pertence à sua conta."
        );
    }

    /*
     * 3. Verifica se algum veículo do usuário já está estacionado.
     */
    $stmt = $pdo->prepare("
        SELECT
            est.id,
            v.numero AS vaga_numero,
            est.veiculo_id,
            ve.placa
        FROM estacionamentos est
        INNER JOIN veiculos ve
            ON ve.id = est.veiculo_id
        INNER JOIN vagas v
            ON v.id = est.vaga_id
        WHERE ve.usuario_id = ?
          AND est.saida IS NULL
        LIMIT 1
    ");
    $stmt->execute([$usuario_id]);
    $ativo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ativo) {
        $pdo->rollBack();

        paginaMensagem(
            "Já existe um estacionamento ativo",
            "O veículo " . $ativo["placa"] .
            " já está estacionado na vaga " . $ativo["vaga_numero"] .
            ". Finalize esse estacionamento antes de iniciar outro."
        );
    }

    /*
     * 4. Bloqueia a vaga e confirma sua disponibilidade.
     */
    $stmt = $pdo->prepare("
        SELECT
            v.id,
            v.numero,
            v.status,
            v.estabelecimento_id,
            est.nome AS estabelecimento_nome
        FROM vagas v
        INNER JOIN estabelecimentos est
            ON est.id = v.estabelecimento_id
        WHERE v.id = ?
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute([$vaga_id]);
    $vaga = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vaga) {
        $pdo->rollBack();

        paginaMensagem(
            "Vaga não encontrada",
            "A vaga selecionada não existe mais."
        );
    }

    if ($vaga["status"] !== "livre") {
        $pdo->rollBack();

        paginaMensagem(
            "Vaga indisponível",
            "Esta vaga já está ocupada. Escolha outra vaga livre."
        );
    }

    if (empty($vaga["estabelecimento_id"])) {
        $pdo->rollBack();

        paginaMensagem(
            "Estacionamento não identificado",
            "Esta vaga não está vinculada a um estabelecimento válido."
        );
    }

    /*
     * 5. Registra o estacionamento.
     */
    $stmt = $pdo->prepare("
        INSERT INTO estacionamentos
            (veiculo_id, vaga_id, entrada, saida)
        VALUES
            (?, ?, NOW(), NULL)
    ");
    $stmt->execute([$veiculo_id, $vaga_id]);

    /*
     * 6. Marca a vaga como ocupada.
     */
    $stmt = $pdo->prepare("
        UPDATE vagas
        SET status = 'ocupada'
        WHERE id = ?
          AND status = 'livre'
    ");
    $stmt->execute([$vaga_id]);

    if ($stmt->rowCount() !== 1) {
        $pdo->rollBack();

        paginaMensagem(
            "Vaga indisponível",
            "Não foi possível confirmar a disponibilidade. Escolha outra vaga."
        );
    }

    /*
     * 7. Confirma todas as alterações.
     */
    $pdo->commit();

    paginaMensagem(
        "Estacionamento iniciado!",
        "O veículo " . $veiculo["placa"] .
        " foi registrado na vaga " . $vaga["numero"] .
        " do estacionamento " . $vaga["estabelecimento_nome"] . ".",
        "sucesso"
    );

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Erro ao confirmar estacionamento: " . $e->getMessage());

    paginaMensagem(
        "Não foi possível confirmar",
        "Ocorreu um erro ao registrar o estacionamento. Tente novamente."
    );
}
?>
