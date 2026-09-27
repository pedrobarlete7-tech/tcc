// NOVO: permite conferir as senhas e informa quando são diferentes.
(() => {
    const senha = document.getElementById('senha');
    const confirmar = document.getElementById('confirmar_senha');
    const botao = document.querySelector('.show-passwords');
    const erro = document.getElementById('password-error');
    if (!senha || !confirmar || !botao || !erro) return;
    botao.hidden = false;
    botao.addEventListener('click', () => {
        const mostrar = senha.type === 'password';
        senha.type = confirmar.type = mostrar ? 'text' : 'password';
        botao.textContent = mostrar ? 'Ocultar senhas' : 'Mostrar senhas';
        botao.setAttribute('aria-pressed', String(mostrar));
    });
    function validar() {
        const mensagem = confirmar.value && senha.value !== confirmar.value ? 'As senhas não coincidem.' : '';
        confirmar.setCustomValidity(mensagem);
        erro.textContent = mensagem;
    }
    senha.addEventListener('input', validar);
    confirmar.addEventListener('input', validar);
})();
