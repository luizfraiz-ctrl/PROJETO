<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];

// Verifica o ID da vaga
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Vaga inválida.");
}

$vaga_id = (int) $_GET["id"];

// Busca a vaga e o estacionamento
$sql = "
    SELECT 
        v.id,
        v.numero,
        v.status,
        v.estabelecimento_id,
        e.nome AS estabelecimento_nome,
        e.endereco,
        e.valor_hora
    FROM vagas v
    INNER JOIN estabelecimentos e 
        ON e.id = v.estabelecimento_id
    WHERE v.id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$vaga_id]);

$vaga = $stmt->fetch();

if (!$vaga) {
    die("Vaga não encontrada.");
}

// Verifica se a vaga está livre
if ($vaga["status"] !== "livre") {
    die("Esta vaga já está ocupada.");
}

// Busca os veículos do usuário
$stmt = $pdo->prepare("
    SELECT id, placa, modelo, cor
    FROM veiculos
    WHERE usuario_id = ?
    ORDER BY id DESC
");

$stmt->execute([$usuario_id]);
$veiculos = $stmt->fetchAll();

// Verifica se o usuário já possui algum veículo estacionado
$stmt = $pdo->prepare("
    SELECT 
        est.id,
        est.vaga_id,
        v.numero AS numero_vaga,
        ve.placa
    FROM estacionamentos est
    INNER JOIN veiculos ve 
        ON ve.id = est.veiculo_id
    INNER JOIN vagas v 
        ON v.id = est.vaga_id
    WHERE ve.usuario_id = ?
      AND est.saida IS NULL
    LIMIT 1
");

$stmt->execute([$usuario_id]);
$estacionamentoAtivo = $stmt->fetch();

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Reservar vaga - Park Point</title>

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
    text-decoration: none;
    color: white;
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
    max-width: 650px;
    margin: 50px auto;
    padding: 20px;
}

.card {
    background: white;
    border-radius: 18px;
    padding: 35px;
    box-shadow: 0 8px 30px rgba(0,0,0,.08);
}

h1 {
    text-align: center;
    margin-top: 0;
}

.info {
    background: #f4f7fb;
    border-radius: 12px;
    padding: 20px;
    margin: 25px 0;
}

.info p {
    margin: 8px 0;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
}

select {
    width: 100%;
    padding: 14px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    font-size: 16px;
    margin-bottom: 20px;
}

button {
    width: 100%;
    border: none;
    padding: 15px;
    border-radius: 10px;
    background: #00aaff;
    color: white;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #008dcc;
}

.alerta {
    background: #fff3cd;
    border: 1px solid #ffe69c;
    padding: 18px;
    border-radius: 12px;
    margin-top: 20px;
}

.erro {
    background: #fee2e2;
    border: 1px solid #fecaca;
    padding: 18px;
    border-radius: 12px;
    margin-top: 20px;
}

.link {
    display: inline-block;
    margin-top: 15px;
    color: #008dcc;
    text-decoration: none;
    font-weight: bold;
}

</style>

</head>

<body>

<nav>

```
<a href="dashboard.php" class="logo">
    <span class="logo-p">P</span>ARK POINT
</a>

<a href="vagas.php" class="voltar">
    ← Voltar
</a>
```

</nav>

<div class="container">

<div class="card">

<h1>Reservar vaga 🅿️</h1>

<div class="info">

<p>
<strong>Estacionamento:</strong>
<?= htmlspecialchars($vaga["estabelecimento_nome"]) ?>
</p>

<p>
<strong>Endereço:</strong>
<?= htmlspecialchars($vaga["endereco"]) ?>
</p>

<p>
<strong>Vaga:</strong>
<?= htmlspecialchars($vaga["numero"]) ?>
</p>

<p>
<strong>Valor por hora:</strong>
R$ <?= number_format($vaga["valor_hora"], 2, ',', '.') ?>
</p>

</div>

<?php if ($estacionamentoAtivo): ?>

<div class="alerta">

<strong>Você já possui um veículo estacionado.</strong>

<p>
Veículo: <?= htmlspecialchars($estacionamentoAtivo["placa"]) ?><br>
Vaga: <?= htmlspecialchars($estacionamentoAtivo["numero_vaga"]) ?>
</p>

<p>
Finalize esse estacionamento antes de reservar outra vaga.
</p>

<a href="minhas-reservas.php" class="link">
Ver meu estacionamento
</a>

</div>

<?php elseif (!$veiculos): ?>

<div class="erro">

<strong>Você ainda não possui veículos cadastrados.</strong>

<p>
Cadastre um veículo antes de reservar uma vaga.
</p>

<a href="cadastrar-veiculo.php" class="link">
Cadastrar veículo
</a>

</div>

<?php else: ?>

<form action="confirmar-reserva.php" method="POST">

<input
type="hidden"
name="vaga_id"
value="<?= (int)$vaga["id"] ?>"

>

<label for="veiculo_id">
Escolha o veículo:
</label>

<select name="veiculo_id" id="veiculo_id" required>

<option value="">
Selecione um veículo
</option>

<?php foreach ($veiculos as $veiculo): ?>

<option value="<?= (int)$veiculo["id"] ?>">

<?= htmlspecialchars($veiculo["placa"]) ?>

<?php if (!empty($veiculo["modelo"])): ?>

* <?= htmlspecialchars($veiculo["modelo"]) ?>

<?php endif; ?>

<?php if (!empty($veiculo["cor"])): ?>

* <?= htmlspecialchars($veiculo["cor"]) ?>

<?php endif; ?>

</option>

<?php endforeach; ?>

</select>

<button type="submit">
Confirmar reserva
</button>

</form>

<?php endif; ?>

</div>

</div>

</body>

</html>
