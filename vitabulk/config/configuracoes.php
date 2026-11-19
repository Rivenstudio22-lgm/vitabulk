<?php

require_once __DIR__ . "/database.php";


/**
 * =========================================================
 * OBTER CONFIGURAÇÃO
 * =========================================================
 */

function obterConfiguracao($chave, $padrao = "")
{
    global $conn;

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

    if (!$stmt->execute()) {
        $stmt->close();
        return $padrao;
    }

    $resultado = $stmt->get_result();
    $dados = $resultado->fetch_assoc();

    $stmt->close();

    if (!$dados) {
        return $padrao;
    }

    return $dados["valor"];
}


/**
 * =========================================================
 * GUARDAR CONFIGURAÇÃO
 * =========================================================
 */

function guardarConfiguracao($chave, $valor)
{
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO configuracoes_site
        (chave, valor)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE
            valor = VALUES(valor)
    ");

    if (!$stmt) {
        return false;
    }

    $valor = (string)$valor;

    $stmt->bind_param("ss", $chave, $valor);

    $resultado = $stmt->execute();

    $stmt->close();

    return $resultado;
}


/**
 * =========================================================
 * VERIFICAR CONFIGURAÇÃO ATIVA
 * =========================================================
 */

function configuracaoAtiva($chave, $padrao = true)
{
    $valor = obterConfiguracao(
        $chave,
        $padrao ? "1" : "0"
    );

    return in_array(
        strtolower(trim((string)$valor)),
        [
            "1",
            "true",
            "sim",
            "yes",
            "on",
            "ativo"
        ],
        true
    );
}


/**
 * =========================================================
 * CARREGAR TODAS AS CONFIGURAÇÕES
 * =========================================================
 */

function carregarConfiguracoes()
{
    global $conn;

    $dados = [];

    $resultado = $conn->query("
        SELECT chave, valor
        FROM configuracoes_site
    ");

    if ($resultado) {

        while ($linha = $resultado->fetch_assoc()) {

            $dados[$linha["chave"]] = $linha["valor"];

        }

    }

    return $dados;
}


/**
 * =========================================================
 * VALIDAR COR
 * =========================================================
 */

function validarCor($cor, $padrao = "#16823a")
{
    $cor = trim((string)$cor);

    if (preg_match('/^#[0-9A-Fa-f]{6}$/', $cor)) {
        return $cor;
    }

    return $padrao;
}