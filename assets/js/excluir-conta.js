// NOVO: pede confirmação antes de enviar a exclusão da conta.
(() => {
    const abrir = document.getElementById('abrir-exclusao');
    const dialog = document.getElementById('confirmar-exclusao');
    const cancelar = document.getElementById('cancelar-exclusao');
    const form = document.getElementById('form-exclusao');
    if (!abrir || !dialog || !cancelar || !form) return;
    let enviando = false;
    let overflowAnterior = '';
    abrir.hidden = false;
    abrir.addEventListener('click', () => {
        overflowAnterior = document.body.style.overflow;
        dialog.showModal();
        document.body.style.overflow = 'hidden';
        cancelar.focus();
    });
    cancelar.addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => {
        document.body.style.overflow = overflowAnterior;
        abrir.focus();
    });
    form.addEventListener('submit', event => {
        if (enviando) {
            event.preventDefault();
            return;
        }
        enviando = true;
        form.setAttribute('aria-busy', 'true');
    });
    window.addEventListener('pageshow', () => {
        enviando = false;
        form.removeAttribute('aria-busy');
    });
})();