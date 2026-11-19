<?php

require_once __DIR__ . "/database.php";

if (
    session_status()
    === PHP_SESSION_NONE
) {
    session_start();
}


$pagina =
    basename(
        $_SERVER["PHP_SELF"]
    );


$clienteId =
    $_SESSION["cliente_id"]
    ?? null;


$chaveVisita =
    "visita_" . $pagina;


$jaRegistrou =
    isset(
        $_SESSION[$chaveVisita]
    );


if (!$jaRegistrou) {

    $ip =
        $_SERVER["REMOTE_ADDR"]
        ?? "";

    $userAgent =
        $_SERVER["HTTP_USER_AGENT"]
        ?? "";

    $ipHash =
        hash(
            "sha256",
            $ip . "|vitabulk"
        );


    $stmt = $conn->prepare("
        INSERT INTO visitas
        (
            cliente_id,
            pagina,
            ip_hash,
            user_agent
        )
        VALUES (?, ?, ?, ?)
    ");


    $stmt->bind_param(
        "isss",
        $clienteId,
        $pagina,
        $ipHash,
        $userAgent
    );


    $stmt->execute();

    $stmt->close();


    $_SESSION[$chaveVisita] =
        time();
}