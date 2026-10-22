(() => {
    'use strict';
    const assetBase = new URL('../locales/', document.currentScript.src);
    const storageKey = 'ensinotec.idioma';
    const languages = ['pt-BR', 'en', 'es'];
    const normalize = value => value.replace(/\s+/g, ' ').trim();
    let saved = 'pt-BR';
    try {
        const value = localStorage.getItem(storageKey);
        if (languages.includes(value)) saved = value;
    } catch {  }
    let draft = saved;
    let ready = false;
    // ALTERADO: traduz textos cadastrados em todas as páginas, inclusive avisos dinâmicos, sem recriar campos ou botões.
    const sources = new WeakMap();
    const attributes = new WeakMap();
    const ignored = 'script,style,textarea,code,pre,[contenteditable="true"],[translate="no"],[data-i18n-ignore]';
    let observer;
    const translate = source => {
        if (!ready) return source;
        const key = normalize(source);
        if (i18next.exists(key)) return i18next.t(key, {defaultValue: source});
        if (key.endsWith(' — EnsinoTec')) return translate(key.slice(0, -12)) + ' — EnsinoTec';
        const patterns = [
            [/^Olá, (.+)!$/, ['Hello, $1!', 'Hola, $1!']],
            [/^Enviado em (.+)$/, ['Sent on $1', 'Enviado el $1']],
            [/^Respondido por (.+) em (.+)\.$/, ['Answered by $1 on $2.', 'Respondido por $1 el $2.']],
            [/^Resultados para (.+)$/, ['Results for $1', 'Resultados para $1']],
            [/^(\d+) matéria\(s\) e (\d+) conteúdo\(s\) encontrado\(s\)\.$/, ['$1 subject(s) and $2 topic(s) found.', '$1 materia(s) y $2 contenido(s) encontrado(s).']],
            [/^(\d+) questão\(ões\) preenchida\(s\)\. Faltam (\d+) para salvar\.$/, ['$1 questions completed. $2 more required to save.', '$1 preguntas completas. Faltan $2 para guardar.']],
            [/^(\d+) questão\(ões\) preenchida\(s\)\. Mínimo obrigatório atendido\.$/, ['$1 questions completed. Required minimum met.', '$1 preguntas completas. Mínimo obligatorio alcanzado.']],
            [/^(\d+) conteúdo\(s\)$/, ['$1 topic(s)', '$1 contenido(s)']],
            [/^(\d+) solicitação\(ões\) · Página (\d+)$/, ['$1 request(s) · Page $2', '$1 solicitud(es) · Página $2']],
            [/^(\d+) conta\(s\) encontrada\(s\) · Página (\d+)$/, ['$1 account(s) found · Page $2', '$1 cuenta(s) encontrada(s) · Página $2']],
            [/^Todas as alterações salvas às ([\d:]+)(\. Salvamento automático desativado\.)?$/, ['All changes saved at $1', 'Todos los cambios guardados a las $1']],
            [/^\/ 1\.000 caracteres$/, ['/ 1,000 characters', '/ 1.000 caracteres']]
        ];
        if (draft !== 'pt-BR') for (const [pattern, values] of patterns) {
            if (pattern.test(key)) return key.replace(pattern, values[draft === 'en' ? 0 : 1]) +
                (key.includes('. Salvamento automático desativado.') ? ' ' + translate('Salvamento automático desativado.') : '');
        }
        return source;
    };
    window.siteT = translate;

    function original(map, object, current) {
        const previous = map.get(object);
        return previous && current === previous.output ? previous.source : current;
    }

    function render() {
        if (observer) observer.disconnect();
        document.documentElement.lang = draft;
        const walker = document.createTreeWalker(document.documentElement, NodeFilter.SHOW_TEXT);
        let node;
        while ((node = walker.nextNode())) {
            const parent = node.parentElement;
            if (!parent || parent.closest(ignored) || !normalize(node.nodeValue)) continue;
            const explicit = parent.childNodes.length === 1 && parent.hasAttribute('data-i18n');
            const source = explicit ? parent.getAttribute('data-i18n') : original(sources, node, node.nodeValue);
            const key = normalize(source);
            const translated = translate(key);
            const output = translated === key ? source : source.replace(/\S[\s\S]*\S|\S/, translated);
            if (node.nodeValue !== output) node.nodeValue = output;
            sources.set(node, {source, output});
        }
        document.querySelectorAll('[placeholder],[aria-label],[title],[alt]').forEach(element => {
            if (element.closest('script,style,code,pre,[contenteditable="true"],[translate="no"],[data-i18n-ignore]')) return;
            let tracked = attributes.get(element);
            if (!tracked) { tracked = {}; attributes.set(element, tracked); }
            ['placeholder','aria-label','title','alt'].forEach(attr => {
                if (!element.hasAttribute(attr)) return;
                const current = element.getAttribute(attr);
                const previous = tracked[attr];
                const source = element.getAttribute('data-i18n-' + attr) ||
                    (previous && current === previous.output ? previous.source : current);
                const output = translate(source);
                if (current !== output) element.setAttribute(attr, output);
                tracked[attr] = {source, output};
            });
        });
        const select = document.getElementById('site-language');
        if (select) select.value = draft;
        if (observer) observer.observe(document.documentElement, {
            childList: true, subtree: true, characterData: true, attributes: true,
            attributeFilter: ['placeholder','aria-label','title','alt','data-i18n']
        });
    }

    function status(source) {
        const element = document.getElementById('language-status');
        if (element) { element.dataset.i18n = source; element.textContent = source; render(); }
    }

    async function change(language) {
        if (!languages.includes(language)) return;
        draft = language;
        await i18next.changeLanguage(language);
        render();
        window.dispatchEvent(new Event('site:language-changed'));
    }

    async function initialize() {
        if (!window.i18next) throw new Error('i18next indisponível');
        const pairs = await Promise.all(languages.map(async language => {
            const response = await fetch(new URL(language + '.json?v=limites-20261010', assetBase), { credentials: 'same-origin' });
            if (!response.ok) throw new Error('Falha ao carregar catálogo');
            return [language, { translation: await response.json() }];
        }));
        await i18next.init({
            lng: saved, fallbackLng: 'pt-BR', supportedLngs: languages,
            load: 'currentOnly', resources: Object.fromEntries(pairs),
            keySeparator: false, nsSeparator: false,
            interpolation: { escapeValue: false }, returnEmptyString: false,
        });
       
        ready = true;
        observer = new MutationObserver(() => render());
        render();
        const select = document.getElementById('site-language');
        if (select) {
            select.disabled = false;
            select.addEventListener('change', async () => {
                await change(select.value);
                status('Prévia do idioma. Clique em Salvar alterações para manter sua escolha.');
            });
        }
        const saveButton = document.querySelector('[data-theme-save]');
        if (saveButton) saveButton.addEventListener('click', () => {
            try {
                localStorage.setItem(storageKey, draft);
                saved = draft;
                status('Idioma salvo neste navegador.');
            } catch {
                status('Não foi possível salvar o idioma. A escolha vale apenas nesta página.');
            }
        });
        const cancelButton = document.querySelector('[data-theme-cancel]');
        if (cancelButton) cancelButton.addEventListener('click', async () => {
            await change(saved);
            status('O idioma salvo foi restaurado.');
        });
        window.addEventListener('storage', async event => {
            if (event.key !== storageKey && event.key !== null) return;
            saved = languages.includes(event.newValue) ? event.newValue : 'pt-BR';
            await change(saved);
        });
        window.addEventListener('pageshow', async event => {
            if (!event.persisted) return;
            try { const value = localStorage.getItem(storageKey); saved = languages.includes(value) ? value : 'pt-BR'; } catch {}
            await change(saved);
        });
        window.dispatchEvent(new Event('site:language-changed'));
    }
    initialize().catch(() => {
        document.documentElement.lang = 'pt-BR';
        status('Não foi possível carregar os idiomas. Atualize a página para tentar novamente.');
    });
})();
