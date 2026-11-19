<?php

if (
    session_status()
    === PHP_SESSION_NONE
) {
    session_start();
}

require_once __DIR__ . "/config/site.php";
require_once __DIR__ . "/config/registrar_visita.php";

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
Sobre nós | <?= e($nomeEmpresa) ?>
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

<a
    href="sobre.php"
    class="active"
>
Sobre nós
</a>

</nav>

</div>

</header>


<main>

<section class="page-hero">

<div class="container">

<span class="section-label">
SOBRE A VITABULK
</span>

<h1>
Vitabulk Alimentos, Lda
</h1>

<p>
Venda a grosso e retalho de produtos alimentares.
</p>

</div>

</section>


<section class="about-section">

<div class="container about-grid">

<div>

<span class="section-label">
QUEM SOMOS
</span>

<h2>
Vitabulk Alimentos, Lda
</h2>

<p>
A Vitabulk Alimentos, Lda é uma empresa
dedicada à venda a grosso e retalho
de produtos alimentares.
</p>

<p>
Trabalhamos para disponibilizar aos nossos
clientes produtos alimentares com qualidade,
variedade e preços adequados às suas
necessidades.
</p>

<p>
As papas e os cereais fazem parte da nossa
oferta, mas a Vitabulk procura construir
uma oferta alimentar diversificada.
</p>

</div>


<div class="about-card">

<h3>
O que fazemos
</h3>

<p>
Fornecemos produtos alimentares para
consumidores, empresas e revendedores.
</p>

</div>

</div>

</section>


<section
    class="contact-section"
    id="contactos"
>

<div class="container">

<span class="section-label">
CONTACTOS
</span>

<h2>
Entre em contacto connosco
</h2>


<div class="contact-grid">


<?php if (
    $emailEmpresa
): ?>

<div class="contact-item">

<strong>
Email
</strong>

<a
    href="mailto:<?= e($emailEmpresa) ?>"
>
<?= e($emailEmpresa) ?>
</a>

</div>

<?php endif; ?>


<?php if (
    $telefoneEmpresa
): ?>

<div class="contact-item">

<strong>
Telefone
</strong>

<a
    href="tel:<?= e($telefoneEmpresa) ?>"
>
<?= e($telefoneEmpresa) ?>
</a>

</div>

<?php endif; ?>


<?php

$whatsapp =
    urlWhatsAppVitabulk(
        $whatsappEmpresa
    );

?>

<?php if ($whatsapp): ?>

<div class="contact-item">

<strong>
WhatsApp
</strong>

<a
    href="<?= e($whatsapp) ?>"
    target="_blank"
>
Falar pelo WhatsApp
</a>

</div>

<?php endif; ?>


<?php if (
    $enderecoEmpresa
): ?>

<div class="contact-item">

<strong>
Endereço
</strong>

<span>
<?= e($enderecoEmpresa) ?>
</span>

</div>

<?php endif; ?>


<?php if (
    $horarioEmpresa
): ?>

<div class="contact-item">

<strong>
Horário
</strong>

<span>
<?= e($horarioEmpresa) ?>
</span>

</div>

<?php endif; ?>

</div>


<div class="social-links">

<?php if (
    $instagramEmpresa
): ?>

<a
    href="<?= e(
        urlRedeSocialVitabulk(
            $instagramEmpresa
        )
    ) ?>"
    target="_blank"
>
Instagram
</a>

<?php endif; ?>


<?php if (
    $facebookEmpresa
): ?>

<a
    href="<?= e(
        urlRedeSocialVitabulk(
            $facebookEmpresa
        )
    ) ?>"
    target="_blank"
>
Facebook
</a>

<?php endif; ?>


<?php if (
    $tiktokEmpresa
): ?>

<a
    href="<?= e(
        urlRedeSocialVitabulk(
            $tiktokEmpresa
        )
    ) ?>"
    target="_blank"
>
TikTok
</a>

<?php endif; ?>

</div>


<?php if (
    $mostrarMapa
    && $urlMapa
): ?>

<div class="map-container">

<iframe
    src="<?= e($urlMapa) ?>"
    loading="lazy"
    allowfullscreen
    referrerpolicy="no-referrer-when-downgrade"
></iframe>

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