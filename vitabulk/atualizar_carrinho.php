<?php

session_start();

require_once __DIR__."/config/database.php";

if(!isset($_SESSION["carrinho"]))
    $_SESSION["carrinho"]=[];

foreach($_POST["quantidade"]??[] as $id=>$qtd){

    $id=(int)$id;
    $qtd=max(0,(int)$qtd);

    if($qtd===0){
        unset($_SESSION["carrinho"][$id]);
        continue;
    }

    $s=$conn->prepare("
        SELECT stock,status
        FROM produtos
        WHERE id=?
    ");

    $s->bind_param("i",$id);
    $s->execute();

    $p=$s->get_result()->fetch_assoc();

    $s->close();

    if(!$p || $p["status"]!=="ativo"){
        unset($_SESSION["carrinho"][$id]);
        continue;
    }

    $_SESSION["carrinho"][$id]=min(
        $qtd,
        (int)$p["stock"]
    );
}

header("Location: carrinho.php");
exit;