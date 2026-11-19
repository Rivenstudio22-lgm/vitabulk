<?php

session_start();

if (!isset($_SESSION["programador_id"])) {
    header("Location: /vitabulk/admin/login.php");
    exit;
}

require_once __DIR__ . "/../config/configuracoes.php";

$configuracoes = [
    "vendas_ativo" => "1",
    "carrinho_ativo" => "1",
    "checkout_ativo" => "1",
    "promocoes_ativo" => "1",
    "pedidos_ativo" => "1",
    "login_compras_ativo" => "1"
];

foreach ($configuracoes as $chave => $valor) {
    guardarConfiguracao(
        $chave,
        $valor
    );
}

header(
    "Location: vendas.php?sucesso=vendas_ativadas"
);

exit;