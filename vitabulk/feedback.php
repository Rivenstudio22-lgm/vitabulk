<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/configuracoes.php";
require_once __DIR__ . "/config/cliente_auth.php";

$sucesso = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $avaliacao = (int)($_POST["avaliacao"] ?? 5);
    $comentario = trim($_POST["comentario"] ?? "");

    if ($nome === "" || $comentario === "") {
        $erro = "Por favor, preencha o seu nome e o comentário.";
    } elseif ($avaliacao < 1 || $avaliacao > 5) {
        $erro = "Selecione uma classificação válida.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO avaliacoes (nome, email, avaliacao, comentario, data_criacao, aprovado)
                VALUES (?, ?, ?, ?, NOW(), 1)
            ");
            $stmt->execute([$nome, $email, $avaliacao, $comentario]);
            $sucesso = "Muito obrigado pelo seu feedback!";
        } catch (PDOException $e) {
            $erro = "Não foi possível guardar a sua avaliação. Tente novamente.";
        }
    }
}

try {
    $stmt = $pdo->query("
        SELECT nome, avaliacao, comentario, data_criacao
        FROM avaliacoes
        WHERE aprovado = 1
        ORDER BY id DESC
        LIMIT 20
    ");
    $avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $avaliacoes = [];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback e Avaliações - Vitabulk</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header class="site-header">
        <div class="container header-container">
            <a href="index.php" class="logo">
                Vitabulk
            </a>
            <nav class="main-nav">
                <a href="index.php">Início</a>
                <a href="produtos.php">Produtos</a>
                <a href="feedback.php" class="active">Feedback</a>
                <a href="sobre.php">Sobre</a>
                <?php if (clienteLogado()): ?>
                    <a href="minha-conta.php">Minha Conta</a>
                <?php else: ?>
                    <a href="login.php">Entrar</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="page-content container" style="max-width: 800px; margin: 40px auto; padding: 0 15px;">
        <h1>Avaliações dos Nossos Clientes</h1>
        <p>A sua opinião ajuda-nos a melhorar a qualidade dos nossos produtos e serviços.</p>

        <?php if ($sucesso): ?>
            <div class="alert alert-success" style="padding: 12px; background: #e6f4ea; color: #137333; border-radius: 8px; margin: 20px 0;">
                <?= htmlspecialchars($sucesso) ?>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger" style="padding: 12px; background: #fce8e6; color: #c5221f; border-radius: 8px; margin: 20px 0;">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <section class="feedback-form-box" style="background: #fff; padding: 24px; border-radius: 12px; border: 1px solid #eee; margin: 30px 0;">
            <h2>Deixe o seu testemunho</h2>
            <form method="POST" action="feedback.php" style="display: flex; flex-direction: column; gap: 15px; margin-top: 15px;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">O seu Nome *</label>
                    <input type="text" name="nome" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;" value="<?= htmlspecialchars(clienteNome() !== 'Cliente' ? clienteNome() : '') ?>">
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">Email (opcional)</label>
                    <input type="email" name="email" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;" value="<?= htmlspecialchars(clienteEmail()) ?>">
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">Classificação *</label>
                    <select name="avaliacao" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                        <option value="5">⭐⭐⭐⭐⭐ (Excelente)</option>
                        <option value="4">⭐⭐⭐⭐ (Muito Bom)</option>
                        <option value="3">⭐⭐⭐ (Bom)</option>
                        <option value="2">⭐⭐ (Razoável)</option>
                        <option value="1">⭐ (Fraco)</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">O seu Comentário *</label>
                    <textarea name="comentario" rows="4" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;" placeholder="Conte-nos a sua experiência com os nossos produtos..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 12px 20px; background: #00875a; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: fit-content;">Enviar Avaliação</button>
            </form>
        </section>

        <section class="feedback-list" style="margin-top: 40px;">
            <h2>Testemunhos Recentes</h2>
            <?php if (empty($avaliacoes)): ?>
                <p style="color: #666; margin-top: 15px;">Ainda não existem avaliações. Seja o primeiro a avaliar!</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
                    <?php foreach ($avaliacoes as $av): ?>
                        <div style="background: #fafafa; border: 1px solid #eaeaea; border-radius: 8px; padding: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong><?= htmlspecialchars($av["nome"]) ?></strong>
                                <span style="color: #f59e0b;"><?= str_repeat("★", (int)$av["avaliacao"]) ?><?= str_repeat("☆", 5 - (int)$av["avaliacao"]) ?></span>
                            </div>
                            <p style="margin: 0; color: #333; line-height: 1.5;"><?= nl2br(htmlspecialchars($av["comentario"])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="site-footer" style="margin-top: 60px; padding: 20px 0; border-top: 1px solid #eee; text-align: center; color: #777;">
        <div class="container">
            <p>&copy; <?= date("Y") ?> Vitabulk Alimentos. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>
</html>
