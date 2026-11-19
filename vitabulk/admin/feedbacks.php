<?php

require_once __DIR__."/inc.php";

if(isset($_GET["acao"],$_GET["id"])){

    $acao=$_GET["acao"];

    if(in_array($acao,["aprovado","pendente","oculto"],true)){

        $id=(int)$_GET["id"];

        $s=$conn->prepare("
            UPDATE feedbacks
            SET status=?
            WHERE id=?
        ");

        $s->bind_param("si",$acao,$id);
        $s->execute();
        $s->close();
    }

    header("Location: feedbacks.php");
    exit;
}

$feedbacks=[];

$r=$conn->query("
SELECT f.*,c.nome,c.email
FROM feedbacks f
INNER JOIN clientes c ON c.id=f.cliente_id
ORDER BY f.id DESC
");

while($r && $x=$r->fetch_assoc()){
    $feedbacks[]=$x;
}

adminHeader("Feedbacks");

?>

<div class="page-head">

<div>
<h1>Feedbacks</h1>
<p>Avaliações dos clientes.</p>
</div>

</div>

<div class="card table-wrap">

<table>

<tr>
<th>Cliente</th>
<th>Avaliação</th>
<th>Mensagem</th>
<th>Status</th>
<th>Ações</th>
</tr>

<?php foreach($feedbacks as $f): ?>

<tr>

<td>
<?=htmlspecialchars($f["nome"])?>
<br>
<small><?=htmlspecialchars($f["email"])?></small>
</td>

<td>
<?=str_repeat("★",(int)$f["avaliacao"])?>
</td>

<td>
<?=nl2br(htmlspecialchars($f["mensagem"]))?>
</td>

<td>
<span class="badge">
<?=htmlspecialchars($f["status"])?>
</span>
</td>

<td>

<a class="btn"
href="?acao=aprovado&id=<?=$f["id"]?>">
Aprovar
</a>

<a class="btn gray"
href="?acao=pendente&id=<?=$f["id"]?>">
Pendente
</a>

<a class="btn red"
href="?acao=oculto&id=<?=$f["id"]?>">
Ocultar
</a>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<?php adminFooter(); ?>