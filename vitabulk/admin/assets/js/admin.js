document.addEventListener("DOMContentLoaded",function(){

    document.querySelectorAll("form").forEach(function(form){

        form.addEventListener("submit",function(){

            const btn=form.querySelector(
                'button[type="submit"],button:not([type])'
            );

            if(btn){
                btn.dataset.originalText=btn.innerText;
                btn.innerText="A guardar...";
                btn.disabled=true;
            }

        });

    });


    document.querySelectorAll(
        'input[type="file"][multiple]'
    ).forEach(function(input){

        input.addEventListener("change",function(){

            if(this.files.length>5){

                alert("Pode selecionar no máximo 5 imagens.");

                this.value="";
            }

        });

    });

});