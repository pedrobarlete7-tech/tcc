<?php
// NOVO: compartilha o pop-up de confirmação entre as páginas de gestão.
?>
<link rel="stylesheet" href="assets/css/popup-sucesso.css">
<div class="popup-sucesso" id="popup-sucesso" data-popup-message="<?= site_escape((string)($popupSucesso ?? '')) ?>" hidden>
    <span class="popup-sucesso-icone" aria-hidden="true">✓</span>
    <div><span class="popup-sucesso-titulo">EnsinoTec</span><p id="popup-sucesso-mensagem" role="status" aria-live="polite" aria-atomic="true"></p></div>
    <button type="button" aria-label="Fechar confirmação">×</button>
</div>
<script src="assets/js/popup-sucesso.js" defer></script>
