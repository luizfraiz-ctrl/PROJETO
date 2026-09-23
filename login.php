<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Entrar - Park Point</title>

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

            <a href="cadastro.php" class="btn-login">
                Criar conta
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
            🔐
        </div>


        <h1>
            Bem-vindo de volta!
        </h1>


        <p>
            Entre na sua conta do Park Point.
        </p>


        <form action="entrar.php" method="POST">


            <label for="email">
                E-mail
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Digite seu e-mail"
                required
            >


            <label for="senha">
                Senha
            </label>

            <input
                type="password"
                id="senha"
                name="senha"
                placeholder="Digite sua senha"
                required
            >


            <button type="submit">
                Entrar
            </button>


        </form>


        <p style="margin-top:25px;">

            Ainda não possui uma conta?

            <a
                href="cadastro.php"
                style="color:#2563eb; font-weight:600;"
            >
                Cadastre-se
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
