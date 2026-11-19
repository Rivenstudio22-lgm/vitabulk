<?php

require_once __DIR__."/config/database.php";
require_once __DIR__."/config/payzeno.php";

$raw=file_get_contents("php://input");

$data=json_decode($raw,true);

if(!$data){
    http_response_code(400);
    exit;
}

$tipo=$data["type"]??"";
$payment=$data["data"]??$data["payment"]??[];

$reference=
    $payment["reference"]
    ??$payment["metadata"]["pedido"]
    ??null;

if(!$reference){
    http_response_code(200);
    exit;
}

$status=null;

if($tipo==="payment.succeeded"){
    $status="pago";
}

if($tipo==="payment.refunded"){
    $status="reembolsado";
}

if($tipo==="payment.chargeback"){
    $status="cancelado";
}

if($status){

    $s=$conn->prepare("
        UPDATE pedidos
        SET status=?
        WHERE payzeno_reference=?
        OR numero_pedido=?
    ");

    $s->bind_param(
        "sss",
        $status,
        $reference,
        $reference
    );

    $s->execute();
    $s->close();
}

http_response_code(200);

echo "OK";