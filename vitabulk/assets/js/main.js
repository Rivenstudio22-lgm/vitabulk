document.addEventListener("DOMContentLoaded", function () {

    const menuButton =
        document.querySelector(".menu-toggle");

    const menu =
        document.querySelector(".main-nav");

    if (menuButton && menu) {

        menuButton.addEventListener(
            "click",
            function () {

                menu.classList.toggle(
                    "open"
                );

                const aberto =
                    menu.classList.contains(
                        "open"
                    );

                menuButton.setAttribute(
                    "aria-expanded",
                    aberto
                        ? "true"
                        : "false"
                );
            }
        );
    }

});


function trocarImagemProduto(botao)
{
    const imagem =
        botao.querySelector("img");

    const principal =
        document.getElementById(
            "imagemPrincipal"
        );

    if (
        imagem
        && principal
    ) {

        principal.src =
            imagem.dataset.image
            || imagem.src;
    }
}