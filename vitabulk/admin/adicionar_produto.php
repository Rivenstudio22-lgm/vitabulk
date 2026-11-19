<?php

require_once __DIR__."/inc.php";

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
    $preco=$_POST["preco"]!=="" ? (float)$_POST["preco"] : 0;
    $stock=max(0,(int)($_POST["stock"]??0));
    $status=$_POST["status"]==="inativo" ? "inativo":"ativo";

    $imagens=$_FILES["imagens"]??null;

    $arquivos=[];

    if($imagens && isset($imagens["name"])){

        foreach($imagens["name"] as $i=>$name){

            if($name==="" || $imagens["error"][$i]!==UPLOAD_ERR_OK)
                continue;

            $tmp=$imagens["tmp_name"][$i];

            $ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));

            if(!in_array($ext,["jpg","jpeg","png","webp"]))
                continue;

            $arquivos[]=[
                "tmp"=>$tmp,
                "ext"=>$ext
            ];
        }
    }

    if(count($arquivos)>5){
        $erro="Pode adicionar no máximo 5 imagens.";
    }

    if(!$erro && $nome===""){
        $erro="Digite o nome do produto.";
    }

    if(!$erro){

        $stmt=$conn->prepare("
            INSERT INTO produtos
            (nome,descricao,categoria,preco,stock,status)
            VALUES(?,?,?,?,?,?)
        ");

        $stmt->bind_param(
            "sssdis",
            $nome,
            $descricao,
            $categoria,
            $preco,
            $stock,
            $status
        );

        if($stmt->execute()){

            $produtoId=$conn->insert_id;

            $pasta=__DIR__."/../imagens/produtos/";

            if(!is_dir($pasta)){
                mkdir($pasta,0777,true);
            }

            foreach($arquivos as $ordem=>$arq){

                $nomeArquivo=
                    "produto_".
                    $produtoId."_".
                    uniqid().
                    ".".$arq["ext"];

                $destino=$pasta.$nomeArquivo;

                if(move_uploaded_file($arq["tmp"],$destino)){

                    $relativo="imagens/produtos/".$nomeArquivo;

                    $s=$conn->prepare("
                        INSERT INTO produto_imagens
                        (produto_id,imagem,ordem)
                        VALUES(?,?,?)
                    ");

                    $s->bind_param(
                        "isi",
                        $produtoId,
                        $relativo,
                        $ordem
                    );

                    $s->execute();
                    $s->close();

                    if($ordem===0){

                        $s=$conn->prepare("
                            UPDATE produtos
                            SET imagem=?
                            WHERE id=?
                        ");

                        $s->bind_param(
                            "si",
                            $relativo,
                            $produtoId
                        );

                        $s->execute();
                        $s->close();
                    }
                }
            }

            $sucesso="Produto criado com sucesso.";

        }else{

            $erro="Erro ao criar produto.";

        }

        $stmt->close();
    }
}

adminHeader("Adicionar produto");

?>

<div class="page-head">

<div>
<h1>Adicionar produto</h1>
<p>Máximo de 5 imagens.</p>
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

<div class="field">
<label>Nome</label>
<input name="nome" required>
</div>

<div class="field">
<label>Categoria</label>

<select name="categoria">

<option value="">Sem categoria</option>

<?php foreach($categorias as $c): ?>

<option value="<?=htmlspecialchars($c)?>">
<?=htmlspecialchars($c)?>
</option>

<?php endforeach; ?>

</select>

</div>

<div class="field">
<label>Preço (MT)</label>
<input type="number"
name="preco"
step="0.01"
min="0"
required>
</div>

<div class="field">
<label>Stock</label>
<input type="number"
name="stock"
min="0"
value="0"
required>
</div>

<div class="field">
<label>Status</label>

<select name="status">
<option value="ativo">Ativo</option>
<option value="inativo">Inativo</option>
</select>

</div>

<div class="field full">
<label>Descrição</label>
<textarea name="descricao"></textarea>
</div>

<div class="field full">

<label>Imagens — máximo 5</label>

<input
type="file"
name="imagens[]"
accept="image/jpeg,image/png,image/webp"
multiple
>

<small>
A primeira imagem será a imagem principal.
</small>

</div>

<div class="full">

<button class="btn" type="submit">
Guardar produto
</button>

</div>

</form>

<?php adminFooter(); ?>