<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$database = "vitabulk";
$porta = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $conn = new mysqli(
        $host,
        $usuario,
        $senha,
        $database,
        $porta
    );

    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    die(
        "Erro na ligação com a base de dados: " .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            "UTF-8"
        )
    );
}