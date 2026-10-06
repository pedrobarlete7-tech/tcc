// NOVO: atualiza o contador do campo Problema enquanto o usuário escreve.
(() => {
  const campo = document.getElementById('problema');
  const contador = document.getElementById('problema-contagem');
  if (!campo || !contador) return;
  const atualizar = () => {
    contador.textContent = campo.value.length.toLocaleString('pt-BR');
  };
  campo.addEventListener('input', atualizar);
  campo.form ? .addEventListener('reset', () => setTimeout(atualizar, 0));
  window.addEventListener('pageshow', atualizar);
  atualizar();
})();