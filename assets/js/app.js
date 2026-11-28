(() => {
  'use strict';

  const menuToggle = document.querySelector('[data-menu-toggle]');
  const menu = document.querySelector('[data-menu]');

  const closeMenu = () => {
    if (!menuToggle || !menu) return;
    menu.classList.remove('open');
    menuToggle.setAttribute('aria-expanded', 'false');
  };

  if (menuToggle && menu) {
    menuToggle.addEventListener('click', () => {
      const isOpen = menu.classList.toggle('open');
      menuToggle.setAttribute('aria-expanded', String(isOpen));
    });

    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeMenu();
    });
    document.addEventListener('click', (event) => {
      if (!menu.contains(event.target) && !menuToggle.contains(event.target)) closeMenu();
    });
  }

  document.querySelectorAll('[data-dismiss]').forEach((button) => {
    button.addEventListener('click', () => button.closest('.alert')?.remove());
  });

  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm || 'Lanjutkan tindakan ini?')) event.preventDefault();
    });
  });

  document.querySelectorAll('[data-count-target]').forEach((field) => {
    const output = document.getElementById(field.dataset.countTarget);
    if (!output) return;
    const max = Number(field.maxLength || field.getAttribute('maxlength') || 0);
    const update = () => { output.textContent = max ? `${field.value.length}/${max}` : String(field.value.length); };
    field.addEventListener('input', update);
    update();
  });

  document.querySelectorAll('[data-clear-search]').forEach((button) => {
    button.addEventListener('click', () => {
      const field = document.getElementById(button.dataset.clearSearch);
      if (!field) return;
      field.value = '';
      field.focus();
    });
  });
})();
