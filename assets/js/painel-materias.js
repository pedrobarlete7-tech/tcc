// ALTERADO: filtra as disciplinas pelo nome, sem alterar os dados do banco.
(() => {
  const campo = document.getElementById('buscar-materia');
  const lista = document.getElementById('lista-disciplinas');
  const vazio = document.getElementById('busca-vazia');
  if (!campo || !lista || !vazio) return;
  const itens = [...lista.querySelectorAll('[data-materia]')];
  const normalizar = (texto) => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR').trim();
  campo.closest('.painel-busca').hidden = false;
  campo.addEventListener('input', () => {
    const busca = normalizar(campo.value);
    let encontrados = 0;
    itens.forEach((item) => {
      item.hidden = !normalizar(item.dataset.materia).includes(busca);
      if (!item.hidden) encontrados++;
    });
    vazio.hidden = encontrados !== 0 || itens.length === 0;
  });
})();
