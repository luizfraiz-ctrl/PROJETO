<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$id = intval($_POST["id"] ?? 0);

if ($id <= 0) {
    header("Location: meus-veiculos.php");
    exit;
}

// Verifica se o veículo pertence ao usuário
$sql = "SELECT id FROM veiculos WHERE id = ? AND usuario_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id, $usuario_id);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) === 0) {
    mysqli_stmt_close($stmt);
    header("Location: meus-veiculos.php");
    exit;
}

mysqli_stmt_close($stmt);

// Verifica se existe reserva ativa para esse veículo
$sql = "SELECT id FROM reservas
        WHERE veiculo_id = ?
        AND usuario_id = ?
        AND status = 'ativa'
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id, $usuario_id);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) > 0) {

    mysqli_stmt_close($stmt);

    echo "
    <!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <link rel='stylesheet' href='style.css'>
        <title>Park Point</title>
    </head>
    <body>

    <main class='form-page'>
        <div class='form-box'>

            <h1>Não é possível excluir</h1>

            <p>
                Este veículo possui uma reserva ativa.
                Cancele a reserva antes de excluir o veículo.
            </p>

            <a href='meus-veiculos.php' class='btn-primary'>
                Voltar para meus veículos
            </a>

        </div>
    </main>

    </body>
    </html>
    ";

    exit;
}

mysqli_stmt_close($stmt);

// Exclui o veículo
$sql = "DELETE FROM veiculos
        WHERE id = ? AND usuario_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id, $usuario_id);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);
mysqli_close($conn);

header("Location: meus-veiculos.php?excluido=1");
exit;
?>