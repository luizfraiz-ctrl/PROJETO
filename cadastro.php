```php
<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Criar conta - Park Point</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

    <!-- NAVBAR -->

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


    <!-- CADASTRO -->

    <main class="form-page">

        <div class="form-box">

            <h1>
                Criar sua conta
            </h1>

            <p>
                Cadastre-se no Park Point para encontrar e reservar sua vaga.
            </p>


            <form action="cadastrar.php" method="POST">

                <label>
                    Nome
                </label>

                <input
                    type="text"
                    name="nome"
                    placeholder="Digite seu nome"
                    required
                >


                <label>
                    E-mail
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="Digite seu e-mail"
                    required
                >


                <label>
                    Senha
                </label>

                <input
                    type="password"
                    name="senha"
                    placeholder="Crie uma senha"
                    required
                >


                <button type="submit">
                    Criar conta
                </button>

            </form>


            <p style="text-align:center; margin-top:20px;">

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
```
