```php
<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: entrar.php");
    exit;
}

if (($_SESSION["perfil"] ?? "cliente") !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$mensagem = "";
$tipoMensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"] ?? "");
    $cnpj = preg_replace('/\D/', '', $_POST["cnpj"] ?? "");
    $endereco = trim($_POST["endereco"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $abertura = $_POST["horario_abertura"] ?? "";
    $fechamento = $_POST["horario_fechamento"] ?? "";
    $valorHora = str_replace(",", ".", trim($_POST["valor_hora"] ?? ""));
    $totalVagas = filter_var(
        $_POST["total_vagas"] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        $nome === "" || $cnpj === "" || $endereco === "" ||
        $telefone === "" || $abertura === "" ||
        $fechamento === "" || $valorHora === ""
    ) {
        $mensagem = "Preencha todos os campos.";
        $tipoMensagem = "erro";
    } elseif (strlen($cnpj) !== 14) {
        $mensagem = "O CNPJ deve conter 14 números.";
        $tipoMensagem = "erro";
    } elseif (!is_numeric($valorHora) || (float)$valorHora < 0) {
        $mensagem = "Informe um valor por hora válido.";
        $tipoMensagem = "erro";
    } elseif ($totalVagas === false || $totalVagas < 1 || $totalVagas > 500) {
        $mensagem = "Informe entre 1 e 500 vagas.";
        $tipoMensagem = "erro";
    } else {
        try {
            $stmt = $pdo->prepare(
                "SELECT id FROM estabelecimentos WHERE cnpj = ? LIMIT 1"
            );
            $stmt->execute([$cnpj]);

            if ($stmt->fetch()) {
                $mensagem = "Este CNPJ já está cadastrado.";
                $tipoMensagem = "erro";
            } else {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    "INSERT INTO estabelecimentos
                    (usuario_id, nome, cnpj, endereco, telefone,
                    horario_abertura, horario_fechamento, valor_hora, total_vagas)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->execute([
                    (int)$_SESSION["usuario_id"],
                    $nome,
                    $cnpj,
                    $endereco,
                    $telefone,
                    $abertura,
                    $fechamento,
                    $valorHora,
                    $totalVagas
                ]);

                $estabelecimentoId = (int)$pdo->lastInsertId();

                // Reaproveita vagas antigas ainda sem estabelecimento.
                $stmt = $pdo->query(
                    "SELECT id FROM vagas
                     WHERE estabelecimento_id IS NULL
                     ORDER BY numero ASC"
                );
                $vagasAntigas = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $reaproveitar = min($totalVagas, count($vagasAntigas));

                $associar = $pdo->prepare(
                    "UPDATE vagas
                     SET estabelecimento_id = ?
                     WHERE id = ? AND estabelecimento_id IS NULL"
                );

                for ($i = 0; $i < $reaproveitar; $i++) {
                    $associar->execute([
                        $estabelecimentoId,
                        $vagasAntigas[$i]
                    ]);
                }

                // Cria apenas as vagas que ainda faltam.
                $faltam = $totalVagas - $reaproveitar;

                if ($faltam > 0) {
                    $stmt = $pdo->query(
                        "SELECT COALESCE(MAX(numero), 0) FROM vagas"
                    );
                    $numero = (int)$stmt->fetchColumn() + 1;

                    $novaVaga = $pdo->prepare(
                        "INSERT INTO vagas (numero, status, estabelecimento_id)
                         VALUES (?, 'livre', ?)"
                    );

                    for ($i = 0; $i < $faltam; $i++) {
                        $novaVaga->execute([$numero, $estabelecimentoId]);
                        $numero++;
                    }
                }

                $pdo->commit();

                $mensagem = "Estacionamento cadastrado com sucesso!";
                $tipoMensagem = "sucesso";
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("Erro ao cadastrar estacionamento: " . $e->getMessage());

            $mensagem = "Não foi possível cadastrar o estacionamento.";

            if (in_array($_SERVER["SERVER_NAME"] ?? "", ["localhost", "127.0.0.1"], true)) {
                $mensagem .= " Detalhes: " . $e->getMessage();
            }

            $tipoMensagem = "erro";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar estacionamento | Park Point</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cadastro-estacionamento {
            width: min(700px, 92%);
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 28px rgba(0,0,0,.08);
        }

        .cadastro-estacionamento h1 {
            margin-bottom: 8px;
            color: #172554;
        }

        .cadastro-estacionamento .descricao {
            margin-bottom: 24px;
            color: #64748b;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .campo {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .campo.completo {
            grid-column: 1 / -1;
        }

        .campo label {
            font-weight: 600;
            color: #334155;
        }

        .campo input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font: inherit;
            box-sizing: border-box;
        }

        .campo input:focus {
            outline: 2px solid #93c5fd;
            border-color: #2563eb;
        }

        .acoes {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .botao {
            display: inline-block;
            padding: 12px 18px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: background .2s, transform .2s;
        }

        .botao:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .botao-secundario {
            background: #e2e8f0;
            color: #1e293b;
        }

        .botao-secundario:hover {
            background: #cbd5e1;
        }

        .mensagem {
            margin-bottom: 20px;
            padding: 13px;
            border-radius: 8px;
        }

        .mensagem.erro {
            background: #fee2e2;
            color: #991b1b;
        }

        .mensagem.sucesso {
            background: #dcfce7;
            color: #166534;
        }

        @media (max-width: 600px) {
            .cadastro-estacionamento {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .campo.completo {
                grid-column: auto;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 5%;flex-wrap:wrap;">
            <a href="dashboard.php" style="font-size:24px;font-weight:800;text-decoration:none;color:#2563eb;">
                P <span style="color:#172554;">Park Point</span>
            </a>
            <a href="dashboard.php" class="botao botao-secundario">Voltar ao painel</a>
        </nav>
    </header>

    <main class="cadastro-estacionamento">
        <h1>Cadastrar estacionamento</h1>
        <p class="descricao">Preencha os dados para registrar um estacionamento no Park Point.</p>

        <?php if ($mensagem !== ""): ?>
            <div class="mensagem <?= htmlspecialchars($tipoMensagem, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="cadastro-estacionamento.php">
            <div class="form-grid">
                <div class="campo completo">
                    <label for="nome">Nome do estacionamento</label>
                    <input type="text" id="nome" name="nome" maxlength="150" required
                           value="<?= htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="campo">
                    <label for="cnpj">CNPJ (14 números)</label>
                    <input type="text" id="cnpj" name="cnpj" maxlength="18" required
                           placeholder="Somente números ou CNPJ formatado"
                           value="<?= htmlspecialchars($_POST['cnpj'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="campo">
                    <label for="telefone">Telefone</label>
                    <input type="tel" id="telefone" name="telefone" maxlength="20" required
                           value="<?= htmlspecialchars($_POST['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="campo completo">
                    <label for="endereco">Endereço</label>
                    <input type="text" id="endereco" name="endereco" maxlength="255" required
                           value="<?= htmlspecialchars($_POST['endereco'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="campo">
                    <label for="horario_abertura">Horário de abertura</label>
                    <input type="time" id="horario_abertura" name="horario_abertura" required
                           value="<?= htmlspecialchars($_POST['horario_abertura'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="campo">
                    <label for="horario_fechamento">Horário de fechamento</label>
                    <input type="time" id="horario_fechamento" name="horario_fechamento" required
                           value="<?= htmlspecialchars($_POST['horario_fechamento'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="campo">
                    <label for="valor_hora">Valor por hora (R$)</label>
                    <input type="number" id="valor_hora" name="valor_hora" min="0" step="0.01" required
                           value="<?= htmlspecialchars($_POST['valor_hora'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="campo">
                    <label for="total_vagas">Quantidade de vagas</label>
                    <input type="number" id="total_vagas" name="total_vagas" min="1" max="500" required
                           value="<?= htmlspecialchars($_POST['total_vagas'] ?? '10', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="acoes">
                <button type="submit" class="botao">Cadastrar estacionamento</button>
                <a href="dashboard.php" class="botao botao-secundario">Cancelar</a>
            </div>
        </form>
    </main>
</body>
</html>
```