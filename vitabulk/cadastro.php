<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/vendas.php";

if (!vendasAtivas()) {
    header("Location: /vitabulk/");
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION["cliente_id"])) {
    header("Location: /vitabulk/minha-conta.php");
    exit;
}

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmarSenha = $_POST["confirmar_senha"] ?? "";
    $receberNovidades = isset($_POST["receber_novidades"]) ? 1 : 0;

    if ($nome === "") {
        $erro = "Digite o seu nome.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Digite um email válido.";

    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";

    } elseif ($senha !== $confirmarSenha) {
        $erro = "As senhas não coincidem.";

    } else {

        $stmt = $conn->prepare("
            SELECT id
            FROM clientes
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {

            $erro = "Já existe uma conta com este email.";

        } else {

            $senhaHash = password_hash(
                $senha,
                PASSWORD_DEFAULT
            );

            $stmtInsert = $conn->prepare("
                INSERT INTO clientes
                (
                    nome,
                    email,
                    telefone,
                    senha,
                    receber_novidades,
                    status
                )
                VALUES (?, ?, ?, ?, ?, 'ativo')
            ");

            $stmtInsert->bind_param(
                "ssssi",
                $nome,
                $email,
                $telefone,
                $senhaHash,
                $receberNovidades
            );

            if ($stmtInsert->execute()) {

                $sucesso = "Conta criada com sucesso. Já pode iniciar sessão.";

            } else {

                $erro = "Não foi possível criar a conta.";
            }

            $stmtInsert->close();
        }

        $stmt->close();
    }
}

require_once __DIR__ . "/config/site.php";
?>
<!DOCTYPE html>
<html lang="pt">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Criar conta | <?= htmlspecialchars($nomeEmpresa) ?></title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7f5;
            color: #222;
        }

        .pagina {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .caixa {
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 18px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(0,0,0,.08);
        }

        h1 {
            margin: 0 0 8px;
            color: #16823a;
        }

        p {
            color: #666;
        }

        label {
            display: block;
            margin: 18px 0 7px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 9px;
            font-size: 15px;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 14px;
            border: 0;
            border-radius: 9px;
            background: #16823a;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .alerta {
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .erro {
            background: #ffe5e5;
            color: #a00000;
        }

        .sucesso {
            background: #e5f8eb;
            color: #146b2d;
        }

        .voltar {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #16823a;
            text-decoration: none;
        }

        .check {
            margin-top: 15px;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .check input {
            width: auto;
        }
    </style>

</head>

<body>

<div class="pagina">

    <div class="caixa">

        <h1>Criar conta</h1>

        <p>
            Crie a sua conta na <?= htmlspecialchars($nomeEmpresa) ?>.
        </p>

        <?php if ($erro): ?>
            <div class="alerta erro">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alerta sucesso">
                <?= htmlspecialchars($sucesso) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Nome</label>

            <input
                type="text"
                name="nome"
                required
            >

            <label>Email</label>

            <input
                type="email"
                name="email"
                required
            >

            <label>Telefone</label>

            <input
                type="text"
                name="telefone"
            >

            <label>Senha</label>

            <input
                type="password"
                name="senha"
                minlength="6"
                required
            >

            <label>Confirmar senha</label>

            <input
                type="password"
                name="confirmar_senha"
                minlength="6"
                required
            >

            <label class="check">

                <input
                    type="checkbox"
                    name="receber_novidades"
                    checked
                >

                <span>
                    Quero receber novidades da Vitabulk.
                </span>

            </label>

            <button type="submit">
                Criar conta
            </button>

        </form>

        <a
            href="/vitabulk/login.php"
            class="voltar"
        >
            Já tenho uma conta
        </a>

    </div>

</div>

</body>
</html>