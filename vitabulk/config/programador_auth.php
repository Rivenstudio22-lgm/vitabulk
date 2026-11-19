<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function programadorLogado()
{
    return isset($_SESSION['programador_id']);
}

function exigirProgramador()
{
    if (!programadorLogado()) {
        header('Location: login.php');
        exit;
    }
}

function programadorNome()
{
    return $_SESSION['programador_nome'] ?? 'Programador';
}

function programadorEmail()
{
    return $_SESSION['programador_email'] ?? '';
}

function programadorRole()
{
    return $_SESSION['programador_role'] ?? 'programador';
}
