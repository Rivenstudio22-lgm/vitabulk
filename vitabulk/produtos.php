<?php

if (
    session_status()
    === PHP_SESSION_NONE
) {
    session_start();
}

require_once __DIR__ . "/config/site.php";
require_once __DIR__ . "/config/registrar_visita.php";


$pesquisa =
    trim($_GET["q"] ?? "");


$categoriaSelecionada =
    trim(
        $_GET["categoria"]
        ?? ""
    );


$categorias = [];

$resultadoCategorias =
    $conn->query("
        SELECT id, nome
        FROM categorias
        ORDER BY nome ASC
    ");

if ($resultadoCategorias) {

    while (
        $categoria =
        $resultadoCategorias->fetch_assoc()
    ) {

        $categorias[] =
            $categoria;
    }
}


$produtos = [];


$sql = "
    SELECT
        id,
        nome,
        descricao,
        categoria,
        preco,
        imagem,
        stock,
        status
    FROM produtos
    WHERE status = 'ativo'
";

$parametros = [];
$tipos = "";


if ($pesquisa !== "") {

    $sql .= "
        AND (
            nome LIKE ?
            OR descricao LIKE ?
            OR categoria LIKE ?
        )
    ";

    $termo =
        "%" . $pesquisa . "%";

    $parametros[] =
        $termo;

    $parametros[] =
        $termo;

    $parametros[] =
        $termo;

    $tipos .= "sss";
}


if ($categoriaSelecionada !== "") {

    $sql .= "
        AND categoria = ?
    ";

    $parametros[] =
        $categoriaSelecionada;

    $tipos .= "s";
}


$sql .= "
    ORDER BY criado_em DESC, id DESC
";


$stmt =
    $conn->prepare($sql);


if (!empty($parametros)) {

    $stmt->bind_param(
        $tipos,
        ...$parametros
    );
}


$stmt->execute();

$resultado =
    $stmt->get_result();


while (
    $produto =
    $resultado->fetch_assoc()
) {

    $produtos[] =
        $produto;
}

$stmt->close();

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
Produtos | <?= e($nomeEmpresa) ?>
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


<button
    class="menu-toggle"
    type="button"
>
☰
</button>


<nav class="main-nav">

<a href="index.php">
Início
</a>

<a
    href="produtos.php"
    class="active"
>
Produtos
</a>

<a href="sobre.php">
Sobre nós
</a>

<?php if ($siteEmVendas): ?>

<?php if (
    isset($_SESSION["cliente_id"])
): ?>

<a href="minha-conta.php">
Minha conta
</a>

<a href="carrinho.php">
Carrinho
</a>

<?php else: ?>

<a href="login.php">
Entrar
</a>

<?php endif; ?>

<?php endif; ?>

</nav>

</div>

</header>


<main>

<section class="page-hero">

<div class="container">

<span class="section-label">
VITABULK
</span>

<h1>
Os nossos produtos
</h1>

<p>
Explore o nosso catálogo de produtos alimentares.
</p>

</div>

</section>


<section class="products-section">

<div class="container">


<?php if (
    $pesquisaProdutos
): ?>

<form
    method="GET"
    class="product-search"
>

<input
    type="search"
    name="q"
    value="<?= e($pesquisa) ?>"
    placeholder="Pesquisar produto..."
>


<select name="categoria">

<option value="">
Todas as categorias
</option>

<?php foreach (
    $categorias as $categoria
): ?>

<option
    value="<?= e($categoria["nome"]) ?>"
    <?= $categoriaSelecionada
        === $categoria["nome"]
        ? "selected"
        : ""
    ?>
>
<?= e($categoria["nome"]) ?>
</option>

<?php endforeach; ?>

</select>


<button
    class="btn btn-primary"
    type="submit"
>
Pesquisar
</button>

</form>

<?php endif; ?>


<div class="products-grid">

<?php foreach (
    $produtos as $produto
): ?>

<?php

$precoInfo =
    calcularPrecoPromocional(
        $produto["preco"] ?? 0,
        $produto["id"]
    );

?>

<article class="product-card">

<a
    href="verproduto.php?id=<?= (int)$produto["id"] ?>"
>

<?php if (
    $produto["imagem"]
): ?>

<img
    src="<?= e($produto["imagem"]) ?>"
    alt="<?= e($produto["nome"]) ?>"
>

<?php else: ?>

<div class="product-placeholder">
Vitabulk
</div>

<?php endif; ?>

</a>


<div class="product-content">

<span class="product-category">
<?= e($produto["categoria"] ?? "") ?>
</span>

<h3>
<?= e($produto["nome"]) ?>
</h3>


<?php if (
    $siteEmVendas
    && $mostrarPrecos
): ?>

<?php if (
    $precoInfo["desconto"] > 0
): ?>

<div class="product-old-price">
<?= precoVitabulk(
    $precoInfo["preco_original"]
) ?>
</div>

<div class="product-price">
<?= precoVitabulk(
    $precoInfo["preco_final"]
) ?>
</div>

<?php else: ?>

<div class="product-price">
<?= precoVitabulk(
    $precoInfo["preco_final"]
) ?>
</div>

<?php endif; ?>

<?php endif; ?>


<a
    href="verproduto.php?id=<?= (int)$produto["id"] ?>"
    class="btn btn-primary product-button"
>
Ver produto
</a>

</div>

</article>

<?php endforeach; ?>

</div>


<?php if (!$produtos): ?>

<div class="empty-state">

<h3>
Nenhum produto encontrado.
</h3>

<p>
Tente outra pesquisa ou categoria.
</p>

</div>

<?php endif; ?>

</div>

</section>

</main>


<footer class="site-footer">

<div class="footer-bottom">

© <?= date("Y") ?>
Vitabulk Alimentos, Lda.
Todos os direitos reservados.

</div>

</footer>


<script src="assets/js/main.js"></script>

</body>

</html>