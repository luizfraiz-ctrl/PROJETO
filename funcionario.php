```php
<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$perfil = $_SESSION["perfil"] ?? "";

if (!in_array($perfil, ["funcionario", "admin"], true)) {
    header("Location: dashboard.php");
    exit;
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$erro = "";

try {
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM vagas WHERE status = 'livre'
    ");
    $vagasLivres = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("
        SELECT COUNT(*) FROM vagas WHERE status = 'ocupada'
    ");
    $vagasOcupadas = (int) $stmt->fetchColumn();

    $totalVagas = $vagasLivres + $vagasOcupadas;

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

    $stmt = $pdo->query("
        SELECT id, numero, status
        FROM vagas
        ORDER BY numero
    ");
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    error_log("Erro no painel Park Point: " . $e->getMessage());
    $erro = "Não foi possível carregar os dados. Confira o banco de dados.";
    $vagasLivres = 0;
    $vagasOcupadas = 0;
    $totalVagas = 0;
    $veiculosEstacionados = [];
    $vagas = [];
}

function h($valor): string
{
    return htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel do Funcionário | Park Point</title>

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f7fb;
    color: #1f2937;
}

header {
    background: #fff;
    padding: 18px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: 0 2px 12px #0000000d;
}

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #172554;
    font-size: 22px;
    font-weight: bold;
    text-decoration: none;
}

.logo-p {
    display: inline-flex;
    width: 40px;
    height: 40px;
    justify-content: center;
    align-items: center;
    border-radius: 10px;
    background: #2563eb;
    color: white;
    font-size: 24px;
}

.logo-point { color: #2563eb; }

nav {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: center;
}

nav a {
    text-decoration: none;
    color: #374151;
    font-size: 14px;
    font-weight: bold;
}

nav a:hover { color: #2563eb; }

nav .sair {
    background: #2563eb;
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
}

main {
    width: 90%;
    max-width: 1200px;
    margin: 35px auto;
}

h1 { color: #172554; margin-bottom: 8px; }

.subtitulo { color: #64748b; margin-bottom: 25px; }

.alerta {
    padding: 14px;
    margin: 20px 0;
    border-radius: 8px;
    background: #fff7ed;
    color: #9a3412;
}

.estatisticas {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    margin: 25px 0;
}

.cartao, .secao {
    background: white;
    border-radius: 12px;
    padding: 22px;
    box-shadow: 0 4px 15px #0f172a0a;
}

.cartao p { color: #64748b; margin-top: 0; }
.cartao h2 { font-size: 30px; margin-bottom: 0; color: #172554; }
.livres h2 { color: #16a34a; }
.ocupadas h2 { color: #dc2626; }

.secao { margin-bottom: 25px; }
.secao h2 { color: #172554; margin-top: 0; }

.grade-vagas {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: 12px;
}

.vaga {
    text-align: center;
    padding: 15px 8px;
    border-radius: 9px;
    border: 1px solid;
}

.vaga strong { display: block; margin-bottom: 7px; }
.vaga span { font-size: 12px; font-weight: bold; }

.vaga.livre {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #15803d;
}

.vaga.ocupada {
    background: #fef2f2;
    border-color: #fecaca;
    color: #b91c1c;
}

.tabela-container { overflow-x: auto; }

table {
    width: 100%;
    min-width: 700px;
    border-collapse: collapse;
}

th, td {
    padding: 14px 10px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
    font-size: 14px;
}

th {
    color: #64748b;
    background: #f8fafc;
    font-size: 12px;
    text-transform: uppercase;
}

.form-saida { margin: 0; }

.btn-saida {
    display: inline-block;
    padding: 11px 14px;
    border: 0;
    border-radius: 7px;
    background: #2563eb;
    color: #fff;
    font-weight: bold;
    font-size: 13px;
    cursor: pointer;
    white-space: nowrap;
}

.btn-saida:hover { background: #1d4ed8; }

.vazio {
    padding: 20px 0;
    text-align: center;
    color: #64748b;
}

footer {
    text-align: center;
    color: #94a3b8;
    padding: 25px;
}

@media (max-width: 700px) {
    header { padding: 16px 5%; }
    main { width: 92%; margin-top: 25px; }
    .estatisticas { grid-template-columns: 1fr; }
}
</style>
</head>

<body>
<header>
    <a href="funcionario.php" class="logo">
        <span class="logo-p">P</span>
        <span>Park <span class="logo-point">Point</span></span>
    </a>

    <nav>
        <a href="funcionario.php">Painel</a>
        <a href="vagas.php">Vagas</a>

        <?php if ($perfil === "admin"): ?>
            <a href="admin.php">Administração</a>
        <?php endif; ?>

        <a href="dashboard.php">Minha conta</a>
        <a href="logout.php" class="sair">Sair</a>
    </nav>
</header>

<main>
    <h1>Painel do Funcionário</h1>
    <p class="subtitulo">Gerencie as vagas e registre a entrada e saída dos veículos.</p>

    <?php if ($erro !== ""): ?>
        <div class="alerta"><?= h($erro) ?></div>
    <?php endif; ?>

    <section class="estatisticas">
        <div class="cartao">
            <p>Total de vagas</p>
            <h2><?= $totalVagas ?></h2>
        </div>

        <div class="cartao livres">
            <p>Vagas livres</p>
            <h2><?= $vagasLivres ?></h2>
        </div>

        <div class="cartao ocupadas">
            <p>Vagas ocupadas</p>
            <h2><?= $vagasOcupadas ?></h2>
        </div>
    </section>

    <section class="secao">
        <h2>Mapa de vagas</h2>

        <?php if (empty($vagas)): ?>
            <p class="vazio">Nenhuma vaga cadastrada.</p>
        <?php else: ?>
            <div class="grade-vagas">
                <?php foreach ($vagas as $vaga): ?>
                    <div class="vaga <?= $vaga["status"] === "ocupada" ? "ocupada" : "livre" ?>">
                        <strong>Vaga <?= h($vaga["numero"]) ?></strong>
                        <span><?= $vaga["status"] === "ocupada" ? "OCUPADA" : "LIVRE" ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="secao">
        <h2>Veículos estacionados</h2>

        <?php if (empty($veiculosEstacionados)): ?>
            <p class="vazio">
                Não há veículos estacionados no momento.
                Para registrar uma saída, primeiro precisa existir um registro de entrada ativo.
            </p>
        <?php else: ?>
            <div class="tabela-container">
                <table>
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th>Modelo</th>
                            <th>Cor</th>
                            <th>Vaga</th>
                            <th>Entrada</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($veiculosEstacionados as $veiculo): ?>
                        <tr>
                            <td><?= h($veiculo["placa"]) ?></td>
                            <td><?= h($veiculo["modelo"]) ?></td>
                            <td><?= h($veiculo["cor"]) ?></td>
                            <td><?= h($veiculo["vaga"]) ?></td>
                            <td>
                                <?php
                                $dataEntrada = strtotime($veiculo["entrada"]);
                                echo $dataEntrada
                                    ? date("d/m/Y H:i", $dataEntrada)
                                    : h($veiculo["entrada"]);
                                ?>
                            </td>
                            <td>
                                <form
                                    class="form-saida"
                                    method="POST"
                                    action="registrar-saida.php"
                                    onsubmit="return confirm('Deseja registrar a saída deste veículo?');"
                                >
                                    <input type="hidden" name="id" value="<?= (int) $veiculo["id"] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION["csrf_token"]) ?>">
                                    <button class="btn-saida" type="submit">
                                        Registrar saída
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<footer>
    Park Point &copy; <?= date("Y") ?> — Sistema de gerenciamento de estacionamento
</footer>
</body>
</html>
```