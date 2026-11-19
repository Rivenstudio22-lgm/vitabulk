<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/vendas.php";
require_once __DIR__ . "/config/cliente_auth.php";
require_once __DIR__ . "/config/site.php";

exigirLoginCliente();

$clienteId = clienteId();

$stmt = $conn->prepare("
    SELECT
        nome,
        email,
        telefone,
        receber_novidades,
        ultimo_login,
        criado_em
    FROM clientes
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $clienteId);
$stmt->execute();

$cliente = $stmt->get_result()->fetch_assoc();

$stmt->close();

$stmtPedidos = $conn->prepare("
    SELECT
        id,
        numero_pedido,
        total,
        status,
        criado_em
    FROM pedidos
    WHERE cliente_id = ?
    ORDER BY id DESC
    LIMIT 10
");

$stmtPedidos->bind_param("i", $clienteId);
$stmtPedidos->execute();

$pedidos = $stmtPedidos->get_result();

$stmtPedidos->close();

?>

<!DOCTYPE html>
<html lang="pt">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Minha conta | Vitabulk</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f5f7f5;
}

.container {
    width: min(1100px, 94%);
    margin: 40px auto;
}

.topo {
    background: #16823a;
    color: #fff;
    padding: 25px;
    border-radius: 16px;
    margin-bottom: 25px;
}

.grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.card {
    background: #fff;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,.05);
}

.card h2 {
    margin-top: 0;
    color: #16823a;
}

.pedido {
    border-bottom: 1px solid #eee;
    padding: 15px 0;
}

.pedido:last-child {
    border-bottom: 0;
}

.btn {
    display: inline-block;
    padding: 11px 15px;
    background: #16823a;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    margin-top: 10px;
}

.btn-sair {
    background: #b42318;
}

@media(max-width:700px) {

    .grid {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<div class="container">

    <div class="topo">

        <h1>
            Olá, <?= htmlspecialchars($cliente["nome"]) ?>
        </h1>

        <p>
            Bem-vindo à sua conta Vitabulk.
        </p>

    </div>

    <div class="grid">

        <div class="card">

            <h2>Os meus dados</h2>

            <p>
                <strong>Nome:</strong>
                <?= htmlspecialchars($cliente["nome"]) ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?= htmlspecialchars($cliente["email"]) ?>
            </p>

            <p>
                <strong>Telefone:</strong>
                <?= htmlspecialchars($cliente["telefone"] ?: "Não informado") ?>
            </p>

            <p>
                <strong>Conta criada:</strong>
                <?= date("d/m/Y", strtotime($cliente["criado_em"])) ?>
            </p>

            <a
                class="btn"
                href="/vitabulk/produtos.php"
            >
                Ver produtos
            </a>

            <a
                class="btn btn-sair"
                href="/vitabulk/logout.php"
            >
                Sair
            </a>

        </div>

        <div class="card">

            <h2>Os meus pedidos</h2>

            <?php if ($pedidos->num_rows === 0): ?>

                <p>
                    Ainda não fez nenhum pedido.
                </p>

            <?php else: ?>

                <?php while ($pedido = $pedidos->fetch_assoc()): ?>

                    <div class="pedido">

                        <strong>
                            <?= htmlspecialchars($pedido["numero_pedido"]) ?>
                        </strong>

                        <br>

                        <?= number_format(
                            $pedido["total"],
                            2,
                            ",",
                            "."
                        ) ?> MT

                        <br>

                        Estado:
                        <?= htmlspecialchars($pedido["status"]) ?>

                        <br>

                        <?= date(
                            "d/m/Y H:i",
                            strtotime($pedido["criado_em"])
                        ) ?>

                    </div>

                <?php endwhile; ?>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>