<?php

require_once __DIR__ . "/configuracoes.php";


function vendasAtivas()
{
    return configuracaoAtiva(
        "vendas_ativas",
        false
    );
}


function modoDemonstracao()
{
    return configuracaoAtiva(
        "modo_demonstracao",
        true
    );
}


function exigirVendasAtivas($redirecionarPara = "index.php")
{
    if (!vendasAtivas()) {

        $_SESSION["alerta_vendas_desativadas"] =
            "As compras online estão temporariamente indisponíveis.";

        header("Location: " . $redirecionarPara);
        exit;
    }
}


function podeComprar()
{
    return vendasAtivas() && !modoDemonstracao();
}


function mensagemEstadoVendas()
{
    if (!vendasAtivas()) {
        return "As encomendas online estão temporariamente desativadas.";
    }

    if (modoDemonstracao()) {
        return "O site está em modo de demonstração. Não são processados pagamentos reais.";
    }

    return "";
}
