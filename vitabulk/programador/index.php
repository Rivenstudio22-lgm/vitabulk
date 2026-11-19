<?php

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/programador_auth.php";
require_once __DIR__ . "/../config/configuracoes.php";

exigirProgramador();

/*
|--------------------------------------------------------------------------
| FUNÇÕES
|--------------------------------------------------------------------------
*/

function e($valor)
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

function tabelaExiste($nome)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $nome);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $dados = $resultado->fetch_assoc();

    $stmt->close();

    return ((int)$dados["total"] > 0);
}

function contarTabela($tabela, $condicao = "")
{
    global $conn;

    $permitidas = [
        "produtos",
        "clientes",
        "visitas",
        "feedbacks",
        "manutencao_erros",
        "manutencao_historico",
        "pedidos",
        "promocoes",
        "produto_imagens",
        "categorias"
    ];

    if (!in_array($tabela, $permitidas, true)) {
        return 0;
    }

    if (!tabelaExiste($tabela)) {
        return 0;
    }

    $sql = "SELECT COUNT(*) AS total FROM `$tabela`";

    if ($condicao !== "") {
        $sql .= " WHERE " . $condicao;
    }

    $resultado = $conn->query($sql);

    if (!$resultado) {
        return 0;
    }

    $dados = $resultado->fetch_assoc();

    return (int)$dados["total"];
}

function obterConfiguracaoSeguro($chave, $padrao = "")
{
    return obterConfiguracao(
        $chave,
        $padrao
    );
}

/*
|--------------------------------------------------------------------------
| CONFIGURAÇÕES
|--------------------------------------------------------------------------
*/

$vendasAtivas = configuracaoAtiva(
    "vendas_ativo",
    false
);

$carrinhoAtivo = configuracaoAtiva(
    "carrinho_ativo",
    false
);

$checkoutAtivo = configuracaoAtiva(
    "checkout_ativo",
    false
);

$promocoesAtivas = configuracaoAtiva(
    "promocoes_ativo",
    false
);

$pedidosAtivos = configuracaoAtiva(
    "pedidos_ativo",
    false
);

$pagamentosAtivos = configuracaoAtiva(
    "pagamentos_ativo",
    false
);

$payzenoAtivo = configuracaoAtiva(
    "payzeno_ativo",
    false
);

$loginComprasAtivo = configuracaoAtiva(
    "login_compras_ativo",
    false
);

/*
|--------------------------------------------------------------------------
| ESTATÍSTICAS
|--------------------------------------------------------------------------
*/

$totalProdutos = contarTabela(
    "produtos"
);

$totalClientes = contarTabela(
    "clientes"
);

$totalVisitas = contarTabela(
    "visitas"
);

$totalFeedbacks = contarTabela(
    "feedbacks"
);

$totalErros = contarTabela(
    "manutencao_erros"
);

$errosAbertos = contarTabela(
    "manutencao_erros",
    "status = 'aberto'"
);

$totalPromocoes = contarTabela(
    "promocoes"
);

$totalPedidos = contarTabela(
    "pedidos"
);

$pedidosPendentes = contarTabela(
    "pedidos",
    "status IN ('pendente','aguardando_pagamento')"
);

/*
|--------------------------------------------------------------------------
| ÚLTIMOS ERROS
|--------------------------------------------------------------------------
*/

$ultimosErros = [];

if (tabelaExiste("manutencao_erros")) {

    $resultado = $conn->query("
        SELECT
            id,
            tipo,
            pagina,
            arquivo,
            linha,
            mensagem,
            severidade,
            status,
            criado_em
        FROM manutencao_erros
        ORDER BY id DESC
        LIMIT 6
    ");

    if ($resultado) {

        while ($erro = $resultado->fetch_assoc()) {
            $ultimosErros[] = $erro;
        }
    }
}

/*
|--------------------------------------------------------------------------
| ÚLTIMOS PEDIDOS
|--------------------------------------------------------------------------
*/

$ultimosPedidos = [];

if (
    $vendasAtivas &&
    tabelaExiste("pedidos")
) {

    $resultado = $conn->query("
        SELECT
            p.id,
            p.numero_pedido,
            p.total,
            p.status,
            p.criado_em,
            c.nome AS cliente_nome
        FROM pedidos p
        LEFT JOIN clientes c
            ON c.id = p.cliente_id
        ORDER BY p.id DESC
        LIMIT 5
    ");

    if ($resultado) {

        while ($pedido = $resultado->fetch_assoc()) {
            $ultimosPedidos[] = $pedido;
        }
    }
}

/*
|--------------------------------------------------------------------------
| MENSAGEM
|--------------------------------------------------------------------------
*/

$mensagem = "";

if (
    isset($_GET["sucesso"]) &&
    $_GET["sucesso"] === "vendas_ativadas"
) {
    $mensagem =
        "O sistema de vendas foi ativado.";
}

if (
    isset($_GET["sucesso"]) &&
    $_GET["sucesso"] === "vendas_desativadas"
) {
    $mensagem =
        "O sistema de vendas foi desativado.";
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
    Programador | Vitabulk
</title>

<style>

/* =========================================================
   RESET
========================================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --verde: #16823a;
    --verde-escuro: #0d642b;
    --verde-claro: #e9f7ee;

    --fundo: #f4f7f5;
    --branco: #ffffff;

    --texto: #172019;
    --texto-secundario: #738078;

    --borda: #e2e8e4;

    --vermelho: #d93025;
    --vermelho-claro: #fdecea;

    --laranja: #e58a00;
    --laranja-claro: #fff4df;

    --azul: #2878c8;
    --azul-claro: #eaf4ff;

    --sidebar: #101614;
    --sidebar-hover: #19231f;

    --sombra:
        0 8px 30px rgba(0,0,0,.06);
}

/* =========================================================
   BODY
========================================================= */

body {
    font-family:
        Inter,
        Arial,
        Helvetica,
        sans-serif;

    background: var(--fundo);
    color: var(--texto);
}

/* =========================================================
   LAYOUT
========================================================= */

.programador-layout {
    min-height: 100vh;
    display: flex;
}

/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {
    width: 260px;
    min-height: 100vh;

    background: var(--sidebar);
    color: white;

    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;

    display: flex;
    flex-direction: column;

    z-index: 1000;
}

.sidebar-logo {
    padding: 25px 22px;
    border-bottom: 1px solid rgba(255,255,255,.07);
}

.sidebar-logo strong {
    display: block;
    font-size: 21px;
    letter-spacing: .5px;
}

.sidebar-logo strong span {
    color: #36b96b;
}

.sidebar-logo small {
    display: block;
    margin-top: 5px;
    color: #829088;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

.sidebar-section {
    padding: 20px 15px 7px;
    color: #66736c;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.5px;
}

.sidebar-nav {
    padding: 8px 12px;
    overflow-y: auto;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 12px 13px;

    color: #c4cec8;
    text-decoration: none;

    border-radius: 9px;

    font-size: 14px;
    font-weight: 600;

    margin-bottom: 3px;

    transition: .2s;
}

.sidebar-nav a:hover {
    background: var(--sidebar-hover);
    color: white;
}

.sidebar-nav a.active {
    background: var(--verde);
    color: white;
}

.sidebar-icon {
    width: 24px;
    text-align: center;
    font-size: 16px;
}

.sidebar-footer {
    margin-top: auto;
    padding: 15px;
    border-top: 1px solid rgba(255,255,255,.07);
}

.programador-user {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.user-avatar {
    width: 38px;
    height: 38px;

    border-radius: 50%;

    background: var(--verde);

    display: flex;
    align-items: center;
    justify-content: center;

    font-weight: 800;
}

.user-info {
    min-width: 0;
}

.user-info strong {
    display: block;
    font-size: 13px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-info span {
    display: block;
    color: #718078;
    font-size: 11px;
    margin-top: 3px;
}

.logout {
    display: block;
    text-align: center;

    color: #c4cec8;
    text-decoration: none;

    border: 1px solid #2b3530;

    border-radius: 8px;

    padding: 9px;

    font-size: 12px;
}

.logout:hover {
    border-color: #c44;
    color: #ff8f8f;
}

/* =========================================================
   MAIN
========================================================= */

.main {
    margin-left: 260px;
    width: calc(100% - 260px);
    min-height: 100vh;
}

/* =========================================================
   TOPBAR
========================================================= */

.topbar {
    height: 76px;

    background: white;

    border-bottom: 1px solid var(--borda);

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 30px;

    position: sticky;
    top: 0;

    z-index: 100;
}

.topbar-left h1 {
    font-size: 20px;
}

.topbar-left p {
    color: var(--texto-secundario);
    font-size: 12px;
    margin-top: 4px;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.site-status {
    display: flex;
    align-items: center;
    gap: 7px;

    padding: 8px 12px;

    background: var(--verde-claro);

    color: var(--verde-escuro);

    border-radius: 20px;

    font-size: 12px;
    font-weight: 800;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--verde);
}

/* =========================================================
   CONTENT
========================================================= */

.content {
    padding: 30px;
    max-width: 1500px;
}

/* =========================================================
   ALERT
========================================================= */

.alert {
    background: var(--verde-claro);
    border: 1px solid #bce5c9;

    color: var(--verde-escuro);

    padding: 14px 16px;

    border-radius: 10px;

    margin-bottom: 25px;

    font-size: 14px;
    font-weight: 600;
}

/* =========================================================
   WELCOME
========================================================= */

.welcome {
    background:
        linear-gradient(
            120deg,
            #101614,
            #16231d
        );

    color: white;

    border-radius: 17px;

    padding: 30px;

    margin-bottom: 25px;

    position: relative;

    overflow: hidden;
}

.welcome::after {
    content: "";

    position: absolute;

    width: 250px;
    height: 250px;

    right: -80px;
    top: -100px;

    border-radius: 50%;

    background:
        rgba(22,130,58,.18);
}

.welcome h2 {
    font-size: 25px;
}

.welcome p {
    color: #aab6af;
    margin-top: 8px;
    max-width: 650px;
    line-height: 1.6;
}

.welcome-user {
    color: #39bd70;
}

/* =========================================================
   CARDS
========================================================= */

.stats {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;

    margin-bottom: 25px;
}

.stat {
    background: white;

    border: 1px solid var(--borda);

    border-radius: 14px;

    padding: 20px;

    box-shadow: var(--sombra);
}

.stat-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-label {
    color: var(--texto-secundario);
    font-size: 12px;
    font-weight: 700;
}

.stat-icon {
    width: 38px;
    height: 38px;

    border-radius: 10px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 18px;
}

.stat-number {
    font-size: 27px;
    font-weight: 800;
    margin-top: 15px;
}

.stat-description {
    color: #8b958f;
    font-size: 11px;
    margin-top: 5px;
}

/* =========================================================
   CORES DOS STATS
========================================================= */

.icon-green {
    background: var(--verde-claro);
}

.icon-blue {
    background: var(--azul-claro);
}

.icon-orange {
    background: var(--laranja-claro);
}

.icon-red {
    background: var(--vermelho-claro);
}

/* =========================================================
   GRID
========================================================= */

.grid-main {
    display: grid;

    grid-template-columns:
        minmax(0, 1.5fr)
        minmax(320px, 1fr);

    gap: 20px;

    margin-bottom: 25px;
}

.card {
    background: white;

    border: 1px solid var(--borda);

    border-radius: 15px;

    box-shadow: var(--sombra);

    overflow: hidden;
}

.card-header {
    padding: 19px 20px;

    border-bottom: 1px solid var(--borda);

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    font-size: 16px;
}

.card-header p {
    color: var(--texto-secundario);
    font-size: 11px;
    margin-top: 4px;
}

.card-link {
    color: var(--verde);
    text-decoration: none;
    font-size: 12px;
    font-weight: 800;
}

.card-body {
    padding: 20px;
}

/* =========================================================
   SALES PANEL
========================================================= */

.sales-panel {
    background:
        linear-gradient(
            135deg,
            #f5fbf7,
            #ffffff
        );

    border: 1px solid #cfe8d7;

    border-radius: 13px;

    padding: 20px;
}

.sales-status {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 15px;
}

.sales-status-left {
    display: flex;
    align-items: center;
    gap: 13px;
}

.sales-status-icon {
    width: 48px;
    height: 48px;

    background: var(--verde);

    color: white;

    border-radius: 12px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 23px;
}

.sales-status h3 {
    font-size: 17px;
}

.sales-status p {
    color: var(--texto-secundario);
    font-size: 12px;
    margin-top: 4px;
}

.sales-badge {
    padding: 8px 13px;

    border-radius: 20px;

    font-size: 11px;
    font-weight: 800;
}

.sales-badge.on {
    background: var(--verde-claro);
    color: var(--verde-escuro);
}

.sales-badge.off {
    background: #eee;
    color: #777;
}

.sales-modules {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 8px;

    margin-top: 18px;
}

.sales-module {
    background: white;

    border: 1px solid var(--borda);

    border-radius: 9px;

    padding: 11px;

    text-align: center;

    font-size: 11px;
}

.sales-module strong {
    display: block;

    margin-top: 5px;

    font-size: 10px;
}

.module-on {
    color: var(--verde);
}

.module-off {
    color: #999;
}

.sales-button {
    display: inline-block;

    margin-top: 18px;

    padding: 12px 17px;

    background: var(--verde);

    color: white;

    text-decoration: none;

    border-radius: 9px;

    font-size: 12px;
    font-weight: 800;
}

.sales-button:hover {
    background: var(--verde-escuro);
}

/* =========================================================
   QUICK ACTIONS
========================================================= */

.quick-grid {
    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 10px;
}

.quick-action {
    display: flex;

    align-items: center;

    gap: 12px;

    padding: 14px;

    border: 1px solid var(--borda);

    border-radius: 10px;

    color: var(--texto);

    text-decoration: none;

    transition: .2s;
}

.quick-action:hover {
    border-color: #aad5b7;

    background: #f8fcf9;

    transform: translateY(-1px);
}

.quick-icon {
    width: 37px;
    height: 37px;

    border-radius: 9px;

    background: var(--verde-claro);

    color: var(--verde-escuro);

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;
}

.quick-action strong {
    display: block;
    font-size: 13px;
}

.quick-action span {
    display: block;
    color: #89938d;
    font-size: 10px;
    margin-top: 3px;
}

/* =========================================================
   ERRORS
========================================================= */

.error-item {
    padding: 14px 0;

    border-bottom: 1px solid var(--borda);
}

.error-item:last-child {
    border-bottom: 0;
}

.error-top {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.error-type {
    font-size: 12px;
    font-weight: 800;
}

.error-message {
    color: #66716b;
    font-size: 11px;

    margin-top: 6px;

    line-height: 1.5;

    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;

    overflow: hidden;
}

.error-date {
    color: #9aa39e;
    font-size: 10px;
    margin-top: 7px;
}

.error-badge {
    padding: 4px 8px;

    border-radius: 15px;

    font-size: 9px;
    font-weight: 800;
}

.error-open {
    background: var(--vermelho-claro);
    color: var(--vermelho);
}

.error-resolved {
    background: var(--verde-claro);
    color: var(--verde-escuro);
}

.error-ignored {
    background: #eee;
    color: #777;
}

/* =========================================================
   ORDERS
========================================================= */

.order-row {
    display: grid;

    grid-template-columns:
        1.2fr
        1.3fr
        .8fr
        .9fr;

    gap: 10px;

    align-items: center;

    padding: 13px 0;

    border-bottom: 1px solid var(--borda);
}

.order-row:last-child {
    border-bottom: 0;
}

.order-number {
    color: var(--verde);
    font-size: 12px;
    font-weight: 800;
}

.order-client {
    font-size: 12px;
}

.order-date {
    color: #89938d;
    font-size: 10px;
}

.order-total {
    font-size: 12px;
    font-weight: 800;
}

.order-status {
    padding: 5px 8px;

    border-radius: 15px;

    background: #eef2ef;

    color: #65716a;

    font-size: 9px;
    font-weight: 800;

    text-align: center;
}

/* =========================================================
   SYSTEM
========================================================= */

.system-grid {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 12px;
}

.system-item {
    padding: 15px;

    background: #fafcfb;

    border: 1px solid var(--borda);

    border-radius: 10px;
}

.system-item-label {
    color: #87918b;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: .5px;
}

.system-item-value {
    margin-top: 7px;

    font-weight: 800;

    font-size: 13px;
}

.system-online {
    color: var(--verde);
}

.system-warning {
    color: var(--laranja);
}

/* =========================================================
   EMPTY
========================================================= */

.empty {
    padding: 30px 10px;

    text-align: center;

    color: #929b96;

    font-size: 12px;
}

/* =========================================================
   MOBILE
========================================================= */

.mobile-menu {
    display: none;
}

@media(max-width:1100px) {

    .stats {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .grid-main {
        grid-template-columns: 1fr;
    }

    .sales-modules {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .system-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }
}

@media(max-width:800px) {

    .sidebar {
        transform: translateX(-100%);
        transition: .25s;
    }

    .sidebar.open {
        transform: translateX(0);
    }

    .main {
        margin-left: 0;
        width: 100%;
    }

    .mobile-menu {
        display: flex;

        width: 40px;
        height: 40px;

        align-items: center;
        justify-content: center;

        border: 1px solid var(--borda);

        background: white;

        border-radius: 8px;

        cursor: pointer;

        font-size: 18px;
    }

    .topbar-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .content {
        padding: 20px 15px;
    }

    .topbar {
        padding: 0 15px;
    }

    .topbar-right {
        display: none;
    }

    .welcome {
        padding: 22px;
    }

    .quick-grid {
        grid-template-columns: 1fr;
    }

    .order-row {
        grid-template-columns:
            1fr
            1fr;
    }
}

@media(max-width:550px) {

    .stats {
        grid-template-columns: 1fr;
    }

    .sales-status {
        align-items: flex-start;
        flex-direction: column;
    }

    .sales-modules {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .system-grid {
        grid-template-columns: 1fr;
    }

    .order-row {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<div class="programador-layout">

<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside
    class="sidebar"
    id="sidebar"
>

<div class="sidebar-logo">

<strong>
    Vita<span>bulk</span>
</strong>

<small>
    Painel do programador
</small>

</div>

<nav class="sidebar-nav">

<div class="sidebar-section">
    PRINCIPAL
</div>

<a
    href="/vitabulk/programador/index.php"
    class="active"
>
    <span class="sidebar-icon">⌂</span>
    Dashboard
</a>

<a
    href="/vitabulk/"
    target="_blank"
>
    <span class="sidebar-icon">↗</span>
    Ver site
</a>

<div class="sidebar-section">
    MANUTENÇÃO
</div>

<a href="/vitabulk/programador/erros.php">
    <span class="sidebar-icon">⚠</span>
    Erros
</a>

<a href="/vitabulk/programador/historico.php">
    <span class="sidebar-icon">◷</span>
    Histórico
</a>

<a href="/vitabulk/programador/backups.php">
    <span class="sidebar-icon">▣</span>
    Backups
</a>

<div class="sidebar-section">
    VENDAS
</div>

<a
    href="/vitabulk/programador/vendas.php"
>
    <span class="sidebar-icon">🛒</span>
    Sistema de vendas
</a>

<?php if ($vendasAtivas): ?>

<a href="/vitabulk/admin/pedidos.php">
    <span class="sidebar-icon">▤</span>
    Pedidos
</a>

<a href="/vitabulk/admin/promocoes.php">
    <span class="sidebar-icon">%</span>
    Promoções
</a>

<?php endif; ?>

<div class="sidebar-section">
    FERRAMENTAS
</div>

<a href="/vitabulk/programador/diagnostico.php">
    <span class="sidebar-icon">⌕</span>
    Diagnóstico
</a>

<a href="/vitabulk/programador/arquivos.php">
    <span class="sidebar-icon">▤</span>
    Arquivos
</a>

<a href="/vitabulk/admin/definicoes.php">
    <span class="sidebar-icon">⚙</span>
    Definições
</a>

</nav>

<div class="sidebar-footer">

<div class="programador-user">

<div class="user-avatar">
    <?= e(
        strtoupper(
            substr(
                programadorNome() ?: "P",
                0,
                1
            )
        )
    ) ?>
</div>

<div class="user-info">

<strong>
    <?= e(programadorNome() ?: "Programador") ?>
</strong>

<span>
    Programador
</span>

</div>

</div>

<a
    href="/vitabulk/programador/logout.php"
    class="logout"
>
    Terminar sessão
</a>

</div>

</aside>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="main">

<header class="topbar">

<div class="topbar-left">

<button
    type="button"
    class="mobile-menu"
    id="mobileMenu"
>
    ☰
</button>

<div>

<h1>
    Dashboard do Programador
</h1>

<p>
    Monitorização e manutenção do Vitabulk
</p>

</div>

</div>

<div class="topbar-right">

<div class="site-status">

<span class="status-dot"></span>

SITE ONLINE

</div>

</div>

</header>


<div class="content">


<?php if ($mensagem): ?>

<div class="alert">
    ✓ <?= e($mensagem) ?>
</div>

<?php endif; ?>


<!-- =====================================================
     WELCOME
===================================================== -->

<section class="welcome">

<h2>

Olá,
<span class="welcome-user">
<?= e(
    programadorNome() ?: "Programador"
) ?>
</span>

</h2>

<p>

Bem-vindo ao centro técnico da Vitabulk.
Aqui podes monitorizar o site, verificar erros,
gerir o sistema de vendas e preparar novas funcionalidades.

</p>

</section>


<!-- =====================================================
     ESTATÍSTICAS
===================================================== -->

<section class="stats">

<div class="stat">

<div class="stat-top">

<span class="stat-label">
PRODUTOS
</span>

<div class="stat-icon icon-green">
    ▣
</div>

</div>

<div class="stat-number">
<?= $totalProdutos ?>
</div>

<div class="stat-description">
Produtos cadastrados
</div>

</div>


<div class="stat">

<div class="stat-top">

<span class="stat-label">
CLIENTES
</span>

<div class="stat-icon icon-blue">
    ♙
</div>

</div>

<div class="stat-number">
<?= $totalClientes ?>
</div>

<div class="stat-description">
Clientes registrados
</div>

</div>


<div class="stat">

<div class="stat-top">

<span class="stat-label">
VISITAS
</span>

<div class="stat-icon icon-orange">
    ◉
</div>

</div>

<div class="stat-number">
<?= $totalVisitas ?>
</div>

<div class="stat-description">
Visitas registradas
</div>

</div>


<div class="stat">

<div class="stat-top">

<span class="stat-label">
ERROS ABERTOS
</span>

<div class="stat-icon icon-red">
    ⚠
</div>

</div>

<div class="stat-number">
<?= $errosAbertos ?>
</div>

<div class="stat-description">
De <?= $totalErros ?> ocorrências registradas
</div>

</div>

</section>


<!-- =====================================================
     GRID PRINCIPAL
===================================================== -->

<div class="grid-main">


<!-- =====================================================
     VENDAS
===================================================== -->

<div class="card">

<div class="card-header">

<div>

<h3>
Sistema de vendas
</h3>

<p>
Estado atual do modo loja
</p>

</div>

<a
    href="/vitabulk/programador/vendas.php"
    class="card-link"
>
    Gerir →
</a>

</div>

<div class="card-body">

<div class="sales-panel">

<div class="sales-status">

<div class="sales-status-left">

<div class="sales-status-icon">
    🛒
</div>

<div>

<h3>

<?php if ($vendasAtivas): ?>

Vendas ativas

<?php else: ?>

Modo catálogo

<?php endif; ?>

</h3>

<p>

<?php if ($vendasAtivas): ?>

O site está preparado para receber compras.

<?php else: ?>

O site ainda funciona apenas como catálogo.

<?php endif; ?>

</p>

</div>

</div>

<div
    class="sales-badge
    <?= $vendasAtivas ? "on" : "off" ?>"
>

<?= $vendasAtivas
    ? "ATIVO"
    : "DESATIVADO"
?>

</div>

</div>


<div class="sales-modules">

<div class="sales-module">

Carrinho

<strong
    class="<?= $carrinhoAtivo
        ? "module-on"
        : "module-off" ?>"
>
<?= $carrinhoAtivo
    ? "ATIVO"
    : "OFF" ?>
</strong>

</div>

<div class="sales-module">

Checkout

<strong
    class="<?= $checkoutAtivo
        ? "module-on"
        : "module-off" ?>"
>
<?= $checkoutAtivo
    ? "ATIVO"
    : "OFF" ?>
</strong>

</div>

<div class="sales-module">

Promoções

<strong
    class="<?= $promocoesAtivas
        ? "module-on"
        : "module-off" ?>"
>
<?= $promocoesAtivas
    ? "ATIVO"
    : "OFF" ?>
</strong>

</div>

<div class="sales-module">

Pedidos

<strong
    class="<?= $pedidosAtivos
        ? "module-on"
        : "module-off" ?>"
>
<?= $pedidosAtivos
    ? "ATIVO"
    : "OFF" ?>
</strong>

</div>

<div class="sales-module">

Pagamentos

<strong
    class="<?= $pagamentosAtivos
        ? "module-on"
        : "module-off" ?>"
>
<?= $pagamentosAtivos
    ? "ATIVO"
    : "OFF" ?>
</strong>

</div>

<div class="sales-module">

Payzeno

<strong
    class="<?= $payzenoAtivo
        ? "module-on"
        : "module-off" ?>"
>
<?= $payzenoAtivo
    ? "ATIVO"
    : "OFF" ?>
</strong>

</div>

<div class="sales-module">

Login

<strong
    class="<?= $loginComprasAtivo
        ? "module-on"
        : "module-off" ?>"
>
<?= $loginComprasAtivo
    ? "ATIVO"
    : "OFF" ?>
</strong>

</div>

<div class="sales-module">

Pedidos

<strong
    class="<?= $pedidosAtivos
        ? "module-on"
        : "module-off" ?>"
>
<?= $pedidosAtivos
    ? "ATIVO"
    : "OFF" ?>
</strong>

</div>

</div>


<a
    href="/vitabulk/programador/vendas.php"
    class="sales-button"
>

<?= $vendasAtivas
    ? "Gerir sistema de vendas"
    : "🛒 Adaptar site para vendas"
?>

</a>

</div>

</div>

</div>


<!-- =====================================================
     AÇÕES RÁPIDAS
===================================================== -->

<div class="card">

<div class="card-header">

<div>

<h3>
Ações rápidas
</h3>

<p>
Ferramentas utilizadas com frequência
</p>

</div>

</div>

<div class="card-body">

<div class="quick-grid">

<a
    href="/vitabulk/programador/diagnostico.php"
    class="quick-action"
>

<div class="quick-icon">
    ⌕
</div>

<div>

<strong>
Executar diagnóstico
</strong>

<span>
Verificar problemas técnicos
</span>

</div>

</a>


<a
    href="/vitabulk/"
    target="_blank"
    class="quick-action"
>

<div class="quick-icon">
    ↗
</div>

<div>

<strong>
Abrir site
</strong>

<span>
Ver versão pública
</span>

</div>

</a>


<a
    href="/vitabulk/admin/produtos.php"
    class="quick-action"
>

<div class="quick-icon">
    ▣
</div>

<div>

<strong>
Produtos
</strong>

<span>
Gerir catálogo
</span>

</div>

</a>


<a
    href="/vitabulk/admin/definicoes.php"
    class="quick-action"
>

<div class="quick-icon">
    ⚙
</div>

<div>

<strong>
Definições
</strong>

<span>
Configurar o sistema
</span>

</div>

</a>


<a
    href="/vitabulk/programador/erros.php"
    class="quick-action"
>

<div class="quick-icon">
    ⚠
</div>

<div>

<strong>
Ver erros
</strong>

<span>
<?= $errosAbertos ?>
erro(s) aberto(s)
</span>

</div>

</a>


<a
    href="/vitabulk/programador/backups.php"
    class="quick-action"
>

<div class="quick-icon">
    ▣
</div>

<div>

<strong>
Backups
</strong>

<span>
Proteção dos ficheiros
</span>

</div>

</a>

</div>

</div>

</div>

</div>


<!-- =====================================================
     PEDIDOS + ERROS
===================================================== -->

<div class="grid-main">


<div class="card">

<div class="card-header">

<div>

<h3>
Últimos pedidos
</h3>

<p>
Atividade recente de vendas
</p>

</div>

<?php if ($vendasAtivas): ?>

<a
    href="/vitabulk/admin/pedidos.php"
    class="card-link"
>
Ver todos →
</a>

<?php endif; ?>

</div>

<div class="card-body">

<?php if (!$vendasAtivas): ?>

<div class="empty">

O sistema de vendas está desativado.

<br>

Ative-o para começar a receber pedidos.

</div>

<?php elseif (empty($ultimosPedidos)): ?>

<div class="empty">

Ainda não existem pedidos.

</div>

<?php else: ?>

<?php foreach ($ultimosPedidos as $pedido): ?>

<div class="order-row">

<div>

<div class="order-number">

#<?= e($pedido["numero_pedido"]) ?>

</div>

<div class="order-date">

<?= e($pedido["criado_em"]) ?>

</div>

</div>

<div class="order-client">

<?= e(
    $pedido["cliente_nome"]
    ?: "Cliente"
) ?>

</div>

<div class="order-total">

<?= number_format(
    (float)$pedido["total"],
    2,
    ",",
    "."
) ?>

MT

</div>

<div class="order-status">

<?= e(
    strtoupper(
        str_replace(
            "_",
            " ",
            $pedido["status"]
        )
    )
) ?>

</div>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>

</div>


<div class="card">

<div class="card-header">

<div>

<h3>
Últimos erros
</h3>

<p>
Eventos registados pelo sistema
</p>

</div>

<a
    href="/vitabulk/programador/erros.php"
    class="card-link"
>
Ver todos →
</a>

</div>

<div class="card-body">

<?php if (empty($ultimosErros)): ?>

<div class="empty">

✓ Nenhum erro registado.

</div>

<?php else: ?>

<?php foreach ($ultimosErros as $erro): ?>

<div class="error-item">

<div class="error-top">

<div class="error-type">

<?= e(
    $erro["tipo"]
) ?>

</div>

<div
    class="error-badge
    <?=
        $erro["status"] === "aberto"
        ? "error-open"
        : (
            $erro["status"] === "resolvido"
            ? "error-resolved"
            : "error-ignored"
        )
    ?>"
>

<?= e(
    strtoupper(
        $erro["status"]
    )
) ?>

</div>

</div>

<div class="error-message">

<?= e(
    $erro["mensagem"]
) ?>

</div>

<div class="error-date">

<?= e(
    $erro["criado_em"]
) ?>

<?php if ($erro["arquivo"]): ?>

 · <?= e(
    $erro["arquivo"]
) ?>

<?php endif; ?>

<?php if ($erro["linha"]): ?>

 :<?= (int)$erro["linha"] ?>

<?php endif; ?>

</div>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>

</div>

</div>


<!-- =====================================================
     SISTEMA
===================================================== -->

<div class="card">

<div class="card-header">

<div>

<h3>
Estado do sistema
</h3>

<p>
Resumo dos principais módulos da plataforma
</p>

</div>

</div>

<div class="card-body">

<div class="system-grid">

<div class="system-item">

<div class="system-item-label">
Site
</div>

<div class="system-item-value system-online">
ONLINE
</div>

</div>


<div class="system-item">

<div class="system-item-label">
Base de dados
</div>

<div class="system-item-value system-online">
CONECTADA
</div>

</div>


<div class="system-item">

<div class="system-item-label">
Vendas
</div>

<div
    class="system-item-value
    <?= $vendasAtivas
        ? "system-online"
        : "system-warning" ?>"
>

<?= $vendasAtivas
    ? "ATIVO"
    : "CATÁLOGO"
?>

</div>

</div>


<div class="system-item">

<div class="system-item-label">
Payzeno
</div>

<div
    class="system-item-value
    <?= $payzenoAtivo
        ? "system-online"
        : "system-warning" ?>"
>

<?= $payzenoAtivo
    ? "ATIVO"
    : "NÃO ATIVO"
?>

</div>

</div>


<div class="system-item">

<div class="system-item-label">
Produtos
</div>

<div class="system-item-value">
<?= $totalProdutos ?>
</div>

</div>


<div class="system-item">

<div class="system-item-label">
Clientes
</div>

<div class="system-item-value">
<?= $totalClientes ?>
</div>

</div>


<div class="system-item">

<div class="system-item-label">
Feedbacks
</div>

<div class="system-item-value">
<?= $totalFeedbacks ?>
</div>

</div>


<div class="system-item">

<div class="system-item-label">
Promoções
</div>

<div class="system-item-value">
<?= $totalPromocoes ?>
</div>

</div>

</div>

</div>

</div>

</div>

</main>

</div>


<script>

const mobileMenu =
    document.getElementById("mobileMenu");

const sidebar =
    document.getElementById("sidebar");

if (mobileMenu && sidebar) {

    mobileMenu.addEventListener(
        "click",
        function () {

            sidebar.classList.toggle(
                "open"
            );

        }
    );

}

</script>

</body>
</html>