// MANTIDO: lista enviada pelo usuário.
(() => {
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
  
// ALTERADO: monta a lista dentro da navbar, em vez do menu lateral.
const target = document.querySelector('#navbar-materias');
if (!target) return;
function group(title, count) {
    const li = document.createElement('li');
    const details = document.createElement('details');
    details.className = 'subject-group';
    const summary = document.createElement('summary');
    const name = document.createElement('span');
    name.className = 'subject-name';
    name.textContent = title;
    const badge = document.createElement('span');
    badge.className = 'subject-count';
    badge.textContent = String(count);
    badge.setAttribute('aria-label', count + ' itens');
    const arrow = document.createElement('span');
    arrow.className = 'subject-chevron';
    arrow.setAttribute('aria-hidden', 'true');
    summary.append(name, badge, arrow);
    const list = document.createElement('ul');
    details.append(summary, list);
    li.append(details);
    return {li, details, list};
}
// NOVO: indica os tópicos sem página, evitando links falsos com #.
function leaf(data) {
    const li = document.createElement('li');
    const href = (data.href || '').trim();
    const valid = href && href !== '#' && !href.startsWith('//') && !/^[a-z][a-z0-9+.-]*:/i.test(href) && !href.includes('\\');
    const content = document.createElement(valid ? 'a' : 'span');
    if (valid) content.href = href;
    else content.className = 'content-pending';
    const title = document.createElement('span');
    title.textContent = data.titulo;
    content.append(title);
    if (!valid) {
        const note = document.createElement('small');
        note.textContent = 'Em breve';
        content.append(note);
    }
    li.append(content);
    return li;
}
const fragment = document.createDocumentFragment();
MATERIAS.forEach(materia => {
    const topics = SUBMENUS.find(s => s.materiaId === materia.id)?.itens ?? [];
    const discipline = group(materia.nome, topics.length);
    topics.forEach(topic => {
        const leaves = DETALHES.find(d => d.materiaId === materia.id && d.assuntoId === topic.id)?.itens ?? [];
        if (!leaves.length) {
            discipline.list.append(leaf(topic));
            return;
        }
        const section = group(topic.titulo, leaves.length);
        section.details.classList.add('topic-group');
        leaves.forEach(item => section.list.append(leaf(item)));
        discipline.list.append(section.li);
    });
    fragment.append(discipline.li);
});
target.replaceChildren(fragment);
// NOVO: abre um grupo por nível; details já oferece suporte ao teclado.
target.querySelectorAll('details').forEach(details => {
    details.addEventListener('toggle', () => {
        if (!details.open) return;
        [...details.parentElement.parentElement.children].forEach(li => {
            const other = li.querySelector(':scope > details');
            if (other && other !== details) other.open = false;
        });
    });
});
// NOVO: Escape fecha primeiro o nível interno do menu.
target.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    const details = event.target.closest('details[open]');
    if (!details || !target.contains(details)) return;
    event.stopPropagation();
    details.open = false;
    details.querySelector('summary').focus();
});
})();
