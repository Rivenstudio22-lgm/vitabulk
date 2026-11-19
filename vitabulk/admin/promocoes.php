<?php

require_once __DIR__."/inc.php";

$erro="";
$sucesso="";

if(isset($_GET["eliminar"])){

    $id=(int)$_GET["eliminar"];

    $s=$conn->prepare("
        DELETE FROM promocoes
        WHERE id=?
    ");

    $s->bind_param("i",$id);
    $s->execute();
    $s->close();

    header("Location: promocoes.php");
    exit;
}


if($_SERVER["REQUEST_METHOD"]==="POST"){

    $produto=(int)($_POST["produto_id"]??0);
    $nome=trim($_POST["nome"]??"Promoção");
    $tipo=$_POST["tipo"]==="valor"?"valor":"percentagem";
    $valor=(float)($_POST["valor"]??0);
    $inicio=$_POST["data_inicio"]!==""?$_POST["data_inicio"]:null;
    $fim=$_POST["data_fim"]!==""?$_POST["data_fim"]:null;

    if(!$produto || $valor<=0){

        $erro="Preencha os dados da promoção.";

    }elseif($tipo==="percentagem" && $valor>100){

        $erro="A percentagem não pode ultrapassar 100%.";

    }else{

        $s=$conn->prepare("
            INSERT INTO promocoes
            (produto_id,nome,tipo,valor,data_inicio,data_fim)
            VALUES(?,?,?,?,?,?)
        ");

        $s->bind_param(
            "issdss",
            $produto,
            $nome,
            $tipo,
            $valor,
            $inicio,
            $fim
        );

        $s->execute();
        $s->close();

        $sucesso="Promoção criada.";
    }
}

$produtos=[];

$r=$conn->query("
SELECT id,nome,preco
FROM produtos
WHERE status='ativo'
ORDER BY nome
");

while($r && $x=$r->fetch_assoc()){
    $produtos[]=$x;
}

$promocoes=[];

$r=$conn->query("
SELECT pr.*,p.nome produto
FROM promocoes pr
INNER JOIN produtos p ON p.id=pr.produto_id
ORDER BY pr.id DESC
");

while($r && $x=$r->fetch_assoc()){
    $promocoes[]=$x;
}

adminHeader("Promoções");

?>

<div class="page-head">

<div>
<h1>Promoções</h1>
<p>Crie descontos para os produtos.</p>
</div>

</div>

<?php if($erro): ?>
<div class="alert error"><?=$erro?></div>
<?php endif; ?>

<?php if($sucesso): ?>
<div class="alert success"><?=$sucesso?></div>
<?php endif; ?>

<div class="card">

<h2>Nova promoção</h2>

<form method="post" class="form-grid">

<div class="field">

<label>Produto</label>

<select name="produto_id" required>

<option value="">Escolher produto</option>

<?php foreach($produtos as $p): ?>

<option value="<?=$p["id"]?>">
<?=htmlspecialchars($p["nome"])?>
</option>

<?php endforeach; ?>

</select>

</div>

<div class="field">

<label>Nome da promoção</label>

<input name="nome"
value="Promoção"
required>

</div>

<div class="field">

<label>Tipo</label>

<select name="tipo">

<option value="percentagem">
Percentagem
</option>

<option value="valor">
Valor em MT
</option>

</select>

</div>

<div class="field">

<label>Valor</label>

<input type="number"
name="valor"
step="0.01"
min="0"
required>

</div>

<div class="field">

<label>Início</label>

<input type="datetime-local"
name="data_inicio">

</div>

<div class="field">

<label>Fim</label>

<input type="datetime-local"
name="data_fim">

</div>

<div class="full">

<button class="btn">
Criar promoção
</button>

</div>

</form>

</div>

<div class="card table-wrap">

<table>

<tr>

<th>Produto</th>
<th>Promoção</th>
<th>Tipo</th>
<th>Valor</th>
<th>Período</th>
<th>Ação</th>

</tr>

<?php foreach($promocoes as $p): ?>

<tr>

<td><?=htmlspecialchars($p["produto"])?></td>

<td><?=htmlspecialchars($p["nome"])?></td>

<td><?=htmlspecialchars($p["tipo"])?></td>

<td>

<?=$p["tipo"]==="percentagem"
?number_format($p["valor"],2,",",".")."%"
:formatarPreco($p["valor"])?>

</td>

<td>

<?=htmlspecialchars($p["data_inicio"]??"")?>
<br>
<?=htmlspecialchars($p["data_fim"]??"")?>

</td>

<td>

<a class="btn red"
href="?eliminar=<?=$p["id"]?>"
onclick="return confirm('Eliminar promoção?')">
Eliminar
</a>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<?php adminFooter(); ?>