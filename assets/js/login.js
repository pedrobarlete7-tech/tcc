(() => {
  const password = document.querySelector("#senha");
  const toggle = document.querySelector(".password-toggle");
  try {
    localStorage.removeItem("nivelar.email");
  } catch {}
  if (!password || !toggle) return;
  toggle.hidden = false;
  toggle.addEventListener("click", () => {
    const visible = password.type === "password";
    password.type = visible ? "text" : "password";
    toggle.setAttribute("aria-pressed", String(visible));
    const label = visible ? "Ocultar senha" : "Mostrar senha";
    toggle.setAttribute("data-i18n-aria-label", label);
    toggle.setAttribute(
      "aria-label",
      window.siteT ? window.siteT(label) : label,
    );
  });
})();
