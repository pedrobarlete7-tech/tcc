// ALTERADO: monta matérias e subdivisões com os dados reais do banco.
(async () => {
    const menuEndpoint = new URL('../../actions/conteudos-menu.php', document.currentScript.src);
    const target = document.querySelector('#navbar-materias');
    if (!target) return;

    function group(title, count) {
        const li = document.createElement('li');
        const details = document.createElement('details');
        details.className = 'subject-group';
        const summary = document.createElement('summary');
        const name = document.createElement('span');
        name.className = 'subject-name';
        name.setAttribute('data-i18n-message', '');
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
        return {
            li,
            details,
            list
        };
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
            note.setAttribute('data-i18n-message', '');
            note.textContent = 'Em breve';
            content.append(note);
        }
        li.append(content);
        return li;
    }

    try {
        const response = await fetch(menuEndpoint, {
            cache: 'no-store'
        });
        if (!response.ok) throw new Error();
        const records = await response.json();
        if (!Array.isArray(records)) throw new Error();
        const materias = new Map();
        records.forEach(row => {
            const id = Number(row.id_materia);
            if (!materias.has(id)) materias.set(id, {
                titulo: row.materia,
                grupos: new Map(),
                avulsos: []
            });
            const materia = materias.get(id);
            if (!Number(row.id_conteudo)) return;
            const item = {
                titulo: row.titulo,
                href: 'conteudo.php?id_conteudo=' + Number(row.id_conteudo)
            };
            if (Number(row.id_subdivisao)) {
                const sub = Number(row.id_subdivisao);
                if (!materia.grupos.has(sub)) materia.grupos.set(sub, {
                    titulo: row.subdivisao,
                    itens: []
                });
                materia.grupos.get(sub).itens.push(item);
            } else materia.avulsos.push(item);
        });
        const fragment = document.createDocumentFragment();
        materias.forEach(m => {
            const bloco = group(m.titulo, m.grupos.size + m.avulsos.length);
            m.grupos.forEach(sub => {
                const g = group(sub.titulo, sub.itens.length);
                g.details.classList.add('topic-group');
                sub.itens.forEach(item => g.list.append(leaf(item)));
                bloco.list.append(g.li);
            });
            m.avulsos.forEach(item => bloco.list.append(leaf(item)));
            if (!bloco.list.children.length) {
                const li = document.createElement('li');
                li.className = 'subjects-message';
                li.textContent = 'Nenhum conteúdo cadastrado.';
                bloco.list.append(li);
            }
            fragment.append(bloco.li);
        });
        target.replaceChildren(fragment);
        if (!materias.size) target.textContent = 'Nenhuma matéria cadastrada.';
    } catch {
        target.textContent = 'Não foi possível carregar os conteúdos agora.';
    }
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