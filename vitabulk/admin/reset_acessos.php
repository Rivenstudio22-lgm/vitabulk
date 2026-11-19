<?php

require_once __DIR__ . "/../config/database.php";

$mensagem = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tipo = $_POST["tipo"] ?? "";
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmar = $_POST["confirmar"] ?? "";

    if (!in_array($tipo, ["admin", "programador"], true)) {
        $erro = "Tipo de acesso inválido.";

    } elseif ($email === "") {
        $erro = "Digite o email da conta.";

    } elseif ($senha === "") {
        $erro = "Digite a nova palavra-passe.";

    } elseif (strlen($senha) < 6) {
        $erro = "A palavra-passe deve ter pelo menos 6 caracteres.";

    } elseif ($senha !== $confirmar) {
        $erro = "As palavras-passe não coincidem.";

    } else {

        $tabela = $tipo === "admin"
            ? "administradores"
            : "programadores";

        $stmt = $conn->prepare(
            "SELECT id, nome, email
             FROM {$tabela}
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {
            $erro = "Erro ao preparar a consulta.";

        } else {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $resultado = $stmt->get_result();
            $conta = $resultado->fetch_assoc();

            $stmt->close();

            if (!$conta) {

                $erro = "Nenhuma conta encontrada com esse email.";

            } else {

                $novaSenha = password_hash(
                    $senha,
                    PASSWORD_DEFAULT
                );

                $stmt = $conn->prepare(
                    "UPDATE {$tabela}
                     SET senha = ?
                     WHERE id = ?
                     LIMIT 1"
                );

                if (!$stmt) {

                    $erro = "Não foi possível atualizar a palavra-passe.";

                } else {

                    $stmt->bind_param(
                        "si",
                        $novaSenha,
                        $conta["id"]
                    );

                    if ($stmt->execute()) {

                        $mensagem =
                            "Palavra-passe alterada com sucesso para " .
                            htmlspecialchars($conta["nome"]) .
                            " (" .
                            htmlspecialchars($conta["email"]) .
                            ").";

                    } else {

                        $erro =
                            "Não foi possível alterar a palavra-passe.";
                    }

                    $stmt->close();
                }
            }
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

    <title>Reset de acessos | Vitabulk</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background:
                linear-gradient(
                    135deg,
                    #eef8f1,
                    #ffffff
                );
        }

        .box {
            width: min(480px, 100%);
            background: #ffffff;
            border: 1px solid #dce7df;
            border-radius: 18px;
            padding: 32px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, .08);
        }

        .logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo h1 {
            color: #16823a;
            font-size: 30px;
        }

        .logo p {
            color: #718078;
            margin-top: 5px;
            font-size: 13px;
        }

        .warning {
            background: #fff7df;
            border: 1px solid #f1d98c;
            color: #765b00;
            padding: 13px;
            border-radius: 9px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.5;
        }

        .success {
            background: #e8f7ed;
            border: 1px solid #b9dfc4;
            color: #17652e;
            padding: 13px;
            border-radius: 9px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.5;
        }

        .error {
            background: #fff0f0;
            border: 1px solid #efbcbc;
            color: #a42323;
            padding: 13px;
            border-radius: 9px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.5;
        }

        .field {
            margin-bottom: 17px;
        }

        .field label {
            display: block;
            margin-bottom: 7px;
            color: #26332b;
            font-size: 13px;
            font-weight: 700;
        }

        .field input,
        .field select {
            width: 100%;
            padding: 13px;
            border: 1px solid #d5dfd8;
            border-radius: 9px;
            background: #fff;
            outline: none;
            font-size: 14px;
        }

        .field input:focus,
        .field select:focus {
            border-color: #16823a;
            box-shadow:
                0 0 0 3px rgba(22, 130, 58, .08);
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: 0;
            border-radius: 9px;
            background: #16823a;
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
        }

        .btn:hover {
            background: #0d642b;
        }

        .links {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .links a {
            color: #16823a;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        .links a:hover {
            text-decoration: underline;
        }

    </style>

</head>

<body>

<div class="box">

    <div class="logo">

        <h1>Vita<span>bulk</span></h1>

        <p>
            Recuperação de acesso administrativo
        </p>

    </div>


    <div class="warning">

        <strong>Atenção:</strong>
        esta página permite alterar diretamente a palavra-passe
        de administradores e programadores.

        Depois de recuperar o acesso,
        <strong>apague este ficheiro do servidor.</strong>

    </div>


    <?php if ($mensagem): ?>

        <div class="success">
            <?= $mensagem ?>
        </div>

    <?php endif; ?>


    <?php if ($erro): ?>

        <div class="error">
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="field">

            <label>
                Tipo de acesso
            </label>

            <select name="tipo" required>

                <option value="">
                    Selecionar
                </option>

                <option
                    value="admin"
                    <?= ($_POST["tipo"] ?? "") === "admin"
                        ? "selected"
                        : "" ?>
                >
                    Administrador
                </option>

                <option
                    value="programador"
                    <?= ($_POST["tipo"] ?? "") === "programador"
                        ? "selected"
                        : "" ?>
                >
                    Programador
                </option>

            </select>

        </div>


        <div class="field">

            <label>
                Email da conta
            </label>

            <input
                type="email"
                name="email"
                placeholder="email da conta"
                value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                required
            >

        </div>


        <div class="field">

            <label>
                Nova palavra-passe
            </label>

            <input
                type="password"
                name="senha"
                placeholder="Digite a nova palavra-passe"
                minlength="6"
                required
            >

        </div>


        <div class="field">

            <label>
                Confirmar palavra-passe
            </label>

            <input
                type="password"
                name="confirmar"
                placeholder="Digite novamente"
                minlength="6"
                required
            >

        </div>


        <button
            type="submit"
            class="btn"
        >
            Alterar palavra-passe
        </button>

    </form>


    <div class="links">

        <a href="/vitabulk/admin/login.php">
            Voltar para o login
        </a>

    </div>

</div>

</body>

</html>