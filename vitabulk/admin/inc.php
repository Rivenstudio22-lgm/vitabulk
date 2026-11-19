<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/configuracoes.php";
require_once __DIR__ . "/../config/promocoes.php";
require_once __DIR__ . "/../config/vendas.php";
require_once __DIR__ . "/../config/seguranca.php";

function adminLogado()
{
    return isset($_SESSION["admin_logado"]) && $_SESSION["admin_logado"] === true;
}

function exigirAdmin()
{
    if (!adminLogado()) {
        header("Location: login.php");
        exit;
    }
}

function adminHeader($titulo = "Painel de Administração")
{
    $adminNome = $_SESSION["admin_nome"] ?? "Administrador";
    $adminEmail = $_SESSION["admin_email"] ?? "";
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($titulo) ?> - Vitabulk Admin</title>
        <link rel="stylesheet" href="assets/css/admin.css">
    </head>
    <body class="admin-body">
        <div class="admin-layout">
            <aside class="admin-sidebar">
                <div class="sidebar-brand">
                    <h2>Vitabulk</h2>
                    <span>Painel de Gestão</span>
                </div>
                <nav class="sidebar-nav">
                    <a href="index.php">Dashboard</a>
                    <a href="produtos.php">Produtos</a>
                    <a href="pedidos.php">Pedidos</a>
                    <a href="galeria.php">Galeria</a>
                    <a href="definicoes.php">Definições</a>
                    <a href="../index.php" target="_blank">Ver Loja</a>
                    <a href="logout.php" class="nav-logout">Terminar Sessão</a>
                </nav>
            </aside>
            <main class="admin-main">
                <header class="admin-topbar">
                    <div class="topbar-title">
                        <h1><?= htmlspecialchars($titulo) ?></h1>
                    </div>
                    <div class="topbar-user">
                        <span><?= htmlspecialchars($adminNome) ?></span>
                        <a href="logout.php" class="btn-logout-small">Sair</a>
                    </div>
                </header>
                <div class="admin-content">
    <?php
}

function adminFooter()
{
    ?>
                </div>
            </main>
        </div>
    </body>
    </html>
    <?php
}
