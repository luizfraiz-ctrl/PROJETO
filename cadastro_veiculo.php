<?php

session_start();

require_once "conexao.php";


if (!isset($_SESSION["usuario_id"])) {

    header("Location: login.php");
    exit;

}


$mensagem = "";
$erro = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $placa = strtoupper(trim($_POST["placa"]));
    $modelo = trim($_POST["modelo"]);
    $cor = trim($_POST["cor"]);
    $tipo = trim($_POST["tipo"]);


    if ($placa == "" || $modelo == "") {

        $erro = "Placa e modelo são obrigatórios.";

    } else {

        $verificar = $pdo->prepare(
            "SELECT id FROM veiculos WHERE placa = ?"
        );

        $verificar->execute([$placa]);


        if ($verificar->fetch()) {

            $erro = "Esta placa já está cadastrada.";

        } else {

            $sql = "INSERT INTO veiculos
                    (usuario_id, placa, modelo, cor, tipo)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $_SESSION["usuario_id"],
                $placa,
                $modelo,
                $cor,
                $tipo
            ]);

            $mensagem = "Veículo cadastrado com sucesso!";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <title>Cadastro de Veículo</title>

</head>

<body>

<h1>Cadastro de Veículo</h1>


<p>
    Bem-vindo,
    <strong>
        <?= htmlspecialchars($_SESSION["usuario_nome"]) ?>
    </strong>
</p>


<?php if ($mensagem != ""): ?>

    <p style="color: green;">
        <?= htmlspecialchars($mensagem) ?>
    </p>

<?php endif; ?>


<?php if ($erro != ""): ?>

    <p style="color: red;">
        <?= htmlspecialchars($erro) ?>
    </p>

<?php endif; ?>


<form method="POST">

    <label>Placa:</label>
    <br>

    <input
        type="text"
        name="placa"
        maxlength="10"
        required
    >

    <br><br>


    <label>Modelo:</label>
    <br>

    <input
        type="text"
        name="modelo"
        required
    >

    <br><br>


    <label>Cor:</label>
    <br>

    <input
        type="text"
        name="cor"
    >

    <br><br>


    <label>Tipo:</label>
    <br>

    <select name="tipo">

        <option value="Carro">
            Carro
        </option>

        <option value="Moto">
            Moto
        </option>

        <option value="Caminhonete">
            Caminhonete
        </option>

        <option value="Outro">
            Outro
        </option>

    </select>

    <br><br>


    <button type="submit">
        Cadastrar veículo
    </button>

</form>


<br>

<a href="logout.php">
    Sair
</a>

</body>

</html>