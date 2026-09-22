<?php

session_start();

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

$sql = "SELECT
            reservas.id,
            reservas.data_reserva,
            reservas.status,
            vagas.numero AS vaga,
            veiculos.placa,
            veiculos.modelo
        FROM reservas
        INNER JOIN vagas
            ON reservas.vaga_id = vagas.id
        INNER JOIN veiculos
            ON reservas.veiculo_id = veiculos.id
        WHERE reservas.usuario_id = '$usuario_id'
        ORDER BY reservas.id DESC";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Erro ao buscar histórico: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Histórico</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <h1>Histórico de Reservas</h1>

    <p>
        <a href="dashboard.php">← Voltar para o painel</a>
    </p>

    <hr>

    <?php if (mysqli_num_rows($resultado) > 0): ?>

        <table border="1" cellpadding="10">

            <tr>
                <th>Vaga</th>
                <th>Veículo</th>
                <th>Placa</th>
                <th>Data</th>
                <th>Status</th>
            </tr>

            <?php while ($reserva = mysqli_fetch_assoc($resultado)): ?>

                <tr>

                    <td>
                        Vaga <?php echo $reserva["vaga"]; ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($reserva["modelo"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($reserva["placa"]); ?>
                    </td>

                    <td>
                        <?php echo $reserva["data_reserva"]; ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($reserva["status"]); ?>
                    </td>

                </tr>

            <?php endwhile; ?>

        </table>

    <?php else: ?>

        <p>Nenhum registro encontrado.</p>

    <?php endif; ?>

</body>

</html>

<?php

mysqli_close($conn);

?>