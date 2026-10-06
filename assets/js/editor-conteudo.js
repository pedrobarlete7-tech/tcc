// ALTERADO: mostra um pop-up ao ativar ou desativar o salvamento automático.
(() => {
  const form = document.getElementById('editor-form');
  if (!form || !window.fetch || !window.crypto ? .getRandomValues) return;
  const materia = document.getElementById('materia-editor');
  const subdivisao = document.getElementById('subdivisao-editor');
  const filtrarSubdivisoes = () => {
    if (!materia || !subdivisao) return;
    [...subdivisao.options].forEach(option => {
      const pertence = !option.value || option.dataset.materia === materia.value;
      option.hidden = !pertence;
      option.disabled = !pertence;
      if (!pertence && option.selected) subdivisao.value = '';
    });
    document.getElementById('gerenciar-subdivisoes').href = 'gerenciar-subdivisoes.php?id_materia=' + encodeURIComponent(materia.value);
  };
  materia ? .addEventListener('change', filtrarSubdivisoes);
  filtrarSubdivisoes();
  const status = document.getElementById('editor-status');
  const revision = form.elements.namedItem('revisao');
  const buttons = [...form.querySelectorAll('button[type="submit"]')];
  const questionStatus = document.getElementById('editor-questoes-status');
  const countQuestions = () => {
    const items = [...form.querySelectorAll('[data-collection="questoes"] > [data-items] > .editor-item')];
    const total = items.filter(item => item.querySelector('textarea[name$="[pergunta]"]').value.trim() !== '' && !item.querySelector(':scope > .editor-remover input').checked).length;
    if (questionStatus) questionStatus.textContent = total + ' questão(ões) preenchida(s).' + (total < 5 ? ' Faltam ' + (5 - total) + ' para salvar.' : ' Mínimo obrigatório atendido.');
    return total;
  };
  let next = Date.now(),
    timer, version = 0,
    saved = 0,
    busy = false,
    pending = null;
  const message = (text, state = '') => {
    status.textContent = text;
    status.dataset.state = state;
  };
  let autoSave = true;
  try {
    autoSave = localStorage.getItem('ensinotec.editor.autosave') !== 'off';
  } catch {}
  const toggle = document.createElement('input');
  toggle.type = 'checkbox';
  toggle.id = 'editor-autosave';
  toggle.checked = autoSave;
  const label = document.createElement('label');
  label.className = 'editor-autosave';
  label.append(toggle, document.createTextNode('Salvar automaticamente'));
  const controls = document.createElement('div');
  controls.className = 'editor-salvamento-info';
  status.before(controls);
  controls.append(label, status);
  const manualMessage = 'Salvamento automático desativado. Use “Salvar agora”.';
  message(autoSave ? 'Salvamento automático ativado.' : manualMessage);
  toggle.addEventListener('change', () => {
    autoSave = toggle.checked;
    window.siteSucesso ? .(autoSave ? 'Salvamento automático ativado.' : 'Salvamento automático desativado.');
    try {
      localStorage.setItem('ensinotec.editor.autosave', autoSave ? 'on' : 'off');
    } catch {}
    clearTimeout(timer);
    if (busy) message('Concluindo o salvamento já iniciado. ' + (autoSave ? '' : 'Próximas alterações serão manuais.'), 'saving');
    else if (!autoSave) message(manualMessage, version !== saved || pending ? 'pending' : '');
    else if (version !== saved || pending) {
      message('Alterações pendentes…', 'pending');
      timer = setTimeout(save, 1000);
    } else message('Salvamento automático ativado.');
  });
  countQuestions();
  buttons.forEach(button => button.textContent = 'Salvar agora');
  const schedule = () => {
    version++;
    countQuestions();
    clearTimeout(timer);
    message(autoSave ? 'Alterações pendentes…' : 'Alterações não salvas. Clique em “Salvar agora”.', 'pending');
    if (autoSave) timer = setTimeout(save, 1000);
  };
  form.addEventListener('input', event => {
    if (event.target !== toggle) schedule();
  });
  form.addEventListener('change', event => {
    const field = event.target;
    if (field === toggle) return;
    if (field.matches('.editor-remover input') && field.checked) {
      if (!confirm('Excluir este item e seus itens vinculados?' + (autoSave ? ' A remoção será salva automaticamente.' : ' A remoção será aplicada ao clicar em “Salvar agora”.'))) {
        field.checked = false;
        countQuestions();
        return;
      }
    }
    schedule();
  });
  const addItem = (collection, focus = false) => {
    const template = document.getElementById('modelo-' + collection.dataset.collection);
    const prefix = collection.dataset.name + '[' + (++next) + ']';
    const fragment = template.content.cloneNode(true);
    fragment.querySelectorAll('*').forEach(element => {
      for (const attr of [...element.attributes]) {
        if (attr.value.includes('__PREFIX__')) element.setAttribute(attr.name, attr.value.replaceAll('__PREFIX__', prefix));
      }
    });
    const first = fragment.querySelector('textarea, input:not([type="hidden"])');
    collection.querySelector(':scope > [data-items]').append(fragment);
    if (focus) first ? .focus();
  };
  form.addEventListener('click', event => {
    const button = event.target.closest('[data-add]');
    if (!button) return;
    addItem(button.closest('[data-collection]'), true);
    schedule();
  });
  async function save(manual = false) {
    if (!manual && !autoSave) return;
    clearTimeout(timer);
    if (busy || (!pending && version === saved)) return;
    if (form.querySelector('[data-upload-state="enviando"], [data-upload-state="erro"]')) {
      message('Conclua ou cancele o envio dos arquivos antes de salvar.', 'pending');
      return;
    }
    if (!pending && countQuestions() < 5) {
      message('Ainda não salvo: preencha pelo menos 5 questões.', 'error');
      return;
    }
    if (!pending && !form.checkValidity()) {
      message('Confira os campos: há uma alteração ainda não salva.', 'error');
      return;
    }
    if (!pending) {
      const body = new FormData(form);
      body.set('automatico', '1');
      const bytes = crypto.getRandomValues(new Uint8Array(16));
      body.set('pedido', [...bytes].map(byte => byte.toString(16).padStart(2, '0')).join(''));
      pending = {
        body,
        version,
        action: form.action
      };
    }
    const attempt = pending;
    busy = true;
    buttons.forEach(button => button.disabled = true);
    const removalFields = [...form.querySelectorAll('.editor-remover input')];
    removalFields.forEach(field => field.disabled = true);
    message('Salvando…', 'saving');
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 20000);
    try {
      const response = await fetch(attempt.action, {
        method: 'POST',
        body: attempt.body,
        credentials: 'same-origin',
        signal: controller.signal,
        headers: {
          Accept: 'application/json'
        }
      });
      if (!response.headers.get('content-type') ? .includes('application/json')) {
        throw new Error('Não foi possível confirmar o salvamento. Confira sua conexão e sessão e tente “Salvar agora”.');
      }
      const result = await response.json();
      if (!response.ok || !result.ok) {
        pending = null;
        message(result.erro || 'Não foi possível salvar. Tente novamente.', 'error');
        if (autoSave && version > attempt.version) timer = setTimeout(save, 500);
        return;
      }
      for (const [name, id] of Object.entries(result.ids)) {
        const field = form.elements.namedItem(name);
        if (field) field.value = id;
      }
      for (const name of result.removidos) form.elements.namedItem(name) ? .closest('.editor-item') ? .remove();
      form.querySelectorAll('[data-collection]').forEach(collection => {
        if (!collection.querySelector(':scope > [data-items] > .editor-item')) addItem(collection);
      });
      countQuestions();
      revision.value = result.revisao;
      const url = new URL('gerenciar-conteudos.php', location.href);
      url.searchParams.set('id_materia', result.materia);
      url.searchParams.set('editar', result.id);
      form.action = url.href;
      history.replaceState(null, '', url.href);
      const publicLink = document.getElementById('editor-ver-pagina');
      publicLink.href = 'conteudo.php?id_conteudo=' + result.id;
      publicLink.hidden = false;
      document.querySelector('.gestao-intro h1').textContent = 'Editar conteúdo';
      const sidebar = document.querySelector('.editor-lista');
      sidebar.querySelectorAll('article').forEach(article => article.remove());
      sidebar.querySelector('a').href = 'gerenciar-conteudos.php?id_materia=' + result.materia;
      for (const item of result.lista) {
        const article = document.createElement('article');
        const title = document.createElement('h3');
        title.textContent = item.titulo;
        const actions = document.createElement('div');
        actions.className = 'gestao-acoes';
        for (const [action, label] of [
            ['editar', 'Editar'],
            ['excluir', 'Excluir']
          ]) {
          const link = document.createElement('a');
          link.className = 'gestao-botao';
          link.href = 'gerenciar-conteudos.php?id_materia=' + result.materia + '&' + action + '=' + item.id_conteudo;
          link.textContent = label;
          actions.append(link);
        }
        article.append(title, actions);
        sidebar.append(article);
      }
      saved = attempt.version;
      pending = null;
      window.siteSucesso ? .(result.mensagem);
      message('Todas as alterações salvas às ' + new Date().toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit'
      }) + (autoSave ? '' : '. Salvamento automático desativado.'), 'saved');
      if (version > saved) {
        message(autoSave ? 'Salvo. Há novas alterações aguardando…' : 'Há novas alterações não salvas. Clique em “Salvar agora”.', 'pending');
        if (autoSave) timer = setTimeout(save, 500);
      }
    } catch (error) {
      message(error.name === 'AbortError' ? 'A conexão demorou. Clique em “Salvar agora” para confirmar o salvamento.' : error.message, 'error');
    } finally {
      clearTimeout(timeout);
      busy = false;
      buttons.forEach(button => button.disabled = false);
      removalFields.forEach(field => field.disabled = false);
    }
  }
  form.addEventListener('submit', event => {
    event.preventDefault();
    if (version === saved && !pending) version++;
    save(true);
  });
  window.addEventListener('beforeunload', event => {
    if (version !== saved || busy || pending) {
      event.preventDefault();
      event.returnValue = '';
    }
  });
})();