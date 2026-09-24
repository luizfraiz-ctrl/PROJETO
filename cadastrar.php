<?php

require_once "conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($nome === "" || $email === "" || $senha === "") {
        die("Preencha todos os campos.");
    }

    try {

        // Verifica se o e-mail já existe
        $verificar = $pdo->prepare(
            "SELECT id FROM usuarios WHERE email = ?"
        );

        $verificar->execute([$email]);

        if ($verificar->fetch()) {
            die("Este e-mail já está cadastrado.");
        }

        // Criptografa a senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Cadastra o usuário
        $sql = "INSERT INTO usuarios (nome, email, senha)
                VALUES (?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $nome,
            $email,
            $senha_hash
        ]);

        echo "Cadastro realizado com sucesso!<br><br>";

        echo '<a href="login.php">Clique aqui para entrar</a>';

    } catch (PDOException $e) {

        die("Erro ao cadastrar: " . $e->getMessage());

    }

} else {

    header("Location: cadastro.php");
    exit;

}
?>