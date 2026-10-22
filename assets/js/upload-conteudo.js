// ALTERADO: mostra a imagem enviada e mantém o aviso coerente com o arquivo atual.
(() => {
  const form = document.getElementById("editor-form");
  if (!form || !window.fetch || !window.AbortController) return;
  const requests = new WeakMap();
  const notify = (box) =>
    box
      .querySelector("[data-upload-caminho]")
      .dispatchEvent(new Event("input", { bubbles: true }));
  form.addEventListener("change", async (event) => {
    const input = event.target.closest("[data-upload-arquivo]");
    if (!input) return;
    const box = input.closest(".editor-upload");
    if (requests.get(box)) requests.get(box).abort();
    requests.delete(box);
    const file = input.files[0];
    const status = box.querySelector("[data-upload-status]");
    const cancel = box.querySelector("[data-upload-cancelar]");
    if (!file) {
      delete box.dataset.uploadState;
      cancel.hidden = true;
      status.textContent =
        "Nenhum novo arquivo selecionado. O vínculo anterior foi mantido.";
      notify(box);
      return;
    }
    box.dataset.uploadState = "enviando";
    cancel.hidden = false;
    if (file.size > Number(box.dataset.limite) || !file.size) {
      box.dataset.uploadState = "erro";
      status.textContent =
        "Arquivo vazio ou maior que o limite indicado. Selecione outro ou cancele o envio.";
      return;
    }
    const controller = new AbortController();
    requests.set(box, controller);
    status.textContent =
      "Enviando arquivo… aguarde antes de sair desta página.";
    const body = new FormData();
    body.set("csrf", form.elements.namedItem("csrf").value);
    body.set("tipo", box.dataset.tipo);
    body.set("arquivo", file);
    const timeout = setTimeout(() => controller.abort(), 60000);
    try {
      const response = await fetch("actions/upload-conteudo.php", {
        method: "POST",
        body,
        credentials: "same-origin",
        signal: controller.signal,
      });
      if (
        !(response.headers.get("content-type") || "").includes(
          "application/json",
        )
      )
        throw new Error(
          "O servidor não concluiu o upload. Confira sua sessão e o limite de envio do PHP.",
        );
      const result = await response.json();
      if (!response.ok || !result.ok)
        throw new Error(result.erro || "Não foi possível enviar o arquivo.");
      if (requests.get(box) !== controller || !box.isConnected) return;
      box.querySelector("[data-upload-caminho]").value = result.caminho;
      const preview = box.querySelector("[data-upload-preview]");
      preview.href = result.caminho;
      preview.hidden = false;
      const image = box.querySelector("[data-upload-imagem]");
      if (image) {
        image.hidden = false;
        image.style.display = "block";
        image.src = result.caminho;
      }
      status.textContent =
        "Arquivo enviado. Salve o conteúdo para concluir o vínculo. O salvamento exige pelo menos 5 questões preenchidas.";
      delete box.dataset.uploadState;
      cancel.hidden = true;
      input.value = "";
      notify(box);
    } catch (error) {
      if (requests.get(box) !== controller) return;
      box.dataset.uploadState = "erro";
      status.textContent =
        (error.name === "AbortError"
          ? "O envio demorou demais."
          : error.message) +
        " Você pode tentar pelo botão “Salvar conteúdo e arquivo”.";
    } finally {
      clearTimeout(timeout);
    }
  });
  form.addEventListener("click", (event) => {
    const cancel = event.target.closest("[data-upload-cancelar]");
    if (!cancel) return;
    const box = cancel.closest(".editor-upload");
    if (requests.get(box)) requests.get(box).abort();
    requests.delete(box);
    box.querySelector("[data-upload-arquivo]").value = "";
    delete box.dataset.uploadState;
    cancel.hidden = true;
    box.querySelector("[data-upload-status]").textContent =
      "Envio cancelado. O arquivo anterior foi mantido.";
    notify(box);
  });
})();
