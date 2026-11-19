<?php

require_once __DIR__."/config/cliente_auth.php";
require_once __DIR__."/config/database.php";
require_once __DIR__."/config/configuracoes.php";
require_once __DIR__."/config/promocoes.php";
require_once __DIR__."/config/payzeno.php";

exigirLoginCliente();

if(!vendasAtivas()){
    die("As vendas estão desativadas.");
}

$carrinho=$_SESSION["carrinho"]??[];

if(!$carrinho){
    header("Location: carrinho.php");
    exit;
}

$subtotal=0;
$desconto=0;
$itens=[];

foreach($carrinho as $id=>$qtd){

    $id=(int)$id;
    $qtd=(int)$qtd;

    $s=$conn->prepare("
        SELECT *
        FROM produtos
        WHERE id=?
        AND status='ativo'
        LIMIT 1
    ");

    $s->bind_param("i",$id);
    $s->execute();

    $p=$s->get_result()->fetch_assoc();

    $s->close();

    if(!$p || $qtd<=0)
        continue;

    $qtd=min($qtd,(int)$p["stock"]);

    $calc=calcularPrecoPromocional(
        $p["preco"],
        $id
    );

    $sub=$calc["preco_final"]*$qtd;

    $subtotal += $calc["preco_original"]*$qtd;

    $desconto += $calc["desconto"]*$qtd;

    $itens[]=[
        "produto"=>$p,
        "quantidade"=>$qtd,
        "calculo"=>$calc,
        "subtotal"=>$sub
    ];
}

$total=$subtotal-$desconto;

if($total<=0){
    die("Valor inválido.");
}

$numero="VTB-".date("YmdHis")."-".random_int(100,999);

$s=$conn->prepare("
INSERT INTO pedidos
(cliente_id,numero_pedido,subtotal,desconto,total,status,metodo_pagamento)
VALUES(?,?,?,?,?,'aguardando_pagamento','payzeno')
");

$cliente=clienteId();

$s->bind_param(
"isddd",
$cliente,
$numero,
$subtotal,
$desconto,
$total
);

$s->execute();

$pedidoId=$conn->insert_id;

$s->close();

foreach($itens as $i){

    $p=$i["produto"];

    $calc=$i["calculo"];

    $q=$i["quantidade"];

    $sub=$i["subtotal"];

    $nomePromo=$calc["promocao"]["nome"]??null;

    $s=$conn->prepare("
    INSERT INTO pedido_itens
    (pedido_id,produto_id,nome_produto,quantidade,
     preco_original,desconto_unitario,preco_unitario,
     subtotal,promocao_nome)
    VALUES(?,?,?,?,?,?,?,?,?)
    ");

    $s->bind_param(
        "iisidddds",
        $pedidoId,
        $p["id"],
        $p["nome"],
        $q,
        $calc["preco_original"],
        $calc["desconto"],
        $calc["preco_final"],
        $sub,
        $nomePromo
    );

    $s->execute();
    $s->close();
}


/*
 * PAYZENO
 *
 * Coloque a API key em config/payzeno.php.
 */

$payload=[
    "amount"=>round($total*100),
    "currency"=>"MZN",
    "success_url"=>VITABULK_URL."/pagamento_sucesso.php?pedido=".$pedidoId,
    "cancel_url"=>VITABULK_URL."/pagamento_cancelado.php?pedido=".$pedidoId
];

$ch=curl_init(
    PAYZENO_API_URL."/v1/checkout/sessions"
);

curl_setopt_array($ch,[
    CURLOPT_POST=>true,
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_HTTPHEADER=>[
        "Content-Type: application/json",
        "Api-Key: ".PAYZENO_API_KEY,
        "Idempotency-Key: ".$numero
    ],
    CURLOPT_POSTFIELDS=>json_encode($payload)
]);

$response=curl_exec($ch);

$http=curl_getinfo($ch,CURLINFO_HTTP_CODE);

curl_close($ch);

$data=json_decode($response,true);

if($http>=200 && $http<300){

    $checkoutId=
        $data["id"]
        ??$data["checkout_session_id"]
        ??null;

    $checkoutUrl=
        $data["url"]
        ??$data["checkout_url"]
        ??null;

    if($checkoutId){

        $s=$conn->prepare("
            UPDATE pedidos
            SET payzeno_checkout_id=?
            WHERE id=?
        ");

        $s->bind_param(
            "si",
            $checkoutId,
            $pedidoId
        );

        $s->execute();
        $s->close();
    }

    if($checkoutUrl){
        header("Location: ".$checkoutUrl);
        exit;
    }
}

http_response_code(500);

echo "Não foi possível criar o pagamento.";