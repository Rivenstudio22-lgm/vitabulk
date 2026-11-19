<?php

require_once __DIR__."/inc.php";

$id=(int)($_GET["id"]??0);
$produto=(int)($_GET["produto"]??0);

if($id){

    $s=$conn->prepare("
        SELECT imagem,produto_id
        FROM produto_imagens
        WHERE id=?
    ");

    $s->bind_param("i",$id);
    $s->execute();

    $img=$s->get_result()->fetch_assoc();

    $s->close();

    if($img){

        $s=$conn->prepare("
            DELETE FROM produto_imagens
            WHERE id=?
        ");

        $s->bind_param("i",$id);
        $s->execute();
        $s->close();

        $path=__DIR__."/../".$img["imagem"];

        if(is_file($path))
            @unlink($path);

        $s=$conn->prepare("
            SELECT imagem
            FROM produto_imagens
            WHERE produto_id=?
            ORDER BY ordem,id
            LIMIT 1
        ");

        $s->bind_param("i",$img["produto_id"]);
        $s->execute();

        $nova=$s->get_result()->fetch_assoc();

        $s->close();

        $imagem=$nova["imagem"]??null;

        $s=$conn->prepare("
            UPDATE produtos
            SET imagem=?
            WHERE id=?
        ");

        $s->bind_param(
            "si",
            $imagem,
            $img["produto_id"]
        );

        $s->execute();
        $s->close();
    }
}

header("Location: editar_produto.php?id=".$produto);
exit;