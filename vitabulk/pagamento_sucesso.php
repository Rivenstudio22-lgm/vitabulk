<?php

session_start();

require_once __DIR__."/config/database.php";

$pedido=(int)($_GET["pedido"]??0);

if($pedido && isset($_SESSION["carrinho"])){

    unset($_SESSION["carrinho"]);
}

?>

<!DOCTYPE html>
<html lang="pt">

<head>
<meta charset="UTF-8">
<title>Pagamento | Vitabulk</title>
</head>

<body>

<main class="container">

<h1>Pagamento recebido</h1>

<p>
O seu pedido foi registado.
</p>

<p>
Número do pedido:
<strong>#<?=$pedido?></strong>
</p>

<a href="index.php">
Voltar ao início
</a>

</main>

</body>

</html>