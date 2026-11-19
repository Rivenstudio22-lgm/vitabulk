<?php

require_once __DIR__."/inc.php";

$erro="";
$sucesso="";

if($_SERVER["REQUEST_METHOD"]==="POST"){

    $nome=trim($_POST["nome"]??"");
    $email=trim($_POST["email"]??"");
    $senha=$_POST["senha"]??"";

    if(!$nome || !$email || strlen($senha)<6){

        $erro="Preencha corretamente os campos.";

    }else{

        $hash=password_hash(
            $senha,
            PASSWORD_DEFAULT
        );

        $s=$conn->prepare("
            INSERT INTO programadores
            (nome,email,senha)
            VALUES(?,?,?)
        ");

        $s->bind_param(
            "sss",
            $nome,
            $email,
            $hash
        );

        if($s->execute()){
            $sucesso="Programador criado.";
        }else{
            $erro="Email já utilizado.";
        }

        $s->close();
    }
}

adminHeader("Criar programador");

?>

<div class="card form-card">

<h1>Criar programador</h1>

<?php if($erro): ?>
<div class="alert error"><?=$erro?></div>
<?php endif; ?>

<?php if($sucesso): ?>
<div class="alert success"><?=$sucesso?></div>
<?php endif; ?>

<form method="post">

<label>Nome</label>
<input name="nome" required>

<label>Email</label>
<input type="email" name="email" required>

<label>Senha</label>
<input type="password" name="senha" required>

<button class="btn">
Criar programador
</button>

</form>

</div>

<?php adminFooter(); ?>