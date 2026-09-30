<?php

$host = "localhost";
$dbname = "estacionamento_novo";
$user = "root";
$pass = "";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    die("
        <!DOCTYPE html>
        <html lang='pt-br'>
        <head>
            <meta charset='UTF-8'>
            <title>Park Point - Erro</title>

            <style>
                * {
                    box-sizing: border-box;
                }

                body {
                    margin: 0;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    font-family: Arial, sans-serif;
                    background: #07111f;
                }

                .box {
                    width: 100%;
                    max-width: 500px;
                    padding: 35px;
                    border-radius: 18px;
                    background: white;
                    text-align: center;
                    box-shadow: 0 20px 50px rgba(0,0,0,.25);
                }

                .logo {
                    margin-bottom: 20px;
                    font-size: 28px;
                    font-weight: 800;
                    color: #07111f;
                }

                .logo span {
                    color: #00aaff;
                }

                h1 {
                    margin-bottom: 12px;
                    font-size: 22px;
                    color: #dc2626;
                }

                p {
                    color: #64748b;
                    line-height: 1.6;
                }

                .erro {
                    margin-top: 20px;
                    padding: 15px;
                    border-radius: 10px;
                    background: #fee2e2;
                    color: #991b1b;
                    font-size: 14px;
                    word-break: break-word;
                }
            </style>
        </head>

        <body>

            <div class='box'>

                <div class='logo'>
                    <span>P</span>ARK POINT
                </div>

                <h1>Erro de conexão</h1>

                <p>
                    Não foi possível conectar o Park Point ao banco de dados.
                </p>

                <div class='erro'>
                    " . htmlspecialchars($e->getMessage()) . "
                </div>

            </div>

        </body>
        </html>
    ");

}
?>
