<?php

session_start();

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    die("Reserva não informada.");
}

$usuario_id = (int) $_SESSION["usuario_id"];
$reserva_id = (int) $_GET["id"];

try {

    /*
    =====================================================
    BUSCAR RESERVA
    =====================================================
    */

    $sql = "SELECT
                e.id,
                e.vaga_id
            FROM estacionamentos e

            INNER JOIN veiculos ve
                ON e.veiculo_id = ve.id

            WHERE e.id = ?
            AND ve.usuario_id = ?
            AND e.saida IS NULL";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $reserva_id,
        $usuario_id
    ]);

    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$reserva) {

        die("
            <!DOCTYPE html>
            <html lang='pt-BR'>

            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>

                <title>Reserva não encontrada - Park Point</title>

                <link rel='stylesheet' href='style.css'>
            </head>

            <body>

                <main class='form-page'>

                    <div class='form-box'>

                        <h1>Reserva não encontrada</h1>

                        <p>
                            Essa reserva não existe,
                            já foi finalizada ou não pertence à sua conta.
                        </p>

                        <br>

                        <a
                            href='minhas-reservas.php'
                            class='btn-primary'
                        >
                            Voltar para minhas reservas
                        </a>

                    </div>

                </main>

            </body>

            </html>
        ");

    }


    $vaga_id = (int) $reserva["vaga_id"];


    /*
    =====================================================
    CANCELAR RESERVA
    =====================================================
    */

    $pdo->beginTransaction();


    /*
    Finaliza o estacionamento
    */

    $sql = "UPDATE estacionamentos

            SET saida = NOW()

            WHERE id = ?

            AND saida IS NULL";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $reserva_id
    ]);


    /*
    Libera a vaga
    */

    $sql = "UPDATE vagas

            SET status = 'livre'

            WHERE id = ?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $vaga_id
    ]);


    /*
    Finaliza a transação
    */

    $pdo->commit();


    /*
    Volta para minhas reservas
    */

    header("Location: minhas-reservas.php");

    exit;


} catch (Exception $erro) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(
        "Erro ao cancelar reserva: " .
        htmlspecialchars($erro->getMessage())
    );

}

?>
