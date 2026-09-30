<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$perfil = $_SESSION["perfil"] ?? "cliente";

if ($perfil !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$mensagem = "";
$tipoMensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $cnpj = trim($_POST["cnpj"] ?? "");
    $endereco = trim($_POST["endereco"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $horario_abertura = $_POST["horario_abertura"] ?? "";
    $horario_fechamento = $_POST["horario_fechamento"] ?? "";
    $valor_hora = str_replace(",", ".", trim($_POST["valor_hora"] ?? ""));
    $total_vagas = (int)($_POST["total_vagas"] ?? 0);

    $cnpjNumeros = preg_replace('/\D/', '', $cnpj);

    if (
        $nome === "" ||
        $cnpj === "" ||
        $endereco === "" ||
        $telefone === "" ||
        $horario_abertura === "" ||
        $horario_fechamento === "" ||
        $valor_hora === "" ||
        $total_vagas <= 0
    ) {
        $mensagem = "Preencha todos os campos.";
        $tipoMensagem = "erro";

    } elseif (strlen($cnpjNumeros) !== 14) {
        $mensagem = "Informe um CNPJ válido com 14 números.";
        $tipoMensagem = "erro";

    } elseif (!is_numeric($valor_hora) || $valor_hora < 0) {
        $mensagem = "Informe um valor por hora válido.";
        $tipoMensagem = "erro";

    } elseif ($total_vagas > 500) {
        $mensagem = "O limite é de 500 vagas por estacionamento.";
        $tipoMensagem = "erro";

    } else {

        try {

            // Verifica CNPJ duplicado
            $stmt = $pdo->prepare("
                SELECT id
                FROM estabelecimentos
                WHERE cnpj = ?
                LIMIT 1
            ");

            $stmt->execute([$cnpj]);
            $existente = $stmt->fetch();

            if ($existente) {

                $mensagem = "Este CNPJ já está cadastrado.";
                $tipoMensagem = "erro";

            } else {

                $pdo->beginTransaction();

                // Cadastra o estabelecimento
                $stmt = $pdo->prepare("
                    INSERT INTO estabelecimentos
                    (
                        usuario_id,
                        nome,
                        cnpj,
                        endereco,
                        telefone,
                        horario_abertura,
                        horario_fechamento,
                        valor_hora,
                        total_vagas
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $usuario_id,
                    $nome,
                    $cnpj,
                    $endereco,
                    $telefone,
                    $horario_abertura,
                    $horario_fechamento,
                    $valor_hora,
                    $total_vagas
                ]);

                $estabelecimento_id = $pdo->lastInsertId();

                // Cria automaticamente as vagas
                $stmtVaga = $pdo->prepare("
                    INSERT INTO vagas
                    (numero, status, estabelecimento_id)
                    VALUES (?, 'livre', ?)
                ");

                for ($i = 1; $i <= $total_vagas; $i++) {
                    $stmtVaga->execute([
                        $i,
                        $estabelecimento_id
                    ]);
                }

                $pdo->commit();

                $mensagem = "Estacionamento cadastrado com sucesso! As $total_vagas vagas foram criadas automaticamente.";
                $tipoMensagem = "sucesso";
            }

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $mensagem = "Não foi possível cadastrar o estacionamento.";
            $tipoMensagem = "erro";
        }
    }
}
?>

<!DOCTYPE html>

<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

```
<title>Cadastrar estacionamento - Park Point</title>

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
    }

    nav {
        height: 72px;
        background: #101827;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 6%;
    }

    .logo {
        text-decoration: none;
        color: white;
        font-size: 21px;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .logo-p {
        color: #00aaff;
    }

    .nav-links {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .nav-links a {
        color: #dbe5f1;
        text-decoration: none;
        font-size: 14px;
    }

    .nav-links a:hover {
        color: #00aaff;
    }

    .logout {
        background: #00aaff;
        color: white !important;
        padding: 10px 16px;
        border-radius: 8px;
    }

    .container {
        width: 90%;
        max-width: 850px;
        margin: 45px auto;
    }

    .top {
        margin-bottom: 25px;
    }

    .top h1 {
        font-size: 30px;
        margin-bottom: 8px;
    }

    .top p {
        color: #68758a;
    }

    .voltar {
        display: inline-block;
        margin-top: 15px;
        color: #00aaff;
        text-decoration: none;
        font-weight: bold;
    }

    .form-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(20,35,55,.07);
        border: 1px solid #e9eef5;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .campo {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .campo.full {
        grid-column: 1 / -1;
    }

    label {
        font-size: 14px;
        font-weight: bold;
    }

    input {
        width: 100%;
        padding: 13px;
        border: 1px solid #d9e1eb;
        border-radius: 9px;
        font-size: 14px;
        outline: none;
    }

    input:focus {
        border-color: #00aaff;
    }

    .info {
        margin: 20px 0;
        padding: 15px;
        background: #eef9ff;
        border: 1px solid #ccefff;
        border-radius: 10px;
        color: #31566d;
        font-size: 14px;
    }

    button {
        width: 100%;
        border: none;
        background: #00aaff;
        color: white;
        padding: 15px;
        border-radius: 10px;
        font-weight: bold;
        font-size: 15px;
        cursor: pointer;
        margin-top: 10px;
    }

    button:hover {
        background: #008dcc;
    }

    .mensagem {
        padding: 14px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .erro {
        background: #fff0f0;
        color: #c62828;
        border: 1px solid #ffcaca;
    }

    .sucesso {
        background: #ecfff5;
        color: #16784a;
        border: 1px solid #b9efd3;
    }

    @media (max-width: 700px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .campo.full {
            grid-column: auto;
        }

        .nav-links a:not(.logout) {
            display: none;
        }
    }
</style>
```

</head>

<body>

<nav>
    <a href="admin.php" class="logo">
        <span class="logo-p">P</span>ARK POINT
    </a>

```
<div class="nav-links">
    <a href="admin.php">Painel</a>
    <a href="funcionario.php">Funcionário</a>
    <a href="logout.php" class="logout">Sair</a>
</div>
```

</nav>

<main class="container">

```
<div class="top">
    <h1>Cadastrar estacionamento</h1>
    <p>Cadastre seu estacionamento e crie suas vagas automaticamente.</p>

    <a href="admin.php" class="voltar">
        ← Voltar para o painel
    </a>
</div>

<?php if ($mensagem !== ""): ?>
    <div class="mensagem <?php echo $tipoMensagem; ?>">
        <?php echo htmlspecialchars($mensagem); ?>
    </div>
<?php endif; ?>

<div class="form-card">

    <form method="POST">

        <div class="form-grid">

            <div class="campo full">
                <label>Nome do estacionamento</label>
                <input
                    type="text"
                    name="nome"
                    maxlength="150"
                    required
                    value="<?php echo htmlspecialchars($_POST["nome"] ?? ""); ?>"
                    placeholder="Ex.: Park Point Centro"
                >
            </div>

            <div class="campo">
                <label>CNPJ</label>
                <input
                    type="text"
                    name="cnpj"
                    maxlength="18"
                    required
                    value="<?php echo htmlspecialchars($_POST["cnpj"] ?? ""); ?>"
                    placeholder="00.000.000/0000-00"
                >
            </div>

            <div class="campo">
                <label>Telefone</label>
                <input
                    type="text"
                    name="telefone"
                    maxlength="20"
                    required
                    value="<?php echo htmlspecialchars($_POST["telefone"] ?? ""); ?>"
                    placeholder="(41) 99999-9999"
                >
            </div>

            <div class="campo full">
                <label>Endereço</label>
                <input
                    type="text"
                    name="endereco"
                    maxlength="255"
                    required
                    value="<?php echo htmlspecialchars($_POST["endereco"] ?? ""); ?>"
                    placeholder="Rua, número, bairro..."
                >
            </div>

            <div class="campo">
                <label>Horário de abertura</label>
                <input
                    type="time"
                    name="horario_abertura"
                    required
                    value="<?php echo htmlspecialchars($_POST["horario_abertura"] ?? ""); ?>"
                >
            </div>

            <div class="campo">
                <label>Horário de fechamento</label>
                <input
                    type="time"
                    name="horario_fechamento"
                    required
                    value="<?php echo htmlspecialchars($_POST["horario_fechamento"] ?? ""); ?>"
                >
            </div>

            <div class="campo">
                <label>Valor por hora (R$)</label>
                <input
                    type="number"
                    name="valor_hora"
                    min="0"
                    step="0.01"
                    required
                    value="<?php echo htmlspecialchars($_POST["valor_hora"] ?? ""); ?>"
                    placeholder="10.00"
                >
            </div>

            <div class="campo">
                <label>Total de vagas</label>
                <input
                    type="number"
                    name="total_vagas"
                    min="1"
                    max="500"
                    required
                    value="<?php echo htmlspecialchars($_POST["total_vagas"] ?? ""); ?>"
                    placeholder="Ex.: 30"
                >
            </div>

        </div>

        <div class="info">
            💡 Ao cadastrar o estacionamento, o Park Point criará automaticamente todas as vagas informadas como <strong>livres</strong>.
        </div>

        <button type="submit">
            Cadastrar estacionamento
        </button>

    </form>

</div>
```

</main>

</body>
</html>
