<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

$stmt = $pdo->prepare(
    "SELECT id, placa, modelo, cor FROM veiculos WHERE usuario_id = ? ORDER BY id DESC"
);

$stmt->execute([$usuario_id]);

$veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

```
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Meus veículos - Park Point</title>

<style>

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: Arial, Helvetica, sans-serif;
        background: #f1f5f9;
        color: #0f172a;
    }

    .veiculos-navbar {
        width: 100%;
        background: #0f172a;
        color: white;
        padding: 0 30px;
    }

    .veiculos-navbar-content {
        max-width: 1200px;
        height: 72px;
        margin: auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .veiculos-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        color: white;
        text-decoration: none;
        font-size: 21px;
        font-weight: bold;
    }

    .veiculos-logo-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .veiculos-logo span {
        color: #60a5fa;
    }

    .veiculos-nav-links {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .veiculos-nav-links a {
        color: #cbd5e1;
        text-decoration: none;
        font-size: 14px;
        transition: 0.2s;
    }

    .veiculos-nav-links a:hover {
        color: white;
    }

    .veiculos-sair {
        background: #2563eb;
        color: white !important;
        padding: 9px 15px;
        border-radius: 8px;
    }

    .veiculos-sair:hover {
        background: #1d4ed8;
    }

    .veiculos-main {
        max-width: 1200px;
        margin: auto;
        padding: 45px 30px 70px;
    }

    .veiculos-topo {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 35px;
    }

    .veiculos-titulo-area h1 {
        margin: 0 0 8px;
        font-size: 34px;
    }

    .veiculos-titulo-area p {
        margin: 0;
        color: #64748b;
        font-size: 15px;
    }

    .veiculos-adicionar {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 18px;
        background: #2563eb;
        color: white;
        text-decoration: none;
        border-radius: 10px;
        font-weight: bold;
        font-size: 14px;
        transition: 0.2s;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18);
    }

    .veiculos-adicionar:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
    }

    .veiculos-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 22px;
    }

    .veiculo-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.05);
        transition: 0.25s;
    }

    .veiculo-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(15, 23, 42, 0.10);
        border-color: #bfdbfe;
    }

    .veiculo-card-topo {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .veiculo-icone {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: #eaf2ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 27px;
    }

    .veiculo-status {
        background: #dcfce7;
        color: #15803d;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
    }

    .veiculo-modelo {
        margin: 0 0 8px;
        font-size: 20px;
    }

    .veiculo-placa {
        display: inline-block;
        background: #0f172a;
        color: white;
        padding: 7px 11px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: bold;
        letter-spacing: 1px;
        margin-bottom: 18px;
    }

    .veiculo-info {
        border-top: 1px solid #e2e8f0;
        padding-top: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .veiculo-info-linha {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
    }

    .veiculo-info-linha span:first-child {
        color: #64748b;
    }

    .veiculo-info-linha span:last-child {
        color: #334155;
        font-weight: 600;
    }

    .veiculo-acoes {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .veiculo-editar,
    .veiculo-excluir {
        flex: 1;
        text-align: center;
        padding: 10px;
        border-radius: 9px;
        text-decoration: none;
        font-size: 13px;
        font-weight: bold;
        transition: 0.2s;
    }

    .veiculo-editar {
        background: #eff6ff;
        color: #2563eb;
    }

    .veiculo-editar:hover {
        background: #dbeafe;
    }

    .veiculo-excluir {
        background: #fef2f2;
        color: #dc2626;
    }

    .veiculo-excluir:hover {
        background: #fee2e2;
    }

    .veiculos-vazio {
        background: white;
        border: 1px dashed #cbd5e1;
        border-radius: 18px;
        padding: 60px 30px;
        text-align: center;
    }

    .veiculos-vazio-icone {
        font-size: 48px;
        margin-bottom: 15px;
    }

    .veiculos-vazio h2 {
        margin: 0 0 10px;
        font-size: 22px;
    }

    .veiculos-vazio p {
        margin: 0 auto 25px;
        max-width: 450px;
        color: #64748b;
        line-height: 1.6;
    }

    .veiculos-voltar {
        display: inline-block;
        margin-top: 30px;
        color: #64748b;
        text-decoration: none;
        font-size: 14px;
    }

    .veiculos-voltar:hover {
        color: #2563eb;
    }

    @media (max-width: 900px) {

        .veiculos-grid {
            grid-template-columns: repeat(2, 1fr);
        }

    }

    @media (max-width: 650px) {

        .veiculos-navbar {
            padding: 0 15px;
        }

        .veiculos-nav-links a:not(.veiculos-sair) {
            display: none;
        }

        .veiculos-main {
            padding: 30px 18px 50px;
        }

        .veiculos-topo {
            flex-direction: column;
            align-items: flex-start;
        }

        .veiculos-grid {
            grid-template-columns: 1fr;
        }

        .veiculos-adicionar {
            width: 100%;
            justify-content: center;
        }

    }

</style>
```

</head>

<body>

<header class="veiculos-navbar">

```
<div class="veiculos-navbar-content">

    <a href="dashboard.php" class="veiculos-logo">

        <div class="veiculos-logo-icon">
            P
        </div>

        Park <span>Point</span>

    </a>

    <nav class="veiculos-nav-links">

        <a href="dashboard.php">
            Início
        </a>

        <a href="vagas.php">
            Vagas
        </a>

        <a href="minhas-reservas.php">
            Reservas
        </a>

        <a href="logout.php" class="veiculos-sair">
            Sair
        </a>

    </nav>

</div>
```

</header>

<main class="veiculos-main">

```
<div class="veiculos-topo">

    <div class="veiculos-titulo-area">

        <h1>
            Meus veículos 🚗
        </h1>

        <p>
            Gerencie os veículos cadastrados na sua conta.
        </p>

    </div>

    <a
        href="cadastrar-veiculo.php"
        class="veiculos-adicionar"
    >
        + Adicionar veículo
    </a>

</div>


<?php if (count($veiculos) > 0): ?>

    <div class="veiculos-grid">

        <?php foreach ($veiculos as $veiculo): ?>

            <div class="veiculo-card">

                <div class="veiculo-card-topo">

                    <div class="veiculo-icone">
                        🚗
                    </div>

                    <div class="veiculo-status">
                        CADASTRADO
                    </div>

                </div>


                <h2 class="veiculo-modelo">

                    <?= htmlspecialchars($veiculo["modelo"]) ?>

                </h2>


                <div class="veiculo-placa">

                    <?= htmlspecialchars($veiculo["placa"]) ?>

                </div>


                <div class="veiculo-info">

                    <div class="veiculo-info-linha">

                        <span>
                            Modelo
                        </span>

                        <span>
                            <?= htmlspecialchars($veiculo["modelo"]) ?>
                        </span>

                    </div>


                    <div class="veiculo-info-linha">

                        <span>
                            Cor
                        </span>

                        <span>
                            <?= htmlspecialchars($veiculo["cor"]) ?>
                        </span>

                    </div>

                </div>


                <div class="veiculo-acoes">

                    <a
                        href="editar-veiculos.php?id=<?= $veiculo["id"] ?>"
                        class="veiculo-editar"
                    >
                        ✏️ Editar
                    </a>

                    <a
                        href="excluir-veiculo.php?id=<?= $veiculo["id"] ?>"
                        class="veiculo-excluir"
                        onclick="return confirm('Tem certeza que deseja excluir este veículo?');"
                    >
                        🗑️ Excluir
                    </a>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

<?php else: ?>

    <div class="veiculos-vazio">

        <div class="veiculos-vazio-icone">
            🚗
        </div>

        <h2>
            Nenhum veículo cadastrado
        </h2>

        <p>
            Cadastre seu primeiro veículo para poder
            realizar reservas no Park Point.
        </p>

        <a
            href="cadastrar-veiculo.php"
            class="veiculos-adicionar"
        >
            + Cadastrar veículo
        </a>

    </div>

<?php endif; ?>


<a
    href="dashboard.php"
    class="veiculos-voltar"
>
    ← Voltar para o painel
</a>
```

</main>

</body>

</html>
