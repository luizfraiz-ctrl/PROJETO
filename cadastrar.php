<?php

require_once "conexao.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cadastro.php");
    exit;
}

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$senha = $_POST["senha"] ?? "";

if ($nome === "" || $email === "" || $senha === "") {
    die("Preencha todos os campos.");
}

// Verifica se o e-mail já existe
$sql = "SELECT id FROM usuarios WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Erro ao verificar usuário: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) > 0) {

    mysqli_stmt_close($stmt);

    echo "<h2>Este e-mail já está cadastrado.</h2>";
    echo "<a href='cadastro.php'>Voltar para o cadastro</a>";

    exit;
}

mysqli_stmt_close($stmt);

// Cria a senha criptografada
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Cadastra o usuário
$sql = "INSERT INTO usuarios (nome, email, senha)
        VALUES (?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Erro ao preparar cadastro: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "sss",
    $nome,
    $email,
    $senha_hash
);

if (mysqli_stmt_execute($stmt)) {

    echo "<h2>Cadastro realizado com sucesso!</h2>";
    echo "<p>Usuário: " . htmlspecialchars($nome) . "</p>";
    echo "<a href='login.php'>Ir para o login</a>";

} else {

    echo "<h2>Não foi possível realizar o cadastro.</h2>";
    echo "<p>Erro: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>";
    echo "<a href='cadastro.php'>Voltar</a>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>