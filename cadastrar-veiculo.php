<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$mensagem = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $placa = strtoupper(trim($_POST["placa"] ?? ""));
    $modelo = trim($_POST["modelo"] ?? "");
    $cor = trim($_POST["cor"] ?? "");

    if ($placa === "" || $modelo === "" || $cor === "") {
        $erro = "Preencha todos os campos.";
    } else {
        try {
            $sql = "INSERT INTO veiculos (placa, modelo, cor, usuario_id)
                    VALUES (?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $placa,
                $modelo,
                $cor,
                $_SESSION["usuario_id"]
            ]);

            $mensagem = "Veículo cadastrado com sucesso!";

        } catch (PDOException $e) {
            $erro = "Não foi possível cadastrar o veículo.";
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
<title>Cadastrar veículo - Park Point</title>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #07111f;
        color: #fff;
        min-height: 100vh;
    }

    /* NAVBAR */

    .navbar {
        height: 72px;
        background: #0b1728;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 7%;
    }

    .logo {
        color: #fff;
        text-decoration: none;
        font-size: 24px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }

    .logo-p {
        color: #00aaff;
    }

    .menu {
        display: flex;
        align-items: center;
        gap: 25px;
    }

    .menu a {
        color: #d5dfeb;
        text-decoration: none;
        font-size: 14px;
        transition: 0.2s;
    }

    .menu a:hover {
        color: #00aaff;
    }

    .sair {
        border: 1px solid #00aaff;
        padding: 9px 16px;
        border-radius: 8px;
    }

    .sair:hover {
        background: #00aaff;
        color: #fff !important;
    }

    /* CONTEÚDO */

    .pagina {
        min-height: calc(100vh - 72px);
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 55px 20px;
    }

    .conteudo {
        width: 100%;
        max-width: 560px;
    }

    .voltar {
        display: inline-block;
        color: #8fa4bb;
        text-decoration: none;
        font-size: 14px;
        margin-bottom: 20px;
        transition: 0.2s;
    }

    .voltar:hover {
        color: #00aaff;
    }

    .card {
        background: #0d1c30;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 18px;
        padding: 38px;
        box-shadow: 0 18px 50px rgba(0,0,0,0.30);
    }

    .titulo {
        text-align: center;
        margin-bottom: 30px;
    }

    .icone {
        width: 64px;
        height: 64px;
        margin: 0 auto 18px;
        border-radius: 16px;
        background: rgba(0,170,255,0.12);
        border: 1px solid rgba(0,170,255,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
    }

    h1 {
        font-size: 28px;
        margin-bottom: 9px;
    }

    .descricao {
        color: #8fa4bb;
        font-size: 14px;
        line-height: 1.5;
    }

    /* MENSAGENS */

    .mensagem {
        padding: 13px 15px;
        border-radius: 10px;
        margin-bottom: 22px;
        font-size: 14px;
        text-align: center;
    }

    .sucesso {
        color: #65efb1;
        background: rgba(0,190,120,0.10);
        border: 1px solid rgba(0,190,120,0.30);
    }

    .erro {
        color: #ff8585;
        background: rgba(255,60,60,0.10);
        border: 1px solid rgba(255,60,60,0.30);
    }

    /* FORMULÁRIO */

    .campo {
        margin-bottom: 20px;
    }

    .campo label {
        display: block;
        color: #dce7f2;
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 8px;
    }

    .campo input {
        width: 100%;
        height: 48px;
        background: #081625;
        border: 1px solid #263c55;
        border-radius: 9px;
        padding: 0 14px;
        color: #fff;
        font-size: 15px;
        outline: none;
        transition: 0.2s;
    }

    .campo input::placeholder {
        color: #63778d;
    }

    .campo input:focus {
        border-color: #00aaff;
        box-shadow: 0 0 0 3px rgba(0,170,255,0.10);
    }

    .botao {
        width: 100%;
        height: 50px;
        border: none;
        border-radius: 9px;
        background: #00aaff;
        color: #fff;
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.2s;
        margin-top: 5px;
    }

    .botao:hover {
        background: #008ed4;
        transform: translateY(-1px);
    }

    .links-sucesso {
        display: flex;
        gap: 10px;
        margin-top: 18px;
    }

    .links-sucesso a {
        flex: 1;
        text-align: center;
        padding: 11px;
        border: 1px solid #263c55;
        border-radius: 8px;
        color: #dce7f2;
        text-decoration: none;
        font-size: 13px;
        transition: 0.2s;
    }

    .links-sucesso a:hover {
        border-color: #00aaff;
        color: #00aaff;
    }

    /* RESPONSIVO */

    @media (max-width: 700px) {

        .navbar {
            padding: 0 20px;
        }

        .menu {
            gap: 10px;
        }

        .menu a:not(.sair) {
            display: none;
        }

        .pagina {
            padding: 35px 15px;
        }

        .card {
            padding: 28px 22px;
        }

        h1 {
            font-size: 25px;
        }
    }
</style>
```

</head>

<body>

<nav class="navbar">

```
<a href="dashboard.php" class="logo">
    <span class="logo-p">P</span>ARK POINT
</a>

<div class="menu">

    <a href="dashboard.php">Início</a>

    <a href="vagas.php">Vagas</a>

    <a href="meus-veiculos.php">Meus veículos</a>

    <a href="minhas-reservas.php">Reservas</a>

    <a href="logout.php" class="sair">Sair</a>

</div>
```

</nav>

<main class="pagina">

```
<div class="conteudo">

    <a href="meus-veiculos.php" class="voltar">
        ← Voltar para meus veículos
    </a>

    <div class="card">

        <div class="titulo">

            <div class="icone">
                🚗
            </div>

            <h1>Cadastrar veículo</h1>

            <p class="descricao">
                Adicione um veículo para poder utilizá-lo
                nas reservas do Park Point.
            </p>

        </div>

        <?php if ($mensagem): ?>

            <div class="mensagem sucesso">

                <?= htmlspecialchars($mensagem) ?>

                <div class="links-sucesso">

                    <a href="meus-veiculos.php">
                        Meus veículos
                    </a>

                    <a href="dashboard.php">
                        Início
                    </a>

                </div>

            </div>

        <?php endif; ?>


        <?php if ($erro): ?>

            <div class="mensagem erro">
                <?= htmlspecialchars($erro) ?>
            </div>

        <?php endif; ?>


        <?php if (!$mensagem): ?>

            <form method="POST">

                <div class="campo">

                    <label for="placa">
                        Placa
                    </label>

                    <input
                        type="text"
                        id="placa"
                        name="placa"
                        placeholder="Ex: ABC1D23"
                        maxlength="8"
                        autocomplete="off"
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
                        placeholder="Ex: Volkswagen Gol"
                        maxlength="100"
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
                        placeholder="Ex: Preto"
                        maxlength="50"
                        required
                    >

                </div>


                <button type="submit" class="botao">
                    Cadastrar veículo
                </button>

            </form>

        <?php endif; ?>

    </div>

</div>
```

</main>

</body>
</html>
