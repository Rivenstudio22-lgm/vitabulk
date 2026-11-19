<?php

require_once __DIR__ . "/database.php";

header("Content-Type: text/css; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

function temaConfig($conn, $chave, $padrao)
{
    $stmt = $conn->prepare("
        SELECT valor
        FROM configuracoes_site
        WHERE chave = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return $padrao;
    }

    $stmt->bind_param("s", $chave);
    $stmt->execute();

    $resultado = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$resultado || trim($resultado["valor"]) === "") {
        return $padrao;
    }

    return $resultado["valor"];
}

function limparCor($cor, $padrao)
{
    $cor = trim($cor);

    if (preg_match('/^#[0-9a-fA-F]{6}$/', $cor)) {
        return strtoupper($cor);
    }

    return $padrao;
}

$primaria = limparCor(
    temaConfig($conn, "cor_primaria", "#16823A"),
    "#16823A"
);

$secundaria = limparCor(
    temaConfig($conn, "cor_secundaria", "#0D642B"),
    "#0D642B"
);

$botoes = limparCor(
    temaConfig($conn, "cor_botoes", "#16823A"),
    "#16823A"
);

$fundo = limparCor(
    temaConfig($conn, "cor_fundo", "#FFFFFF"),
    "#FFFFFF"
);

$texto = limparCor(
    temaConfig($conn, "cor_texto", "#17231C"),
    "#17231C"
);

$animacoes = temaConfig($conn, "animacoes", "1");
$modoEscuro = temaConfig($conn, "modo_escuro", "0");

?>

/* =========================================================
   VITABULK - TEMA DINÂMICO
========================================================= */

:root {

    --verde: <?= $primaria ?> !important;

    --verde-escuro: <?= $secundaria ?> !important;

    --cor-primaria: <?= $primaria ?> !important;

    --cor-secundaria: <?= $secundaria ?> !important;

    --cor-botoes: <?= $botoes ?> !important;

    --cor-fundo: <?= $fundo ?> !important;

    --cor-texto: <?= $texto ?> !important;

    --transicao-site:
        <?= $animacoes === "1" ? "0.25s" : "0s" ?>;

}


/* =========================================================
   FUNDO E TEXTO
========================================================= */

html,
body {

    background-color: <?= $fundo ?> !important;

    color: <?= $texto ?> !important;

}


/* =========================================================
   LINKS
========================================================= */

a {

    transition:
        color <?= $animacoes === "1" ? "0.25s" : "0s" ?>,
        background-color <?= $animacoes === "1" ? "0.25s" : "0s" ?>,
        border-color <?= $animacoes === "1" ? "0.25s" : "0s" ?>;

}


/* =========================================================
   CORES PRINCIPAIS
========================================================= */

.logo,
.logo-text,
.brand,
.titulo-verde,
.texto-verde {

    color: <?= $primaria ?> !important;

}


/* =========================================================
   BOTÕES
========================================================= */

.btn,
.btn-primary,
.botao,
.botao-principal,
.btn-verde,
.botao-verde,
button[type="submit"],
input[type="submit"] {

    background-color: <?= $botoes ?> !important;

    border-color: <?= $botoes ?> !important;

}


/* HOVER */

.btn:hover,
.btn-primary:hover,
.botao:hover,
.botao-principal:hover,
.btn-verde:hover,
.botao-verde:hover,
button[type="submit"]:hover,
input[type="submit"]:hover {

    background-color: <?= $secundaria ?> !important;

    border-color: <?= $secundaria ?> !important;

}


/* =========================================================
   ELEMENTOS VERDES
========================================================= */

.bg-verde,
.fundo-verde,
.destaque-verde {

    background-color: <?= $primaria ?> !important;

}


.bg-verde-escuro,
.fundo-verde-escuro {

    background-color: <?= $secundaria ?> !important;

}


/* =========================================================
   TEXTOS
========================================================= */

.text-verde,
.texto-verde {

    color: <?= $primaria ?> !important;

}


.text-verde-escuro,
.texto-verde-escuro {

    color: <?= $secundaria ?> !important;

}


/* =========================================================
   BORDAS
========================================================= */

.border-verde,
.borda-verde {

    border-color: <?= $primaria ?> !important;

}


/* =========================================================
   LINKS HOVER
========================================================= */

a:hover {

    color: <?= $primaria ?> !important;

}


/* =========================================================
   INPUTS FOCUS
========================================================= */

input:focus,
textarea:focus,
select:focus {

    border-color: <?= $primaria ?> !important;

    outline-color: <?= $primaria ?> !important;

}


/* =========================================================
   MODO ESCURO
========================================================= */

<?php if ($modoEscuro === "1"): ?>

body {

    background-color: #111111 !important;

    color: #f5f5f5 !important;

}

.card,
.produto-card,
.product-card,
.caixa,
.secao-card {

    background-color: #1b1b1b !important;

    color: #f5f5f5 !important;

}

input,
textarea,
select {

    background-color: #1b1b1b !important;

    color: #ffffff !important;

    border-color: #444444 !important;

}

<?php endif; ?>