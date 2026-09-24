<?php

require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/* ================================
   RECEBER DADOS DO FORMULÁRIO
   ================================ */

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$assunto = trim($_POST["assunto"] ?? "");
$mensagem = trim($_POST["mensagem"] ?? "");


/* ================================
   VERIFICAR CAMPOS
   ================================ */

if (
    $nome === "" ||
    $email === "" ||
    $assunto === "" ||
    $mensagem === ""
) {
    die("Preencha todos os campos.");
}


/* ================================
   VALIDAR E-MAIL
   ================================ */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Digite um e-mail válido.");
}


/* ================================
   CRIAR E-MAIL
   ================================ */

$mail = new PHPMailer(true);

try {

    /* ================================
       CONFIGURAÇÃO DO GMAIL
       ================================ */

    $mail->isSMTP();

    $mail->Host = "smtp.gmail.com";

    $mail->SMTPAuth = true;

    $mail->Username = "luiz.fraiz@aluno.senai.br";

    /*
       IMPORTANTE:
       Coloque aqui a NOVA senha de app
       criada na sua conta Google.
    */

    $mail->Password = "exwe zxxp zjau puuf";

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->Port = 587;

    $mail->CharSet = "UTF-8";


    /* ================================
       REMETENTE
       ================================ */

    $mail->setFrom(
        "luiz.fraiz@aluno.senai.br",
        "Park Point - Contato"
    );

    $mail->Sender = "luiz.fraiz@aluno.senai.br";


    /* ================================
       DESTINATÁRIO
       ================================ */

    $mail->addAddress(
        "luiz.fraiz@aluno.senai.br",
        "Luiz"
    );


    /* ================================
       RESPONDER PARA O E-MAIL INFORMADO
       ================================ */

    $mail->addReplyTo(
        $email,
        $nome
    );


    /* ================================
       ASSUNTO
       ================================ */

    $mail->Subject = "Park Point - " . $assunto;


    /* ================================
       MENSAGEM HTML
       ================================ */

    $mail->isHTML(true);

    $mail->Body = "

        <div style='font-family: Arial, sans-serif; max-width: 600px;'>

            <h2 style='color: #2563eb;'>
                Nova mensagem pelo Park Point
            </h2>

            <p>
                <strong>Nome:</strong>
                " . htmlspecialchars($nome) . "
            </p>

            <p>
                <strong>E-mail:</strong>
                " . htmlspecialchars($email) . "
            </p>

            <p>
                <strong>Assunto:</strong>
                " . htmlspecialchars($assunto) . "
            </p>

            <hr>

            <p>
                <strong>Mensagem:</strong>
            </p>

            <p>
                " . nl2br(htmlspecialchars($mensagem)) . "
            </p>

        </div>

    ";


    /* ================================
       VERSÃO TEXTO
       ================================ */

    $mail->AltBody =
        "Nova mensagem pelo Park Point\n\n" .
        "Nome: " . $nome . "\n" .
        "E-mail: " . $email . "\n" .
        "Assunto: " . $assunto . "\n\n" .
        "Mensagem:\n" .
        $mensagem;


    /* ================================
       ENVIAR
       ================================ */

    $mail->send();


} catch (Exception $e) {

    die(
        "Erro ao enviar mensagem:<br><br>" .
        htmlspecialchars($mail->ErrorInfo)
    );

}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

```
<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Mensagem enviada - Park Point</title>

<link
    rel="stylesheet"
    href="style.css"
>

<style>

    .contato-sucesso {

        min-height: calc(100vh - 150px);

        display: flex;

        justify-content: center;

        align-items: center;

        padding: 50px 20px;

    }

    .contato-box {

        width: 100%;

        max-width: 520px;

        background: white;

        padding: 45px 35px;

        border-radius: 22px;

        border: 1px solid #e8edf5;

        box-shadow: 0 15px 40px rgba(0,0,0,0.06);

        text-align: center;

    }

    .contato-icon {

        width: 75px;

        height: 75px;

        margin: 0 auto 20px;

        border-radius: 50%;

        background: #dcfce7;

        color: #16a34a;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 38px;

    }

    .contato-box h1 {

        font-size: 27px;

        margin-bottom: 10px;

    }

    .contato-box p {

        color: #6b7280;

        font-size: 14px;

        margin-bottom: 15px;

        line-height: 1.6;

    }

</style>
```

</head>

<body>

<header class="navbar">

```
<div class="navbar-content">

    <a
        href="index.php"
        class="logo"
    >

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

        <a
            href="login.php"
            class="btn-login"
        >
            Entrar
        </a>

    </nav>

</div>
```

</header>

<main class="contato-sucesso">

```
<div class="contato-box">

    <div class="contato-icon">
        ✓
    </div>

    <h1>
        Mensagem enviada!
    </h1>

    <p>

        Obrigado pelo contato,

        <strong>
            <?= htmlspecialchars($nome) ?>
        </strong>!

    </p>

    <p>
        Sua mensagem foi enviada para a equipe do Park Point.
    </p>

    <a
        href="index.php"
        class="btn-primary"
    >
        Voltar para o início
    </a>

</div>
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
