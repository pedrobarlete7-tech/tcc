/* i18next 25.5.2, distribuído localmente. Nenhum texto é enviado a terceiros. */
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
    } catch {
        /* O idioma ainda pode ser usado temporariamente. */ }
    let draft = saved;
    let ready = false;
    const liveSources = new WeakMap();
    let observer;
    const translate = source => ready ? i18next.t(source, {
        defaultValue: source
    }) : source;
    window.siteT = translate;

    function render() {
        observer ? .disconnect();
        document.documentElement.lang = draft;
        document.querySelectorAll('[data-i18n]').forEach(element => {
            if (element.parentElement ? .closest('[data-i18n-message]')) return;
            element.textContent = translate(element.dataset.i18n);
        });
        for (const attr of ['placeholder', 'aria-label', 'title']) {
            document.querySelectorAll('[data-i18n-' + attr + ']').forEach(element => {
                element.setAttribute(attr, translate(element.getAttribute('data-i18n-' + attr)));
            });
        }
        document.querySelectorAll('[data-i18n-message]').forEach(element => {
            const previous = liveSources.get(element);
            const current = element.textContent;
            const source = previous && current === previous.output ? previous.source : normalize(current);
            const output = translate(source);
            element.textContent = output;
            liveSources.set(element, {
                source,
                output
            });
        });
        const select = document.getElementById('site-language');
        if (select) select.value = draft;
        observer ? .observe(document.body, {
            childList: true,
            subtree: true,
            characterData: true
        });
    }

    function status(source) {
        const element = document.getElementById('language-status');
        if (element) {
            element.dataset.i18n = source;
            element.textContent = translate(source);
        }
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
            const response = await fetch(new URL(language + '.json', assetBase), {
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error('Falha ao carregar catálogo');
            return [language, {
                translation: await response.json()
            }];
        }));
        await i18next.init({
            lng: saved,
            fallbackLng: 'pt-BR',
            supportedLngs: languages,
            load: 'currentOnly',
            resources: Object.fromEntries(pairs),
            keySeparator: false,
            nsSeparator: false,
            interpolation: {
                escapeValue: false
            },
            returnEmptyString: false,
        });
        // textContent e setAttribute fazem a inserção segura; nunca usamos innerHTML.
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
        document.querySelector('[data-theme-save]') ? .addEventListener('click', () => {
            try {
                localStorage.setItem(storageKey, draft);
                saved = draft;
                status('Idioma salvo neste navegador.');
            } catch {
                status('Não foi possível salvar o idioma. A escolha vale apenas nesta página.');
            }
        });
        document.querySelector('[data-theme-cancel]') ? .addEventListener('click', async () => {
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
            try {
                const value = localStorage.getItem(storageKey);
                saved = languages.includes(value) ? value : 'pt-BR';
            } catch {}
            await change(saved);
        });
        window.dispatchEvent(new Event('site:language-changed'));
    }
    initialize().catch(() => {
        document.documentElement.lang = 'pt-BR';
        status('Não foi possível carregar os idiomas. Atualize a página para tentar novamente.');
    });
})();