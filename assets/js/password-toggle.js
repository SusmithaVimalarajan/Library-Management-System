document.querySelectorAll("[data-password-toggle]").forEach(button => {
    const input = document.getElementById(button.getAttribute("aria-controls"));

    if (!input || input.type !== "password") {
        return;
    }

    button.addEventListener("click", () => {
        const showPassword = input.type === "password";
        input.type = showPassword ? "text" : "password";
        button.textContent = showPassword ? "Hide" : "Show";
        button.setAttribute("aria-pressed", String(showPassword));
    });
});
