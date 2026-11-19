<?php

require_once __DIR__."/inc.php";

$id=(int)($_POST["id"]??0);

$status=$_POST["status"]??"";

$permitidos=[
"aguardando_pagamento",
"pago",
"processando",
"enviado",
"entregue",
"cancelado",
"reembolsado"
];

if($id && in_array($status,$permitidos,true)){

    $s=$conn->prepare("
        UPDATE pedidos
        SET status=?
        WHERE id=?
    ");

    $s->bind_param("si",$status,$id);
    $s->execute();
    $s->close();
}

header("Location: ver_pedido.php?id=".$id);
exit;