<?php

session_start();

require_once __DIR__."/config/database.php";
require_once __DIR__."/config/configuracoes.php";
require_once __DIR__."/config/promocoes.php";

if(!vendasAtivas()){
    header("Location: verproduto.php?id=".(int)($_POST["produto_id"]??0)."&vendas=desativadas");
    exit;
}

$id=(int)($_POST["produto_id"]??0);
$qtd=max(1,(int)($_POST["quantidade"]??1));

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

if(!$p){
    header("Location: produtos.php");
    exit;
}

$stock=(int)$p["stock"];

if($stock<=0){
    header("Location: verproduto.php?id=".$id."&erro=stock");
    exit;
}

$qtd=min($qtd,$stock);

if(!isset($_SESSION["carrinho"]))
    $_SESSION["carrinho"]=[];

$atual=(int)($_SESSION["carrinho"][$id]??0);

$_SESSION["carrinho"][$id]=min(
    $atual+$qtd,
    $stock
);

header("Location: carrinho.php");
exit;