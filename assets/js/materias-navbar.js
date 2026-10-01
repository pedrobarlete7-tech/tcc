(async () => {
const menuEndpoint = new URL('../../actions/conteudos-menu.php', document.currentScript.src);
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
        { id: 'probabilidade', titulo: 'Probabilidade', href: '#' },
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
    { materiaId: 'matematica', assuntoId: 'probabilidade', itens: [{ titulo: 'Probabilidade', href: '#' }] },
    {
      materiaId: 'matematica',
      assuntoId: 'numeros',
      itens: [
        { titulo: 'Números naturais', href: '#' },
        { titulo: 'Frações', href: '#' },
        { titulo: 'Decimais', href: '#' },
        { titulo: 'Porcentagem', href: '#' },
        { titulo: 'Razão e Proporção', href: '#' },
      ],
    },
    {
      materiaId: 'matematica',
      assuntoId: 'geometria',
      itens: [
        { titulo: 'Triângulos', href: '#' },
        { titulo: 'Quadriláteros', href: '#' },
        { titulo: 'Círculos', href: '#' },
        { titulo: 'Área e Perímetro', href: '#' },
        { titulo: 'Teorema de Pitágoras', href: '#' },
      ],
    },
    {
      materiaId: 'matematica',
      assuntoId: 'algebra',
      itens: [
        { titulo: 'Equações do 1º grau', href: '#' },
        { titulo: 'Expressões algébricas', href: '#' },
        { titulo: 'Equações do 2º Grau', href: '#' },
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
const normalize = value => String(value).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
const aliases = { 'decimais': 'numeros decimais' };
let records = [];
let failed = false;
try {
    const response = await fetch(menuEndpoint, {cache: 'no-store'});
    if (!response.ok) throw new Error('Menu indisponível');
    records = await response.json();
    if (!Array.isArray(records)) throw new Error('Resposta inválida');
} catch {
    failed = true;
}
// ALTERADO: as disciplinas e seus IDs vêm do banco, inclusive após edição ou exclusão.
const disciplines = [];
records.forEach(record => {
    if (!disciplines.some(item => item.bancoId === Number(record.id_materia))) {
        const modelo = MATERIAS.find(item => normalize(item.nome) === normalize(record.materia));
        disciplines.push({id: modelo?.id ?? 'banco-' + record.id_materia, bancoId: Number(record.id_materia), nome: record.materia});
    }
});
const fragment = document.createDocumentFragment();
if (failed) {
    const warning = document.createElement('li');
    warning.className = 'subjects-message';
    warning.textContent = 'Não foi possível carregar os conteúdos do banco. Confira o arquivo actions/conteudos-menu.php e a conexão, depois atualize a página.';
    fragment.append(warning);
    target.replaceChildren(fragment);
    return;
}
disciplines.forEach(materia => {
    const topics = SUBMENUS.find(s => s.materiaId === materia.id)?.itens ?? [];
    const registered = records.filter(item => Number(item.id_materia) === materia.bancoId && Number(item.id_conteudo) > 0);
    const used = new Set();
    const resolve = item => {
        const name = normalize(item.titulo);
        const matches = registered.filter(row => normalize(row.titulo) === (aliases[name] || name));
        if (matches.length !== 1) return {...item, href: '#'};
        const row = matches[0];
        used.add(Number(row.id_conteudo));
        return {...item, href: 'conteudo.php?id_conteudo=' + Number(row.id_conteudo)};
    };
    const discipline = group(materia.nome, topics.length);
    topics.forEach(topic => {
        const leaves = DETALHES.find(d => d.materiaId === materia.id && d.assuntoId === topic.id)?.itens ?? [];
        if (!leaves.length) {
            discipline.list.append(leaf(resolve(topic)));
            return;
        }
        const section = group(topic.titulo, leaves.length);
        section.details.classList.add('topic-group');
        const topicPage = resolve(topic);
        if (topicPage.href !== '#' && !leaves.some(item => normalize(item.titulo) === normalize(topic.titulo))) section.list.append(leaf({...topicPage, titulo: 'Ver conteúdo: ' + topic.titulo}));
        leaves.forEach(item => section.list.append(leaf(resolve(item))));
        discipline.list.append(section.li);
    });
    const remaining = registered.filter(row => !used.has(Number(row.id_conteudo)));
    remaining.forEach(row => discipline.list.append(leaf({
        titulo: row.titulo,
        href: 'conteudo.php?id_conteudo=' + Number(row.id_conteudo),
    })));
    if (!topics.length && !registered.length) {
        const empty = document.createElement('li');
        empty.className = 'subjects-message';
        empty.textContent = 'Conteúdos em breve.';
        discipline.list.append(empty);
    }
    if (materia.id === 'matematica') {
        discipline.list.querySelectorAll('.content-pending').forEach(item => item.closest('li').remove());
        discipline.list.querySelectorAll('.topic-group').forEach(section => {
            const list = section.querySelector(':scope > ul');
            if (!list.children.length) {
                section.parentElement.remove();
                return;
            }
            const badge = section.querySelector('.subject-count');
            badge.textContent = String(list.children.length);
            badge.setAttribute('aria-label', list.children.length + ' conteúdos');
        });
    }
    const count = discipline.details.querySelector('.subject-count');
    count.textContent = String(discipline.list.children.length);
    count.setAttribute('aria-label', discipline.list.children.length + ' grupos ou assuntos');
    fragment.append(discipline.li);
});
if (!disciplines.length) {
    const empty = document.createElement('li');
    empty.className = 'subjects-message';
    empty.textContent = 'Nenhuma disciplina cadastrada.';
    fragment.append(empty);
}
target.replaceChildren(fragment);
target.querySelectorAll('details').forEach(details => {
    details.addEventListener('toggle', () => {
        if (!details.open) return;
        [...details.parentElement.parentElement.children].forEach(li => {
            const other = li.querySelector(':scope > details');
            if (other && other !== details) other.open = false;
        });
    });
});
target.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    const details = event.target.closest('details[open]');
    if (!details || !target.contains(details)) return;
    event.stopPropagation();
    details.open = false;
    details.querySelector('summary').focus();
});
})();
