<?php

if (
    session_status()
    === PHP_SESSION_NONE
) {
    session_start();
}

require_once __DIR__ . "/config/site.php";
require_once __DIR__ . "/config/registrar_visita.php";


$id =
    (int)(
        $_GET["id"]
        ?? 0
    );


if ($id <= 0) {

    header(
        "Location: produtos.php"
    );

    exit;
}


$stmt = $conn->prepare("
    SELECT *
    FROM produtos
    WHERE id = ?
      AND status = 'ativo'
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$produto =
    $stmt
        ->get_result()
        ->fetch_assoc();

$stmt->close();


if (!$produto) {

    header(
        "Location: produtos.php"
    );

    exit;
}


$imagens = [];

$stmt = $conn->prepare("
    SELECT imagem
    FROM produto_imagens
    WHERE produto_id = ?
    ORDER BY ordem ASC, id ASC
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$resultado =
    $stmt->get_result();


while (
    $imagem =
    $resultado->fetch_assoc()
) {

    $imagens[] =
        $imagem["imagem"];
}

$stmt->close();


if (
    empty($imagens)
    && !empty($produto["imagem"])
) {

    $imagens[] =
        $produto["imagem"];
}


$precoInfo =
    calcularPrecoPromocional(
        $produto["preco"] ?? 0,
        $produto["id"]
    );

?>
<!DOCTYPE html>
<html lang="pt">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
<?= e($produto["nome"]) ?>
| <?= e($nomeEmpresa) ?>
</title>

<link
    rel="stylesheet"
    href="assets/css/style.css"
>

</head>

<body
style="
--verde: <?= e($corPrimaria) ?>;
--verde-escuro: <?= e($corSecundaria) ?>;
--cor-botoes: <?= e($corBotoes) ?>;
--cor-texto: <?= e($corTexto) ?>;
--cor-fundo: <?= e($corFundo) ?>;
"
>

<header class="site-header">

<div class="container header-container">

<a
    href="index.php"
    class="brand"
>

<div class="logo-image">

<img
    src="<?= $logo
        ? e($logo)
        : 'imagens/logo-vitabulk.png'
    ?>"
    alt="Vitabulk"
>

</div>

<div class="brand-text">

<strong>
Vitabulk
</strong>

<span>
Alimentos, Lda
</span>

</div>

</a>


<nav class="main-nav">

<a href="index.php">
Início
</a>

<a href="produtos.php">
Produtos
</a>

<a href="sobre.php">
Sobre nós
</a>

</nav>

</div>

</header>


<main>

<section class="product-detail-section">

<div class="container">


<div class="product-detail">


<div class="product-gallery">

<div class="product-main-image">

<?php if (
    !empty($imagens)
): ?>

<img
    id="imagemPrincipal"
    src="<?= e($imagens[0]) ?>"
    alt="<?= e($produto["nome"]) ?>"
>

<?php else: ?>

<div class="product-placeholder">
Vitabulk
</div>

<?php endif; ?>

</div>


<?php if (
    count($imagens) > 1
): ?>

<div class="product-thumbnails">

<?php foreach (
    $imagens as $imagem
): ?>

<button
    type="button"
    class="product-thumbnail"
    onclick="trocarImagemProduto(this)"
>

<img
    src="<?= e($imagem) ?>"
    data-image="<?= e($imagem) ?>"
    alt=""
>

</button>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>


<div class="product-detail-info">

<span class="product-category">
<?= e($produto["categoria"] ?? "") ?>
</span>


<h1>
<?= e($produto["nome"]) ?>
</h1>


<?php if (
    $siteEmVendas
    && $mostrarPrecos
): ?>

<?php if (
    $precoInfo["desconto"] > 0
): ?>

<div class="product-old-price large">
<?= precoVitabulk(
    $precoInfo["preco_original"]
) ?>
</div>

<div class="product-price large">
<?= precoVitabulk(
    $precoInfo["preco_final"]
) ?>
</div>

<?php else: ?>

<div class="product-price large">
<?= precoVitabulk(
    $precoInfo["preco_final"]
) ?>
</div>

<?php endif; ?>

<?php endif; ?>


<div class="product-description">

<?= nl2br(
    e(
        $produto["descricao"]
        ?? ""
    )
) ?>

</div>


<?php if (
    $siteEmVendas
): ?>

<?php if (
    $mostrarStock
): ?>

<div class="stock-info">

Stock:
<strong>
<?= (int)$produto["stock"] ?>
</strong>

</div>

<?php endif; ?>


<?php if (
    (int)$produto["stock"] > 0
): ?>

<form
    action="adicionar_carrinho.php"
    method="POST"
    class="buy-form"
>

<input
    type="hidden"
    name="produto_id"
    value="<?= (int)$produto["id"] ?>"
>


<input
    type="number"
    name="quantidade"
    value="1"
    min="1"
    max="<?= (int)$produto["stock"] ?>"
>


<button
    type="submit"
    class="btn btn-primary"
>
Adicionar ao carrinho
</button>

</form>

<?php else: ?>

<div class="notice">
Produto sem stock.
</div>

<?php endif; ?>

<?php else: ?>

<div class="demo-info">
Entre em contacto connosco para saber mais
sobre este produto.
</div>

<?php endif; ?>

</div>

</div>

</div>

</section>

</main>


<script src="assets/js/main.js"></script>

</body>

</html>