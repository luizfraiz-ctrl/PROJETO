<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    header("Location: meus-veiculos.php");
    exit;
}

$erro = "";
$sucesso = "";

try {

    // Busca o veículo garantindo que pertence ao usuário logado
    $stmt = $pdo->prepare("
        SELECT id, placa, modelo, cor
        FROM veiculos
        WHERE id = ?
          AND usuario_id = ?
    ");

    $stmt->execute([$id, $usuario_id]);

    $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$veiculo) {
        header("Location: meus-veiculos.php");
        exit;
    }


    // Atualização
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $placa = trim($_POST["placa"] ?? "");
        $modelo = trim($_POST["modelo"] ?? "");
        $cor = trim($_POST["cor"] ?? "");


        if ($placa === "" || $modelo === "" || $cor === "") {

            $erro = "Preencha todos os campos.";

        } else {

            // Padroniza a placa
            $placa = strtoupper($placa);


            // Verifica se outra pessoa não está usando a mesma placa
            $stmt = $pdo->prepare("
                SELECT id
                FROM veiculos
                WHERE placa = ?
                  AND id != ?
            ");

            $stmt->execute([$placa, $id]);

            if ($stmt->fetch()) {

                $erro = "Essa placa já está cadastrada.";

            } else {

                $stmt = $pdo->prepare("
                    UPDATE veiculos
                    SET placa = ?, modelo = ?, cor = ?
                    WHERE id = ?
                      AND usuario_id = ?
                ");

                $stmt->execute([
                    $placa,
                    $modelo,
                    $cor,
                    $id,
                    $usuario_id
                ]);

                $sucesso = "Veículo atualizado com sucesso!";


                // Atualiza os dados exibidos no formulário
                $veiculo["placa"] = $placa;
                $veiculo["modelo"] = $modelo;
                $veiculo["cor"] = $cor;
            }
        }
    }

} catch (PDOException $e) {

    $erro = "Ocorreu um erro ao atualizar o veículo.";
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

```
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Editar Veículo - Park Point</title>

<style>

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f3f6fa;
        color: #172033;
        min-height: 100vh;
    }

    a {
        text-decoration: none;
    }


    /* NAVBAR */

    .navbar {
        width: 100%;
        min-height: 68px;
        background: #111827;
    }

    .navbar-content {
        min-height: 68px;

        padding: 0 32px;

        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .logo {
        color: white;

        font-size: 24px;
        font-weight: 800;
    }

    .logo span {
        color: #60a5fa;
    }

    .nav {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .nav a {
        color: #dbe4ef;

        font-size: 14px;
        font-weight: 600;

        padding: 10px 13px;

        border-radius: 8px;

        transition: 0.2s;
    }

    .nav a:hover {
        background: #273449;
        color: white;
    }

    .nav .sair {
        background: #dc2626;
        color: white;
        margin-left: 5px;
    }

    .nav .sair:hover {
        background: #b91c1c;
    }


    /* CONTEÚDO */

    .main {
        width: 100%;
        padding: 30px;
    }

    .container {
        width: 100%;
        max-width: 850px;
        margin: 0 auto;
    }


    /* VOLTAR */

    .voltar {
        display: inline-flex;

        align-items: center;

        gap: 8px;

        color: #475569;

        font-size: 14px;
        font-weight: 700;

        padding: 8px 12px 8px 7px;

        margin-bottom: 18px;

        border-radius: 9px;

        transition: 0.2s;
    }

    .voltar:hover {
        background: #e2e8f0;

        color: #111827;

        transform: translateX(-3px);
    }

    .seta {
        font-size: 22px;
    }


    /* CARD */

    .card {
        background: white;

        border: 1px solid #e2e8f0;

        border-radius: 20px;

        overflow: hidden;

        box-shadow:
            0 10px 30px rgba(15, 23, 42, 0.08);
    }


    /* CABEÇALHO DO CARD */

    .card-header {
        background: linear-gradient(
            135deg,
            #111827,
            #1e3a5f
        );

        color: white;

        padding: 32px;
    }

    .icone {
        width: 55px;
        height: 55px;

        display: flex;
        align-items: center;
        justify-content: center;

        background: rgba(255, 255, 255, 0.12);

        border-radius: 14px;

        font-size: 27px;

        margin-bottom: 18px;
    }

    .card-header h1 {
        font-size: 28px;

        margin-bottom: 7px;
    }

    .card-header p {
        color: #d7e0ec;

        font-size: 14px;

        line-height: 1.5;
    }


    /* FORMULÁRIO */

    .form {
        padding: 32px;
    }

    .campo {
        margin-bottom: 21px;
    }

    .campo label {
        display: block;

        color: #334155;

        font-size: 13px;
        font-weight: 700;

        margin-bottom: 8px;
    }

    .campo input {
        width: 100%;

        height: 48px;

        padding: 0 14px;

        border: 1px solid #cbd5e1;

        border-radius: 10px;

        background: #f8fafc;

        color: #172033;

        font-size: 14px;

        outline: none;

        transition: 0.2s;
    }

    .campo input:focus {
        border-color: #2563eb;

        background: white;

        box-shadow:
            0 0 0 3px rgba(37, 99, 235, 0.10);
    }


    /* MENSAGENS */

    .mensagem {
        padding: 13px 15px;

        border-radius: 10px;

        font-size: 13px;

        font-weight: 600;

        margin-bottom: 20px;
    }

    .erro {
        background: #fff1f2;

        border: 1px solid #fecdd3;

        color: #be123c;
    }

    .sucesso {
        background: #f0fdf4;

        border: 1px solid #bbf7d0;

        color: #15803d;
    }


    /* BOTÕES */

    .botoes {
        display: flex;

        gap: 12px;

        margin-top: 28px;
    }

    .botao {
        flex: 1;

        min-height: 48px;

        border: none;

        border-radius: 10px;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 14px;

        font-weight: 700;

        cursor: pointer;

        transition: 0.2s;
    }

    .salvar {
        background: #2563eb;

        color: white;
    }

    .salvar:hover {
        background: #1d4ed8;

        transform: translateY(-2px);
    }

    .cancelar {
        background: #f1f5f9;

        color: #475569;

        border: 1px solid #e2e8f0;
    }

    .cancelar:hover {
        background: #e2e8f0;

        color: #111827;
    }


    /* INFORMAÇÃO */

    .info {
        margin-top: 20px;

        padding: 14px 16px;

        background: #eff6ff;

        border: 1px solid #dbeafe;

        border-radius: 10px;

        color: #1e40af;

        font-size: 12px;

        line-height: 1.5;
    }


    /* RESPONSIVO */

    @media (max-width: 700px) {

        .navbar-content {
            padding: 14px 15px;

            flex-direction: column;

            gap: 12px;
        }

        .nav {
            flex-wrap: wrap;

            justify-content: center;
        }

        .main {
            padding: 18px 15px 35px;
        }

        .card-header {
            padding: 27px 23px;
        }

        .form {
            padding: 25px 20px;
        }

    }


    @media (max-width: 480px) {

        .botoes {
            flex-direction: column;
        }

    }

</style>
```

</head>

<body>

<header class="navbar">

```
<div class="navbar-content">


    <a href="dashboard.php" class="logo">
        Park <span>Point</span>
    </a>


    <nav class="nav">

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
```

</header>

<main class="main">

```
<div class="container">


    <a href="meus-veiculos.php" class="voltar">

        <span class="seta">
            ←
        </span>

        Voltar para meus veículos

    </a>


    <section class="card">


        <div class="card-header">

            <div class="icone">
                🚗
            </div>

            <h1>
                Editar veículo
            </h1>

            <p>
                Atualize as informações do seu veículo
                cadastrado no Park Point.
            </p>

        </div>


        <form
            method="POST"
            class="form"
        >


            <?php if ($erro !== ""): ?>

                <div class="mensagem erro">
                    <?= htmlspecialchars($erro) ?>
                </div>

            <?php endif; ?>


            <?php if ($sucesso !== ""): ?>

                <div class="mensagem sucesso">
                    <?= htmlspecialchars($sucesso) ?>
                </div>

            <?php endif; ?>


            <div class="campo">

                <label for="placa">
                    Placa
                </label>

                <input
                    type="text"
                    id="placa"
                    name="placa"
                    value="<?= htmlspecialchars($veiculo["placa"]) ?>"
                    maxlength="10"
                    placeholder="Ex.: ABC1D23"
                    required
                >

            </div>


            <div class="campo">

                <label for="modelo">
                    Modelo
                </label>

                <input
                    type="text"
                    id="modelo"
                    name="modelo"
                    value="<?= htmlspecialchars($veiculo["modelo"]) ?>"
                    placeholder="Ex.: Chevrolet Onix"
                    required
                >

            </div>


            <div class="campo">

                <label for="cor">
                    Cor
                </label>

                <input
                    type="text"
                    id="cor"
                    name="cor"
                    value="<?= htmlspecialchars($veiculo["cor"]) ?>"
                    placeholder="Ex.: Preto"
                    required
                >

            </div>


            <div class="botoes">

                <a
                    href="meus-veiculos.php"
                    class="botao cancelar"
                >
                    Cancelar
                </a>


                <button
                    type="submit"
                    class="botao salvar"
                >
                    Salvar alterações
                </button>

            </div>


            <div class="info">

                💡 As alterações serão salvas
                automaticamente no seu cadastro.

            </div>


        </form>


    </section>


</div>
```

</main>

</body>

</html>
