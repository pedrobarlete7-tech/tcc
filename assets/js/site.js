(() => {
    const toggle = document.querySelector('.nav-toggle');
    const links = document.querySelector('#navigation-links');
    const drawer = document.querySelector('#mobile-drawer');
    const close = drawer?.querySelector('.drawer-close');
    const content = drawer?.querySelector('.drawer-content');
    if (!toggle || !links || !drawer || !close || !content) return;
    const originalParent = links.parentElement;
    const accountSlot = document.querySelector('#mobile-account');
    const profileItem = links.querySelector('.profile')?.closest('li');
    const desktop = window.matchMedia('(min-width: 48rem)');
    const dropdowns = [...links.querySelectorAll('.dropdown')];
    let savedOverflow = '';
    let locked = false;
    toggle.hidden = false;

    // ALTERADO: mantém o mesmo perfil na barra mobile e restaura sua posição no desktop.
    function closeDrawer() {
        if (drawer.open) drawer.close();
        if (locked) document.body.style.overflow = savedOverflow;
        locked = false;
        toggle.setAttribute('aria-expanded', 'false');
    }
    function sync() {
        closeDrawer();
        if (profileItem && accountSlot) (desktop.matches ? links : accountSlot).append(profileItem);
        (desktop.matches ? originalParent : content).append(links);
        dropdowns.forEach(item => { item.open = false; });
        if (desktop.matches && document.activeElement === toggle) originalParent.querySelector('.brand').focus();
    }
    sync();
    desktop.addEventListener('change', sync);
    toggle.addEventListener('click', () => {
        if (desktop.matches) return;
        dropdowns.forEach(item => { item.open = false; });
        savedOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        locked = true;
        drawer.showModal();
        toggle.setAttribute('aria-expanded', 'true');
        close.focus();
    });
    close.addEventListener('click', closeDrawer);
    drawer.addEventListener('close', () => {
        if (locked) document.body.style.overflow = savedOverflow;
        locked = false;
        toggle.setAttribute('aria-expanded', 'false');
    });
    drawer.addEventListener('cancel', event => {
        event.preventDefault();
        closeDrawer();
    });
    drawer.addEventListener('click', event => {
        const rect = drawer.getBoundingClientRect();
        if (event.target === drawer && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) closeDrawer();
    });
    links.addEventListener('click', event => {
        if (event.target.closest('a') && drawer.open) closeDrawer();
    });
    dropdowns.forEach(item => item.addEventListener('toggle', () => {
        if (item.open) dropdowns.forEach(other => { if (other !== item) other.open = false; });
    }));
    document.addEventListener('click', event => {
        if (drawer.open) return;
        dropdowns.forEach(item => { if (!item.contains(event.target)) item.open = false; });
    });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape' || drawer.open) return;
        const open = dropdowns.find(item => item.open);
        if (open) { open.open = false; open.querySelector('summary').focus(); }
    });
    document.addEventListener('focusin', event => {
        if (drawer.open) return;
        dropdowns.forEach(item => { if (!item.contains(event.target)) item.open = false; });
    });
})();
