<?php

session_start();

require_once __DIR__ . "/../config/database.php";

$mensagem = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $novaSenha = $_POST["nova_senha"] ?? "";
    $confirmar = $_POST["confirmar_senha"] ?? "";

    if ($email === "" || $novaSenha === "") {

        $erro = "Preencha todos os campos.";

    } elseif (strlen($novaSenha) < 6) {

        $erro = "A palavra-passe deve ter pelo menos 6 caracteres.";

    } elseif ($novaSenha !== $confirmar) {

        $erro = "As palavras-passe não coincidem.";

    } else {

        $stmt = $conn->prepare("
            SELECT id
            FROM programadores
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $programador = $resultado->fetch_assoc();

        $stmt->close();

        if (!$programador) {

            $erro = "Programador não encontrado.";

        } else {

            $hash = password_hash(
                $novaSenha,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare("
                UPDATE programadores
                SET senha = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "si",
                $hash,
                $programador["id"]
            );

            if ($stmt->execute()) {

                $mensagem =
                    "Palavra-passe alterada com sucesso.";

            } else {

                $erro =
                    "Não foi possível alterar a palavra-passe.";
            }

            $stmt->close();
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
    Recuperar Programador | Vitabulk
</title>

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

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #0d642b,
            #101614
        );
}

.card {

    width: 100%;
    max-width: 430px;

    background: white;

    border-radius: 18px;

    padding: 30px;

    box-shadow:
        0 25px 70px
        rgba(0,0,0,.25);
}

.logo {

    width: 60px;
    height: 60px;

    margin: 0 auto 18px;

    border-radius: 16px;

    background: #16823a;

    color: white;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 30px;

    font-weight: 900;
}

h1 {

    text-align: center;

    font-size: 24px;

    color: #172019;

    margin-bottom: 8px;
}

.description {

    text-align: center;

    color: #77827b;

    font-size: 13px;

    line-height: 1.5;

    margin-bottom: 25px;
}

.alert {

    padding: 13px;

    border-radius: 9px;

    margin-bottom: 18px;

    font-size: 13px;

    font-weight: 600;
}

.alert.error {

    background: #fdecea;

    border: 1px solid #f3c1bd;

    color: #b3261e;
}

.alert.success {

    background: #e9f7ee;

    border: 1px solid #bce3c8;

    color: #0d642b;
}

.form-group {

    margin-bottom: 17px;
}

label {

    display: block;

    font-size: 12px;

    font-weight: 800;

    margin-bottom: 7px;

    color: #26302a;
}

input {

    width: 100%;

    height: 47px;

    border: 1px solid #d7dfda;

    border-radius: 9px;

    padding: 0 13px;

    font-size: 14px;

    outline: none;
}

input:focus {

    border-color: #16823a;

    box-shadow:
        0 0 0 3px
        rgba(22,130,58,.1);
}

button {

    width: 100%;

    height: 48px;

    border: 0;

    border-radius: 9px;

    background: #16823a;

    color: white;

    font-size: 14px;

    font-weight: 800;

    cursor: pointer;
}

button:hover {

    background: #0d642b;
}

.back {

    display: block;

    text-align: center;

    margin-top: 18px;

    color: #16823a;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;
}

</style>

</head>

<body>

<div class="card">

<div class="logo">
    V
</div>

<h1>
    Recuperar Programador
</h1>

<div class="description">

Crie uma nova palavra-passe para acessar
o painel do programador.

</div>


<?php if ($erro): ?>

<div class="alert error">

<?= htmlspecialchars(
    $erro,
    ENT_QUOTES,
    "UTF-8"
) ?>

</div>

<?php endif; ?>


<?php if ($mensagem): ?>

<div class="alert success">

<?= htmlspecialchars(
    $mensagem,
    ENT_QUOTES,
    "UTF-8"
) ?>

</div>

<?php endif; ?>


<form method="POST">

<div class="form-group">

<label>
    Email do programador
</label>

<input
    type="email"
    name="email"
    placeholder="programador@vitabulk.com"
    required
>

</div>


<div class="form-group">

<label>
    Nova palavra-passe
</label>

<input
    type="password"
    name="nova_senha"
    placeholder="Nova palavra-passe"
    minlength="6"
    required
>

</div>


<div class="form-group">

<label>
    Confirmar palavra-passe
</label>

<input
    type="password"
    name="confirmar_senha"
    placeholder="Repita a palavra-passe"
    minlength="6"
    required
>

</div>


<button type="submit">
    Alterar palavra-passe
</button>

</form>


<a
    href="/vitabulk/admin/login.php"
    class="back"
>
    ← Voltar ao login
</a>

</div>

</body>

</html>