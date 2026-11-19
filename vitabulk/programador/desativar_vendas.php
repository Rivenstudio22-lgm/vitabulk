<?php

session_start();

if (!isset($_SESSION["programador_id"])) {
    header("Location: /vitabulk/admin/login.php");
    exit;
}

require_once __DIR__ . "/../config/configuracoes.php";

$configuracoes = [
    "vendas_ativo" => "0",
    "carrinho_ativo" => "0",
    "checkout_ativo" => "0",
    "promocoes_ativo" => "0",
    "pedidos_ativo" => "0",
    "pagamentos_ativo" => "0",
    "payzeno_ativo" => "0",
    "login_compras_ativo" => "0"
];

foreach ($configuracoes as $chave => $valor) {
    guardarConfiguracao(
        $chave,
        $valor
    );
}

header(
    "Location: vendas.php?sucesso=vendas_desativadas"
);

exit;