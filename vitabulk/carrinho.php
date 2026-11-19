<?php

session_start();

require_once __DIR__."/config/database.php";
require_once __DIR__."/config/configuracoes.php";
require_once __DIR__."/config/promocoes.php";

if(!vendasAtivas()){
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Vendas desativadas</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<main class="container">

<h1>Modo demonstração</h1>

<p>
As vendas ainda não estão ativas neste momento.
</p>

<a href="produtos.php">
Voltar aos produtos
</a>

</main>

</body>
</html>

<?php
exit;
}

$carrinho=$_SESSION["carrinho"]??[];

$itens=[];
$total=0;
$descontoTotal=0;

foreach($carrinho as $id=>$qtd){

    $id=(int)$id;
    $qtd=(int)$qtd;

    $s=$conn->prepare("
    SELECT *
    FROM produtos
    WHERE id=?
    AND status='ativo'
    ");

    $s->bind_param("i",$id);
    $s->execute();

    $p=$s->get_result()->fetch_assoc();

    $s->close();

    if(!$p) continue;

    $calc=calcularPrecoPromocional(
        $p["preco"],
        $id
    );

    $subtotal=$calc["preco_final"]*$qtd;

    $total+=$subtotal;

    $descontoTotal+=
        $calc["desconto"]*$qtd;

    $p["quantidade"]=$qtd;
    $p["calculo"]=$calc;
    $p["subtotal"]=$subtotal;

    $itens[]=$p;
}

?>

<!DOCTYPE html>
<html lang="pt">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>Carrinho | Vitabulk</title>

<link rel="stylesheet"
href="assets/css/style.css">

</head>

<body>

<header class="site-header">

<div class="container">

<a href="index.php">
Vitabulk Alimentos
</a>

<nav>

<a href="produtos.php">
Produtos
</a>

<a href="carrinho.php">
Carrinho
</a>

</nav>

</div>

</header>

<main>

<section class="container">

<h1>O seu carrinho</h1>

<?php if(!$itens): ?>

<p>O carrinho está vazio.</p>

<a href="produtos.php">
Ver produtos
</a>

<?php else: ?>

<form method="post"
action="atualizar_carrinho.php">

<?php foreach($itens as $p): ?>

<div class="cart-item">

<?php if($p["imagem"]): ?>

<img
src="<?=htmlspecialchars($p["imagem"])?>"
alt="">

<?php endif; ?>

<div>

<h3><?=htmlspecialchars($p["nome"])?></h3>

<p>
<?=formatarPreco($p["calculo"]["preco_final"])?>
</p>

<input
type="number"
name="quantidade[<?=$p["id"]?>]"
min="0"
max="<?=$p["stock"]?>"
value="<?=$p["quantidade"]?>">

<a href="remover_carrinho.php?id=<?=$p["id"]?>">
Remover
</a>

</div>

</div>

<?php endforeach; ?>

<button type="submit">
Atualizar carrinho
</button>

</form>

<hr>

<p>
Subtotal/descontos aplicados
</p>

<h2>
Total: <?=formatarPreco($total)?>
</h2>

<a href="checkout.php">
Continuar para checkout
</a>

<?php endif; ?>

</section>

</main>

</body>
</html>