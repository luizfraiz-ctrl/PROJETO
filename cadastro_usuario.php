<?php

require_once "conexao.php";

$mensagem = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];
    $tipo = $_POST["tipo"];

    if ($nome == "" || $email == "" || $senha == "") {

        $erro = "Preencha todos os campos.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erro = "Digite um e-mail válido.";

    } elseif (strlen($senha) < 6) {

        $erro = "A senha deve ter pelo menos 6 caracteres.";

    } else {

        $verificar = $pdo->prepare(
            "SELECT id FROM usuarios WHERE email = ?"
        );

        $verificar->execute([$email]);

        if ($verificar->fetch()) {

            $erro = "Este e-mail já está cadastrado.";

        } else {

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios
                    (nome, email, senha, tipo)
                    VALUES (?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $nome,
                $email,
                $senhaHash,
                $tipo
            ]);

            $mensagem = "Usuário cadastrado com sucesso!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <title>Cadastro de Usuário</title>

</head>

<body>

<h1>Cadastro de Usuário</h1>

<?php if ($mensagem != ""): ?>

    <p style="color: green;">
        <?= htmlspecialchars($mensagem) ?>
    </p>

<?php endif; ?>

<?php if ($erro != ""): ?>

    <p style="color: red;">
        <?= htmlspecialchars($erro) ?>
    </p>

<?php endif; ?>


<form method="POST">

    <label>Nome:</label>
    <br>

    <input
        type="text"
        name="nome"
        required
    >

    <br><br>


    <label>E-mail:</label>
    <br>

    <input
        type="email"
        name="email"
        required
    >

    <br><br>


    <label>Senha:</label>
    <br>

    <input
        type="password"
        name="senha"
        minlength="6"
        required
    >

    <br><br>


    <label>Tipo de usuário:</label>
    <br>

    <select name="tipo">

        <option value="funcionario">
            Funcionário
        </option>

        <option value="admin">
            Administrador
        </option>

    </select>

    <br><br>


    <button type="submit">
        Cadastrar
    </button>

</form>


<br>

<a href="login.php">
    Já tenho uma conta
</a>

</body>

</html>