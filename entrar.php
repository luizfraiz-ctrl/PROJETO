<?php

session_start();

require_once "conexao.php";

$email = $_POST["email"];
$senha = $_POST["senha"];

$sql = "SELECT * FROM usuarios WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Erro no login: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $email);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) == 1) {

    $usuario = mysqli_fetch_assoc($resultado);

    if (password_verify($senha, $usuario["senha"])) {

        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nome"] = $usuario["nome"];
        $_SESSION["usuario_email"] = $usuario["email"];

        header("Location: dashboard.php");
        exit;

    } else {

        echo "<h2>Senha incorreta!</h2>";
        echo "<a href='login.php'>Voltar</a>";

    }

} else {

    echo "<h2>Usuário não encontrado!</h2>";
    echo "<a href='login.php'>Voltar</a>";

}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>