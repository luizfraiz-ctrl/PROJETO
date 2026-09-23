<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];

if (!isset($_POST["vaga_id"]) || !isset($_POST["veiculo_id"])) {
    die("Dados da reserva não foram enviados.");
}

$vaga_id = (int) $_POST["vaga_id"];
$veiculo_id = (int) $_POST["veiculo_id"];

if ($vaga_id <= 0 || $veiculo_id <= 0) {
    die("Dados de reserva inválidos.");
}


/*
=====================================================
INICIAR TRANSAÇÃO
=====================================================
*/

mysqli_begin_transaction($conn);

try {

    /*
    =====================================================
    VERIFICAR SE O VEÍCULO PERTENCE AO USUÁRIO
    =====================================================
    */

    $sql = "SELECT id
            FROM veiculos
            WHERE id = ?
            AND usuario_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception("Erro ao preparar verificação do veículo.");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $veiculo_id,
        $usuario_id
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) === 0) {

        mysqli_stmt_close($stmt);

        throw new Exception(
            "Esse veículo não pertence ao usuário."
        );
    }

    mysqli_stmt_close($stmt);


    /*
    =====================================================
    VERIFICAR RESERVA ATIVA DO VEÍCULO
    =====================================================
    */

    $sql = "SELECT id
            FROM reservas
            WHERE veiculo_id = ?
            AND usuario_id = ?
            AND status = 'ativa'
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception(
            "Erro ao verificar reserva existente."
        );
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $veiculo_id,
        $usuario_id
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) > 0) {

        mysqli_stmt_close($stmt);

        mysqli_rollback($conn);

        mysqli_close($conn);

        ?>

        <!DOCTYPE html>

        <html lang="pt-BR">

        <head>

            <meta charset="UTF-8">

            <meta
                name="viewport"
                content="width=device-width, initial-scale=1.0"
            >

            <title>
                Reserva não permitida - Park Point
            </title>

            <link
                rel="stylesheet"
                href="style.css"
            >

            <style>

                .erro-page {
                    min-height: calc(100vh - 150px);

                    display: flex;
                    justify-content: center;
                    align-items: center;

                    padding: 50px 20px;
                }

                .erro-box {
                    width: 100%;
                    max-width: 520px;

                    background: white;

                    padding: 45px 35px;

                    border-radius: 22px;

                    border: 1px solid #e8edf5;

                    box-shadow:
                        0 15px 40px rgba(0,0,0,0.06);

                    text-align: center;
                }

                .erro-icon {
                    width: 75px;
                    height: 75px;

                    margin: 0 auto 20px;

                    border-radius: 50%;

                    background: #fee2e2;

                    color: #dc2626;

                    display: flex;
                    align-items: center;
                    justify-content: center;

                    font-size: 35px;
                }

                .erro-box h1 {
                    font-size: 25px;
                    margin-bottom: 12px;
                }

                .erro-box p {
                    color: #6b7280;

                    font-size: 14px;

                    line-height: 1.6;

                    margin-bottom: 25px;
                }

            </style>

        </head>


        <body>


        <header class="navbar">

            <div class="navbar-content">

                <a
                    href="dashboard.php"
                    class="logo"
                >

                    <div class="logo-icon">
                        P
                    </div>

                    Park <span>Point</span>

                </a>


                <nav class="nav-menu">

                    <a href="dashboard.php">
                        Início
                    </a>

                    <a href="vagas.php">
                        Vagas
                    </a>

                    <a href="minhas-reservas.php">
                        Reservas
                    </a>

                    <a href="historico.php">
                        Histórico
                    </a>

                    <a
                        href="logout.php"
                        class="btn-login"
                    >
                        Sair
                    </a>

                </nav>

            </div>

        </header>


        <main class="erro-page">

            <div class="erro-box">

                <div class="erro-icon">
                    !
                </div>

                <h1>
                    Este veículo já possui uma reserva
                </h1>

                <p>
                    Você não pode reservar outra vaga
                    utilizando o mesmo veículo enquanto
                    a reserva atual estiver ativa.
                </p>

                <a
                    href="minhas-reservas.php"
                    class="btn-primary"
                >
                    Ver minhas reservas
                </a>

            </div>

        </main>


        <footer>

            <p>
                © 2026 Park Point —
                Sistema de Estacionamento
            </p>

        </footer>


        </body>

        </html>

        <?php

        exit;
    }

    mysqli_stmt_close($stmt);


    /*
    =====================================================
    VERIFICAR E BLOQUEAR A VAGA
    =====================================================
    */

    $sql = "SELECT id, numero
            FROM vagas
            WHERE id = ?
            AND status = 'livre'
            FOR UPDATE";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception(
            "Erro ao preparar verificação da vaga."
        );
    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $vaga_id
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) === 0) {

        mysqli_stmt_close($stmt);

        throw new Exception(
            "Essa vaga não está disponível."
        );
    }

    $vaga = mysqli_fetch_assoc($resultado);

    mysqli_stmt_close($stmt);


    /*
    =====================================================
    CRIAR RESERVA
    =====================================================
    */

    $sql = "INSERT INTO reservas
            (
                usuario_id,
                veiculo_id,
                vaga_id,
                status
            )
            VALUES
            (?, ?, ?, 'ativa')";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception(
            "Erro ao preparar criação da reserva."
        );
    }

    mysqli_stmt_bind_param(
        $stmt,
        "iii",
        $usuario_id,
        $veiculo_id,
        $vaga_id
    );

    if (!mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        throw new Exception(
            "Erro ao criar reserva."
        );
    }

    mysqli_stmt_close($stmt);


    /*
    =====================================================
    OCUPAR VAGA
    =====================================================
    */

    $sql = "UPDATE vagas
            SET status = 'ocupada'
            WHERE id = ?
            AND status = 'livre'";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception(
            "Erro ao preparar atualização da vaga."
        );
    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $vaga_id
    );

    if (!mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        throw new Exception(
            "Erro ao ocupar vaga."
        );
    }

    if (mysqli_stmt_affected_rows($stmt) !== 1) {

        mysqli_stmt_close($stmt);

        throw new Exception(
            "A vaga não pôde ser ocupada."
        );
    }

    mysqli_stmt_close($stmt);


    /*
    =====================================================
    CONFIRMAR TRANSAÇÃO
    =====================================================
    */

    mysqli_commit($conn);

    mysqli_close($conn);


} catch (Exception $erro) {

    mysqli_rollback($conn);

    mysqli_close($conn);

    die(
        "Não foi possível realizar a reserva. " .
        htmlspecialchars($erro->getMessage())
    );
}

?>


<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Reserva confirmada - Park Point
    </title>

    <link
        rel="stylesheet"
        href="style.css"
    >

    <style>

        .confirmacao-page {
            min-height: calc(100vh - 150px);

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 50px 20px;
        }

        .confirmacao-box {
            width: 100%;
            max-width: 520px;

            background: white;

            padding: 45px 35px;

            border-radius: 22px;

            border: 1px solid #e8edf5;

            box-shadow:
                0 15px 40px rgba(0,0,0,0.06);

            text-align: center;
        }

        .confirmacao-icon {
            width: 75px;
            height: 75px;

            margin: 0 auto 20px;

            border-radius: 50%;

            background: #dcfce7;

            color: #16a34a;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 38px;
        }

        .confirmacao-box h1 {
            font-size: 27px;

            margin-bottom: 10px;
        }

        .confirmacao-box p {
            color: #6b7280;

            font-size: 14px;

            margin-bottom: 10px;
        }

        .vaga-confirmada {
            margin: 25px 0;

            padding: 20px;

            background: #f5f8fc;

            border-radius: 15px;
        }

        .vaga-confirmada strong {
            display: block;

            font-size: 13px;

            color: #6b7280;

            margin-bottom: 5px;
        }

        .vaga-confirmada span {
            font-size: 30px;

            font-weight: 700;

            color: #2563eb;
        }

        .confirmacao-botoes {
            display: flex;

            justify-content: center;

            gap: 12px;

            margin-top: 25px;

            flex-wrap: wrap;
        }

    </style>

</head>


<body>


<header class="navbar">

    <div class="navbar-content">

        <a
            href="dashboard.php"
            class="logo"
        >

            <div class="logo-icon">
                P
            </div>

            Park <span>Point</span>

        </a>


        <nav class="nav-menu">

            <a href="dashboard.php">
                Início
            </a>

            <a href="vagas.php">
                Vagas
            </a>

            <a href="minhas-reservas.php">
                Reservas
            </a>

            <a href="historico.php">
                Histórico
            </a>

            <a
                href="logout.php"
                class="btn-login"
            >
                Sair
            </a>

        </nav>

    </div>

</header>


<main class="confirmacao-page">

    <div class="confirmacao-box">


        <div class="confirmacao-icon">
            ✓
        </div>


        <h1>
            Reserva confirmada!
        </h1>


        <p>
            Sua vaga foi reservada com sucesso.
        </p>


        <div class="vaga-confirmada">

            <strong>
                VAGA RESERVADA
            </strong>

            <span>
                <?= htmlspecialchars(
                    $vaga["numero"]
                ) ?>
            </span>

        </div>


        <p>
            Agora você pode consultar sua reserva
            na área <strong>Minhas reservas</strong>.
        </p>


        <div class="confirmacao-botoes">


            <a
                href="minhas-reservas.php"
                class="btn-primary"
            >
                Ver minhas reservas
            </a>


            <a
                href="dashboard.php"
                class="btn-secondary"
            >
                Voltar ao início
            </a>


        </div>


    </div>

</main>


<footer>

    <p>
        © 2026 Park Point —
        Sistema de Estacionamento
    </p>

</footer>


</body>

</html>