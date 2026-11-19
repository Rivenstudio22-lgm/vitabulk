<?php

require_once __DIR__."/inc.php";

$clientes=[];

$r=$conn->query("
SELECT *
FROM clientes
ORDER BY id DESC
");

while($r && $x=$r->fetch_assoc()){
    $clientes[]=$x;
}

adminHeader("Clientes");

?>

<div class="page-head">

<div>
<h1>Clientes</h1>
<p>Clientes registados.</p>
</div>

</div>

<div class="card table-wrap">

<table>

<tr>
<th>Nome</th>
<th>Email</th>
<th>Telefone</th>
<th>Status</th>
<th>Último login</th>
<th>Registo</th>
</tr>

<?php foreach($clientes as $c): ?>

<tr>

<td><?=htmlspecialchars($c["nome"])?></td>

<td><?=htmlspecialchars($c["email"])?></td>

<td><?=htmlspecialchars($c["telefone"]??"")?></td>

<td>

<span class="badge <?=$c["status"]==="ativo"?"":"red"?>">
<?=htmlspecialchars($c["status"])?>
</span>

</td>

<td><?=htmlspecialchars($c["ultimo_login"]??"Nunca")?></td>

<td><?=htmlspecialchars($c["criado_em"])?></td>

</tr>

<?php endforeach; ?>

</table>

</div>

<?php adminFooter(); ?>