<?php
session_start();
require_once "conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($email === "" || $senha === "") {
        $erro = "Preencha todos os campos.";
    } else {

        $stmt = $pdo->prepare("
            SELECT id, nome, email, senha, perfil
            FROM usuarios
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario["senha"])) {

            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["usuario_nome"] = $usuario["nome"];
            $_SESSION["usuario_email"] = $usuario["email"];
            $_SESSION["perfil"] = $usuario["perfil"];

            if ($usuario["perfil"] === "admin") {
                header("Location: admin.php");
                exit;
            }

            if ($usuario["perfil"] === "funcionario") {
                header("Location: funcionario.php");
                exit;
            }

            header("Location: dashboard.php");
            exit;

        } else {
            $erro = "E-mail ou senha incorretos.";
        }
    }
}
?>

<!DOCTYPE html>

<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Park Point</title>

```
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    body {
        min-height: 100vh;
        background: #07111f;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .container {
        width: 100%;
        max-width: 950px;
        min-height: 560px;
        background: white;
        border-radius: 22px;
        overflow: hidden;
        display: flex;
        box-shadow: 0 20px 60px rgba(0,0,0,0.35);
    }

    .lado-esquerdo {
        width: 45%;
        background: linear-gradient(135deg, #0077ff, #00aaff);
        color: white;
        padding: 50px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .lado-esquerdo h1 {
        font-size: 42px;
        margin-bottom: 20px;
    }

    .lado-esquerdo p {
        font-size: 17px;
        line-height: 1.6;
        opacity: 0.95;
    }

    .logo {
        font-size: 27px;
        font-weight: bold;
        margin-bottom: 45px;
    }

    .lado-direito {
        width: 55%;
        padding: 55px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .lado-direito h2 {
        font-size: 32px;
        color: #102033;
        margin-bottom: 10px;
    }

    .subtitulo {
        color: #718096;
        margin-bottom: 30px;
    }

    .campo {
        margin-bottom: 18px;
    }

    .campo label {
        display: block;
        margin-bottom: 7px;
        font-weight: bold;
        color: #263548;
    }

    .campo input {
        width: 100%;
        padding: 14px;
        border: 1px solid #d6dce5;
        border-radius: 10px;
        font-size: 16px;
        outline: none;
    }

    .campo input:focus {
        border-color: #008cff;
    }

    .botao {
        width: 100%;
        border: none;
        padding: 15px;
        border-radius: 10px;
        background: #008cff;
        color: white;
        font-size: 17px;
        font-weight: bold;
        cursor: pointer;
        margin-top: 5px;
    }

    .botao:hover {
        background: #0074d4;
    }

    .erro {
        background: #ffe8e8;
        color: #c62828;
        padding: 12px;
        border-radius: 9px;
        margin-bottom: 18px;
        text-align: center;
    }

    .links {
        margin-top: 25px;
        text-align: center;
        color: #667085;
    }

    .links a {
        color: #008cff;
        text-decoration: none;
        font-weight: bold;
    }

    .voltar {
        margin-top: 18px;
        text-align: center;
    }

    .voltar a {
        color: #667085;
        text-decoration: none;
    }

    @media (max-width: 750px) {
        .container {
            flex-direction: column;
        }

        .lado-esquerdo,
        .lado-direito {
            width: 100%;
        }

        .lado-esquerdo {
            padding: 35px;
            min-height: 260px;
        }

        .lado-esquerdo h1 {
            font-size: 32px;
        }

        .lado-direito {
            padding: 35px;
        }
    }
</style>
```

</head>

<body>

<div class="container">

```
<div class="lado-esquerdo">
    <div class="logo">PARK POINT</div>

    <h1>Bem-vindo de volta!</h1>

    <p>
        Entre na sua conta para acessar suas vagas,
        veículos, reservas e todas as funcionalidades
        do Park Point.
    </p>
</div>

<div class="lado-direito">

    <h2>Entrar</h2>

    <p class="subtitulo">
        Acesse sua conta do Park Point
    </p>

    <?php if ($erro !== ""): ?>
        <div class="erro">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="campo">
            <label for="email">E-mail</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="Digite seu e-mail"
                required
            >
        </div>

        <div class="campo">
            <label for="senha">Senha</label>
            <input
                type="password"
                id="senha"
                name="senha"
                placeholder="Digite sua senha"
                required
            >
        </div>

        <button type="submit" class="botao">
            Entrar
        </button>

    </form>

    <div class="links">
        Ainda não possui uma conta?
        <a href="cadastro.php">Criar conta</a>
    </div>

    <div class="voltar">
        <a href="index.php">← Voltar para o início</a>
    </div>

</div>
```

</div>

</body>
</html>
