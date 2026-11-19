<?php

require_once __DIR__ . "/inc.php";

$secao = $_GET["secao"] ?? "vendas";
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    foreach ($_POST as $chave => $valor) {
        if ($chave === "secao") {
            continue;
        }

        if (is_string($valor)) {
            guardarConfiguracao($chave, trim($valor));
        }
    }

    // Se for formulário de switches/checkboxes, desmarca os que não vieram no POST
    if ($secao === "vendas") {
        if (!isset($_POST["vendas_ativas"])) {
            guardarConfiguracao("vendas_ativas", "0");
        }
        if (!isset($_POST["modo_demonstracao"])) {
            guardarConfiguracao("modo_demonstracao", "0");
        }
    } elseif ($secao === "produtos") {
        $chavesProdutos = [
            "mostrar_precos",
            "mostrar_stock",
            "mostrar_categorias",
            "pesquisa_produtos",
            "mostrar_produtos_sem_stock"
        ];
        foreach ($chavesProdutos as $chk) {
            if (!isset($_POST[$chk])) {
                guardarConfiguracao($chk, "0");
            }
        }
    } elseif ($secao === "aparencia") {
        if (!isset($_POST["modo_escuro"])) {
            guardarConfiguracao("modo_escuro", "0");
        }
        if (!isset($_POST["animacoes"])) {
            guardarConfiguracao("animacoes", "0");
        }
    } elseif ($secao === "localizacao") {
        if (!isset($_POST["mostrar_mapa"])) {
            guardarConfiguracao("mostrar_mapa", "0");
        }
    }

    $mensagem = "Definições guardadas com sucesso!";
    $secao = $_POST["secao"] ?? $secao;
}

adminHeader("Definições do Site");

?>

<style>
/* ==========================================================================
   VITABULK - DESIGN SYSTEM DEFINIÇÕES (ESTILO MODERNO E PROFISSIONAL)
   ========================================================================== */

:root {
    --vb-primary: #15803d;
    --vb-primary-hover: #166534;
    --vb-primary-light: #f0fdf4;
    --vb-primary-border: #bbf7d0;
    --vb-text-main: #1f2937;
    --vb-text-muted: #6b7280;
    --vb-bg-card: #ffffff;
    --vb-border: #e5e7eb;
    --vb-border-light: #f3f4f6;
    --vb-radius: 12px;
    --vb-shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
    --vb-shadow-md: 0 4px 12px -1px rgba(0, 0, 0, 0.07);
}

.settings-header-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--vb-border);
}

.settings-header-banner h1 {
    font-size: 24px;
    font-weight: 700;
    color: var(--vb-text-main);
    letter-spacing: -0.02em;
    margin: 0 0 6px 0;
}

.settings-header-banner p {
    font-size: 14px;
    color: var(--vb-text-muted);
    margin: 0;
}

.settings-badge-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.settings-badge-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.settings-hub {
    display: grid;
    grid-template-columns: 290px minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}

/* SIDEBAR DE NAVEGAÇÃO */
.settings-nav-card {
    background: var(--vb-bg-card);
    border: 1px solid var(--vb-border);
    border-radius: var(--vb-radius);
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    box-shadow: var(--vb-shadow-sm);
}

.settings-nav-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    border-radius: 10px;
    color: #4b5563;
    text-decoration: none;
    transition: all 0.15s ease-in-out;
    border: 1px solid transparent;
}

.settings-nav-item:hover {
    background: #f8fafc;
    color: var(--vb-primary);
    border-color: #f1f5f9;
}

.settings-nav-item.active {
    background: var(--vb-primary-light);
    color: var(--vb-primary);
    border-color: var(--vb-primary-border);
    font-weight: 600;
}

.nav-item-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.nav-item-icon {
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #f3f4f6;
    transition: background 0.15s ease;
}

.settings-nav-item.active .nav-item-icon {
    background: #dcfce7;
}

.nav-item-text {
    display: flex;
    flex-direction: column;
}

.nav-item-title {
    font-size: 14px;
    line-height: 1.3;
}

.nav-item-desc {
    font-size: 11px;
    color: var(--vb-text-muted);
    font-weight: 400;
}

.nav-item-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
    background: #f3f4f6;
    color: #6b7280;
    white-space: nowrap;
}

.settings-nav-item.active .nav-item-badge {
    background: #dcfce7;
    color: #166534;
}

/* PAINEL PRINCIPAL DE CONTEÚDO */
.settings-panel {
    background: var(--vb-bg-card);
    border: 1px solid var(--vb-border);
    border-radius: var(--vb-radius);
    padding: 32px;
    box-shadow: var(--vb-shadow-sm);
}

.settings-panel-head {
    padding-bottom: 20px;
    margin-bottom: 28px;
    border-bottom: 1px solid var(--vb-border-light);
}

.settings-panel-head h2 {
    font-size: 20px;
    font-weight: 700;
    color: var(--vb-text-main);
    margin: 0 0 6px 0;
    letter-spacing: -0.01em;
}

.settings-panel-head p {
    font-size: 13.5px;
    color: var(--vb-text-muted);
    margin: 0;
    line-height: 1.5;
}

/* FORMULÁRIO E CAMPOS ALINHADOS */
.form-grid-modern {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px 24px;
}

.field-modern {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-modern.full {
    grid-column: 1 / -1;
}

.field-modern label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.field-modern input[type="text"],
.field-modern input[type="email"],
.field-modern input[type="tel"],
.field-modern input[type="url"],
.field-modern input[type="number"],
.field-modern textarea,
.field-modern select {
    width: 100%;
    box-sizing: border-box;
    height: 44px;
    padding: 10px 14px;
    font-size: 14px;
    color: #111827;
    background: #ffffff;
    border: 1.5px solid #d1d5db;
    border-radius: 9px;
    outline: none;
    transition: all 0.18s ease-in-out;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    font-family: inherit;
}

.field-modern textarea {
    height: auto;
    min-height: 90px;
    padding: 12px 14px;
    resize: vertical;
}

.field-modern input:focus,
.field-modern textarea:focus,
.field-modern select:focus {
    border-color: var(--vb-primary);
    box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.15);
    background-color: #ffffff;
}

.field-modern input::placeholder,
.field-modern textarea::placeholder {
    color: #9ca3af;
}

.field-modern .help-text {
    font-size: 12px;
    color: var(--vb-text-muted);
    margin-top: 2px;
}

/* BOTÕES DE AÇÃO */
.btn-save-settings {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: linear-gradient(180deg, #16a34a 0%, #15803d 100%);
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
    padding: 12px 26px;
    border-radius: 9px;
    border: 1px solid #15803d;
    box-shadow: 0 2px 5px rgba(21, 128, 61, 0.25);
    cursor: pointer;
    transition: all 0.18s ease;
}

.btn-save-settings:hover {
    background: linear-gradient(180deg, #15803d 0%, #166534 100%);
    box-shadow: 0 4px 10px rgba(21, 128, 61, 0.35);
    transform: translateY(-1px);
}

.btn-save-settings:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(21, 128, 61, 0.2);
}

.form-actions-bar {
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid var(--vb-border-light);
    display: flex;
    justify-content: flex-start;
}

/* CARDS DE SWITCH (TOGGLES) */
.switch-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    margin-bottom: 14px;
    background: #fafafa;
    cursor: pointer;
    transition: all 0.18s ease;
}

.switch-card:hover {
    background: #ffffff;
    border-color: #d1d5db;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}

.switch-card-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding-right: 20px;
}

.switch-card-info strong {
    font-size: 14.5px;
    color: #111827;
    font-weight: 600;
}

.switch-card-info small {
    font-size: 12.5px;
    color: var(--vb-text-muted);
    line-height: 1.4;
}

.toggle-switch-input {
    width: 22px;
    height: 22px;
    accent-color: var(--vb-primary);
    cursor: pointer;
    flex-shrink: 0;
}

/* PALETA DE CORES */
.color-picker-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.color-card {
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    padding: 18px;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: all 0.18s ease;
}

.color-card:hover {
    border-color: var(--vb-primary);
    box-shadow: var(--vb-shadow-md);
}

.color-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.color-card-top label {
    font-weight: 700;
    font-size: 13.5px;
    color: #374151;
    margin: 0;
}

.color-input-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}

.color-input-wrap input[type="color"] {
    -webkit-appearance: none;
    border: none;
    width: 52px;
    height: 44px;
    border-radius: 8px;
    cursor: pointer;
    background: none;
    padding: 0;
}

.color-input-wrap input[type="color"]::-webkit-color-swatch-wrapper {
    padding: 0;
}

.color-input-wrap input[type="color"]::-webkit-color-swatch {
    border: 1.5px solid rgba(0,0,0,0.15);
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.color-input-wrap input[type="text"] {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 14px;
    letter-spacing: 0.05em;
    padding: 10px 14px;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    height: 44px;
    width: 100%;
    outline: none;
    box-sizing: border-box;
}

.color-input-wrap input[type="text"]:focus {
    border-color: var(--vb-primary);
    box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.15);
}

.color-desc {
    font-size: 12px;
    color: var(--vb-text-muted);
    line-height: 1.45;
}

.color-preview-banner {
    border-radius: 12px;
    padding: 24px;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 26px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    transition: background 0.25s ease;
}

.alert-message-success {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    background: #ecfdf5;
    border: 1.5px solid #a7f3d0;
    color: #065f46;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 22px;
}

@media (max-width: 900px) {
    .settings-hub {
        grid-template-columns: 1fr;
    }
    .form-grid-modern {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="settings-header-banner">
    <div>
        <h1>Definições da Vitabulk</h1>
        <p>Personalize o catálogo, modo de encomendas, dados de contacto e todas as cores da loja.</p>
    </div>
    <div class="settings-badge-status">
        <span class="settings-badge-dot"></span>
        Loja Online Operacional
    </div>
</div>

<?php if ($mensagem): ?>
    <div class="alert-message-success">
        <span>✓</span>
        <span><?= htmlspecialchars($mensagem) ?></span>
    </div>
<?php endif; ?>

<div class="settings-hub">

    <!-- NAVEGAÇÃO LATERAL -->
    <nav class="settings-nav-card">
        <a href="?secao=vendas" class="settings-nav-item <?= $secao === 'vendas' ? 'active' : '' ?>">
            <div class="nav-item-left">
                <span class="nav-item-icon">🛒</span>
                <div class="nav-item-text">
                    <span class="nav-item-title">Vendas & Catálogo</span>
                    <span class="nav-item-desc">Checkout e compras</span>
                </div>
            </div>
            <span class="nav-item-badge"><?= vendasAtivas() ? "Ativo" : "Demo" ?></span>
        </a>

        <a href="?secao=aparencia" class="settings-nav-item <?= $secao === 'aparencia' ? 'active' : '' ?>">
            <div class="nav-item-left">
                <span class="nav-item-icon">🎨</span>
                <div class="nav-item-text">
                    <span class="nav-item-title">Cores & Aparência</span>
                    <span class="nav-item-desc">Identidade visual</span>
                </div>
            </div>
            <span class="nav-item-badge">Design</span>
        </a>

        <a href="?secao=empresa" class="settings-nav-item <?= $secao === 'empresa' ? 'active' : '' ?>">
            <div class="nav-item-left">
                <span class="nav-item-icon">🏢</span>
                <div class="nav-item-text">
                    <span class="nav-item-title">Dados da Empresa</span>
                    <span class="nav-item-desc">Telefone, email, redes</span>
                </div>
            </div>
            <span class="nav-item-badge">Contactos</span>
        </a>

        <a href="?secao=produtos" class="settings-nav-item <?= $secao === 'produtos' ? 'active' : '' ?>">
            <div class="nav-item-left">
                <span class="nav-item-icon">📦</span>
                <div class="nav-item-text">
                    <span class="nav-item-title">Regras de Produtos</span>
                    <span class="nav-item-desc">Preços, stock e visibilidade</span>
                </div>
            </div>
            <span class="nav-item-badge">Catálogo</span>
        </a>

        <a href="?secao=localizacao" class="settings-nav-item <?= $secao === 'localizacao' ? 'active' : '' ?>">
            <div class="nav-item-left">
                <span class="nav-item-icon">📍</span>
                <div class="nav-item-text">
                    <span class="nav-item-title">Localização & Mapa</span>
                    <span class="nav-item-desc">Google Maps e morada</span>
                </div>
            </div>
            <span class="nav-item-badge">Mapa</span>
        </a>
    </nav>

    <!-- PAINEL CENTRAL DE CONTEÚDO -->
    <div class="settings-panel">

        <!-- ================= SEÇÃO: APARÊNCIA & CORES ================= -->
        <?php if ($secao === "aparencia"): ?>
            <div class="settings-panel-head">
                <h2>Cores e Identidade Visual</h2>
                <p>Personalize todas as cores do site da Vitabulk. As alterações aplicam-se a cabeçalhos, botões, rodapé e textos.</p>
            </div>

            <?php
            $corPrimaria = obterConfiguracao("cor_primaria", "#16823a");
            $corSecundaria = obterConfiguracao("cor_secundaria", "#0d642b");
            $corBotoes = obterConfiguracao("cor_botoes", "#16823a");
            $corFundo = obterConfiguracao("cor_fundo", "#ffffff");
            $corTexto = obterConfiguracao("cor_texto", "#222222");
            ?>

            <!-- BANNER DE PRÉ-VISUALIZAÇÃO DAS CORES -->
            <div class="color-preview-banner" id="colorBanner" style="background: <?= htmlspecialchars($corPrimaria) ?>;">
                <div>
                    <h3 style="font-size:18px;font-weight:800;margin:0 0 4px 0;">Pré-visualização do Tema</h3>
                    <p style="font-size:13px;opacity:0.95;margin:0;">Veja como a cor da marca se destaca com os botões.</p>
                </div>
                <button type="button" style="background:<?= htmlspecialchars($corBotoes) ?>;color:#fff;border:1px solid rgba(255,255,255,0.4);padding:10px 20px;border-radius:8px;font-weight:600;font-size:13px;cursor:pointer;">
                    Exemplo de Botão
                </button>
            </div>

            <form method="post">
                <input type="hidden" name="secao" value="aparencia">

                <div class="color-picker-grid">
                    <!-- COR PRINCIPAL -->
                    <div class="color-card">
                        <div class="color-card-top">
                            <label>Cor Principal (Marca)</label>
                        </div>
                        <div class="color-input-wrap">
                            <input type="color" id="picker_primaria" value="<?= htmlspecialchars($corPrimaria) ?>" onchange="syncHex('picker_primaria', 'hex_primaria'); atualizarBanner();">
                            <input type="text" id="hex_primaria" name="cor_primaria" value="<?= htmlspecialchars($corPrimaria) ?>" maxlength="7" oninput="syncPicker('hex_primaria', 'picker_primaria'); atualizarBanner();">
                        </div>
                        <div class="color-desc">Usada no logotipo, barra superior, destaques e títulos principais.</div>
                    </div>

                    <!-- COR SECUNDÁRIA / HOVER -->
                    <div class="color-card">
                        <div class="color-card-top">
                            <label>Cor Secundária / Hover</label>
                        </div>
                        <div class="color-input-wrap">
                            <input type="color" id="picker_secundaria" value="<?= htmlspecialchars($corSecundaria) ?>" onchange="syncHex('picker_secundaria', 'hex_secundaria');">
                            <input type="text" id="hex_secundaria" name="cor_secundaria" value="<?= htmlspecialchars($corSecundaria) ?>" maxlength="7" oninput="syncPicker('hex_secundaria', 'picker_secundaria');">
                        </div>
                        <div class="color-desc">Usada quando o cliente passa o rato sobre botões, links e rodapé.</div>
                    </div>

                    <!-- COR DOS BOTÕES -->
                    <div class="color-card">
                        <div class="color-card-top">
                            <label>Cor dos Botões</label>
                        </div>
                        <div class="color-input-wrap">
                            <input type="color" id="picker_botoes" value="<?= htmlspecialchars($corBotoes) ?>" onchange="syncHex('picker_botoes', 'hex_botoes');">
                            <input type="text" id="hex_botoes" name="cor_botoes" value="<?= htmlspecialchars($corBotoes) ?>" maxlength="7" oninput="syncPicker('hex_botoes', 'picker_botoes');">
                        </div>
                        <div class="color-desc">Cor de preenchimento dos botões de adicionar ao carrinho e checkout.</div>
                    </div>

                    <!-- COR DE FUNDO DA LOJA -->
                    <div class="color-card">
                        <div class="color-card-top">
                            <label>Cor de Fundo da Loja</label>
                        </div>
                        <div class="color-input-wrap">
                            <input type="color" id="picker_fundo" value="<?= htmlspecialchars($corFundo) ?>" onchange="syncHex('picker_fundo', 'hex_fundo');">
                            <input type="text" id="hex_fundo" name="cor_fundo" value="<?= htmlspecialchars($corFundo) ?>" maxlength="7" oninput="syncPicker('hex_fundo', 'picker_fundo');">
                        </div>
                        <div class="color-desc">Fundo do site público (padrão: branco #FFFFFF ou cinza suave).</div>
                    </div>

                    <!-- COR DO TEXTO -->
                    <div class="color-card">
                        <div class="color-card-top">
                            <label>Cor do Texto Geral</label>
                        </div>
                        <div class="color-input-wrap">
                            <input type="color" id="picker_texto" value="<?= htmlspecialchars($corTexto) ?>" onchange="syncHex('picker_texto', 'hex_texto');">
                            <input type="text" id="hex_texto" name="cor_texto" value="<?= htmlspecialchars($corTexto) ?>" maxlength="7" oninput="syncPicker('hex_texto', 'picker_texto');">
                        </div>
                        <div class="color-desc">Cor dos parágrafos, fichas de produto e descrições nutricionais.</div>
                    </div>
                </div>

                <div class="settings-panel-head" style="margin-top:20px;">
                    <h2>Opções Visuais Adicionais</h2>
                </div>

                <label class="switch-card">
                    <div class="switch-card-info">
                        <strong>Modo Escuro Global (Dark Mode)</strong>
                        <small>Aplica uma paleta de tons escuros sofisticada em toda a loja pública.</small>
                    </div>
                    <input type="checkbox" class="toggle-switch-input" name="modo_escuro" value="1" <?= configuracaoAtiva("modo_escuro", false) ? "checked" : "" ?>>
                </label>

                <label class="switch-card">
                    <div class="switch-card-info">
                        <strong>Animações e Transições Suaves</strong>
                        <small>Efeitos de transição fluida ao navegar entre produtos e abrir menus.</small>
                    </div>
                    <input type="checkbox" class="toggle-switch-input" name="animacoes" value="1" <?= configuracaoAtiva("animacoes", true) ? "checked" : "" ?>>
                </label>

                <div class="form-actions-bar">
                    <button type="submit" class="btn-save-settings">
                        <span>💾</span>
                        <span>Guardar Todas as Cores</span>
                    </button>
                </div>
            </form>

            <script>
            function syncHex(pickerId, hexId) {
                document.getElementById(hexId).value = document.getElementById(pickerId).value.toUpperCase();
            }
            function syncPicker(hexId, pickerId) {
                let val = document.getElementById(hexId).value;
                if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                    document.getElementById(pickerId).value = val;
                }
            }
            function atualizarBanner() {
                let cor = document.getElementById('picker_primaria').value;
                document.getElementById('colorBanner').style.backgroundColor = cor;
            }
            </script>
        <?php endif; ?>

        <!-- ================= SEÇÃO: VENDAS ================= -->
        <?php if ($secao === "vendas"): ?>
            <div class="settings-panel-head">
                <h2>Modo de Vendas e Operação</h2>
                <p>Configure se a loja permite encomendas diretas no site ou opera como catálogo digital.</p>
            </div>

            <form method="post">
                <input type="hidden" name="secao" value="vendas">

                <label class="switch-card">
                    <div class="switch-card-info">
                        <strong>Ativar Carrinho & Vendas Online</strong>
                        <small>Permite que os clientes adicionem produtos ao cesto e enviem pedidos para a sua equipa.</small>
                    </div>
                    <input type="checkbox" class="toggle-switch-input" name="vendas_ativas" value="1" <?= configuracaoAtiva("vendas_ativas") ? "checked" : "" ?>>
                </label>

                <label class="switch-card">
                    <div class="switch-card-info">
                        <strong>Modo Catálogo Digital / Demonstração</strong>
                        <small>Os visitantes visualizam os produtos, informações e fotos sem botão de compra imediata.</small>
                    </div>
                    <input type="checkbox" class="toggle-switch-input" name="modo_demonstracao" value="1" <?= configuracaoAtiva("modo_demonstracao", true) ? "checked" : "" ?>>
                </label>

                <div class="form-actions-bar">
                    <button type="submit" class="btn-save-settings">
                        <span>💾</span>
                        <span>Guardar Modo de Vendas</span>
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <!-- ================= SEÇÃO: EMPRESA ================= -->
        <?php if ($secao === "empresa"): ?>
            <div class="settings-panel-head">
                <h2>Dados e Contactos da Empresa</h2>
                <p>Estes dados serão apresentados no rodapé, página de contactos e recibos dos clientes.</p>
            </div>

            <form method="post">
                <input type="hidden" name="secao" value="empresa">

                <div class="form-grid-modern">
                    <div class="field-modern">
                        <label for="inp_nome_empresa">Nome da Empresa</label>
                        <input id="inp_nome_empresa" type="text" name="nome_empresa" value="<?= htmlspecialchars(obterConfiguracao("nome_empresa", "Vitabulk Alimentos, Lda")) ?>" required>
                    </div>

                    <div class="field-modern">
                        <label for="inp_email">Email de Apoio ao Cliente</label>
                        <input id="inp_email" type="email" name="email" value="<?= htmlspecialchars(obterConfiguracao("email", "")) ?>" placeholder="suporte@vitabulkalimentos.co.mz">
                    </div>

                    <div class="field-modern">
                        <label for="inp_telefone">Telefone Principal</label>
                        <input id="inp_telefone" type="tel" name="telefone" value="<?= htmlspecialchars(obterConfiguracao("telefone", "")) ?>" placeholder="+258 84 000 0000">
                    </div>

                    <div class="field-modern">
                        <label for="inp_whatsapp">WhatsApp para Encomendas</label>
                        <input id="inp_whatsapp" type="tel" name="whatsapp" value="<?= htmlspecialchars(obterConfiguracao("whatsapp", "")) ?>" placeholder="258840000000">
                    </div>

                    <div class="field-modern">
                        <label for="inp_instagram">Instagram Oficial (Link)</label>
                        <input id="inp_instagram" type="url" name="instagram" value="<?= htmlspecialchars(obterConfiguracao("instagram", "")) ?>" placeholder="https://instagram.com/vitabulk">
                    </div>

                    <div class="field-modern">
                        <label for="inp_facebook">Facebook Oficial (Link)</label>
                        <input id="inp_facebook" type="url" name="facebook" value="<?= htmlspecialchars(obterConfiguracao("facebook", "")) ?>" placeholder="https://facebook.com/vitabulk">
                    </div>

                    <div class="field-modern full">
                        <label for="inp_endereco">Endereço Físico</label>
                        <input id="inp_endereco" type="text" name="endereco" value="<?= htmlspecialchars(obterConfiguracao("endereco", "")) ?>" placeholder="Ex: Av. 24 de Julho, Maputo, Moçambique">
                    </div>

                    <div class="field-modern full">
                        <label for="inp_horario">Horário de Funcionamento</label>
                        <input id="inp_horario" type="text" name="horario" value="<?= htmlspecialchars(obterConfiguracao("horario", "Segunda a Sábado: 08:00 - 18:00")) ?>" placeholder="Segunda a Sábado: 08:00 - 18:00">
                    </div>
                </div>

                <div class="form-actions-bar">
                    <button type="submit" class="btn-save-settings">
                        <span>💾</span>
                        <span>Guardar Dados da Empresa</span>
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <!-- ================= SEÇÃO: PRODUTOS ================= -->
        <?php if ($secao === "produtos"): ?>
            <div class="settings-panel-head">
                <h2>Regras do Catálogo e Produtos</h2>
                <p>Personalize as informações mostradas nos cartões e listagens de produtos para os utilizadores.</p>
            </div>

            <form method="post">
                <input type="hidden" name="secao" value="produtos">

                <?php
                $opcoes = [
                    "mostrar_precos"             => ["Mostrar Preços (MT)", "Exibe o valor monetário em Meticais nos produtos para os clientes."],
                    "mostrar_stock"              => ["Mostrar Quantidade em Stock", "Informa o cliente se o produto tem stock abundante ou reduzido."],
                    "mostrar_categorias"         => ["Menu de Categorias", "Exibe a barra de filtros por categoria para rápida navegação."],
                    "pesquisa_produtos"          => ["Barra de Pesquisa Ativa", "Permite aos clientes pesquisar produtos por nome, código ou ingrediente."],
                    "mostrar_produtos_sem_stock" => ["Manter Produtos Esgotados Visíveis", "Exibe produtos com stock a zero assinalados com o selo 'Esgotado'."]
                ];

                foreach ($opcoes as $chave => $detalhes): ?>
                    <label class="switch-card">
                        <div class="switch-card-info">
                            <strong><?= $detalhes[0] ?></strong>
                            <small><?= $detalhes[1] ?></small>
                        </div>
                        <input type="checkbox" class="toggle-switch-input" name="<?= $chave ?>" value="1" <?= configuracaoAtiva($chave, true) ? "checked" : "" ?>>
                    </label>
                <?php endforeach; ?>

                <div class="form-actions-bar">
                    <button type="submit" class="btn-save-settings">
                        <span>💾</span>
                        <span>Guardar Regras de Produtos</span>
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <!-- ================= SEÇÃO: LOCALIZAÇÃO ================= -->
        <?php if ($secao === "localizacao"): ?>
            <div class="settings-panel-head">
                <h2>Localização & Mapa Interativo</h2>
                <p>Configure a apresentação do mapa Google Maps na página da loja e no rodapé.</p>
            </div>

            <form method="post">
                <input type="hidden" name="secao" value="localizacao">

                <label class="switch-card">
                    <div class="switch-card-info">
                        <strong>Exibir Mapa Google Maps na Loja</strong>
                        <small>Apresenta o mapa interativo na secção de contactos e localização da Vitabulk.</small>
                    </div>
                    <input type="checkbox" class="toggle-switch-input" name="mostrar_mapa" value="1" <?= configuracaoAtiva("mostrar_mapa", true) ? "checked" : "" ?>>
                </label>

                <div class="form-grid-modern" style="margin-top:16px;">
                    <div class="field-modern full">
                        <label for="inp_url_mapa">Link de Embed do Google Maps</label>
                        <textarea id="inp_url_mapa" name="url_mapa" rows="4" placeholder="https://www.google.com/maps/embed?... ou código de incorporação"><?= htmlspecialchars(obterConfiguracao("url_mapa", "")) ?></textarea>
                        <span class="help-text">Cole o link do Google Maps Embed ou o endereço da localização.</span>
                    </div>
                </div>

                <div class="form-actions-bar">
                    <button type="submit" class="btn-save-settings">
                        <span>💾</span>
                        <span>Guardar Localização</span>
                    </button>
                </div>
            </form>
        <?php endif; ?>

    </div>

</div>

<?php adminFooter(); ?>