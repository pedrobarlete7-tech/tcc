// NOVO: mostra confirmações de sucesso sem interromper a edição automática.
(() => {
  const popup = document.getElementById('popup-sucesso');
  if (!popup) return;
  const text = document.getElementById('popup-sucesso-mensagem');
  let timer;
  const close = () => { clearTimeout(timer); popup.hidden = true; };
  window.siteSucesso = (message) => {
    if (typeof message !== 'string' || !message.trim()) return;
    clearTimeout(timer);
    text.textContent = message;
    popup.hidden = false;
    timer = setTimeout(close, 6000);
  };
  popup.querySelector('button').addEventListener('click', close);
  popup.addEventListener('mouseenter', () => clearTimeout(timer));
  popup.addEventListener('mouseleave', () => { timer = setTimeout(close, 6000); });
  popup.addEventListener('focusin', () => clearTimeout(timer));
  popup.addEventListener('focusout', () => { timer = setTimeout(close, 6000); });
  document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
  if (popup.dataset.popupMessage) {
    window.siteSucesso(popup.dataset.popupMessage);
    document.querySelectorAll('[data-popup-aviso]').forEach(element => element.hidden = true);
  }
})();
w