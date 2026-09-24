<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];

$nome = $_SESSION["usuario_nome"] ?? "Usuário";

$total_veiculos = 0;
$vagas_livres = 0;
$vagas_ocupadas = 0;
$reservas_ativas = 0;

try {

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM veiculos WHERE usuario_id = ?"
    );

    $stmt->execute([$usuario_id]);

    $total_veiculos = (int) $stmt->fetchColumn();


    $stmt = $pdo->query(
        "SELECT COUNT(*) FROM vagas WHERE status = 'livre'"
    );

    $vagas_livres = (int) $stmt->fetchColumn();


    $stmt = $pdo->query(
        "SELECT COUNT(*) FROM vagas WHERE status = 'ocupada'"
    );

    $vagas_ocupadas = (int) $stmt->fetchColumn();


    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM estacionamentos e
         INNER JOIN veiculos v ON e.veiculo_id = v.id
         WHERE v.usuario_id = ?
         AND e.saida IS NULL"
    );

    $stmt->execute([$usuario_id]);

    $reservas_ativas = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    die(
        "<div style='font-family:Arial;padding:30px;color:#b91c1c;'>
            <h2>Erro no Dashboard</h2>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
        </div>"
    );

}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

```
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Park Point - Dashboard</title>

<style>

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    html,
    body {
        width: 100%;
        min-height: 100%;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f3f6fa;
        color: #172033;
    }

    a {
        text-decoration: none;
    }


    /* MENU */

    .pp-menu {
        width: 100%;
        background: #111827;
        min-height: 68px;
    }

    .pp-menu-content {
        width: 100%;
        min-height: 68px;
        padding: 0 32px;

        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pp-logo {
        color: white;
        font-size: 24px;
        font-weight: 800;
    }

    .pp-logo span {
        color: #60a5fa;
    }

    .pp-nav {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .pp-nav a {
        color: #dbe4ef;
        font-size: 14px;
        font-weight: 600;
        padding: 10px 13px;
        border-radius: 8px;
        transition: 0.2s;
    }

    .pp-nav a:hover {
        background: #273449;
        color: white;
    }

    .pp-nav .pp-sair {
        background: #dc2626;
        color: white;
        margin-left: 5px;
    }

    .pp-nav .pp-sair:hover {
        background: #b91c1c;
    }


    /* ÁREA */

    .pp-main {
        width: 100%;
        padding: 30px 30px 45px;
    }

    .pp-content {
        width: 100%;
        max-width: 1550px;
        margin: 0 auto;
    }


    /* VOLTAR */

    .pp-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        color: #475569;
        font-size: 14px;
        font-weight: 700;

        padding: 8px 12px 8px 7px;
        margin-bottom: 16px;

        border-radius: 9px;

        transition: 0.2s;
    }

    .pp-back:hover {
        background: #e2e8f0;
        color: #111827;
        transform: translateX(-3px);
    }

    .pp-back-arrow {
        font-size: 23px;
    }


    /* BANNER */

    .pp-banner {
        width: 100%;

        min-height: 185px;

        padding: 35px 40px;

        border-radius: 20px;

        background: linear-gradient(
            135deg,
            #111827,
            #1e3a5f
        );

        color: white;

        display: flex;
        align-items: center;
        justify-content: space-between;

        margin-bottom: 23px;

        box-shadow:
            0 10px 25px rgba(15, 23, 42, 0.13);
    }

    .pp-banner h1 {
        font-size: 31px;
        margin-bottom: 9px;
        font-weight: 800;
    }

    .pp-banner p {
        color: #d7e0ec;
        font-size: 15px;
        line-height: 1.6;
    }

    .pp-banner-icon {
        font-size: 75px;
        padding-right: 20px;
    }


    /* ESTATÍSTICAS */

    .pp-stats {
        width: 100%;

        display: grid;
        grid-template-columns: repeat(3, 1fr);

        gap: 18px;

        margin-bottom: 28px;
    }

    .pp-stat {
        background: white;

        border: 1px solid #e2e8f0;

        border-radius: 16px;

        padding: 22px;

        display: flex;
        align-items: center;
        gap: 16px;

        box-shadow:
            0 5px 16px rgba(15, 23, 42, 0.05);

        transition: 0.2s;
    }

    .pp-stat:hover {
        transform: translateY(-3px);
    }

    .pp-stat-icon {
        width: 52px;
        height: 52px;

        flex-shrink: 0;

        border-radius: 14px;

        background: #eff6ff;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 24px;
    }

    .pp-stat-label {
        color: #64748b;

        font-size: 13px;

        font-weight: 700;

        margin-bottom: 4px;
    }

    .pp-stat-number {
        color: #111827;

        font-size: 30px;

        font-weight: 800;
    }


    /* TÍTULO */

    .pp-title {
        display: flex;
        align-items: center;
        justify-content: space-between;

        margin-bottom: 16px;
    }

    .pp-title h2 {
        font-size: 22px;
        color: #111827;
    }

    .pp-title p {
        color: #64748b;
        font-size: 13px;
    }


    /* CARDS */

    .pp-actions {
        width: 100%;

        display: grid;

        grid-template-columns: repeat(3, 1fr);

        gap: 18px;
    }

    .pp-card {
        min-height: 130px;

        background: white;

        border: 1px solid #e2e8f0;

        border-radius: 16px;

        padding: 21px;

        display: flex;
        align-items: center;

        gap: 16px;

        color: #111827;

        box-shadow:
            0 5px 16px rgba(15, 23, 42, 0.05);

        transition: 0.2s;
    }

    .pp-card:hover {
        transform: translateY(-4px);

        border-color: #bfdbfe;

        box-shadow:
            0 12px 27px rgba(15, 23, 42, 0.10);
    }

    .pp-card-icon {
        width: 55px;
        height: 55px;

        flex-shrink: 0;

        border-radius: 14px;

        background: #f1f5f9;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 26px;

        transition: 0.2s;
    }

    .pp-card:hover .pp-card-icon {
        background: #dbeafe;
        transform: scale(1.05);
    }

    .pp-card-content {
        flex: 1;
    }

    .pp-card-title {
        font-size: 16px;
        font-weight: 800;
        margin-bottom: 5px;
    }

    .pp-card-description {
        color: #64748b;
        font-size: 13px;
        line-height: 1.4;
    }

    .pp-card-arrow {
        color: #94a3b8;
        font-size: 22px;
        transition: 0.2s;
    }

    .pp-card:hover .pp-card-arrow {
        color: #2563eb;
        transform: translateX(4px);
    }


    /* RESPONSIVO */

    @media (max-width: 950px) {

        .pp-actions {
            grid-template-columns: repeat(2, 1fr);
        }

    }


    @media (max-width: 750px) {

        .pp-menu-content {
            padding: 14px 15px;

            flex-direction: column;

            gap: 12px;
        }

        .pp-nav {
            flex-wrap: wrap;
            justify-content: center;
        }

        .pp-main {
            padding: 18px 15px 35px;
        }

        .pp-stats {
            grid-template-columns: 1fr;
        }

        .pp-actions {
            grid-template-columns: 1fr;
        }

        .pp-banner {
            padding: 28px 23px;
        }

        .pp-banner h1 {
            font-size: 25px;
        }

        .pp-banner-icon {
            display: none;
        }

        .pp-title p {
            display: none;
        }

    }

</style>
```

</head>

<body>

<header class="pp-menu">

```
<div class="pp-menu-content">

    <a href="dashboard.php" class="pp-logo">
        Park <span>Point</span>
    </a>

    <nav class="pp-nav">

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

        <a href="logout.php" class="pp-sair">
            Sair
        </a>

    </nav>

</div>
```

</header>

<main class="pp-main">

```
<div class="pp-content">


    <!-- BOTÃO VOLTAR CORRIGIDO -->

    <a href="index.php" class="pp-back">

        <span class="pp-back-arrow">
            ←
        </span>

        Voltar

    </a>


    <!-- BANNER -->

    <section class="pp-banner">

        <div>

            <h1>
                Olá, <?php echo htmlspecialchars($nome); ?>! 👋
            </h1>

            <p>
                Bem-vindo ao Park Point.
                Gerencie seus veículos, consulte vagas
                e acompanhe suas reservas.
            </p>

        </div>

        <div class="pp-banner-icon">
            🅿️
        </div>

    </section>


    <!-- ESTATÍSTICAS -->

    <section class="pp-stats">


        <div class="pp-stat">

            <div class="pp-stat-icon">
                🚗
            </div>

            <div>

                <div class="pp-stat-label">
                    Meus veículos
                </div>

                <div class="pp-stat-number">
                    <?php echo $total_veiculos; ?>
                </div>

            </div>

        </div>


        <div class="pp-stat">

            <div class="pp-stat-icon">
                🟢
            </div>

            <div>

                <div class="pp-stat-label">
                    Vagas disponíveis
                </div>

                <div class="pp-stat-number">
                    <?php echo $vagas_livres; ?>
                </div>

            </div>

        </div>


        <div class="pp-stat">

            <div class="pp-stat-icon">
                🔴
            </div>

            <div>

                <div class="pp-stat-label">
                    Vagas ocupadas
                </div>

                <div class="pp-stat-number">
                    <?php echo $vagas_ocupadas; ?>
                </div>

            </div>

        </div>


    </section>


    <!-- TÍTULO -->

    <div class="pp-title">

        <h2>
            Acesso rápido
        </h2>

        <p>
            Escolha uma opção para continuar
        </p>

    </div>


    <!-- CARDS -->

    <section class="pp-actions">


        <a href="meus-veiculos.php" class="pp-card">

            <div class="pp-card-icon">
                🚗
            </div>

            <div class="pp-card-content">

                <div class="pp-card-title">
                    Meus veículos
                </div>

                <div class="pp-card-description">
                    Consulte e gerencie seus veículos cadastrados.
                </div>

            </div>

            <div class="pp-card-arrow">
                →
            </div>

        </a>


        <a href="vagas.php" class="pp-card">

            <div class="pp-card-icon">
                🅿️
            </div>

            <div class="pp-card-content">

                <div class="pp-card-title">
                    Encontrar uma vaga
                </div>

                <div class="pp-card-description">
                    Veja as vagas livres e ocupadas.
                </div>

            </div>

            <div class="pp-card-arrow">
                →
            </div>

        </a>


        <a href="minhas-reservas.php" class="pp-card">

            <div class="pp-card-icon">
                📅
            </div>

            <div class="pp-card-content">

                <div class="pp-card-title">
                    Minhas reservas
                </div>

                <div class="pp-card-description">
                    Acompanhe suas reservas atuais.
                </div>

            </div>

            <div class="pp-card-arrow">
                →
            </div>

        </a>


        <a href="historico.php" class="pp-card">

            <div class="pp-card-icon">
                🕘
            </div>

            <div class="pp-card-content">

                <div class="pp-card-title">
                    Histórico
                </div>

                <div class="pp-card-description">
                    Consulte seus estacionamentos anteriores.
                </div>

            </div>

            <div class="pp-card-arrow">
                →
            </div>

        </a>


        <a href="cadastrar-veiculo.php" class="pp-card">

            <div class="pp-card-icon">
                ➕
            </div>

            <div class="pp-card-content">

                <div class="pp-card-title">
                    Cadastrar veículo
                </div>

                <div class="pp-card-description">
                    Cadastre um novo veículo no sistema.
                </div>

            </div>

            <div class="pp-card-arrow">
                →
            </div>

        </a>


        <a href="vagas.php" class="pp-card">

            <div class="pp-card-icon">
                🎫
            </div>

            <div class="pp-card-content">

                <div class="pp-card-title">
                    Reservar vaga
                </div>

                <div class="pp-card-description">
                    Escolha uma vaga disponível para reservar.
                </div>

            </div>

            <div class="pp-card-arrow">
                →
            </div>

        </a>


    </section>


</div>
```

</main>

</body>

</html>
