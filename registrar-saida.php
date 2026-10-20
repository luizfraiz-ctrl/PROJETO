
<?php
session_start();
require_once "conexao.php";

// Verifica se o usuário está logado.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

// Somente administradores e funcionários podem registrar saídas.
$perfil = $_SESSION["perfil"] ?? "";

if (!in_array($perfil, ["admin", "funcionario"], true)) {
    http_response_code(403);
    exit("Você não tem permissão para registrar saídas.");
}

// Aceita somente requisições POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: funcionario.php");
    exit;
}

// Valida o ID e o token de segurança.
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$token = $_POST["csrf_token"] ?? "";

if (
    !$id ||
    !isset($_SESSION["csrf_token"]) ||
    !is_string($token) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(400);
    exit("Solicitação inválida. Atualize a página e tente novamente.");
}

try {
    $pdo->beginTransaction();

    // Busca o estacionamento que ainda está ativo.
    $stmt = $pdo->prepare("
        SELECT id, vaga_id
        FROM estacionamentos
        WHERE id = ?
          AND saida IS NULL
        FOR UPDATE
    ");
    $stmt->execute([$id]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registro) {
        $pdo->rollBack();
        header("Location: funcionario.php");
        exit;
    }

    // Registra o horário de saída.
    $stmt = $pdo->prepare("
        UPDATE estacionamentos
        SET saida = NOW()
        WHERE id = ?
          AND saida IS NULL
    ");
    $stmt->execute([$id]);

    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException(
            "Não foi possível registrar a saída."
        );
    }

    // Libera a vaga ocupada correspondente.
    $stmt = $pdo->prepare("
        UPDATE vagas
        SET status = 'livre'
        WHERE id = ?
          AND status = 'ocupada'
    ");
    $stmt->execute([(int) $registro["vaga_id"]]);

    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException(
            "Não foi possível liberar a vaga."
        );
    }

    // Confirma as alterações no banco.
    $pdo->commit();

    header("Location: funcionario.php");
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        "Erro ao registrar saída: " . $e->getMessage()
    );

    http_response_code(500);
    exit("Não foi possível registrar a saída. Tente novamente.");
}
?>
