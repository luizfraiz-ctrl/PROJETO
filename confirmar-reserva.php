<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: vagas.php");
    exit;
}

$vaga_id = (int)($_POST["vaga_id"] ?? 0);
$veiculo_id = (int)($_POST["veiculo_id"] ?? 0);

function paginaMensagem($titulo, $mensagem, $tipo = "erro")
{
    $corPrincipal = $tipo === "sucesso" ? "#19a463" : "#e53935";
    $corFundo = $tipo === "sucesso" ? "#ecfff5" : "#fff0f0";

    ?>

```
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?php echo htmlspecialchars($titulo); ?> - Park Point</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            color: #172033;
        }

        nav {
            height: 72px;
            background: #101827;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 6%;
        }

        .logo {
            text-decoration: none;
            color: white;
            font-size: 21px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .logo-p {
            color: #00aaff;
        }

        .sair {
            text-decoration: none;
            color: white;
            background: #00aaff;
            padding: 10px 16px;
            border-radius: 8px;
        }

        .container {
            width: 90%;
            max-width: 650px;
            margin: 80px auto;
        }

        .card {
            background: white;
            border-radius: 18px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(20,35,55,.08);
            border: 1px solid #e5ebf2;
        }

        .icone {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: <?php echo $corFundo; ?>;
            color: <?php echo $corPrincipal; ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 20px;
        }

        h1 {
            font-size: 27px;
            margin-bottom: 12px;
        }

        p {
            color: #68758a;
            line-height: 1.6;
        }

        .botoes {
            display: flex;
            gap: 12px;
            margin-top: 28px;
        }

        .botao {
            flex: 1;
            text-decoration: none;
            padding: 14px;
            border-radius: 9px;
            background: #00aaff;
            color: white;
            font-weight: bold;
        }

        .botao:hover {
            background: #008dcc;
        }

        .botao.secundario {
            background: #e9eef5;
            color: #172033;
        }

        .botao.secundario:hover {
            background: #dce4ee;
        }

        @media(max-width:600px) {
            .card {
                padding: 28px 20px;
            }

            .botoes {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    <nav>

        <a href="dashboard.php" class="logo">
            <span class="logo-p">P</span>ARK POINT
        </a>

        <a href="logout.php" class="sair">
            Sair
        </a>

    </nav>

    <main class="container">

        <div class="card">

            <div class="icone">
                <?php echo $tipo === "sucesso" ? "✓" : "!"; ?>
            </div>

            <h1>
                <?php echo htmlspecialchars($titulo); ?>
            </h1>

            <p>
                <?php echo htmlspecialchars($mensagem); ?>
            </p>

            <div class="botoes">

                <a
                    href="vagas.php"
                    class="botao"
                >
                    Ver vagas
                </a>

                <a
                    href="dashboard.php"
                    class="botao secundario"
                >
                    Voltar ao painel
                </a>

            </div>

        </div>

    </main>

</body>
</html>
<?php
exit;


}

if ($vaga_id <= 0 || $veiculo_id <= 0) {


paginaMensagem(
    "Dados inválidos",
    "Não foi possível identificar a vaga ou o veículo selecionado."
);


}

try {


$pdo->beginTransaction();

/*
 * 1. Verifica o veículo.
 */
$stmt = $pdo->prepare("
    SELECT
        id,
        placa,
        modelo,
        cor
    FROM veiculos
    WHERE id = ?
      AND usuario_id = ?
    LIMIT 1
");

$stmt->execute([
    $veiculo_id,
    $usuario_id
]);

$veiculo = $stmt->fetch();

if (!$veiculo) {

    $pdo->rollBack();

    paginaMensagem(
        "Veículo não encontrado",
        "O veículo selecionado não pertence à sua conta."
    );
}


/*
 * 2. Verifica se o veículo já está estacionado.
 */
$stmt = $pdo->prepare("
    SELECT
        e.id,
        e.vaga_id,
        v.numero AS vaga_numero,
        est.nome AS estabelecimento_nome
    FROM estacionamentos e

    INNER JOIN vagas v
        ON v.id = e.vaga_id

    INNER JOIN estabelecimentos est
        ON est.id = v.estabelecimento_id

    WHERE e.veiculo_id = ?
      AND e.saida IS NULL

    LIMIT 1

    FOR UPDATE
");

$stmt->execute([$veiculo_id]);

$estacionamentoAtivo = $stmt->fetch();

if ($estacionamentoAtivo) {

    $pdo->rollBack();

    paginaMensagem(
        "Veículo já estacionado",
        "O veículo " .
        $veiculo["placa"] .
        " já está estacionado na vaga " .
        $estacionamentoAtivo["vaga_numero"] .
        " do estacionamento " .
        $estacionamentoAtivo["estabelecimento_nome"] .
        "."
    );
}


/*
 * 3. Bloqueia a vaga enquanto verifica.
 */
$stmt = $pdo->prepare("
    SELECT
        v.id,
        v.numero,
        v.status,
        v.estabelecimento_id,
        e.nome AS estabelecimento_nome
    FROM vagas v

    INNER JOIN estabelecimentos e
        ON e.id = v.estabelecimento_id

    WHERE v.id = ?

    LIMIT 1

    FOR UPDATE
");

$stmt->execute([$vaga_id]);

$vaga = $stmt->fetch();

if (!$vaga) {

    $pdo->rollBack();

    paginaMensagem(
        "Vaga não encontrada",
        "A vaga selecionada não existe."
    );
}


/*
 * 4. Confirma que a vaga está livre.
 */
if ($vaga["status"] !== "livre") {

    $pdo->rollBack();

    paginaMensagem(
        "Vaga ocupada",
        "Esta vaga acabou de ser ocupada. Escolha outra vaga."
    );
}


/*
 * 5. Confirma que a vaga pertence a um estacionamento.
 */
if (
    empty($vaga["estabelecimento_id"]) ||
    empty($vaga["estabelecimento_nome"])
) {

    $pdo->rollBack();

    paginaMensagem(
        "Vaga sem estacionamento",
        "Esta vaga ainda não está vinculada a um estacionamento."
    );
}


/*
 * 6. Cria o estacionamento/reserva.
 */
$stmt = $pdo->prepare("
    INSERT INTO estacionamentos
    (
        veiculo_id,
        vaga_id,
        entrada,
        saida
    )
    VALUES
    (
        ?,
        ?,
        NOW(),
        NULL
    )
");

$stmt->execute([
    $veiculo_id,
    $vaga_id
]);


/*
 * 7. Atualiza o status da vaga.
 */
$stmt = $pdo->prepare("
    UPDATE vagas
    SET status = 'ocupada'
    WHERE id = ?
      AND status = 'livre'
");

$stmt->execute([$vaga_id]);


/*
 * Se nenhuma linha foi alterada, outra operação
 * ocupou a vaga antes da atualização.
 */
if ($stmt->rowCount() !== 1) {

    $pdo->rollBack();

    paginaMensagem(
        "Vaga ocupada",
        "A vaga não está mais disponível. Escolha outra."
    );
}


/*
 * 8. Finaliza tudo.
 */
$pdo->commit();


paginaMensagem(
    "Reserva realizada!",
    "O veículo " .
    $veiculo["placa"] .
    " foi estacionado na vaga " .
    $vaga["numero"] .
    " do " .
    $vaga["estabelecimento_nome"] .
    ".",
    "sucesso"
);


} catch (PDOException $e) {


if ($pdo->inTransaction()) {
    $pdo->rollBack();
}

paginaMensagem(
    "Não foi possível reservar",
    "Ocorreu um erro ao realizar a reserva. Verifique os dados e tente novamente."
);


}
?>
