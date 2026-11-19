<?php

if (
    session_status()
    === PHP_SESSION_NONE
) {
    session_start();
}

require_once __DIR__ . "/config/site.php";
require_once __DIR__ . "/config/registrar_visita.php";


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

$stmt = $conn->prepare("
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
    ORDER BY criado_em DESC, id DESC
    LIMIT 8
");

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


$clienteLogado =
    isset($_SESSION["cliente_id"]);

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
    <?= e($tituloSite) ?>
</title>

<meta
    name="description"
    content="<?= e($descricaoSite) ?>"
>

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

<strong>Vitabulk</strong>

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

<a
    href="index.php"
    class="active"
>
Início
</a>

<a href="produtos.php">
Produtos
</a>

<a href="sobre.php">
Sobre nós
</a>

<?php if ($siteEmVendas): ?>

<?php if ($clienteLogado): ?>

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

<section class="hero">

<div class="container">

<span class="section-label">
VITABULK ALIMENTOS
</span>

<h1>
Produtos alimentares para
famílias, negócios e revendedores.
</h1>

<p>
Venda a grosso e retalho de produtos
alimentares com qualidade e variedade.
</p>

<div class="hero-actions">

<a
    href="produtos.php"
    class="btn btn-primary"
>
Ver produtos
</a>

<a
    href="sobre.php#contactos"
    class="btn btn-outline"
>
Contactar-nos
</a>

</div>

</div>

</section>


<?php if ($categorias): ?>

<section class="categories-section">

<div class="container">

<span class="section-label">
CATEGORIAS
</span>

<h2>
Explore os nossos produtos
</h2>

<div class="category-list">

<?php foreach (
    $categorias as $categoria
): ?>

<a
    href="produtos.php?categoria=<?= urlencode($categoria["nome"]) ?>"
    class="category-card"
>

<?= e($categoria["nome"]) ?>

</a>

<?php endforeach; ?>

</div>

</div>

</section>

<?php endif; ?>


<section class="products-section">

<div class="container">

<div class="section-heading">

<div>

<span class="section-label">
CATÁLOGO
</span>

<h2>
Os nossos produtos
</h2>

</div>

<a
    href="produtos.php"
    class="btn btn-outline"
>
Ver todos
</a>

</div>


<?php if ($produtos): ?>

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
    !empty($produto["imagem"])
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

<?php else: ?>

<div class="empty-state">

<h3>
Ainda não existem produtos.
</h3>

<p>
Estamos a preparar o nosso catálogo.
</p>

</div>

<?php endif; ?>

</div>

</section>


<section class="cta-section">

<div class="container">

<span class="section-label">
VITABULK
</span>

<h2>
Precisa de mais informações?
</h2>

<p>
Entre em contacto connosco para conhecer
os nossos produtos e condições.
</p>

<a
    href="sobre.php#contactos"
    class="btn btn-primary"
>
Contactar-nos
</a>

</div>

</section>

</main>


<footer class="site-footer">

<div class="container footer-grid">

<div>

<strong>
Vitabulk Alimentos, Lda
</strong>

<p>
Venda a grosso e retalho de produtos alimentares.
</p>

</div>


<div>

<h4>
Navegação
</h4>

<a href="index.php">
Início
</a>

<a href="produtos.php">
Produtos
</a>

<a href="sobre.php">
Sobre nós
</a>

</div>


<div>

<h4>
Contacto
</h4>

<?php if ($emailEmpresa): ?>

<a
    href="mailto:<?= e($emailEmpresa) ?>"
>
<?= e($emailEmpresa) ?>
</a>

<?php endif; ?>


<?php if ($telefoneEmpresa): ?>

<a
    href="tel:<?= e($telefoneEmpresa) ?>"
>
<?= e($telefoneEmpresa) ?>
</a>

<?php endif; ?>


<?php

$whatsapp =
    urlWhatsAppVitabulk(
        $whatsappEmpresa
    );

?>

<?php if ($whatsapp): ?>

<a
    href="<?= e($whatsapp) ?>"
    target="_blank"
>
WhatsApp
</a>

<?php endif; ?>

</div>

</div>


<div class="footer-bottom">

© <?= date("Y") ?>
Vitabulk Alimentos, Lda.
Todos os direitos reservados.

</div>

</footer>


<script src="assets/js/main.js"></script>

</body>

</html>