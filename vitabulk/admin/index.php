<?php

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/configuracoes.php";
require_once __DIR__ . "/../config/vendas.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php?tipo=admin");
    exit;
}

/* =========================================================
   DADOS DO ADMIN
========================================================= */

$adminNome = $_SESSION["admin_nome"] ?? "Administrador";
$adminEmail = $_SESSION["admin_email"] ?? "";

/* =========================================================
   ESTATÍSTICAS
========================================================= */

$totalProdutos = 0;
$totalCategorias = 0;
$totalClientes = 0;
$totalPedidos = 0;
$totalVisitas = 0;
$totalFeedbacks = 0;
$totalVendas = 0;

$resultado = $conn->query("SELECT COUNT(*) AS total FROM produtos");

if ($resultado) {
    $totalProdutos = (int)$resultado->fetch_assoc()["total"];
}

$resultado = $conn->query("SELECT COUNT(*) AS total FROM categorias");

if ($resultado) {
    $totalCategorias = (int)$resultado->fetch_assoc()["total"];
}

$resultado = $conn->query("SELECT COUNT(*) AS total FROM clientes");

if ($resultado) {
    $totalClientes = (int)$resultado->fetch_assoc()["total"];
}

$resultado = $conn->query("SELECT COUNT(*) AS total FROM pedidos");

if ($resultado) {
    $totalPedidos = (int)$resultado->fetch_assoc()["total"];
}

$resultado = $conn->query("SELECT COUNT(*) AS total FROM visitas");

if ($resultado) {
    $totalVisitas = (int)$resultado->fetch_assoc()["total"];
}

$resultado = $conn->query("SELECT COUNT(*) AS total FROM feedbacks");

if ($resultado) {
    $totalFeedbacks = (int)$resultado->fetch_assoc()["total"];
}

$resultado = $conn->query("
    SELECT COALESCE(SUM(total), 0) AS total
    FROM pedidos
    WHERE status IN ('pago','processando','enviado','entregue')
");

if ($resultado) {
    $totalVendas = (float)$resultado->fetch_assoc()["total"];
}

/* =========================================================
   PEDIDOS RECENTES
========================================================= */

$pedidosRecentes = [];

$resultado = $conn->query("
    SELECT
        p.id,
        p.numero_pedido,
        p.total,
        p.status,
        p.criado_em,
        c.nome AS cliente_nome
    FROM pedidos p
    INNER JOIN clientes c
        ON c.id = p.cliente_id
    ORDER BY p.id DESC
    LIMIT 6
");

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $pedidosRecentes[] = $linha;
    }
}

/* =========================================================
   PRODUTOS RECENTES
========================================================= */

$produtosRecentes = [];

$resultado = $conn->query("
    SELECT
        id,
        nome,
        categoria,
        preco,
        stock,
        status,
        imagem,
        criado_em
    FROM produtos
    ORDER BY id DESC
    LIMIT 6
");

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $produtosRecentes[] = $linha;
    }
}

/* =========================================================
   ESTADO DAS VENDAS
========================================================= */

$vendasAtivas = vendasAtivas();

function adminPreco($valor)
{
    return number_format(
        (float)$valor,
        2,
        ",",
        "."
    ) . " MT";
}

function adminStatusPedido($status)
{
    $nomes = [
        "pendente" => "Pendente",
        "aguardando_pagamento" => "Aguardando pagamento",
        "pago" => "Pago",
        "processando" => "Processando",
        "enviado" => "Enviado",
        "entregue" => "Entregue",
        "cancelado" => "Cancelado",
        "reembolsado" => "Reembolsado"
    ];

    return $nomes[$status] ?? ucfirst($status);
}

function adminStatusClasse($status)
{
    if (in_array($status, ["pago", "entregue"], true)) {
        return "badge-success";
    }

    if (in_array($status, ["cancelado", "reembolsado"], true)) {
        return "badge-danger";
    }

    if (in_array($status, ["pendente", "aguardando_pagamento"], true)) {
        return "badge-warning";
    }

    return "badge-info";
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

    <title>Dashboard | Vitabulk Admin</title>

    <link
        rel="stylesheet"
        href="/vitabulk/admin/assets/css/admin.css"
    >

</head>

<body>

<div class="admin-layout">

    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="sidebar" id="sidebar">

        <div class="brand">
            Vita<span>bulk</span> Admin
        </div>

        <nav class="nav">

            <div class="nav-title">
                Principal
            </div>

            <a
                href="index.php"
                class="active"
            >
                Dashboard
            </a>

            <a href="produtos.php">
                Produtos
            </a>

            <a href="categorias.php">
                Categorias
            </a>

            <a href="promocoes.php">
                Promoções
            </a>

            <a href="pedidos.php">
                Pedidos
            </a>

            <div class="nav-title">
                Clientes
            </div>

            <a href="clientes.php">
                Clientes
            </a>

            <a href="feedbacks.php">
                Feedbacks
            </a>

            <div class="nav-title">
                Sistema
            </div>

            <a href="definicoes.php">
                Definições
            </a>

            <a href="criar_admin.php">
                Criar administrador
            </a>

            <a href="criar_programador.php">
                Criar programador
            </a>

            <a href="/vitabulk/programador/index.php">
                Programador
            </a>

            <a
                href="logout.php"
                class="logout"
            >
                Sair
            </a>

        </nav>

    </aside>

    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="admin-main">

        <!-- TOPBAR -->

        <header class="topbar">

            <div
                class="topbar-left"
                style="display:flex;align-items:center;gap:12px;"
            >

                <button
                    type="button"
                    class="menu-toggle"
                    id="menuToggle"
                >
                    ☰
                </button>

                <div>

                    <h1>
                        Dashboard
                    </h1>

                    <p>
                        Gestão da Vitabulk Alimentos
                    </p>

                </div>

            </div>

            <div class="admin-user">

                <div class="admin-avatar">
                    <?php
                    echo strtoupper(
                        substr($adminNome, 0, 1)
                    );
                    ?>
                </div>

                <div class="admin-user-info">

                    <strong>
                        <?= htmlspecialchars($adminNome) ?>
                    </strong>

                    <span>
                        Administrador
                    </span>

                </div>

            </div>

        </header>

        <!-- CONTENT -->

        <section class="admin-content">

            <!-- =================================================
                 ESTATÍSTICAS
            ================================================== -->

            <div class="stats-grid">

                <div class="stat-card">

                    <div class="stat-top">

                        <div class="stat-title">
                            Produtos
                        </div>

                        <div class="stat-icon">
                            P
                        </div>

                    </div>

                    <div class="stat-value">
                        <?= $totalProdutos ?>
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-top">

                        <div class="stat-title">
                            Categorias
                        </div>

                        <div class="stat-icon">
                            C
                        </div>

                    </div>

                    <div class="stat-value">
                        <?= $totalCategorias ?>
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-top">

                        <div class="stat-title">
                            Clientes
                        </div>

                        <div class="stat-icon">
                            U
                        </div>

                    </div>

                    <div class="stat-value">
                        <?= $totalClientes ?>
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-top">

                        <div class="stat-title">
                            Pedidos
                        </div>

                        <div class="stat-icon">
                            $
                        </div>

                    </div>

                    <div class="stat-value">
                        <?= $totalPedidos ?>
                    </div>

                </div>

            </div>

            <!-- =================================================
                 VENDAS / AÇÕES
            ================================================== -->

            <div class="card" style="margin-bottom:20px;">

                <div class="card-header">

                    <div>

                        <h2>
                            Estado da loja
                        </h2>

                        <p>
                            Controle o funcionamento comercial do site.
                        </p>

                    </div>

                    <?php if ($vendasAtivas): ?>

                        <span class="badge badge-success">
                            Vendas ativas
                        </span>

                    <?php else: ?>

                        <span class="badge badge-gray">
                            Vendas desativadas
                        </span>

                    <?php endif; ?>

                </div>

                <div
                    style="
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                    gap:20px;
                    flex-wrap:wrap;
                    "
                >

                    <div>

                        <?php if ($vendasAtivas): ?>

                            <strong>
                                A loja está preparada para receber pedidos.
                            </strong>

                            <p
                                style="
                                margin-top:5px;
                                color:#6b7280;
                                font-size:12px;
                                "
                            >
                                Clientes podem utilizar carrinho,
                                checkout e pagamentos.
                            </p>

                        <?php else: ?>

                            <strong>
                                O site está a apresentar a empresa e os produtos.
                            </strong>

                            <p
                                style="
                                margin-top:5px;
                                color:#6b7280;
                                font-size:12px;
                                "
                            >
                                As funções comerciais permanecem desativadas.
                            </p>

                        <?php endif; ?>

                    </div>

                    <a
                        href="definicoes.php"
                        class="btn btn-primary"
                    >
                        Gerir vendas
                    </a>

                </div>

            </div>

            <!-- =================================================
                 DASHBOARD GRID
            ================================================== -->

            <div class="dashboard-grid">

                <!-- PEDIDOS -->

                <div class="card">

                    <div class="card-header">

                        <div>

                            <h2>
                                Pedidos recentes
                            </h2>

                            <p>
                                Últimos pedidos registados.
                            </p>

                        </div>

                        <a
                            href="pedidos.php"
                            class="btn btn-secondary btn-sm"
                        >
                            Ver todos
                        </a>

                    </div>

                    <?php if (count($pedidosRecentes) > 0): ?>

                        <div class="table-wrapper">

                            <table class="admin-table">

                                <thead>

                                    <tr>

                                        <th>
                                            Pedido
                                        </th>

                                        <th>
                                            Cliente
                                        </th>

                                        <th>
                                            Total
                                        </th>

                                        <th>
                                            Estado
                                        </th>

                                        <th>
                                            Data
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                <?php foreach ($pedidosRecentes as $pedido): ?>

                                    <tr>

                                        <td>

                                            <a
                                                href="ver_pedido.php?id=<?= (int)$pedido["id"] ?>"
                                                class="order-number"
                                            >
                                                #<?= htmlspecialchars($pedido["numero_pedido"]) ?>
                                            </a>

                                        </td>

                                        <td>
                                            <?= htmlspecialchars($pedido["cliente_nome"]) ?>
                                        </td>

                                        <td class="order-total">

                                            <?= adminPreco($pedido["total"]) ?>

                                        </td>

                                        <td>

                                            <span
                                                class="badge <?= adminStatusClasse($pedido["status"]) ?>"
                                            >
                                                <?= htmlspecialchars(
                                                    adminStatusPedido(
                                                        $pedido["status"]
                                                    )
                                                ) ?>
                                            </span>

                                        </td>

                                        <td>

                                            <?= date(
                                                "d/m/Y H:i",
                                                strtotime($pedido["criado_em"])
                                            ) ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="empty-state">

                            <strong>
                                Ainda não existem pedidos.
                            </strong>

                            <p>
                                Os novos pedidos aparecerão aqui.
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

                <!-- RESUMO -->

                <div class="card">

                    <div class="card-header">

                        <div>

                            <h2>
                                Resumo
                            </h2>

                            <p>
                                Dados gerais da plataforma.
                            </p>

                        </div>

                    </div>

                    <div
                        style="
                        display:grid;
                        gap:13px;
                        "
                    >

                        <div class="switch-row">

                            <div class="switch-text">

                                <strong>
                                    Visitas
                                </strong>

                                <span>
                                    Acessos registados
                                </span>

                            </div>

                            <strong>
                                <?= $totalVisitas ?>
                            </strong>

                        </div>

                        <div class="switch-row">

                            <div class="switch-text">

                                <strong>
                                    Feedbacks
                                </strong>

                                <span>
                                    Avaliações recebidas
                                </span>

                            </div>

                            <strong>
                                <?= $totalFeedbacks ?>
                            </strong>

                        </div>

                        <div class="switch-row">

                            <div class="switch-text">

                                <strong>
                                    Vendas
                                </strong>

                                <span>
                                    Pedidos concluídos
                                </span>

                            </div>

                            <strong>
                                <?= adminPreco($totalVendas) ?>
                            </strong>

                        </div>

                    </div>

                </div>

            </div>

            <!-- =================================================
                 PRODUTOS
            ================================================== -->

            <div
                class="card"
                style="margin-top:20px;"
            >

                <div class="card-header">

                    <div>

                        <h2>
                            Produtos recentes
                        </h2>

                        <p>
                            Produtos adicionados recentemente.
                        </p>

                    </div>

                    <div
                        style="
                        display:flex;
                        gap:8px;
                        flex-wrap:wrap;
                        "
                    >

                        <a
                            href="adicionar_produto.php"
                            class="btn btn-primary btn-sm"
                        >
                            Adicionar produto
                        </a>

                        <a
                            href="produtos.php"
                            class="btn btn-secondary btn-sm"
                        >
                            Ver produtos
                        </a>

                    </div>

                </div>

                <?php if (count($produtosRecentes) > 0): ?>

                    <div class="table-wrapper">

                        <table class="admin-table">

                            <thead>

                                <tr>

                                    <th>
                                        Produto
                                    </th>

                                    <th>
                                        Categoria
                                    </th>

                                    <th>
                                        Preço
                                    </th>

                                    <th>
                                        Stock
                                    </th>

                                    <th>
                                        Estado
                                    </th>

                                    <th>
                                        Ações
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                            <?php foreach ($produtosRecentes as $produto): ?>

                                <tr>

                                    <td>

                                        <div class="product-admin-info">

                                            <?php if (!empty($produto["imagem"])): ?>

                                                <img
                                                    src="/vitabulk/<?= htmlspecialchars($produto["imagem"]) ?>"
                                                    alt="<?= htmlspecialchars($produto["nome"]) ?>"
                                                    class="product-admin-image"
                                                >

                                            <?php else: ?>

                                                <div
                                                    class="product-admin-image"
                                                    style="
                                                    display:flex;
                                                    align-items:center;
                                                    justify-content:center;
                                                    "
                                                >
                                                    V
                                                </div>

                                            <?php endif; ?>

                                            <div>

                                                <strong>
                                                    <?= htmlspecialchars($produto["nome"]) ?>
                                                </strong>

                                                <small>
                                                    #<?= (int)$produto["id"] ?>
                                                </small>

                                            </div>

                                        </div>

                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $produto["categoria"] ?: "Sem categoria"
                                        ) ?>
                                    </td>

                                    <td>

                                        <?php if ($produto["preco"] !== null): ?>

                                            <?= adminPreco($produto["preco"]) ?>

                                        <?php else: ?>

                                            <span class="badge badge-gray">
                                                Sem preço
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php
                                        $stock = (int)$produto["stock"];

                                        if ($stock <= 0) {
                                            $classeStock = "stock-zero";
                                        } elseif ($stock <= 5) {
                                            $classeStock = "stock-low";
                                        } else {
                                            $classeStock = "stock-good";
                                        }
                                        ?>

                                        <span class="<?= $classeStock ?>">
                                            <?= $stock ?>
                                        </span>

                                    </td>

                                    <td>

                                        <?php if ($produto["status"] === "ativo"): ?>

                                            <span class="badge badge-success">
                                                Ativo
                                            </span>

                                        <?php else: ?>

                                            <span class="badge badge-gray">
                                                Inativo
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <div class="table-actions">

                                            <a
                                                href="editar_produto.php?id=<?= (int)$produto["id"] ?>"
                                                class="btn btn-secondary btn-sm"
                                            >
                                                Editar
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <strong>
                            Nenhum produto encontrado.
                        </strong>

                        <p>
                            Comece por adicionar o primeiro produto.
                        </p>

                        <br>

                        <a
                            href="adicionar_produto.php"
                            class="btn btn-primary"
                        >
                            Adicionar produto
                        </a>

                    </div>

                <?php endif; ?>

            </div>

            <!-- =================================================
                 ACESSOS RÁPIDOS
            ================================================== -->

            <div
                class="card"
                style="margin-top:20px;"
            >

                <div class="card-header">

                    <div>

                        <h2>
                            Acessos rápidos
                        </h2>

                        <p>
                            Principais áreas do painel.
                        </p>

                    </div>

                </div>

                <div class="actions-grid">

                    <a
                        href="adicionar_produto.php"
                        class="action-card"
                    >

                        <strong>
                            Adicionar produto
                        </strong>

                        <span>
                            Criar um novo produto.
                        </span>

                    </a>

                    <a
                        href="categorias.php"
                        class="action-card"
                    >

                        <strong>
                            Categorias
                        </strong>

                        <span>
                            Organizar os produtos.
                        </span>

                    </a>

                    <a
                        href="promocoes.php"
                        class="action-card"
                    >

                        <strong>
                            Promoções
                        </strong>

                        <span>
                            Gerir preços promocionais.
                        </span>

                    </a>

                    <a
                        href="clientes.php"
                        class="action-card"
                    >

                        <strong>
                            Clientes
                        </strong>

                        <span>
                            Consultar clientes registados.
                        </span>

                    </a>

                    <a
                        href="feedbacks.php"
                        class="action-card"
                    >

                        <strong>
                            Feedbacks
                        </strong>

                        <span>
                            Gerir avaliações.
                        </span>

                    </a>

                    <a
                        href="definicoes.php"
                        class="action-card"
                    >

                        <strong>
                            Definições
                        </strong>

                        <span>
                            Configurar o site.
                        </span>

                    </a>

                </div>

            </div>

        </section>

    </main>

</div>

<script>

const menuToggle = document.getElementById("menuToggle");
const sidebar = document.getElementById("sidebar");

if (menuToggle && sidebar) {

    menuToggle.addEventListener("click", function () {

        sidebar.classList.toggle("open");

    });

}

</script>

</body>

</html>