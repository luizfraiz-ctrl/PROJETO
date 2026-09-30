<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];
$perfil = $_SESSION["perfil"] ?? "cliente";

/* Busca os estacionamentos */
if ($perfil === "admin") {
    $stmt = $pdo->prepare("
        SELECT id, nome, endereco, valor_hora
        FROM estabelecimentos
        WHERE usuario_id = ?
        ORDER BY nome
    ");
    $stmt->execute([$usuario_id]);
} else {
    $stmt = $pdo->query("
        SELECT id, nome, endereco, valor_hora
        FROM estabelecimentos
        ORDER BY nome
    ");
}

$estabelecimentos = $stmt->fetchAll();

/* Se não houver estacionamento */
if (!$estabelecimentos) {
    die("Nenhum estacionamento cadastrado.");
}

/* Escolhe o estacionamento */
$estabelecimento_id = isset($_GET["estabelecimento"])
    ? (int) $_GET["estabelecimento"]
    : (int) $estabelecimentos[0]["id"];

/* Procura o estacionamento selecionado */
$estabelecimento = null;

foreach ($estabelecimentos as $est) {
    if ((int)$est["id"] === $estabelecimento_id) {
        $estabelecimento = $est;
        break;
    }
}

/* Se o ID não existir, usa o primeiro */
if (!$estabelecimento) {
    $estabelecimento = $estabelecimentos[0];
    $estabelecimento_id = (int)$estabelecimento["id"];
}

/* Busca as vagas */
$stmt = $pdo->prepare("
    SELECT id, numero, status
    FROM vagas
    WHERE estabelecimento_id = ?
    ORDER BY numero
");

$stmt->execute([$estabelecimento_id]);

$vagas = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Vagas - Park Point</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f7fb;
    color: #172033;
}

nav {
    height: 70px;
    background: #101827;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
}

.logo {
    color: white;
    text-decoration: none;
    font-size: 24px;
    font-weight: bold;
}

.logo-p {
    color: #00aaff;
}

.voltar {
    color: white;
    text-decoration: none;
}

.voltar:hover {
    color: #00aaff;
}

.container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 20px;
}

h1 {
    text-align: center;
    margin-bottom: 10px;
}

.subtitulo {
    text-align: center;
    color: #667085;
    margin-bottom: 30px;
}

/* ESTACIONAMENTOS */

.estabelecimentos {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 30px;
}

.estabelecimento {
    display: block;
    padding: 14px 22px;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    color: #172033;
    text-decoration: none;
    font-weight: bold;
}

.estabelecimento:hover {
    border-color: #00aaff;
    color: #00aaff;
}

.estabelecimento.ativo {
    background: #00aaff;
    border-color: #00aaff;
    color: white;
}

/* INFORMAÇÕES */

.info {
    background: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,.06);
}

.info h2 {
    margin-top: 0;
}

.info p {
    color: #667085;
}

/* LEGENDA */

.legenda {
    display: flex;
    justify-content: center;
    gap: 25px;
    margin: 25px 0;
}

.legenda-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.bolinha {
    width: 16px;
    height: 16px;
    border-radius: 50%;
}

.bolinha.verde {
    background: #16a34a;
}

.bolinha.vermelha {
    background: #dc2626;
}

/* ESTACIONAMENTO */

.estacionamento {
    background: #172033;
    padding: 35px;
    border-radius: 20px;
    max-width: 850px;
    margin: auto;
}

.entrada {
    text-align: center;
    color: white;
    font-weight: bold;
    margin-bottom: 30px;
}

/* VAGAS */

.vagas {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
}

/* LINK DA VAGA */

.vaga {
    min-height: 120px;
    border-radius: 14px;
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    font-weight: bold;
}

.vaga.livre {
    background: #16a34a;
    cursor: pointer;
    transition: .2s;
}

.vaga.livre:hover {
    background: #15803d;
    transform: translateY(-4px);
}

.vaga.ocupada {
    background: #dc2626;
}

.numero {
    font-size: 30px;
}

.status {
    margin-top: 8px;
    font-size: 12px;
    text-align: center;
}

.mensagem {
    text-align: center;
    color: white;
    padding: 30px;
}

@media (max-width: 700px) {

    .vagas {
        grid-template-columns: repeat(2, 1fr);
    }

    nav {
        padding: 0 20px;
    }

    .estacionamento {
        padding: 20px;
    }

}

</style>

</head>

<body>

<nav>

```
<a href="dashboard.php" class="logo">
    <span class="logo-p">P</span>ARK POINT
</a>

<a href="dashboard.php" class="voltar">
    ← Voltar
</a>
```

</nav>

<div class="container">

```
<h1>Encontre sua vaga 🅿️</h1>

<p class="subtitulo">
    Escolha um estacionamento e uma vaga disponível.
</p>


<!-- ESTACIONAMENTOS -->

<div class="estabelecimentos">

    <?php foreach ($estabelecimentos as $est): ?>

        <a
            href="vagas.php?estabelecimento=<?= (int)$est["id"] ?>"
            class="estabelecimento
            <?= ((int)$est["id"] === $estabelecimento_id) ? "ativo" : "" ?>"
        >

            <?= htmlspecialchars($est["nome"]) ?>

        </a>

    <?php endforeach; ?>

</div>


<!-- INFORMAÇÕES -->

<div class="info">

    <h2>
        <?= htmlspecialchars($estabelecimento["nome"]) ?>
    </h2>

    <p>
        📍 <?= htmlspecialchars($estabelecimento["endereco"]) ?>
    </p>

    <p>
        💰 R$
        <?= number_format(
            $estabelecimento["valor_hora"],
            2,
            ",",
            "."
        ) ?>
        por hora
    </p>

</div>


<!-- LEGENDA -->

<div class="legenda">

    <div class="legenda-item">

        <span class="bolinha verde"></span>

        Livre

    </div>

    <div class="legenda-item">

        <span class="bolinha vermelha"></span>

        Ocupada

    </div>

</div>


<!-- VAGAS -->

<div class="estacionamento">

    <div class="entrada">
        ENTRADA / SAÍDA
    </div>

    <?php if (!$vagas): ?>

        <div class="mensagem">

            Nenhuma vaga cadastrada neste estacionamento.

        </div>

    <?php else: ?>

        <div class="vagas">

            <?php foreach ($vagas as $vaga): ?>

                <?php if ($vaga["status"] === "livre"): ?>

                    <!-- VAGA LIVRE -->

                    <a
                        href="reservar.php?id=<?= (int)$vaga["id"] ?>"
                        class="vaga livre"
                    >

                        <div class="numero">
                            <?= htmlspecialchars($vaga["numero"]) ?>
                        </div>

                        <div class="status">
                            CLIQUE PARA RESERVAR
                        </div>

                    </a>

                <?php else: ?>

                    <!-- VAGA OCUPADA -->

                    <div class="vaga ocupada">

                        <div class="numero">
                            <?= htmlspecialchars($vaga["numero"]) ?>
                        </div>

                        <div class="status">
                            OCUPADA
                        </div>

                    </div>

                <?php endif; ?>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>
```

</div>

</body>

</html>
