// NOVO: abre a confirmação de desfazer como pop-up acessível.
(() => {
  const dialog = document.getElementById('confirmar-desfazer');
  if (!dialog || typeof dialog.showModal !== 'function') return;
  dialog.close();
  dialog.showModal();
  const cancel = dialog.querySelector('[data-cancelar-desfazer]');
  cancel.focus();
  dialog.addEventListener('cancel', event => {
    event.preventDefault();
    location.assign(cancel.href);
  });
  const form = dialog.querySelector('form');
  let submitting = false;
  form.addEventListener('submit', event => {
    if (submitting) {
      event.preventDefault();
      return;
    }
    submitting = true;
    const button = form.querySelector('button[type="submit"]');
    button.textContent = 'Desfazendo…';
    button.setAttribute('aria-disabled', 'true');
  });
})();