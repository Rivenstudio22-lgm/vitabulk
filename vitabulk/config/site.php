<?php

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/configuracoes.php";
require_once __DIR__ . "/vendas.php";
require_once __DIR__ . "/promocoes.php";


$nomeEmpresa = obterConfiguracao(
    "nome_empresa",
    "Vitabulk Alimentos, Lda"
);


$tituloSite = obterConfiguracao(
    "titulo_site",
    "Vitabulk Alimentos, Lda"
);


$descricaoSite = obterConfiguracao(
    "descricao_site",
    "Venda a grosso e retalho de produtos alimentares."
);


$emailEmpresa = obterConfiguracao(
    "email",
    "info@vitabulkalimentos.co.mz"
);


$telefoneEmpresa = obterConfiguracao(
    "telefone",
    ""
);


$whatsappEmpresa = obterConfiguracao(
    "whatsapp",
    ""
);


$enderecoEmpresa = obterConfiguracao(
    "endereco",
    ""
);


$horarioEmpresa = obterConfiguracao(
    "horario",
    ""
);


$instagramEmpresa = obterConfiguracao(
    "instagram",
    ""
);


$facebookEmpresa = obterConfiguracao(
    "facebook",
    ""
);


$tiktokEmpresa = obterConfiguracao(
    "tiktok",
    ""
);


$urlMapa = obterConfiguracao(
    "url_mapa",
    ""
);


$corPrimaria = validarCor(
    obterConfiguracao(
        "cor_primaria",
        "#16823a"
    ),
    "#16823a"
);


$corSecundaria = validarCor(
    obterConfiguracao(
        "cor_secundaria",
        "#0d642b"
    ),
    "#0d642b"
);


$corBotoes = validarCor(
    obterConfiguracao(
        "cor_botoes",
        "#16823a"
    ),
    "#16823a"
);


$corTexto = validarCor(
    obterConfiguracao(
        "cor_texto",
        "#222222"
    ),
    "#222222"
);


$corFundo = validarCor(
    obterConfiguracao(
        "cor_fundo",
        "#ffffff"
    ),
    "#ffffff"
);


$logo = obterConfiguracao(
    "logo",
    ""
);


$favicon = obterConfiguracao(
    "favicon",
    ""
);


$mostrarPrecos =
    configuracaoAtiva(
        "mostrar_precos",
        true
    );


$mostrarStock =
    configuracaoAtiva(
        "mostrar_stock",
        true
    );


$mostrarCategorias =
    configuracaoAtiva(
        "mostrar_categorias",
        true
    );


$mostrarMapa =
    configuracaoAtiva(
        "mostrar_mapa",
        true
    );


$pesquisaProdutos =
    configuracaoAtiva(
        "pesquisa_produtos",
        true
    );


$siteEmDemo =
    modoDemonstracao();


$siteEmVendas =
    vendasAtivas();


if (!function_exists(
    "urlWhatsAppVitabulk"
)) {

    function urlWhatsAppVitabulk(
        $numero
    ) {

        $numero =
            preg_replace(
                '/[^0-9]/',
                '',
                (string)$numero
            );

        if ($numero === '') {
            return '';
        }

        return
            "https://wa.me/" .
            $numero;
    }
}


if (!function_exists("urlRedeSocialVitabulk")) {

    function urlRedeSocialVitabulk(
        $url
    ) {

        $url =
            trim((string)$url);

        if ($url === '') {
            return '';
        }

        if (
            strpos(
                $url,
                "http://"
            ) !== 0
            &&
            strpos(
                $url,
                "https://"
            ) !== 0
        ) {

            $url =
                "https://" . $url;
        }

        return $url;
    }
}


if (!function_exists("e")) {

    function e($valor)
    {
        return htmlspecialchars(
            (string)$valor,
            ENT_QUOTES,
            "UTF-8"
        );
    }
}


if (!function_exists("precoVitabulk")) {

    function precoVitabulk($valor)
    {
        return number_format(
            (float)$valor,
            2,
            ",",
            "."
        ) . " MT";
    }
}