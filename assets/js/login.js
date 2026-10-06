(() => {
  const password = document.querySelector("#senha");
  const toggle = document.querySelector(".password-toggle");
  const email = document.querySelector("#email");
  const remember = document.querySelector("#lembrar-email");
  const form = document.querySelector("form");
  if (!password || !toggle || !email || !remember || !form) return;
  toggle.hidden = false;
  toggle.addEventListener("click", () => {
    const visible = password.type === "password";
    password.type = visible ? "text" : "password";
    toggle.setAttribute("aria-pressed", String(visible));
    toggle.setAttribute(
      "aria-label",
      visible ? "Ocultar senha" : "Mostrar senha",
    );
  });
  const key = "nivelar.email";
  try {
    const saved = localStorage.getItem(key);
    if (saved && !email.value) email.value = saved;
    remember.checked = Boolean(saved);
    remember.closest("label").hidden = false;
  } catch {
    /* Login remains usable when storage is blocked. */
  }
  remember.addEventListener("change", () => {
    if (!remember.checked) {
      try {
        localStorage.removeItem(key);
      } catch {
        /* Optional preference. */
      }
    }
  });
  form.addEventListener("submit", () => {
    try {
      if (remember.checked) localStorage.setItem(key, email.value.trim());
      else localStorage.removeItem(key);
    } catch {
      /* Never prevent submission because storage is blocked. */
    }
  });
})();
