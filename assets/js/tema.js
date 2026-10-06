// NOVO: prévia, salvamento e cancelamento da aparência em todas as páginas.
(() => {
    'use strict';
    const key = 'ensinotec.aparencia';
    const modes = ['light', 'dark', 'system'];
    const system = window.matchMedia('(prefers-color-scheme: dark)');
    const read = () => {
        try {
            const value = localStorage.getItem(key);
            return modes.includes(value) ? value : 'light';
        } catch {
            return 'light';
        }
    };
    let saved = read();
    let draft = saved;

    function apply() {
        document.documentElement.dataset.theme = draft === 'system' ? (system.matches ? 'dark' : 'light') : draft;
        document.querySelectorAll('[data-theme-choice]').forEach(button => {
            const active = button.dataset.themeChoice === draft;
            button.classList.toggle('selected', active);
            button.setAttribute('aria-pressed', String(active));
        });
    }

    function status(message) {
        const target = document.getElementById('theme-status');
        if (target) target.textContent = message;
    }

    function save() {
        try {
            localStorage.setItem(key, draft);
            saved = draft;
            status('Preferência de aparência salva neste navegador.');
        } catch {
            status('Não foi possível salvar neste navegador. A aparência foi aplicada apenas nesta página.');
        }
    }
    apply();
    system.addEventListener('change', () => {
        if (draft === 'system') apply();
    });
    window.addEventListener('storage', event => {
        if (event.key === key || event.key === null) {
            saved = read();
            draft = saved;
            apply();
        }
    });
    window.addEventListener('pageshow', event => {
        if (event.persisted) {
            saved = read();
            draft = saved;
            apply();
        }
    });
    document.addEventListener('DOMContentLoaded', () => {
        apply();
        document.querySelectorAll('[data-theme-choice]').forEach(button => button.addEventListener('click', () => {
            draft = button.dataset.themeChoice;
            apply();
            status('Prévia de aparência. Clique em Salvar alterações para manter sua escolha.');
        }));
        document.querySelector('[data-theme-save]') ? .addEventListener('click', save);
        document.querySelector('[data-theme-cancel]') ? .addEventListener('click', () => {
            draft = saved;
            apply();
            status('A aparência salva foi restaurada.');
        });
        document.querySelector('[data-theme-toggle]') ? .addEventListener('click', () => {
            draft = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            apply();
            save();
        });
    });
})();