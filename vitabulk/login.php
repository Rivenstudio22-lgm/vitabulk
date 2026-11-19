<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/vendas.php";
require_once __DIR__ . "/config/site.php";

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erro = "Digite um email válido.";

    } elseif ($senha === "") {

        $erro = "Digite a sua senha.";

    } else {

        $stmt = $conn->prepare("
            SELECT
                id,
                nome,
                email,
                telefone,
                senha,
                status
            FROM clientes
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $cliente = $resultado->fetch_assoc();

        $stmt->close();

        if (!$cliente) {

            $erro = "Email ou senha incorretos.";

        } elseif ($cliente["status"] !== "ativo") {

            $erro = "A sua conta está bloqueada.";

        } elseif (!password_verify($senha, $cliente["senha"])) {

            $erro = "Email ou senha incorretos.";

        } else {

            session_regenerate_id(true);

            $_SESSION["cliente_id"] = (int)$cliente["id"];
            $_SESSION["cliente_nome"] = $cliente["nome"];
            $_SESSION["cliente_email"] = $cliente["email"];

            $stmtLogin = $conn->prepare("
                UPDATE clientes
                SET ultimo_login = NOW()
                WHERE id = ?
            ");

            $stmtLogin->bind_param(
                "i",
                $cliente["id"]
            );

            $stmtLogin->execute();
            $stmtLogin->close();

            $destino = $_SESSION["apos_login"] ?? "";

            unset($_SESSION["apos_login"]);

            if (
                $destino &&
                strpos($destino, "/vitabulk/") === 0
            ) {
                header("Location: " . $destino);
            } else {
                header("Location: /vitabulk/minha-conta.php");
            }

            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Entrar | <?= htmlspecialchars($nomeEmpresa) ?>
    </title>

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

        .pagina-login {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }

        .login-caixa {
            width: 100%;
            max-width: 450px;
            background: #fff;
            padding: 38px;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(0,0,0,.08);
        }

        .logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo img {
            max-width: 180px;
            max-height: 80px;
            object-fit: contain;
        }

        h1 {
            margin: 0;
            color: #16823a;
            text-align: center;
        }

        .descricao {
            text-align: center;
            color: #777;
            margin: 10px 0 25px;
        }

        label {
            display: block;
            margin: 17px 0 7px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 9px;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #16823a;
        }

        button {
            width: 100%;
            border: 0;
            padding: 14px;
            margin-top: 22px;
            border-radius: 9px;
            background: #16823a;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #0d642b;
        }

        .erro {
            padding: 13px;
            border-radius: 9px;
            background: #ffe7e7;
            color: #a00000;
            margin-bottom: 15px;
        }

        .links {
            text-align: center;
            margin-top: 22px;
        }

        .links a {
            display: block;
            color: #16823a;
            text-decoration: none;
            margin-top: 10px;
        }

    </style>

</head>

<body>

<div class="pagina-login">

    <div class="login-caixa">

        <?php if ($logo): ?>

            <div class="logo">

                <img
                    src="<?= htmlspecialchars($logo) ?>"
                    alt="<?= htmlspecialchars($nomeEmpresa) ?>"
                >

            </div>

        <?php endif; ?>

        <h1>Entrar</h1>

        <div class="descricao">
            Entre na sua conta Vitabulk.
        </div>

        <?php if ($erro): ?>

            <div class="erro">
                <?= htmlspecialchars($erro) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <label for="email">
                Email
            </label>

            <input
                id="email"
                type="email"
                name="email"
                autocomplete="email"
                required
            >

            <label for="senha">
                Senha
            </label>

            <input
                id="senha"
                type="password"
                name="senha"
                autocomplete="current-password"
                required
            >

            <button type="submit">
                Entrar
            </button>

        </form>

        <div class="links">

            <a href="/vitabulk/cadastro.php">
                Ainda não tenho conta
            </a>

            <a href="/vitabulk/">
                Voltar ao site
            </a>

        </div>

    </div>

</div>

</body>

</html>