<?php

require_once __DIR__."/inc.php";

$id=(int)($_GET["id"]??0);

$s=$conn->prepare("
SELECT p.*,c.nome cliente,c.email,c.telefone
FROM pedidos p
INNER JOIN clientes c ON c.id=p.cliente_id
WHERE p.id=?
");

$s->bind_param("i",$id);
$s->execute();

$pedido=$s->get_result()->fetch_assoc();

$s->close();

if(!$pedido){
    header("Location: pedidos.php");
    exit;
}

$itens=[];

$s=$conn->prepare("
SELECT *
FROM pedido_itens
WHERE pedido_id=?
ORDER BY id
");

$s->bind_param("i",$id);
$s->execute();

$r=$s->get_result();

while($x=$r->fetch_assoc()){
    $itens[]=$x;
}

$s->close();

adminHeader("Pedido ".$pedido["numero_pedido"]);

?>

<div class="page-head">

<div>
<h1>Pedido <?=htmlspecialchars($pedido["numero_pedido"])?></h1>
<p><?=htmlspecialchars($pedido["cliente"])?></p>
</div>

<a class="btn gray" href="pedidos.php">
Voltar
</a>

</div>

<div class="grid-2">

<div class="card">

<h2>Cliente</h2>

<p>
<strong><?=htmlspecialchars($pedido["cliente"])?></strong>
</p>

<p>
<?=htmlspecialchars($pedido["email"])?>
</p>

<p>
<?=htmlspecialchars($pedido["telefone"]??"")?>
</p>

</div>

<div class="card">

<h2>Status</h2>

<form method="post"
action="atualizar_pedido.php">

<input type="hidden"
name="id"
value="<?=$id?>">

<select name="status">

<?php

$statuses=[
"aguardando_pagamento",
"pago",
"processando",
"enviado",
"entregue",
"cancelado",
"reembolsado"
];

foreach($statuses as $st):

?>

<option
value="<?=$st?>"
<?=$pedido["status"]===$st?"selected":""?>>

<?=$st?>

</option>

<?php endforeach; ?>

</select>

<br><br>

<button class="btn">
Atualizar
</button>

</form>

</div>

</div>

<div class="card table-wrap">

<table>

<tr>
<th>Produto</th>
<th>Qtd.</th>
<th>Preço</th>
<th>Desconto</th>
<th>Subtotal</th>
</tr>

<?php foreach($itens as $i): ?>

<tr>

<td><?=htmlspecialchars($i["nome_produto"])?></td>

<td><?=$i["quantidade"]?></td>

<td><?=formatarPreco($i["preco_unitario"])?></td>

<td><?=formatarPreco($i["desconto_unitario"])?></td>

<td><?=formatarPreco($i["subtotal"])?></td>

</tr>

<?php endforeach; ?>

</table>

</div>

<div class="card order-total">

<p>Subtotal: <strong><?=formatarPreco($pedido["subtotal"])?></strong></p>

<p>Desconto: <strong><?=formatarPreco($pedido["desconto"])?></strong></p>

<h2>Total: <?=formatarPreco($pedido["total"])?></h2>

</div>

<?php adminFooter(); ?>