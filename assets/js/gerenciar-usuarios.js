// NOVO: confirma mudanças de perfil ou situação antes de salvar.
(() => {
  const form = document.getElementById('editar-usuario'),
    dialog = document.getElementById('confirmar-usuario');
  if (!form || !dialog) return;
  let confirmado = false;
  form.addEventListener('submit', event => {
    if (confirmado) return;
    const tipo = form.elements.tipo_usuario,
      ativo = form.elements.ativo;
    if (tipo.value === tipo.dataset.original && ativo.value === ativo.dataset.original) return;
    event.preventDefault();
    document.getElementById('confirmar-texto').textContent = `A conta ficará com perfil ${tipo.selectedOptions[0].text} e situação ${ativo.selectedOptions[0].text}. ${ativo.value==='0'?'A pessoa perderá o acesso ao site.':'As permissões serão atualizadas.'}`;
    dialog.showModal();
    document.getElementById('cancelar-salvar').focus();
  });
  document.getElementById('cancelar-salvar').addEventListener('click', () => dialog.close());
  document.getElementById('confirmar-salvar').addEventListener('click', () => {
    confirmado = true;
    dialog.close();
    form.requestSubmit();
  });
})();