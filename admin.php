<?php

session_start();

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];
$perfil = $_SESSION["perfil"] ?? "cliente";

if ($perfil !== "admin") {
    header("Location: dashboard.php");
    exit;
}

/*
 * Marca que o administrador está navegando
 * pela área administrativa.
 */
$_SESSION["navegacao_admin"] = true;


/* =========================
   ESTATÍSTICAS
========================= */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM usuarios
");

$totalUsuarios = (int) $stmt->fetchColumn();


$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM veiculos
");

$totalVeiculos = (int) $stmt->fetchColumn();


$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM estabelecimentos
");

$totalEstabelecimentos = (int) $stmt->fetchColumn();


$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM vagas
    WHERE status = 'livre'
");

$vagasLivres = (int) $stmt->fetchColumn();


/* =========================
   ESTACIONAMENTOS
========================= */

$stmt = $pdo->query("
    SELECT
        e.id,
        e.nome,
        e.endereco,
        e.telefone,
        e.valor_hora,
        e.total_vagas,
        COUNT(v.id) AS vagas_cadastradas,
        SUM(
            CASE
                WHEN v.status = 'livre' THEN 1
                ELSE 0
            END
        ) AS vagas_livres
    FROM estabelecimentos e
    LEFT JOIN vagas v
        ON v.estabelecimento_id = e.id
    GROUP BY
        e.id,
        e.nome,
        e.endereco,
        e.telefone,
        e.valor_hora,
        e.total_vagas
    ORDER BY e.id DESC
");

$estabelecimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Administrativo - Park Point</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;

    font-family: Arial, Helvetica, sans-serif;

    background: #f4f7fb;

    color: #172033;
}


/* =========================
   NAVBAR
========================= */

nav {
    min-height: 70px;

    background: #101827;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 15px 40px;

    gap: 20px;
}

.logo {
    color: white;

    text-decoration: none;

    font-size: 24px;

    font-weight: bold;

    white-space: nowrap;
}

.logo-p {
    color: #00aaff;
}

.menu {
    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 22px;

    flex-wrap: wrap;
}

.menu a {
    color: white;

    text-decoration: none;

    font-size: 14px;

    transition: .2s;
}

.menu a:hover {
    color: #00aaff;
}

.menu .ativo {
    color: #00aaff;
}

.menu .sair {
    color: #ff7070;
}

.menu .sair:hover {
    color: #ff3b3b;
}


/* =========================
   CONTAINER
========================= */

.container {
    max-width: 1150px;

    margin: 40px auto;

    padding: 0 20px;
}


/* =========================
   CABEÇALHO
========================= */

.cabecalho {
    margin-bottom: 30px;
}

.cabecalho h1 {
    margin: 0 0 8px;
}

.cabecalho p {
    color: #667085;

    margin: 0;
}


/* =========================
   ESTATÍSTICAS
========================= */

.stats {
    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 18px;

    margin-bottom: 30px;
}

.stat {
    background: white;

    border-radius: 16px;

    padding: 23px;

    box-shadow: 0 5px 20px rgba(0,0,0,.06);
}

.stat span {
    display: block;

    color: #667085;

    font-size: 14px;
}

.stat strong {
    display: block;

    color: #00aaff;

    font-size: 32px;

    margin-top: 10px;
}


/* =========================
   AÇÕES
========================= */

.acoes {
    background: white;

    border-radius: 16px;

    padding: 25px;

    margin-bottom: 30px;

    box-shadow: 0 5px 20px rgba(0,0,0,.06);
}

.acoes h2 {
    margin-top: 0;
}

.botoes {
    display: flex;

    flex-wrap: wrap;

    gap: 12px;
}

.botao {
    display: inline-block;

    padding: 13px 18px;

    border-radius: 10px;

    background: #00aaff;

    color: white;

    text-decoration: none;

    font-weight: bold;

    transition: .2s;
}

.botao:hover {
    background: #008dcc;

    transform: translateY(-2px);
}

.botao.escuro {
    background: #172033;
}

.botao.escuro:hover {
    background: #26344d;
}


/* =========================
   ESTACIONAMENTOS
========================= */

.secao {
    background: white;

    border-radius: 16px;

    padding: 30px;

    margin-bottom: 30px;

    box-shadow: 0 5px 20px rgba(0,0,0,.06);
}

.secao h2 {
    margin-top: 0;
}

.estabelecimento {
    border: 1px solid #e4e7ec;

    border-radius: 14px;

    padding: 22px;

    margin-top: 18px;

    background: #fafbfc;
}

.estabelecimento h3 {
    margin-top: 0;

    margin-bottom: 8px;
}

.endereco {
    color: #667085;

    margin-bottom: 18px;
}

.dados {
    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 12px;
}

.dado {
    background: white;

    border-radius: 10px;

    padding: 14px;

    border: 1px solid #e5e7eb;
}

.dado span {
    display: block;

    font-size: 12px;

    color: #667085;

    margin-bottom: 5px;
}

.dado strong {
    font-size: 15px;
}


/* =========================
   ESTADO DAS VAGAS
========================= */

.vagas-status {
    margin-top: 18px;

    display: flex;

    gap: 10px;

    flex-wrap: wrap;
}

.status {
    padding: 8px 12px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: bold;
}

.status.livre {
    background: #dcfce7;

    color: #15803d;
}

.status.ocupada {
    background: #fee2e2;

    color: #b91c1c;
}


/* =========================
   SEM ESTACIONAMENTO
========================= */

.vazio {
    text-align: center;

    padding: 40px 20px;

    color: #667085;
}


/* =========================
   RESPONSIVO
========================= */

@media (max-width: 950px) {

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }

    nav {
        flex-direction: column;

        align-items: center;
    }

    .menu {
        justify-content: center;
    }

}

@media (max-width: 650px) {

    .stats {
        grid-template-columns: 1fr;
    }

    .dados {
        grid-template-columns: 1fr;
    }

    .container {
        margin-top: 25px;
    }

}

</style>

</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

<nav>

```
<a href="admin.php" class="logo">

    <span class="logo-p">P</span>ARK POINT

</a>


<div class="menu">

    <a href="admin.php" class="ativo">
        Administração
    </a>

    <a href="vagas.php">
        Vagas
    </a>

    <a href="funcionario.php">
        Funcionário
    </a>

    <a href="cadastro-estacionamento.php">
        Novo estacionamento
    </a>

    <a href="dashboard.php">
        Meu painel
    </a>

    <a href="logout.php" class="sair">
        Sair
    </a>

</div>
```

</nav>

<div class="container">

```
<!-- CABEÇALHO -->

<div class="cabecalho">

    <h1>
        Área administrativa
    </h1>

    <p>
        Gerencie os estacionamentos e acompanhe o sistema Park Point.
    </p>

</div>


<!-- ESTATÍSTICAS -->

<div class="stats">


    <div class="stat">

        <span>
            Usuários
        </span>

        <strong>
            <?= $totalUsuarios ?>
        </strong>

    </div>


    <div class="stat">

        <span>
            Veículos
        </span>

        <strong>
            <?= $totalVeiculos ?>
        </strong>

    </div>


    <div class="stat">

        <span>
            Estacionamentos
        </span>

        <strong>
            <?= $totalEstabelecimentos ?>
        </strong>

    </div>


    <div class="stat">

        <span>
            Vagas livres
        </span>

        <strong>
            <?= $vagasLivres ?>
        </strong>

    </div>


</div>


<!-- AÇÕES -->

<div class="acoes">

    <h2>
        Ações rápidas
    </h2>


    <div class="botoes">

        <a
            href="cadastro-estacionamento.php"
            class="botao"
        >
            + Novo estacionamento
        </a>


        <a
            href="vagas.php"
            class="botao escuro"
        >
            🅿️ Ver vagas
        </a>


        <a
            href="funcionario.php"
            class="botao escuro"
        >
            👨‍💼 Área do funcionário
        </a>


        <a
            href="dashboard.php"
            class="botao escuro"
        >
            🏠 Meu painel
        </a>

    </div>

</div>


<!-- ESTACIONAMENTOS -->

<div class="secao">

    <h2>
        Meus estacionamentos
    </h2>


    <?php if (!$estabelecimentos): ?>

        <div class="vazio">

            <p>
                Você ainda não possui estacionamentos cadastrados.
            </p>

            <a
                href="cadastro-estacionamento.php"
                class="botao"
            >
                Cadastrar estacionamento
            </a>

        </div>

    <?php else: ?>


        <?php foreach ($estabelecimentos as $est): ?>

            <?php

            $totalVagasEst = (int) $est["vagas_cadastradas"];

            $livres = (int) ($est["vagas_livres"] ?? 0);

            $ocupadas = $totalVagasEst - $livres;

            ?>


            <div class="estabelecimento">

                <h3>
                    <?= htmlspecialchars($est["nome"]) ?>
                </h3>


                <div class="endereco">

                    📍
                    <?= htmlspecialchars($est["endereco"]) ?>

                </div>


                <div class="dados">


                    <div class="dado">

                        <span>
                            Total de vagas
                        </span>

                        <strong>
                            <?= $totalVagasEst ?>
                        </strong>

                    </div>


                    <div class="dado">

                        <span>
                            Valor por hora
                        </span>

                        <strong>

                            R$
                            <?= number_format(
                                $est["valor_hora"],
                                2,
                                ",",
                                "."
                            ) ?>

                        </strong>

                    </div>


                    <div class="dado">

                        <span>
                            Telefone
                        </span>

                        <strong>
                            <?= htmlspecialchars($est["telefone"]) ?>
                        </strong>

                    </div>


                </div>


                <div class="vagas-status">

                    <div class="status livre">

                        <?= $livres ?> vagas livres

                    </div>


                    <div class="status ocupada">

                        <?= $ocupadas ?> vagas ocupadas

                    </div>

                </div>

            </div>


        <?php endforeach; ?>


    <?php endif; ?>

</div>
```

</div>

</body>

</html>
