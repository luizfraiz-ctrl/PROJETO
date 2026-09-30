<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$perfil = $_SESSION["perfil"] ?? "";

// Funcionário e administrador podem acessar esta área
if ($perfil !== "funcionario" && $perfil !== "admin") {
    header("Location: dashboard.php");
    exit;
}

// Verifica se veio do painel Admin
$veioDoAdmin = ($_SESSION["navegacao_admin"] ?? false) === true;

// =========================
// ESTATÍSTICAS DAS VAGAS
// =========================

$stmt = $pdo->query("SELECT COUNT(*) FROM vagas WHERE status = 'livre'");
$vagasLivres = (int) $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM vagas WHERE status = 'ocupada'");
$vagasOcupadas = (int) $stmt->fetchColumn();

$totalVagas = $vagasLivres + $vagasOcupadas;

// =========================
// VEÍCULOS ESTACIONADOS
// =========================

$stmt = $pdo->query("
    SELECT 
        e.id,
        e.entrada,
        v.numero AS vaga,
        ve.placa,
        ve.modelo,
        ve.cor
    FROM estacionamentos e
    INNER JOIN vagas v ON e.vaga_id = v.id
    INNER JOIN veiculos ve ON e.veiculo_id = ve.id
    WHERE e.saida IS NULL
    ORDER BY e.entrada DESC
");

$veiculosEstacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =========================
// TODAS AS VAGAS
// =========================

$stmt = $pdo->query("
    SELECT id, numero, status
    FROM vagas
    ORDER BY numero
");

$vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

```
<title>Área do Funcionário - Park Point</title>

<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: Arial, sans-serif;
        background: #f4f7fb;
        color: #172033;
    }

    header {
        background: #07111f;
        color: white;
        padding: 18px 7%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .logo {
        text-decoration: none;
        color: white;
        font-size: 24px;
        font-weight: 800;
    }

    .logo-p {
        color: #00aaff;
    }

    .perfil {
        color: #b9c7d8;
        font-size: 14px;
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: 35px auto;
    }

    .topo {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .topo h1 {
        font-size: 32px;
        margin-bottom: 8px;
    }

    .topo p {
        color: #687386;
    }

    .btn-voltar {
        display: inline-block;
        padding: 12px 20px;
        border-radius: 10px;
        background: #07111f;
        color: white;
        text-decoration: none;
        font-weight: bold;
        transition: 0.2s;
    }

    .btn-voltar:hover {
        background: #00aaff;
    }

    .cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
    }

    .card h3 {
        color: #687386;
        font-size: 15px;
        margin-bottom: 10px;
    }

    .numero {
        font-size: 34px;
        font-weight: bold;
    }

    .livre {
        color: #16a34a;
    }

    .ocupada {
        color: #dc2626;
    }

    .secao {
        background: white;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
    }

    .secao h2 {
        margin-bottom: 20px;
    }

    .vagas-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 15px;
    }

    .vaga {
        border-radius: 12px;
        padding: 20px 10px;
        text-align: center;
        font-weight: bold;
    }

    .vaga.livre {
        background: #dcfce7;
        color: #15803d;
    }

    .vaga.ocupada {
        background: #fee2e2;
        color: #b91c1c;
    }

    .vaga-numero {
        font-size: 22px;
        margin-bottom: 6px;
    }

    .tabela-container {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    th,
    td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    th {
        color: #687386;
        font-size: 14px;
    }

    td {
        font-size: 14px;
    }

    .btn-saida {
        display: inline-block;
        background: #dc2626;
        color: white;
        text-decoration: none;
        padding: 9px 14px;
        border-radius: 8px;
        font-weight: bold;
        transition: 0.2s;
    }

    .btn-saida:hover {
        background: #b91c1c;
    }

    .vazio {
        text-align: center;
        padding: 25px;
        color: #687386;
    }

    .acoes {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .btn {
        text-decoration: none;
        padding: 12px 18px;
        border-radius: 10px;
        background: #07111f;
        color: white;
        font-weight: bold;
        transition: 0.2s;
    }

    .btn:hover {
        background: #00aaff;
    }

    @media (max-width: 800px) {
        .cards {
            grid-template-columns: 1fr;
        }

        .vagas-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 500px) {
        .vagas-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .topo h1 {
            font-size: 26px;
        }
    }
</style>
```

</head>

<body>

<header>
    <a href="<?php echo $veioDoAdmin ? 'admin.php' : 'dashboard.php'; ?>" class="logo">
        <span class="logo-p">P</span>ARK POINT
    </a>

```
<div class="perfil">
    Área do <?php echo $perfil === "admin" ? "Administrador" : "Funcionário"; ?>
</div>
```

</header>

<div class="container">

```
<div class="topo">
    <div>
        <h1>Área do Funcionário</h1>
        <p>Gerencie as vagas e acompanhe os veículos estacionados.</p>
    </div>

    <a href="<?php echo $veioDoAdmin ? 'admin.php' : 'dashboard.php'; ?>" class="btn-voltar">
        ← Voltar
    </a>
</div>

<!-- ESTATÍSTICAS -->

<div class="cards">

    <div class="card">
        <h3>Total de vagas</h3>
        <div class="numero">
            <?php echo $totalVagas; ?>
        </div>
    </div>

    <div class="card">
        <h3>Vagas livres</h3>
        <div class="numero livre">
            <?php echo $vagasLivres; ?>
        </div>
    </div>

    <div class="card">
        <h3>Vagas ocupadas</h3>
        <div class="numero ocupada">
            <?php echo $vagasOcupadas; ?>
        </div>
    </div>

</div>

<!-- VAGAS -->

<div class="secao">

    <h2>Status das vagas</h2>

    <div class="vagas-grid">

        <?php if (count($vagas) > 0): ?>

            <?php foreach ($vagas as $vaga): ?>

                <div class="vaga <?php echo $vaga["status"]; ?>">

                    <div class="vaga-numero">
                        Vaga <?php echo htmlspecialchars($vaga["numero"]); ?>
                    </div>

                    <?php if ($vaga["status"] === "livre"): ?>
                        Livre
                    <?php else: ?>
                        Ocupada
                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="vazio">
                Nenhuma vaga cadastrada.
            </div>

        <?php endif; ?>

    </div>

    <div class="acoes">

        <a href="vagas.php" class="btn">
            Ver vagas
        </a>

        <a href="historico.php" class="btn">
            Ver histórico
        </a>

    </div>

</div>

<!-- VEÍCULOS ESTACIONADOS -->

<div class="secao">

    <h2>Veículos estacionados</h2>

    <?php if (count($veiculosEstacionados) > 0): ?>

        <div class="tabela-container">

            <table>

                <thead>
                    <tr>
                        <th>Vaga</th>
                        <th>Placa</th>
                        <th>Modelo</th>
                        <th>Cor</th>
                        <th>Entrada</th>
                        <th>Ação</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($veiculosEstacionados as $veiculo): ?>

                        <tr>

                            <td>
                                Vaga <?php echo htmlspecialchars($veiculo["vaga"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($veiculo["placa"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($veiculo["modelo"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($veiculo["cor"]); ?>
                            </td>

                            <td>
                                <?php echo date("d/m/Y H:i", strtotime($veiculo["entrada"])); ?>
                            </td>

                            <td>
                                <a
                                    href="cancelar-reserva.php?id=<?php echo $veiculo["id"]; ?>"
                                    class="btn-saida"
                                    onclick="return confirm('Deseja registrar a saída deste veículo?');"
                                >
                                    Registrar saída
                                </a>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php else: ?>

        <div class="vazio">
            Nenhum veículo está estacionado no momento.
        </div>

    <?php endif; ?>

</div>
```

</div>

</body>
</html>
