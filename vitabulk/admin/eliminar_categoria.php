<?php

require_once __DIR__."/inc.php";

$id=(int)($_GET["id"]??0);

if($id){

    $s=$conn->prepare("
        SELECT nome
        FROM categorias
        WHERE id=?
    ");

    $s->bind_param("i",$id);
    $s->execute();

    $cat=$s->get_result()->fetch_assoc();

    $s->close();

    if($cat){

        $s=$conn->prepare("
            UPDATE produtos
            SET categoria=NULL
            WHERE categoria=?
        ");

        $s->bind_param("s",$cat["nome"]);
        $s->execute();
        $s->close();

        $s=$conn->prepare("
            DELETE FROM categorias
            WHERE id=?
        ");

        $s->bind_param("i",$id);
        $s->execute();
        $s->close();
    }
}

header("Location: categorias.php");
exit;