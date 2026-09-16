<?php

session_start();

require_once "conexao.php";

$erro = "";

if (isset($_SESSION["usuario_id"])) {

    header("Location: cadastro_veiculo.php");
    exit;

}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];


    if ($email == "" || $senha == "") {

        $erro = "Preencha todos os campos.";

    } else {

        $sql = "SELECT *
                FROM usuarios
                WHERE email = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([$email]);

        $usuario = $stmt->fetch();


        if ($usuario && password_verify($senha, $usuario["senha"])) {

            session_regenerate_id(true);

            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["usuario_nome"] = $usuario["nome"];
            $_SESSION["usuario_email"] = $usuario["email"];
            $_SESSION["usuario_tipo"] = $usuario["tipo"];


            header("Location: cadastro_veiculo.php");
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

    <title>Login</title>

</head>

<body>

<h1>Login</h1>


<?php if ($erro != ""): ?>

    <p style="color: red;">
        <?= htmlspecialchars($erro) ?>
    </p>

<?php endif; ?>


<form method="POST">

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
        required
    >

    <br><br>


    <button type="submit">
        Entrar
    </button>

</form>


<br>

<a href="cadastro_usuario.php">
    Criar uma conta
</a>

</body>

</html>