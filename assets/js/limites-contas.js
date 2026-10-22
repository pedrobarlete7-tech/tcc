(() => {
  const encoder = new TextEncoder();
  const bytes = value => encoder.encode(value).length;
  const campos = [...document.querySelectorAll('input[type="email"], input[type="password"]')];
  const proprias = new WeakMap();
  function validar(campo) {
    const anterior = proprias.get(campo);
    if (anterior && campo.validationMessage === anterior) campo.setCustomValidity('');
    proprias.delete(campo);
    const valor = campo.value;
    let mensagem = '';
    if (campo.type === 'email') {
      const email = valor.trim();
      if (bytes(email) > 150 || bytes(email.slice(0, email.lastIndexOf('@'))) > 64 && email.includes('@'))
        mensagem = 'Informe um e-mail válido com até 150 caracteres e até 64 antes do @.';
    } else if (bytes(valor) > 72 || valor.includes('\0')) {
      mensagem = 'A senha ultrapassa o limite permitido ou contém um caractere inválido. Use até 72 bytes; acentos ocupam mais espaço.';
    }
    if (mensagem) {
      const traduzida = window.siteT ? window.siteT(mensagem) : mensagem;
      campo.setCustomValidity(traduzida);
      proprias.set(campo, traduzida);
    }
  }
  campos.forEach(campo => {
    campo.addEventListener('input', () => validar(campo));
    campo.addEventListener('change', () => validar(campo));
    validar(campo);
  });
  window.addEventListener('site:language-changed', () => campos.forEach(validar));
  document.addEventListener('submit', event => {
    campos.filter(campo => campo.form === event.target).forEach(validar);
    if (!event.target.checkValidity()) {
      event.preventDefault();
      event.target.reportValidity();
    }
  }, true);
})();
