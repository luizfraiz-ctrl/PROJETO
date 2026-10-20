
<?php
session_start();
require_once "conexao.php";

// Verifica se o usuário está logado.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

// Aceita somente requisições POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: minhas-reservas.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];
$reserva_id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$token = $_POST["csrf_token"] ?? "";

// Valida o ID e o token CSRF.
if (
    !$reserva_id ||
    !isset($_SESSION["csrf_token"]) ||
    !is_string($token) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(400);
    exit("Solicitação inválida. Volte para Minhas Reservas e tente novamente.");
}

try {
    $pdo->beginTransaction();

    // Verifica se o estacionamento pertence ao usuário
    // e se ainda está ativo.
    $sql = "
        SELECT e.id, e.vaga_id
        FROM estacionamentos e
        INNER JOIN veiculos ve ON ve.id = e.veiculo_id
        WHERE e.id = ?
          AND ve.usuario_id = ?
          AND e.saida IS NULL
        FOR UPDATE
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reserva_id, $usuario_id]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        $pdo->rollBack();
        header("Location: minhas-reservas.php");
        exit;
    }

    // Finaliza o estacionamento ativo.
    $stmt = $pdo->prepare("
        UPDATE estacionamentos
        SET saida = NOW()
        WHERE id = ?
          AND saida IS NULL
    ");
    $stmt->execute([$reserva_id]);

    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException(
            "O estacionamento não pôde ser finalizado."
        );
    }

    // Libera somente a vaga correspondente.
    $stmt = $pdo->prepare("
        UPDATE vagas
        SET status = 'livre'
        WHERE id = ?
          AND status = 'ocupada'
    ");
    $stmt->execute([(int) $reserva["vaga_id"]]);

    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException(
            "Não foi possível liberar a vaga."
        );
    }

    $pdo->commit();

    header("Location: minhas-reservas.php");
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        "Erro ao cancelar estacionamento: " . $e->getMessage()
    );

    http_response_code(500);
    exit(
        "Não foi possível cancelar o estacionamento. "
        . "Tente novamente."
    );
}
?>
