(() => {
    const toggle = document.querySelector('.nav-toggle');
    const links = document.querySelector('#navigation-links');
    const desktop = window.matchMedia('(min-width: 48rem)');
    const dropdowns = [...document.querySelectorAll('.dropdown')];
    if (!toggle || !links) return;
    toggle.hidden = false;
    const closeDropdowns = () => dropdowns.forEach(item => { item.open = false; });
    const syncNavigation = () => {
        links.hidden = !desktop.matches;
        toggle.setAttribute('aria-expanded', 'false');
        closeDropdowns();
    };
    syncNavigation();
    desktop.addEventListener('change', syncNavigation);
    toggle.addEventListener('click', () => {
        const expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!expanded));
        links.hidden = expanded;
        if (expanded) closeDropdowns();
    });
    dropdowns.forEach(item => {
        item.addEventListener('toggle', () => {
            if (item.open) dropdowns.forEach(other => { if (other !== item) other.open = false; });
        });
    });
    document.addEventListener('click', event => {
        dropdowns.forEach(item => { if (!item.contains(event.target)) item.open = false; });
    });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        const open = dropdowns.find(item => item.open);
        if (open) {
            open.open = false;
            open.querySelector('summary').focus();
        } else if (!desktop.matches && !links.hidden) {
            syncNavigation();
            toggle.focus();
        }
    });
    document.addEventListener('focusin', event => {
        dropdowns.forEach(item => { if (!item.contains(event.target)) item.open = false; });
    });
})();
