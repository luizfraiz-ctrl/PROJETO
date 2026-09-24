<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: vagas.php");
    exit;
}

$veiculo_id = isset($_POST["veiculo_id"]) ? (int) $_POST["veiculo_id"] : 0;
$vaga_id = isset($_POST["vaga_id"]) ? (int) $_POST["vaga_id"] : 0;

function telaMensagem($tipo, $titulo, $mensagem, $botao1, $link1, $botao2 = null, $link2 = null)
{
    $classe = $tipo === "sucesso" ? "sucesso" : "erro";
    $icone = $tipo === "sucesso" ? "✓" : "!";

    ?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Park Point</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fa;
            color: #172033;
        }

        .pp-menu {
            width: 100%;
            min-height: 68px;
            background: #111827;
            display: flex;
            align-items: center;
        }

        .pp-menu-content {
            width: 100%;
            padding: 0 35px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .pp-logo {
            color: white;
            text-decoration: none;
            font-size: 24px;
            font-weight: 800;
        }

        .pp-logo span {
            color: #60a5fa;
        }

        .pp-nav {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .pp-nav a {
            color: #dbe4ef;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 13px;
            border-radius: 8px;
            transition: 0.2s;
        }

        .pp-nav a:hover {
            background: #273449;
            color: white;
        }

        .pp-nav .sair {
            background: #dc2626;
            color: white;
        }

        .pp-page {
            min-height: calc(100vh - 68px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .pp-box {
            width: 100%;
            max-width: 620px;
            background: white;
            border-radius: 24px;
            padding: 45px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.10);
            border: 1px solid #e2e8f0;
        }

        .pp-icon {
            width: 76px;
            height: 76px;
            margin: 0 auto 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 800;
        }

        .pp-icon.erro {
            background: #fee2e2;
            color: #dc2626;
        }

        .pp-icon.sucesso {
            background: #dcfce7;
            color: #16a34a;
        }

        .pp-box h1 {
            font-size: 27px;
            color: #111827;
            margin-bottom: 13px;
        }

        .pp-box p {
            color: #64748b;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .pp-buttons {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .pp-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 190px;
            padding: 13px 20px;
            border-radius: 11px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: 0.2s;
        }

        .pp-primary {
            background: #2563eb;
            color: white;
        }

        .pp-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .pp-secondary {
            background: #eef2f7;
            color: #334155;
        }

        .pp-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .pp-back {
            display: inline-block;
            margin-top: 25px;
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .pp-back:hover {
            color: #2563eb;
        }

        @media (max-width: 650px) {

            .pp-menu-content {
                padding: 14px 18px;
                flex-direction: column;
                gap: 12px;
            }

            .pp-nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .pp-page {
                padding: 25px 15px;
            }

            .pp-box {
                padding: 35px 22px;
            }

            .pp-box h1 {
                font-size: 23px;
            }

            .pp-button {
                width: 100%;
            }

        }

    </style>

</head>

<body>

    <header class="pp-menu">

        <div class="pp-menu-content">

            <a href="dashboard.php" class="pp-logo">
                Park <span>Point</span>
            </a>

            <nav class="pp-nav">

                <a href="dashboard.php">
                    Início
                </a>

                <a href="vagas.php">
                    Vagas
                </a>

                <a href="minhas-reservas.php">
                    Minhas Reservas
                </a>

                <a href="historico.php">
                    Histórico
                </a>

                <a href="logout.php" class="sair">
                    Sair
                </a>

            </nav>

        </div>

    </header>


    <main class="pp-page">

        <div class="pp-box">

            <div class="pp-icon <?php echo $classe; ?>">
                <?php echo $icone; ?>
            </div>

            <h1>
                <?php echo htmlspecialchars($titulo); ?>
            </h1>

            <p>
                <?php echo htmlspecialchars($mensagem); ?>
            </p>

            <div class="pp-buttons">

                <a href="<?php echo htmlspecialchars($link1); ?>" class="pp-button pp-primary">
                    <?php echo htmlspecialchars($botao1); ?>
                </a>

                <?php if ($botao2 !== null): ?>

                    <a href="<?php echo htmlspecialchars($link2); ?>" class="pp-button pp-secondary">
                        <?php echo htmlspecialchars($botao2); ?>
                    </a>

                <?php endif; ?>

            </div>

            <a href="dashboard.php" class="pp-back">
                ← Voltar para o início
            </a>

        </div>

    </main>

</body>

</html>
<?php

exit;


}

if ($veiculo_id <= 0 || $vaga_id <= 0) {


telaMensagem(
    "erro",
    "Dados inválidos",
    "Não foi possível identificar o veículo ou a vaga selecionada.",
    "Ver vagas",
    "vagas.php",
    "Voltar ao início",
    "dashboard.php"
);


}

/* Verifica se o veículo pertence ao usuário */

$stmt = $pdo->prepare("
SELECT id, placa, modelo
FROM veiculos
WHERE id = ?
AND usuario_id = ?
");

$stmt->execute([
$veiculo_id,
$usuario_id
]);

$veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$veiculo) {


telaMensagem(
    "erro",
    "Veículo não encontrado",
    "Esse veículo não pertence à sua conta ou não está mais cadastrado.",
    "Meus veículos",
    "meus-veiculos.php",
    "Voltar ao início",
    "dashboard.php"
);


}

/* Verifica se o veículo já possui reserva */

$stmt = $pdo->prepare("
SELECT e.id, v.numero
FROM estacionamentos e
INNER JOIN vagas v ON e.vaga_id = v.id
WHERE e.veiculo_id = ?
AND e.saida IS NULL
LIMIT 1
");

$stmt->execute([
$veiculo_id
]);

$reserva_existente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($reserva_existente) {


telaMensagem(
    "erro",
    "Veículo já estacionado",
    "O veículo " . $veiculo["placa"] . " já possui uma reserva ativa na vaga " . $reserva_existente["numero"] . ". Finalize essa reserva antes de fazer outra.",
    "Ver minhas reservas",
    "minhas-reservas.php",
    "Escolher outra vaga",
    "vagas.php"
);


}

/* Inicia a reserva */

try {


$pdo->beginTransaction();


/* Bloqueia a vaga */

$stmt = $pdo->prepare("
    SELECT id, numero, status
    FROM vagas
    WHERE id = ?
    FOR UPDATE
");

$stmt->execute([
    $vaga_id
]);

$vaga = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$vaga) {

    $pdo->rollBack();

    telaMensagem(
        "erro",
        "Vaga não encontrada",
        "A vaga selecionada não existe mais.",
        "Ver vagas",
        "vagas.php",
        "Voltar ao início",
        "dashboard.php"
    );
}


if ($vaga["status"] !== "livre") {

    $pdo->rollBack();

    telaMensagem(
        "erro",
        "Vaga ocupada",
        "Essa vaga acabou de ser ocupada. Escolha outra vaga disponível.",
        "Escolher outra vaga",
        "vagas.php",
        "Voltar ao início",
        "dashboard.php"
    );
}


/* Cria a reserva */

$stmt = $pdo->prepare("
    INSERT INTO estacionamentos
    (veiculo_id, vaga_id, entrada)
    VALUES (?, ?, NOW())
");

$stmt->execute([
    $veiculo_id,
    $vaga_id
]);


/* Marca a vaga como ocupada */

$stmt = $pdo->prepare("
    UPDATE vagas
    SET status = 'ocupada'
    WHERE id = ?
");

$stmt->execute([
    $vaga_id
]);


$pdo->commit();


telaMensagem(
    "sucesso",
    "Reserva realizada!",
    "A vaga " . $vaga["numero"] . " foi reservada com sucesso para o veículo " . $veiculo["placa"] . ".",
    "Ver minha reserva",
    "minhas-reservas.php",
    "Ver outras vagas",
    "vagas.php"
);


} catch (PDOException $e) {

if ($pdo->inTransaction()) {
    $pdo->rollBack();
}

telaMensagem(
    "erro",
    "Não foi possível realizar a reserva",
    "Ocorreu um problema ao processar sua reserva. Tente novamente.",
    "Tentar novamente",
    "vagas.php",
    "Voltar ao início",
    "dashboard.php"
);


}

?>
