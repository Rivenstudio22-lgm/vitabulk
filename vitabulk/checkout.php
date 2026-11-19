<?php

require_once __DIR__."/config/cliente_auth.php";
require_once __DIR__."/config/database.php";
require_once __DIR__."/config/configuracoes.php";
require_once __DIR__."/config/promocoes.php";

exigirLoginCliente();

if(!vendasAtivas()){
    die("As vendas estão desativadas.");
}

$carrinho=$_SESSION["carrinho"]??[];

if(!$carrinho){
    header("Location: carrinho.php");
    exit;
}

$total=0;
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

    if(!$p) continue;

    $qtd=min($qtd,(int)$p["stock"]);

    if($qtd<=0) continue;

    $calc=calcularPrecoPromocional(
        $p["preco"],
        $id
    );

    $subtotal=$calc["preco_final"]*$qtd;

    $total+=$subtotal;

    $itens[]=[
        "produto"=>$p,
        "quantidade"=>$qtd,
        "calculo"=>$calc,
        "subtotal"=>$subtotal
    ];
}

?>

<!DOCTYPE html>
<html lang="pt">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>Checkout | Vitabulk</title>

<link rel="stylesheet"
href="assets/css/style.css">

</head>

<body>

<main class="container">

<h1>Finalizar compra</h1>

<p>
Cliente:
<strong><?=htmlspecialchars(clienteNome())?></strong>
</p>

<div>

<?php foreach($itens as $i): ?>

<p>

<?=htmlspecialchars($i["produto"]["nome"])?>
× <?=$i["quantidade"]?>

<strong>
<?=formatarPreco($i["subtotal"])?>
</strong>

</p>

<?php endforeach; ?>

</div>

<h2>
Total:
<?=formatarPreco($total)?>
</h2>

<form method="post"
action="criar_pagamento.php">

<input
type="hidden"
name="total"
value="<?=htmlspecialchars($total)?>">

<button type="submit">
Pagar com Payzeno
</button>

</form>

</main>

</body>

</html>