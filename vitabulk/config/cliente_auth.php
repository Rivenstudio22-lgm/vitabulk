<?php

if (
    session_status()
    === PHP_SESSION_NONE
) {
    session_start();
}

require_once __DIR__ . "/vendas.php";


function clienteLogado()
{
    return isset(
        $_SESSION["cliente_id"]
    ) &&
    !empty(
        $_SESSION["cliente_id"]
    );
}


function clienteId()
{
    return $_SESSION["cliente_id"] ?? null;
}


function clienteNome()
{
    return $_SESSION["cliente_nome"] ?? "Cliente";
}


function clienteEmail()
{
    return $_SESSION["cliente_email"] ?? "";
}


function exigirCliente(
    $redirecionarPara = "login.php"
) {
    if (!clienteLogado()) {

        $_SESSION["url_retorno"] =
            $_SERVER["REQUEST_URI"] ?? "index.php";

        header(
            "Location: " .
            $redirecionarPara
        );
        exit;
    }
}
