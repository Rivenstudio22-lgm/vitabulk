<?php

require_once __DIR__."/inc.php";

$id=(int)($_GET["id"]??0);

if($id){

    $files=[];

    $s=$conn->prepare("
        SELECT imagem
        FROM produto_imagens
        WHERE produto_id=?
    ");

    $s->bind_param("i",$id);
    $s->execute();

    $r=$s->get_result();

    while($x=$r->fetch_assoc()){
        $files[]=$x["imagem"];
    }

    $s->close();

    $s=$conn->prepare("
        SELECT imagem
        FROM produtos
        WHERE id=?
    ");

    $s->bind_param("i",$id);
    $s->execute();

    $x=$s->get_result()->fetch_assoc();

    $s->close();

    if($x && $x["imagem"])
        $files[]=$x["imagem"];

    $s=$conn->prepare("
        DELETE FROM produtos
        WHERE id=?
    ");

    $s->bind_param("i",$id);
    $s->execute();
    $s->close();

    foreach(array_unique($files) as $f){

        $path=__DIR__."/../".$f;

        if(is_file($path))
            @unlink($path);
    }
}

header("Location: produtos.php");
exit;