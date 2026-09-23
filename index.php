<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Park Point - Estacionamento inteligente</title>

<link rel="stylesheet" href="style.css">

<style>

.hero {
    max-width: 1200px;
    margin: auto;
    padding: 80px 30px;

    display: grid;
    grid-template-columns: 1fr 1fr;

    align-items: center;
    gap: 60px;
}

.hero-tag {
    display: inline-block;

    background: #eaf2ff;
    color: #2563eb;

    padding: 8px 15px;

    border-radius: 20px;

    font-size: 13px;
    font-weight: 600;

    margin-bottom: 20px;
}

.hero h1 {
    font-size: 48px;
    line-height: 1.15;

    margin-bottom: 20px;
}

.hero h1 span {
    color: #2563eb;
}

.hero p {
    color: #667085;

    font-size: 16px;
    line-height: 1.7;

    margin-bottom: 30px;

    max-width: 520px;
}

.hero-buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn-secondary {
    display: inline-block;

    padding: 12px 22px;

    border-radius: 10px;

    border: 1px solid #d9e0eb;

    color: #374151;

    background: white;

    font-weight: 600;
    font-size: 14px;
}

.btn-secondary:hover {
    border-color: #2563eb;
    color: #2563eb;
}

.hero-visual {
    display: flex;
    justify-content: center;
}

.parking-card {
    background: white;

    width: 100%;
    max-width: 450px;

    padding: 25px;

    border-radius: 25px;

    box-shadow: 0 20px 50px rgba(37, 99, 235, 0.12);

    border: 1px solid #e8edf5;
}

.parking-top {
    display: flex;

    justify-content: space-between;
    align-items: center;

    margin-bottom: 25px;
}

.parking-top strong {
    font-size: 17px;
}

.available {
    background: #dcfce7;
    color: #16a34a;

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 11px;
    font-weight: 700;
}

.parking-spaces {
    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 15px;
}

.space {
    height: 110px;

    border: 2px dashed #d6deeb;

    border-radius: 15px;

    display: flex;

    align-items: center;
    justify-content: center;

    position: relative;

    color: #8a94a6;

    font-weight: 600;
}

.space .car {
    font-size: 32px;
}

.home-section {
    max-width: 1200px;

    margin: auto;

    padding: 80px 30px;
}

.home-section-title {
    text-align: center;

    margin-bottom: 45px;
}

.home-section-title h2 {
    font-size: 30px;

    margin-bottom: 10px;
}

.home-section-title p {
    color: #6b7280;
}

.home-cards {
    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 25px;
}

.home-card {
    background: white;

    padding: 30px;

    border-radius: 20px;

    border: 1px solid #e8edf5;

    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);

    text-align: center;
}

.home-card-icon {
    width: 55px;
    height: 55px;

    background: #eaf2ff;

    border-radius: 15px;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 25px;

    margin: 0 auto 20px;
}

.home-card h3 {
    margin-bottom: 10px;
}

.home-card p {
    color: #6b7280;

    font-size: 14px;

    line-height: 1.6;
}


/* CONTATO */

.contact-form-box {

    max-width: 700px;

    margin: auto;

    background: white;

    padding: 35px;

    border-radius: 22px;

    border: 1px solid #e8edf5;

    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.contact-form-box label {
    text-align: left;
}

.contact-form-box input {

    width: 100%;
}

.contact-form-box textarea {

    width: 100%;

    min-height: 140px;

    padding: 13px 15px;

    border: 1px solid #d9e0eb;

    border-radius: 10px;

    outline: none;

    font-family: inherit;

    font-size: 14px;

    resize: vertical;

    margin-bottom: 20px;
}

.contact-form-box textarea:focus {

    border-color: #2563eb;

    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
}

.contact-form-box button {

    width: 100%;
}


/* RESPONSIVO */

@media (max-width: 800px) {

    .hero {

        grid-template-columns: 1fr;

        padding-top: 50px;
    }

    .hero h1 {

        font-size: 38px;
    }

    .home-cards {

        grid-template-columns: 1fr;
    }

}

</style>

</head>


<body>


<!-- NAVBAR -->

<header class="navbar">

<div class="navbar-content">

<a href="index.php" class="logo">

<div class="logo-icon">P</div>

Park <span>Point</span>

</a>


<nav class="nav-menu">

<a href="index.php">Início</a>

<a href="#sobre">Sobre</a>

<a href="#como-funciona">Como funciona</a>

<a href="#contato">Contato</a>

<a href="login.php" class="btn-login">
Entrar
</a>

</nav>

</div>

</header>



<!-- HERO -->

<section class="hero">

<div>

<span class="hero-tag">
🚗 Estacionamento inteligente
</span>

<h1>
Encontre sua vaga.
<span>Em tempo real.</span>
</h1>

<p>
O Park Point facilita sua busca por estacionamento,
permitindo visualizar vagas disponíveis e realizar
reservas de forma rápida e prática.
</p>

<div class="hero-buttons">

<a href="login.php" class="btn-primary">
Encontrar minha vaga
</a>

<a href="cadastro.php" class="btn-secondary">
Criar minha conta
</a>

</div>

</div>


<div class="hero-visual">

<div class="parking-card">

<div class="parking-top">

<strong>
Estacionamento
</strong>

<span class="available">
● VAGAS DISPONÍVEIS
</span>

</div>


<div class="parking-spaces">

<div class="space">
<span>A1</span>
</div>

<div class="space">
<span class="car">🚗</span>
</div>

<div class="space">
<span>B1</span>
</div>

<div class="space">
<span class="car">🚙</span>
</div>

</div>

</div>

</div>

</section>



<!-- SOBRE -->

<section class="home-section" id="sobre">

<div class="home-section-title">

<h2>
Sobre o Park Point
</h2>

<p>
Uma maneira simples de encontrar e reservar sua vaga.
</p>

</div>


<div class="home-cards">


<div class="home-card">

<div class="home-card-icon">
🅿️
</div>

<h3>
Vagas em tempo real
</h3>

<p>
Consulte quais vagas estão livres ou ocupadas
antes de chegar ao estacionamento.
</p>

</div>


<div class="home-card">

<div class="home-card-icon">
📅
</div>

<h3>
Reserva fácil
</h3>

<p>
Escolha uma vaga e reserve utilizando um dos
veículos cadastrados na sua conta.
</p>

</div>


<div class="home-card">

<div class="home-card-icon">
🔒
</div>

<h3>
Tudo organizado
</h3>

<p>
Tenha acesso às suas reservas e ao histórico
sempre que precisar.
</p>

</div>


</div>

</section>



<!-- COMO FUNCIONA -->

<section class="home-section" id="como-funciona">

<div class="home-section-title">

<h2>
Como funciona?
</h2>

<p>
Reserve sua vaga em poucos passos.
</p>

</div>


<div class="home-cards">


<div class="home-card">

<div class="home-card-icon">
1️⃣
</div>

<h3>
Crie sua conta
</h3>

<p>
Faça seu cadastro gratuitamente no Park Point.
</p>

</div>


<div class="home-card">

<div class="home-card-icon">
2️⃣
</div>

<h3>
Cadastre seu veículo
</h3>

<p>
Informe a placa, modelo e cor do seu veículo.
</p>

</div>


<div class="home-card">

<div class="home-card-icon">
3️⃣
</div>

<h3>
Reserve sua vaga
</h3>

<p>
Escolha uma vaga livre e confirme sua reserva.
</p>

</div>


</div>

</section>



<!-- CONTATO -->

<section class="home-section" id="contato">

<div class="home-section-title">

<h2>
Entre em contato conosco
</h2>

<p>
Tem alguma dúvida ou precisa de ajuda?
Fale com a equipe do Park Point.
</p>

</div>


<div class="contact-form-box">

<form action="enviar-contato.php" method="POST">


<label for="nome">
Nome
</label>

<input
type="text"
id="nome"
name="nome"
placeholder="Digite seu nome"
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
required
>


<label for="assunto">
Assunto
</label>

<input
type="text"
id="assunto"
name="assunto"
placeholder="Qual é o assunto?"
required
>


<label for="mensagem">
Mensagem
</label>

<textarea
id="mensagem"
name="mensagem"
placeholder="Digite sua mensagem..."
required
></textarea>


<button type="submit">
Enviar mensagem
</button>


</form>

</div>

</section>



<!-- RODAPÉ -->

<footer>

<p>
© 2026 Park Point — Sistema de Estacionamento
</p>

</footer>


</body>

</html>