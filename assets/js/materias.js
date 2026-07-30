const MATERIAS = [
    { id: 'matematica', nome: 'Matemática' },
    { id: 'portugues', nome: 'Português' },
    { id: 'historia', nome: 'História' },
    { id: 'geografia', nome: 'Geografia' },
    { id: 'ingles', nome: 'Inglês' },
  ];
  
  const SUBMENUS = [
    {
      materiaId: 'matematica',
      itens: [
        { id: 'numeros', titulo: 'Números e operações', href: '#' },
        { id: 'geometria', titulo: 'Geometria', href: '#' },
        { id: 'algebra', titulo: 'Álgebra', href: '#' },
      ],
    },
    {
      materiaId: 'portugues',
      itens: [
        { id: 'gramatica', titulo: 'Gramática', href: '#' },
        { id: 'interpretacao', titulo: 'Interpretação de texto', href: '#' },
        { id: 'redacao', titulo: 'Redação', href: '#' },
      ],
    },
    {
      materiaId: 'historia',
      itens: [
        { id: 'colonia', titulo: 'Brasil Colônia', href: '#' },
        { id: 'imperio', titulo: 'Brasil Império', href: '#' },
        { id: 'republica', titulo: 'Brasil República', href: '#' },
      ],
    },
    {
      materiaId: 'geografia',
      itens: [
        { id: 'fisica', titulo: 'Geografia física', href: '#' },
        { id: 'humana', titulo: 'Geografia humana', href: '#' },
        { id: 'cartografia', titulo: 'Cartografia', href: '#' },
      ],
    },
    {
      materiaId: 'ingles',
      itens: [
        { id: 'vocabulario', titulo: 'Vocabulário', href: '#' },
        { id: 'gramatica', titulo: 'Gramática', href: '#' },
        { id: 'conversacao', titulo: 'Conversação', href: '#' },
      ],
    },
  ];
  
  const DETALHES = [
    {
      materiaId: 'matematica',
      assuntoId: 'numeros',
      itens: [
        { titulo: 'Números naturais', href: '#' },
        { titulo: 'Frações', href: '#' },
        { titulo: 'Decimais', href: '#' },
      ],
    },
    {
      materiaId: 'matematica',
      assuntoId: 'geometria',
      itens: [
        { titulo: 'Triângulos', href: '#' },
        { titulo: 'Quadriláteros', href: '#' },
        { titulo: 'Círculos', href: '#' },
      ],
    },
    {
      materiaId: 'matematica',
      assuntoId: 'algebra',
      itens: [
        { titulo: 'Equações do 1º grau', href: '#' },
        { titulo: 'Expressões algébricas', href: '#' },
      ],
    },
    {
      materiaId: 'portugues',
      assuntoId: 'gramatica',
      itens: [
        { titulo: 'Classes de palavras', href: '#' },
        { titulo: 'Sintaxe', href: '#' },
      ],
    },
    {
      materiaId: 'portugues',
      assuntoId: 'interpretacao',
      itens: [
        { titulo: 'Texto narrativo', href: '#' },
        { titulo: 'Texto dissertativo', href: '#' },
      ],
    },
    {
      materiaId: 'historia',
      assuntoId: 'colonia',
      itens: [
        { titulo: 'Descobrimento', href: '#' },
        { titulo: 'Escravidão', href: '#' },
      ],
    },
    {
      materiaId: 'historia',
      assuntoId: 'imperio',
      itens: [
        { titulo: 'Independência', href: '#' },
        { titulo: 'Segundo Reinado', href: '#' },
      ],
    },
    {
      materiaId: 'geografia',
      assuntoId: 'fisica',
      itens: [
        { titulo: 'Relevo', href: '#' },
        { titulo: 'Clima', href: '#' },
      ],
    },
    {
      materiaId: 'geografia',
      assuntoId: 'humana',
      itens: [
        { titulo: 'População', href: '#' },
        { titulo: 'Urbanização', href: '#' },
      ],
    },
    {
      materiaId: 'ingles',
      assuntoId: 'vocabulario',
      itens: [
        { titulo: 'Cumprimentos', href: '#' },
        { titulo: 'Família', href: '#' },
      ],
    },
    {
      materiaId: 'ingles',
      assuntoId: 'gramatica',
      itens: [
        { titulo: 'Present Simple', href: '#' },
        { titulo: 'Past Simple', href: '#' },
      ],
    },
  ];
  
  function getSubmenuPorMateria(materiaId) {
    return SUBMENUS.find((s) => s.materiaId === materiaId)?.itens ?? [];
  }
  
  function getDetalhesPorAssunto(materiaId, assuntoId) {
    return (
      DETALHES.find(
        (d) => d.materiaId === materiaId && d.assuntoId === assuntoId
      )?.itens ?? []
    );
  }
  
  function criarLinkDetalhe(detalhe) {
    const link = document.createElement('a');
    link.className = 'menu-detalhe-item';
    link.href = detalhe.href;
    link.textContent = detalhe.titulo;
    link.addEventListener('click', (e) => e.stopPropagation());
    return link;
  }
  
  function criarItemAssunto(materiaId, item) {
    const detalhes = getDetalhesPorAssunto(materiaId, item.id);
    const submenuItem = document.createElement('li');
  
    if (detalhes.length > 0) {
      submenuItem.className = 'menu-assunto-item';
  
      const tituloAssunto = document.createElement('div');
      tituloAssunto.className = 'menu-assunto';
      tituloAssunto.textContent = item.titulo;
      tituloAssunto.setAttribute('data-materia-id', materiaId);
      tituloAssunto.setAttribute('data-assunto-id', item.id);
      tituloAssunto.setAttribute('role', 'button');
      tituloAssunto.setAttribute('tabindex', '0');
      tituloAssunto.setAttribute('aria-expanded', 'false');
  
      const listaDetalhes = document.createElement('ul');
      listaDetalhes.className = 'menu-detalhes';
  
      detalhes.forEach((detalhe) => {
        const detalheItem = document.createElement('li');
        detalheItem.appendChild(criarLinkDetalhe(detalhe));
        listaDetalhes.appendChild(detalheItem);
      });
  
      submenuItem.appendChild(tituloAssunto);
      submenuItem.appendChild(listaDetalhes);
    } else {
      const link = document.createElement('a');
      link.className = 'menu-submenu-item';
      link.href = item.href;
      link.textContent = item.titulo;
      link.addEventListener('click', (e) => e.stopPropagation());
      submenuItem.appendChild(link);
    }
  
    return submenuItem;
  }
  
  function renderMenuLateral() {
    const menuLateral = document.getElementById('menu-lateral');
    if (!menuLateral) return;
  
    let lista = menuLateral.querySelector('ul');
    if (!lista) {
      lista = document.createElement('ul');
      menuLateral.appendChild(lista);
    }
  
    lista.innerHTML = '';
  
    MATERIAS.forEach((materia) => {
      const itens = getSubmenuPorMateria(materia.id);
  
      const itemWrapper = document.createElement('li');
      itemWrapper.className = 'menu-item';
  
      const tituloMateria = document.createElement('div');
      tituloMateria.className = 'menu-materia';
      tituloMateria.textContent = materia.nome;
      tituloMateria.setAttribute('data-materia-id', materia.id);
      tituloMateria.setAttribute('role', 'button');
      tituloMateria.setAttribute('tabindex', '0');
      tituloMateria.setAttribute('aria-expanded', 'false');
  
      const submenu = document.createElement('ul');
      submenu.className = 'menu-submenu';
  
      itens.forEach((item) => {
        submenu.appendChild(criarItemAssunto(materia.id, item));
      });
  
      itemWrapper.appendChild(tituloMateria);
      itemWrapper.appendChild(submenu);
      lista.appendChild(itemWrapper);
    });
  }
  
  function fecharAssuntosNoMenu(menuItem) {
    menuItem.querySelectorAll('.menu-assunto-item.is-open').forEach((item) => {
      item.classList.remove('is-open');
      const titulo = item.querySelector('.menu-assunto');
      if (titulo) titulo.setAttribute('aria-expanded', 'false');
    });
  }
  
  function initAccordionMenu() {
    const menuLateral = document.getElementById('menu-lateral');
    if (!menuLateral) return;
  
    menuLateral.addEventListener('click', (e) => {
      const assuntoEl = e.target.closest('.menu-assunto');
      if (assuntoEl) {
        e.stopPropagation();
  
        const assuntoWrapper = assuntoEl.closest('.menu-assunto-item');
        if (!assuntoWrapper) return;
  
        const menuItem = assuntoWrapper.closest('.menu-item');
        const estavaAberto = assuntoWrapper.classList.contains('is-open');
  
        if (menuItem) {
          menuItem.querySelectorAll('.menu-assunto-item.is-open').forEach((item) => {
            if (item !== assuntoWrapper) {
              item.classList.remove('is-open');
              const titulo = item.querySelector('.menu-assunto');
              if (titulo) titulo.setAttribute('aria-expanded', 'false');
            }
          });
        }
  
        if (estavaAberto) {
          assuntoWrapper.classList.remove('is-open');
          assuntoEl.setAttribute('aria-expanded', 'false');
        } else {
          assuntoWrapper.classList.add('is-open');
          assuntoEl.setAttribute('aria-expanded', 'true');
        }
        return;
      }
  
      const materiaEl = e.target.closest('.menu-materia');
      if (!materiaEl) return;
  
      const itemWrapper = materiaEl.closest('.menu-item');
      if (!itemWrapper) return;
  
      const estavaAberto = itemWrapper.classList.contains('is-open');
  
      menuLateral.querySelectorAll('.menu-item.is-open').forEach((item) => {
        item.classList.remove('is-open');
        fecharAssuntosNoMenu(item);
        const titulo = item.querySelector('.menu-materia');
        if (titulo) titulo.setAttribute('aria-expanded', 'false');
      });
  
      if (!estavaAberto) {
        itemWrapper.classList.add('is-open');
        materiaEl.setAttribute('aria-expanded', 'true');
      }
    });
  
    menuLateral.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter' && e.key !== ' ') return;
  
      const assuntoEl = e.target.closest('.menu-assunto');
      if (assuntoEl) {
        e.preventDefault();
        assuntoEl.click();
        return;
      }
  
      const materiaEl = e.target.closest('.menu-materia');
      if (!materiaEl) return;
      e.preventDefault();
      materiaEl.click();
    });
  }
  
  document.addEventListener('DOMContentLoaded', () => {
    renderMenuLateral();
    initAccordionMenu();
  });
  