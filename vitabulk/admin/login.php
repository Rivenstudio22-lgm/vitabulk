<?php

if(session_status()===PHP_SESSION_NONE){
    session_start();
}

if(isset($_SESSION["admin_id"])){
    header("Location: index.php");
    exit;
}

require_once __DIR__."/../config/database.php";

$erro="";

if($_SERVER["REQUEST_METHOD"]==="POST"){

    $email=trim($_POST["email"]??"");
    $senha=$_POST["senha"]??"";

    $stmt=$conn->prepare("
        SELECT id,nome,email,senha
        FROM administradores
        WHERE email=?
        LIMIT 1
    ");

    $stmt->bind_param("s",$email);
    $stmt->execute();

    $admin=$stmt->get_result()->fetch_assoc();

    $stmt->close();

    if($admin && password_verify($senha,$admin["senha"])){

        $_SESSION["admin_id"]=$admin["id"];
        $_SESSION["admin_nome"]=$admin["nome"];
        $_SESSION["admin_email"]=$admin["email"];

        header("Location: index.php");
        exit;

    }else{
        $erro="Email ou senha inválidos.";
    }
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin | Vitabulk</title>
<link rel="stylesheet" href="assets/css/admin.css">
</head>

<body class="login-body">

<form method="post" class="login-box">

<h1>Vita<span>bulk</span></h1>

<p>Painel administrativo</p>

<?php if($erro): ?>
<div class="alert error"><?=$erro?></div>
<?php endif; ?>

<label>Email</label>
<input type="email" name="email" required>

<label>Senha</label>
<input type="password" name="senha" required>

<button class="btn" type="submit">
Entrar
</button>

</form>

</body>
</html>