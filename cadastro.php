<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Criar conta - Park Point</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>


<header class="navbar">

    <div class="navbar-content">

        <a href="index.php" class="logo">

            <div class="logo-icon">
                P
            </div>

            Park <span>Point</span>

        </a>


        <nav class="nav-menu">

            <a href="index.php">
                Início
            </a>

            <a href="index.php#sobre">
                Sobre
            </a>

            <a href="index.php#como-funciona">
                Como funciona
            </a>

            <a href="index.php#contato">
                Contato
            </a>

            <a href="login.php" class="btn-login">
                Entrar
            </a>

        </nav>

    </div>

</header>


<main class="auth-page">


    <div class="auth-box">


        <div
            class="card-icon"
            style="margin-left:auto; margin-right:auto;"
        >
            👤
        </div>


        <h1>
            Crie sua conta
        </h1>


        <p>
            Cadastre-se para começar a usar o Park Point.
        </p>


        <form action="cadastrar.php" method="POST">


            <label for="nome">
                Nome
            </label>

            <input
                type="text"
                id="nome"
                name="nome"
                placeholder="Digite seu nome"
                maxlength="100"
                required
            >


            <label for="email">
                E-mail
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Digite seu e-mail"
                maxlength="100"
                required
            >


            <label for="senha">
                Senha
            </label>

            <input
                type="password"
                id="senha"
                name="senha"
                placeholder="Crie uma senha"
                required
            >


            <button type="submit">
                Criar conta
            </button>


        </form>


        <p style="margin-top:25px;">

            Já possui uma conta?

            <a
                href="login.php"
                style="color:#2563eb; font-weight:600;"
            >
                Entrar
            </a>

        </p>


    </div>


</main>


<footer>

    <p>
        © 2026 Park Point — Sistema de Estacionamento
    </p>

</footer>


</body>

</html>