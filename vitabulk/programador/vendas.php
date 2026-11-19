<?php

session_start();

if (!isset($_SESSION["programador_id"])) {
    header("Location: /vitabulk/admin/login.php");
    exit;
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/configuracoes.php";

function estado($chave)
{
    return configuracaoAtiva($chave, false);
}

$vendas = estado("vendas_ativo");
$carrinho = estado("carrinho_ativo");
$checkout = estado("checkout_ativo");
$promocoes = estado("promocoes_ativo");
$pedidos = estado("pedidos_ativo");
$pagamentos = estado("pagamentos_ativo");
$payzeno = estado("payzeno_ativo");
$loginCompras = estado("login_compras_ativo");

function e($valor)
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
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

<title>Vendas | Programador | Vitabulk</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #f4f7f5;
    color: #1d1d1d;
}

.header {
    background: #101614;
    color: white;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 23px;
    font-weight: 800;
}

.logo span {
    color: #16823a;
}

.back {
    color: white;
    text-decoration: none;
}

.container {
    width: min(1100px, 94%);
    margin: 35px auto;
}

.title {
    margin-bottom: 25px;
}

.title h1 {
    font-size: 30px;
}

.title p {
    color: #777;
    margin-top: 7px;
}

.status-box {
    background: white;
    padding: 28px;
    border-radius: 16px;
    margin-bottom: 25px;
    box-shadow: 0 5px 25px rgba(0,0,0,.06);
}

.status {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 20px;
    font-weight: 800;
}

.dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
}

.dot.on {
    background: #16823a;
    box-shadow: 0 0 12px #16823a;
}

.dot.off {
    background: #999;
}

.status-description {
    margin-top: 12px;
    color: #777;
}

.modules {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.module {
    background: white;
    padding: 20px;
    border-radius: 13px;
    border: 1px solid #e5e5e5;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.module-name {
    font-weight: 700;
}

.module small {
    display: block;
    color: #888;
    margin-top: 5px;
}

.badge {
    padding: 7px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 800;
}

.badge.on {
    background: #e6f7eb;
    color: #16823a;
}

.badge.off {
    background: #eee;
    color: #777;
}

.actions {
    margin-top: 30px;
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 5px 25px rgba(0,0,0,.06);
}

.btn {
    border: 0;
    border-radius: 10px;
    padding: 14px 20px;
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-green {
    background: #16823a;
    color: white;
}

.btn-red {
    background: #b3261e;
    color: white;
}

.btn-gray {
    background: #eee;
    color: #222;
}

.actions form {
    display: inline;
}

.warning {
    margin-top: 15px;
    background: #fff6df;
    color: #795b00;
    padding: 14px;
    border-radius: 10px;
}

@media(max-width:700px) {

    .modules {
        grid-template-columns: 1fr;
    }

    .header {
        padding: 18px;
    }

}

</style>

</head>

<body>

<header class="header">

    <div class="logo">
        Vita<span>bulk</span> Programador
    </div>

    <a
        href="/vitabulk/programador/index.php"
        class="back"
    >
        ← Voltar
    </a>

</header>

<div class="container">

<div class="title">

<h1>Sistema de vendas</h1>

<p>
Controle toda a adaptação do site para vendas.
</p>

</div>

<div class="status-box">

<div class="status">

<span class="dot <?= $vendas ? "on" : "off" ?>"></span>

<?php if ($vendas): ?>

    MODO VENDAS ATIVO

<?php else: ?>

    MODO CATÁLOGO

<?php endif; ?>

</div>

<div class="status-description">

<?php if ($vendas): ?>

O site está preparado para receber compras.

<?php else: ?>

O site está funcionando apenas como catálogo.

<?php endif; ?>

</div>

</div>

<div class="modules">

<div class="module">
<div>
<div class="module-name">Carrinho</div>
<small>Adicionar e remover produtos</small>
</div>
<span class="badge <?= $carrinho ? "on" : "off" ?>">
<?= $carrinho ? "ATIVO" : "OFF" ?>
</span>
</div>

<div class="module">
<div>
<div class="module-name">Checkout</div>
<small>Finalização das compras</small>
</div>
<span class="badge <?= $checkout ? "on" : "off" ?>">
<?= $checkout ? "ATIVO" : "OFF" ?>
</span>
</div>

<div class="module">
<div>
<div class="module-name">Promoções</div>
<small>Descontos nos produtos</small>
</div>
<span class="badge <?= $promocoes ? "on" : "off" ?>">
<?= $promocoes ? "ATIVO" : "OFF" ?>
</span>
</div>

<div class="module">
<div>
<div class="module-name">Pedidos</div>
<small>Gestão das compras</small>
</div>
<span class="badge <?= $pedidos ? "on" : "off" ?>">
<?= $pedidos ? "ATIVO" : "OFF" ?>
</span>
</div>

<div class="module">
<div>
<div class="module-name">Pagamentos</div>
<small>Sistema de pagamentos</small>
</div>
<span class="badge <?= $pagamentos ? "on" : "off" ?>">
<?= $pagamentos ? "ATIVO" : "OFF" ?>
</span>
</div>

<div class="module">
<div>
<div class="module-name">Payzeno</div>
<small>Pagamento online</small>
</div>
<span class="badge <?= $payzeno ? "on" : "off" ?>">
<?= $payzeno ? "ATIVO" : "OFF" ?>
</span>
</div>

<div class="module">
<div>
<div class="module-name">Login para compras</div>
<small>Conta obrigatória para comprar</small>
</div>
<span class="badge <?= $loginCompras ? "on" : "off" ?>">
<?= $loginCompras ? "ATIVO" : "OFF" ?>
</span>
</div>

</div>

<div class="actions">

<?php if (!$vendas): ?>

<h2>Adaptar site para vendas</h2>

<p style="margin-top:8px;color:#777;">
Ativa automaticamente todos os módulos necessários para começar a vender.
</p>

<form
    action="ativar_vendas.php"
    method="POST"
    style="margin-top:20px;"
    onsubmit="return confirm('Ativar o sistema de vendas?');"
>

<button
    type="submit"
    class="btn btn-green"
>
    🛒 ADAPTAR SITE PARA VENDAS
</button>

</form>

<?php else: ?>

<h2>Site preparado para vendas</h2>

<p style="margin-top:8px;color:#777;">
O sistema de vendas está atualmente ativo.
</p>

<form
    action="desativar_vendas.php"
    method="POST"
    style="margin-top:20px;"
    onsubmit="return confirm('Desativar temporariamente o sistema de vendas?');"
>

<button
    type="submit"
    class="btn btn-red"
>
    Desativar vendas
</button>

</form>

<div class="warning">
Desativar vendas não elimina produtos, promoções, pedidos ou configurações.
</div>

<?php endif; ?>

</div>

</div>

</body>
</html>