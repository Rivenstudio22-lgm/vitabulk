<?php

require_once __DIR__."/inc.php";

$pedidos=[];

$status=$_GET["status"]??"";

$sql="
SELECT
p.*,
c.nome cliente,
c.email email_cliente
FROM pedidos p
INNER JOIN clientes c ON c.id=p.cliente_id
";

$params=[];
$types="";

if($status!==""){

    $sql.=" WHERE p.status=?";

    $params[]=$status;
    $types="s";
}

$sql.=" ORDER BY p.id DESC";

$s=$conn->prepare($sql);

if($params){
    $s->bind_param($types,...$params);
}

$s->execute();

$r=$s->get_result();

while($x=$r->fetch_assoc()){
    $pedidos[]=$x;
}

$s->close();

adminHeader("Pedidos");

?>

<div class="page-head">

<div>
<h1>Pedidos</h1>
<p>Compras dos clientes.</p>
</div>

</div>

<?php if(!vendasAtivas()): ?>

<div class="alert">
As vendas estão desativadas. Esta área está pronta para quando ativares o modo de vendas.
</div>

<?php endif; ?>

<div class="filters">

<a href="pedidos.php">Todos</a>
<a href="?status=aguardando_pagamento">Aguardando</a>
<a href="?status=pago">Pagos</a>
<a href="?status=processando">Processando</a>
<a href="?status=enviado">Enviados</a>
<a href="?status=entregue">Entregues</a>
<a href="?status=cancelado">Cancelados</a>

</div>

<div class="card table-wrap">

<table>

<tr>

<th>Pedido</th>
<th>Cliente</th>
<th>Total</th>
<th>Pagamento</th>
<th>Status</th>
<th>Data</th>
<th></th>

</tr>

<?php foreach($pedidos as $p): ?>

<tr>

<td>
<strong><?=htmlspecialchars($p["numero_pedido"])?></strong>
</td>

<td>
<?=htmlspecialchars($p["cliente"])?>
<br>
<small><?=htmlspecialchars($p["email_cliente"])?></small>
</td>

<td>
<?=formatarPreco($p["total"])?>
</td>

<td>
<?=htmlspecialchars($p["metodo_pagamento"]??"—")?>
</td>

<td>
<span class="badge">
<?=htmlspecialchars($p["status"])?>
</span>
</td>

<td>
<?=htmlspecialchars($p["criado_em"])?>
</td>

<td>

<a class="btn"
href="ver_pedido.php?id=<?=$p["id"]?>">
Ver
</a>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<?php adminFooter(); ?>