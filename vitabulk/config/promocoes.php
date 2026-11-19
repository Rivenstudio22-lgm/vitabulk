<?php

require_once __DIR__ . "/database.php";


function obterPromocaoProduto($produtoId)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT
            id,
            produto_id,
            nome,
            tipo,
            valor,
            data_inicio,
            data_fim
        FROM promocoes
        WHERE produto_id = ?
          AND ativo = 1
          AND (
              data_inicio IS NULL
              OR data_inicio <= NOW()
          )
          AND (
              data_fim IS NULL
              OR data_fim >= NOW()
          )
        ORDER BY id DESC
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param(
        "i",
        $produtoId
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();

    $promocao =
        $resultado->fetch_assoc();

    $stmt->close();

    return $promocao ?: null;
}


function calcularPrecoPromocional(
    $preco,
    $produtoId
) {

    $preco = (float)$preco;

    $promocao =
        obterPromocaoProduto(
            $produtoId
        );

    if (!$promocao) {

        return [
            "preco_original" => $preco,
            "desconto" => 0,
            "preco_final" => $preco,
            "promocao" => null
        ];
    }

    if (
        $promocao["tipo"]
        === "percentagem"
    ) {

        $desconto =
            $preco *
            (
                (float)$promocao["valor"]
                / 100
            );

    } else {

        $desconto =
            (float)$promocao["valor"];
    }

    $desconto =
        min(
            $desconto,
            $preco
        );

    return [
        "preco_original" => $preco,
        "desconto" => $desconto,
        "preco_final" =>
            max(
                0,
                $preco - $desconto
            ),
        "promocao" => $promocao
    ];
}


function formatarPreco($valor)
{
    return number_format(
        (float)$valor,
        2,
        ",",
        "."
    ) . " MT";
}