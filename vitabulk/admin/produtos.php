<?php

require_once __DIR__ . "/inc.php";

exigirAdmin();

$mensagem = "";
$tipoMensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acao"])) {

    if ($_POST["acao"] === "eliminar" && !empty($_POST["produto_id"])) {

        $produtoId = (int)$_POST["produto_id"];

        try {
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->execute([$produtoId]);

            $mensagem = "Produto eliminado com sucesso.";
            $tipoMensagem = "sucesso";

        } catch (PDOException $e) {
            $mensagem = "Não foi possível eliminar o produto.";
            $tipoMensagem = "erro";
        }
    }
}

try {
    $stmt = $pdo->query("
        SELECT 
            p.*,
            (SELECT imagem FROM produto_imagens WHERE produto_id = p.id AND principal = 1 LIMIT 1) as imagem_principal
        FROM produtos p
        ORDER BY p.id DESC
    ");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $produtos = [];
    $mensagem = "Erro ao carregar a lista de produtos.";
    $tipoMensagem = "erro";
}

adminHeader("Gestão de Produtos");
?>

<div class="admin-page-header">
    <div class="header-actions">
        <a href="novo_produto.php" class="btn btn-primary">+ Novo Produto</a>
    </div>
</div>

<?php if ($mensagem): ?>
    <div class="alert alert-<?= $tipoMensagem ?>">
        <?= htmlspecialchars($mensagem) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($produtos)): ?>
            <p class="empty-state">Nenhum produto cadastrado até ao momento.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Imagem</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th class="actions-col">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $p): ?>
                            <tr>
                                <td class="thumb-col">
                                    <?php if (!empty($p["imagem_principal"])): ?>
                                        <img src="../assets/img/produtos/<?= htmlspecialchars($p["imagem_principal"]) ?>" alt="<?= htmlspecialchars($p["nome"]) ?>" class="table-thumb">
                                    <?php else: ?>
                                        <span class="no-thumb">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($p["nome"]) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($p["categoria"] ?? "Geral") ?></td>
                                <td><?= function_exists("formatarPreco") ? formatarPreco($p["preco"]) : number_format($p["preco"], 2, ",", ".") . " MT" ?></td>
                                <td><?= (int)$p["stock"] ?></td>
                                <td>
                                    <span class="badge badge-<?= ($p["ativo"] ?? 1) ? 'success' : 'danger' ?>">
                                        <?= ($p["ativo"] ?? 1) ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td class="actions-col">
                                    <a href="editar_produto.php?id=<?= $p["id"] ?>" class="btn btn-sm btn-outline">Editar</a>
                                    <form method="POST" class="inline-form" onsubmit="return confirm('Tem certeza que pretende eliminar este produto?');">
                                        <input type="hidden" name="acao" value="eliminar">
                                        <input type="hidden" name="produto_id" value="<?= $p["id"] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php adminFooter(); ?>
