<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT id, numero, status
        FROM vagas
        ORDER BY numero";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

```
<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Vagas - Park Point</title>

<link rel="stylesheet" href="style.css">

<style>

    .parking-page {
        max-width: 1100px;
        margin: 0 auto;
        padding: 40px 25px;
    }

    .parking-header {
        margin-bottom: 25px;
    }

    .parking-header h1 {
        margin-bottom: 8px;
    }

    .parking-header p {
        opacity: 0.7;
    }

    .parking-legend {
        display: flex;
        gap: 25px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .legend-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
    }

    .dot-free {
        background: #35b86b;
    }

    .dot-busy {
        background: #e05252;
    }

    .parking-area {
        background: #202124;
        border-radius: 24px;
        padding: 30px;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
    }

    .parking-entrance {
        color: white;
        text-align: center;
        font-weight: bold;
        padding: 15px;
        border-bottom: 2px dashed #777;
        margin-bottom: 30px;
    }

    .parking-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }

    .parking-space {
        min-height: 145px;
        border-radius: 16px;
        padding: 18px;
        text-align: center;
        box-sizing: border-box;
        transition: transform 0.25s ease;
    }

    .parking-space.free {
        background: #ecfff3;
        border: 2px solid #35b86b;
    }

    .parking-space.busy {
        background: #fff0f0;
        border: 2px solid #e05252;
        opacity: 0.8;
    }

    .parking-space.free:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(53, 184, 107, 0.25);
    }

    .space-number {
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 6px;
    }

    .space-status {
        font-size: 13px;
        font-weight: bold;
        margin-bottom: 15px;
    }

    .free .space-status {
        color: #239653;
    }

    .busy .space-status {
        color: #c83f3f;
    }

    .reserve-button {
        display: inline-block;
        padding: 9px 16px;
        border-radius: 8px;
        background: #222;
        color: white;
        text-decoration: none;
        font-size: 13px;
        transition: transform 0.2s ease;
    }

    .reserve-button:hover {
        transform: scale(1.05);
    }

    .disabled-button {
        display: inline-block;
        padding: 9px 16px;
        border-radius: 8px;
        background: #999;
        color: white;
        font-size: 13px;
    }

    @media (max-width: 800px) {

        .parking-grid {
            grid-template-columns: repeat(2, 1fr);
        }

    }

    @media (max-width: 500px) {

        .parking-grid {
            grid-template-columns: 1fr;
        }

        .parking-area {
            padding: 18px;
        }

    }

</style>
```

</head>

<body>

<header class="navbar">

```
<div class="navbar-content">

    <a href="dashboard.php" class="logo">

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
            Minhas Reservas
        </a>

        <a href="historico.php">
            Histórico
        </a>

        <a href="logout.php" class="btn-login">
            Sair
        </a>

    </nav>

</div>
```

</header>

<main class="parking-page">

```
<div class="parking-header">

    <h1>
        Encontre sua vaga 🅿️
    </h1>

    <p>
        Escolha uma vaga disponível no estacionamento.
    </p>

</div>


<div class="parking-legend">

    <div class="legend-item">

        <span class="legend-dot dot-free"></span>

        Vaga livre

    </div>

    <div class="legend-item">

        <span class="legend-dot dot-busy"></span>

        Vaga ocupada

    </div>

</div>


<section class="parking-area">

    <div class="parking-entrance">
        🚘 ENTRADA / SAÍDA
    </div>


    <div class="parking-grid">

        <?php foreach ($vagas as $vaga): ?>

            <?php

            $livre = $vaga["status"] === "livre";

            ?>

            <div class="parking-space <?= $livre ? "free" : "busy" ?>">

                <div class="space-number">
                    <?= htmlspecialchars($vaga["numero"]) ?>
                </div>

                <div class="space-status">
                    <?= $livre ? "● LIVRE" : "● OCUPADA" ?>
                </div>

                <?php if ($livre): ?>

                    <a
                        href="reservar.php?id=<?= $vaga["id"] ?>"
                        class="reserve-button"
                    >
                        Reservar vaga
                    </a>

                <?php else: ?>

                    <span class="disabled-button">
                        Ocupada
                    </span>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>

</section>
```

</main>

<footer>

```
<p>
    © 2026 Park Point — Sistema de Estacionamento
</p>
```

</footer>

</body>

</html>
