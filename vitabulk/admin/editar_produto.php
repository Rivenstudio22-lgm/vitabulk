<?php

require_once __DIR__."/inc.php";

$id=(int)($_GET["id"]??$_POST["id"]??0);

if(!$id){
    header("Location: produtos.php");
    exit;
}

$stmt=$conn->prepare("SELECT * FROM produtos WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$p=$stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$p){
    header("Location: produtos.php");
    exit;
}

$erro="";
$sucesso="";

$categorias=[];

$r=$conn->query("SELECT nome FROM categorias ORDER BY nome");

while($r && $x=$r->fetch_assoc()){
    $categorias[]=$x["nome"];
}

if($_SERVER["REQUEST_METHOD"]==="POST"){

    $nome=trim($_POST["nome"]??"");
    $descricao=trim($_POST["descricao"]??"");
    $categoria=trim($_POST["categoria"]??"");
    $preco=(float)($_POST["preco"]??0);
    $stock=max(0,(int)($_POST["stock"]??0));
    $status=$_POST["status"]==="inativo"?"inativo":"ativo";

    if($nome===""){
        $erro="O nome é obrigatório.";
    }

    $novas=[];

    if(!$erro && isset($_FILES["imagens"])){

        foreach($_FILES["imagens"]["name"] as $i=>$name){

            if(!$name || $_FILES["imagens"]["error"][$i]!==UPLOAD_ERR_OK)
                continue;

            $ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));

            if(in_array($ext,["jpg","jpeg","png","webp"])){

                $novas[]=[
                    "tmp"=>$_FILES["imagens"]["tmp_name"][$i],
                    "ext"=>$ext
                ];
            }
        }
    }

    $s=$conn->prepare("
        SELECT COUNT(*) total
        FROM produto_imagens
        WHERE produto_id=?
    ");

    $s->bind_param("i",$id);
    $s->execute();
    $existentes=(int)$s->get_result()->fetch_assoc()["total"];
    $s->close();

    if($existentes+count($novas)>5){
        $erro="O produto pode ter no máximo 5 imagens.";
    }

    if(!$erro){

        $s=$conn->prepare("
            UPDATE produtos
            SET nome=?,descricao=?,categoria=?,preco=?,stock=?,status=?
            WHERE id=?
        ");

        $s->bind_param(
            "sssdisi",
            $nome,
            $descricao,
            $categoria,
            $preco,
            $stock,
            $status,
            $id
        );

        $s->execute();
        $s->close();

        $pasta=__DIR__."/../imagens/produtos/";

        if(!is_dir($pasta))
            mkdir($pasta,0777,true);

        $ordem=$existentes;

        foreach($novas as $n){

            $arquivo="produto_".$id."_".uniqid().".".$n["ext"];

            if(move_uploaded_file(
                $n["tmp"],
                $pasta.$arquivo
            )){

                $relativo="imagens/produtos/".$arquivo;

                $s=$conn->prepare("
                    INSERT INTO produto_imagens
                    (produto_id,imagem,ordem)
                    VALUES(?,?,?)
                ");

                $s->bind_param(
                    "isi",
                    $id,
                    $relativo,
                    $ordem
                );

                $s->execute();
                $s->close();

                $ordem++;
            }
        }

        $sucesso="Produto atualizado.";

        $stmt=$conn->prepare("SELECT * FROM produtos WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $p=$stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

$s=$conn->prepare("
SELECT *
FROM produto_imagens
WHERE produto_id=?
ORDER BY ordem,id
");

$s->bind_param("i",$id);
$s->execute();

$imagens=$s->get_result()->fetch_all(MYSQLI_ASSOC);

$s->close();

adminHeader("Editar produto");

?>

<div class="page-head">

<div>
<h1>Editar produto</h1>
<p><?=htmlspecialchars($p["nome"])?></p>
</div>

<a class="btn gray" href="produtos.php">
Voltar
</a>

</div>

<?php if($erro): ?>
<div class="alert error"><?=$erro?></div>
<?php endif; ?>

<?php if($sucesso): ?>
<div class="alert success"><?=$sucesso?></div>
<?php endif; ?>

<form method="post"
enctype="multipart/form-data"
class="card form-grid">

<input type="hidden" name="id" value="<?=$id?>">

<div class="field">
<label>Nome</label>
<input name="nome"
value="<?=htmlspecialchars($p["nome"])?>"
required>
</div>

<div class="field">
<label>Categoria</label>

<select name="categoria">

<option value="">Sem categoria</option>

<?php foreach($categorias as $c): ?>

<option
value="<?=htmlspecialchars($c)?>"
<?=$p["categoria"]===$c?"selected":""?>>

<?=htmlspecialchars($c)?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="field">
<label>Preço</label>
<input type="number"
step="0.01"
min="0"
name="preco"
value="<?=htmlspecialchars($p["preco"])?>">
</div>

<div class="field">
<label>Stock</label>
<input type="number"
min="0"
name="stock"
value="<?=intval($p["stock"])?>">
</div>

<div class="field">
<label>Status</label>

<select name="status">

<option value="ativo"
<?=$p["status"]==="ativo"?"selected":""?>>
Ativo
</option>

<option value="inativo"
<?=$p["status"]==="inativo"?"selected":""?>>
Inativo
</option>

</select>

</div>

<div class="field full">

<label>Descrição</label>

<textarea name="descricao"><?=htmlspecialchars($p["descricao"]??"")?></textarea>

</div>

<div class="full">

<h3>Imagens atuais</h3>

<div class="image-grid">

<?php foreach($imagens as $img): ?>

<div class="image-card">

<img src="/vitabulk/<?=htmlspecialchars($img["imagem"])?>">

<a
class="btn red"
href="eliminar_imagem.php?id=<?=$img["id"]?>&produto=<?=$id?>"
onclick="return confirm('Eliminar imagem?')">
Eliminar
</a>

</div>

<?php endforeach; ?>

</div>

</div>

<div class="field full">

<label>Adicionar imagens</label>

<input
type="file"
name="imagens[]"
multiple
accept="image/jpeg,image/png,image/webp">

</div>

<div class="full">

<button class="btn">
Guardar alterações
</button>

</div>

</form>

<?php adminFooter(); ?>