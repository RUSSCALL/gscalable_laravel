/**
 * Auth pages (login / register / password reset): password visibility toggles.
 * Any element with [data-password-toggle] switches the matching password
 * field between type="password" and type="text" and swaps its eye icon.
 */
(function() {
  "use strict";

  const HIDDEN_LABEL = "Show password";
  const VISIBLE_LABEL = "Hide password";

  const findToggleInput = (button) => {
    const controlledId = button.getAttribute("aria-controls");
    if (controlledId) {
      const input = document.getElementById(controlledId);
      if (input) return input;
    }
    const wrapper = button.closest(".gst_login_password_input_wrapper") || button.parentElement;
    return wrapper ? wrapper.querySelector("[data-password-input]") : null;
  };

  const paintToggle = (button, isVisible) => {
    const icon = button.querySelector(".bi-eye, .bi-eye-slash");
    if (icon) {
      icon.classList.toggle("bi-eye", !isVisible);
      icon.classList.toggle("bi-eye-slash", isVisible);
    }
    button.setAttribute("aria-pressed", isVisible ? "true" : "false");
    button.setAttribute("aria-label", isVisible ? VISIBLE_LABEL : HIDDEN_LABEL);
  };

  const togglePassword = (button) => {
    const input = findToggleInput(button);
    if (!input) return;
    const isVisible = input.type === "text";
    input.type = isVisible ? "password" : "text";
    input.focus({ preventScroll: true });
    paintToggle(button, !isVisible);
  };

  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-password-toggle]");
    if (!button) return;
    event.preventDefault();
    togglePassword(button);
  });
})();
