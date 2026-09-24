<?php
session_start();
require_once "conexao.php";

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmar_senha = $_POST["confirmar_senha"] ?? "";

    if ($nome === "" || $email === "" || $senha === "" || $confirmar_senha === "") {

        $erro = "Preencha todos os campos.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erro = "Digite um e-mail válido.";

    } elseif ($senha !== $confirmar_senha) {

        $erro = "As senhas não são iguais.";

    } elseif (strlen($senha) < 6) {

        $erro = "A senha deve ter pelo menos 6 caracteres.";

    } else {

        $stmt = $pdo->prepare(
            "SELECT id FROM usuarios WHERE email = ?"
        );

        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $erro = "Este e-mail já está cadastrado.";

        } else {

            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)"
            );

            if ($stmt->execute([$nome, $email, $senha_hash])) {

                $sucesso = "Cadastro realizado com sucesso!";

            } else {

                $erro = "Não foi possível realizar o cadastro.";

            }
        }
    }
}
?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

```
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Criar conta - Park Point</title>

<style>

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        min-height: 100vh;
        font-family: Arial, Helvetica, sans-serif;
        background: #0f172a;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 25px;
    }

    .cadastro-page {
        width: 100%;
        max-width: 950px;
        min-height: 600px;
        display: flex;
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
    }

    .cadastro-info {
        width: 50%;
        background: linear-gradient(135deg, #2563eb, #1e40af);
        color: white;
        padding: 50px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .cadastro-logo {
        width: 60px;
        height: 60px;
        background: white;
        color: #2563eb;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 25px;
    }

    .cadastro-info h1 {
        margin: 0 0 15px;
        font-size: 42px;
    }

    .cadastro-info h1 span {
        color: #bfdbfe;
    }

    .cadastro-info p {
        margin: 0;
        color: #dbeafe;
        line-height: 1.7;
        font-size: 16px;
        max-width: 360px;
    }

    .cadastro-items {
        margin-top: 40px;
    }

    .cadastro-item {
        margin-bottom: 15px;
        color: #eff6ff;
        font-size: 15px;
    }

    .cadastro-form-area {
        width: 50%;
        padding: 45px 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
    }

    .cadastro-form {
        width: 100%;
        max-width: 380px;
    }

    .cadastro-form h2 {
        margin: 0 0 8px;
        color: #0f172a;
        font-size: 30px;
    }

    .cadastro-subtitle {
        margin: 0 0 25px;
        color: #64748b;
        font-size: 14px;
    }

    .cadastro-error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 18px;
        font-size: 14px;
    }

    .cadastro-success {
        background: #dcfce7;
        border: 1px solid #bbf7d0;
        color: #15803d;
        padding: 14px;
        border-radius: 10px;
        margin-bottom: 18px;
        font-size: 14px;
        line-height: 1.5;
    }

    .cadastro-label {
        display: block;
        margin-bottom: 7px;
        color: #334155;
        font-size: 14px;
        font-weight: bold;
    }

    .cadastro-input {
        width: 100%;
        height: 48px;
        padding: 0 14px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: white;
        outline: none;
        font-size: 15px;
        margin-bottom: 16px;
    }

    .cadastro-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .cadastro-button {
        width: 100%;
        height: 50px;
        border: 0;
        border-radius: 10px;
        background: #2563eb;
        color: white;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.2s;
        margin-top: 5px;
    }

    .cadastro-button:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
    }

    .cadastro-login {
        text-align: center;
        margin-top: 20px;
        color: #64748b;
        font-size: 14px;
    }

    .cadastro-login a {
        color: #2563eb;
        font-weight: bold;
        text-decoration: none;
    }

    .cadastro-login a:hover {
        text-decoration: underline;
    }

    .cadastro-back {
        display: block;
        text-align: center;
        margin-top: 15px;
        color: #64748b;
        text-decoration: none;
        font-size: 14px;
    }

    .cadastro-back:hover {
        color: #2563eb;
    }

    @media (max-width: 750px) {

        .cadastro-page {
            flex-direction: column;
        }

        .cadastro-info {
            width: 100%;
            padding: 35px;
        }

        .cadastro-form-area {
            width: 100%;
            padding: 35px;
        }

        .cadastro-info h1 {
            font-size: 34px;
        }

        .cadastro-items {
            margin-top: 25px;
        }
    }

    @media (max-width: 450px) {

        body {
            padding: 10px;
        }

        .cadastro-info,
        .cadastro-form-area {
            padding: 25px;
        }

        .cadastro-info h1 {
            font-size: 30px;
        }
    }

</style>
```

</head>

<body>

```
<div class="cadastro-page">

    <div class="cadastro-info">

        <div class="cadastro-logo">
            P
        </div>

        <h1>
            Park <span>Point</span>
        </h1>

        <p>
            Crie sua conta e tenha tudo o que precisa
            para encontrar, reservar e acompanhar suas
            vagas de estacionamento.
        </p>

        <div class="cadastro-items">

            <div class="cadastro-item">
                🚗 Cadastre seus veículos
            </div>

            <div class="cadastro-item">
                🅿️ Encontre vagas disponíveis
            </div>

            <div class="cadastro-item">
                📋 Acompanhe suas reservas
            </div>

        </div>

    </div>


    <div class="cadastro-form-area">

        <div class="cadastro-form">

            <h2>Criar conta</h2>

            <p class="cadastro-subtitle">
                Preencha seus dados para começar.
            </p>


            <?php if ($erro !== ""): ?>

                <div class="cadastro-error">
                    ⚠️ <?= htmlspecialchars($erro) ?>
                </div>

            <?php endif; ?>


            <?php if ($sucesso !== ""): ?>

                <div class="cadastro-success">
                    ✅ <?= htmlspecialchars($sucesso) ?>

                    <br><br>

                    <a href="entrar.php">
                        Clique aqui para entrar
                    </a>
                </div>

            <?php else: ?>


                <form method="POST" action="cadastro.php">

                    <label
                        class="cadastro-label"
                        for="nome"
                    >
                        Nome
                    </label>

                    <input
                        class="cadastro-input"
                        type="text"
                        id="nome"
                        name="nome"
                        placeholder="Digite seu nome"
                        value="<?= htmlspecialchars($_POST["nome"] ?? "") ?>"
                        required
                    >


                    <label
                        class="cadastro-label"
                        for="email"
                    >
                        E-mail
                    </label>

                    <input
                        class="cadastro-input"
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Digite seu e-mail"
                        value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                        required
                    >


                    <label
                        class="cadastro-label"
                        for="senha"
                    >
                        Senha
                    </label>

                    <input
                        class="cadastro-input"
                        type="password"
                        id="senha"
                        name="senha"
                        placeholder="Mínimo de 6 caracteres"
                        required
                    >


                    <label
                        class="cadastro-label"
                        for="confirmar_senha"
                    >
                        Confirmar senha
                    </label>

                    <input
                        class="cadastro-input"
                        type="password"
                        id="confirmar_senha"
                        name="confirmar_senha"
                        placeholder="Digite a senha novamente"
                        required
                    >


                    <button
                        class="cadastro-button"
                        type="submit"
                    >
                        Criar minha conta →
                    </button>

                </form>

            <?php endif; ?>


            <div class="cadastro-login">

                Já possui uma conta?

                <a href="entrar.php">
                    Entrar
                </a>

            </div>


            <a
                class="cadastro-back"
                href="index.php"
            >
                ← Voltar para o início
            </a>

        </div>

    </div>

</div>
```

</body>

</html>
