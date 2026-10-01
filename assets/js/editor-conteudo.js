// NOVO: adiciona campos no mesmo formulário, sem abrir outras páginas.
(() => {
  let next = Date.now();
  document.querySelector('#editor-form')?.addEventListener('click', event => {
    const button = event.target.closest('[data-add]');
    if (!button) return;
    const collection = button.closest('[data-collection]');
    const template = document.getElementById('modelo-' + button.dataset.add);
    const prefix = collection.dataset.name + '[' + (++next) + ']';
    const fragment = template.content.cloneNode(true);
    fragment.querySelectorAll('*').forEach(element => {
      for (const attr of [...element.attributes]) {
        if (attr.value.includes('__PREFIX__')) element.setAttribute(attr.name, attr.value.replaceAll('__PREFIX__', prefix));
      }
    });
    const first = fragment.querySelector('textarea, input:not([type="hidden"])');
    collection.querySelector(':scope > [data-items]').append(fragment);
    first?.focus();
  });
})();
