(() => {
    const password = document.querySelector('#senha');
    const confirmation = document.querySelector('#confirmar_senha');
    const error = document.querySelector('#password-error');
    const toggle = document.querySelector('.show-passwords');
    if (!password || !confirmation || !error || !toggle) return;

    const validateConfirmation = () => {
        const mismatch = confirmation.value !== '' && password.value !== confirmation.value;
        const message = mismatch ? 'As senhas não coincidem.' : '';
        confirmation.setCustomValidity(message);
        confirmation.setAttribute('aria-invalid', String(mismatch));
        error.textContent = message;
    };
    password.addEventListener('input', validateConfirmation);
    confirmation.addEventListener('input', validateConfirmation);
    toggle.hidden = false;
    toggle.addEventListener('click', () => {
        const show = password.type === 'password';
        password.type = confirmation.type = show ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(show));
        toggle.textContent = show ? 'Ocultar senhas' : 'Mostrar senhas';
    });
})();
