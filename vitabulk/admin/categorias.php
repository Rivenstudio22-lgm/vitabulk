<?php

require_once __DIR__."/inc.php";

$erro="";
$sucesso="";

if($_SERVER["REQUEST_METHOD"]==="POST"){

    $nome=trim($_POST["nome"]??"");

    if($nome===""){
        $erro="Digite uma categoria.";
    }else{

        $s=$conn->prepare("
            INSERT INTO categorias(nome)
            VALUES(?)
        ");

        $s->bind_param("s",$nome);

        if($s->execute()){
            $sucesso="Categoria criada.";
        }else{
            $erro="Esta categoria já existe.";
        }

        $s->close();
    }
}

$categorias=[];

$r=$conn->query("
SELECT c.*,
(
 SELECT COUNT(*)
 FROM produtos p
 WHERE p.categoria=c.nome
) total_produtos
FROM categorias c
ORDER BY c.nome
");

while($r && $x=$r->fetch_assoc()){
    $categorias[]=$x;
}

adminHeader("Categorias");

?>

<div class="page-head">

<div>
<h1>Categorias</h1>
<p>Organize os produtos.</p>
</div>

</div>

<?php if($erro): ?>
<div class="alert error"><?=$erro?></div>
<?php endif; ?>

<?php if($sucesso): ?>
<div class="alert success"><?=$sucesso?></div>
<?php endif; ?>

<div class="card">

<form method="post" class="inline-form">

<input
name="nome"
placeholder="Nova categoria"
required>

<button class="btn">
Adicionar
</button>

</form>

</div>

<div class="card table-wrap">

<table>

<tr>
<th>Categoria</th>
<th>Produtos</th>
<th>Ação</th>
</tr>

<?php foreach($categorias as $c): ?>

<tr>

<td><?=htmlspecialchars($c["nome"])?></td>

<td><?=$c["total_produtos"]?></td>

<td>

<a class="btn red"
href="eliminar_categoria.php?id=<?=$c["id"]?>"
onclick="return confirm('Eliminar categoria?')">
Eliminar
</a>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<?php adminFooter(); ?>