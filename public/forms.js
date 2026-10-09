(() => {
  document.querySelectorAll('[data-ajax-form]').forEach((form) => {
    const status = document.getElementById(form.dataset.status);
    const button = form.querySelector('[type="submit"]');
    if (!status || !button) return;
    let pending = false;
    const fieldErrors = [];

    const announce = (message, state) => {
      status.textContent = message;
      status.dataset.state = state;
    };

    const clearErrors = () => {
      fieldErrors.forEach((error) => {
        const input = form.elements.namedItem(error.dataset.fieldError);
        if (input instanceof HTMLElement) {
          input.removeAttribute('aria-invalid');
          const describedBy = (input.getAttribute('aria-describedby') || '').split(' ').filter((id) => id && id !== error.id);
          if (describedBy.length) input.setAttribute('aria-describedby', describedBy.join(' '));
          else input.removeAttribute('aria-describedby');
        }
        error.remove();
      });
      fieldErrors.length = 0;
    };

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (pending || !form.reportValidity()) return;

      pending = true;
      button.disabled = true;
      form.setAttribute('aria-busy', 'true');
      clearErrors();
      announce('Submitting...', 'pending');
      const controller = new AbortController();
      const timeout = window.setTimeout(() => controller.abort(), 30000);

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
          credentials: 'same-origin',
          signal: controller.signal,
        });
        const data = await response.json().catch(() => null);

        if (response.status === 422) {
          let firstInvalid;
          Object.entries(data?.errors || {}).forEach(([name, messages]) => {
            const input = form.elements.namedItem(name);
            if (!(input instanceof HTMLElement) || input.type === 'hidden' || input.closest('.form-trap')) return;
            const error = document.createElement('span');
            error.className = 'field-error';
            error.dataset.fieldError = name;
            error.id = form.id + '-' + name + '-error';
            error.textContent = Array.isArray(messages) ? messages[0] : String(messages);
            input.setAttribute('aria-invalid', 'true');
            input.setAttribute('aria-describedby', ((input.getAttribute('aria-describedby') || '') + ' ' + error.id).trim());
            // Keep the newsletter's horizontal input/button layout intact.
            if (form.id === 'newsletter-form') form.insertAdjacentElement('afterend', error);
            else input.insertAdjacentElement('afterend', error);
            fieldErrors.push(error);
            firstInvalid ||= input;
          });
          announce(data?.message || 'Please check the highlighted fields.', 'error');
          firstInvalid?.focus();
          return;
        }

        if (response.status === 419) {
          announce('Your session expired. Copy your message, then refresh this page and submit again.', 'error');
          return;
        }

        if (response.status === 429) {
          announce('Too many submissions. Please wait a minute before trying again.', 'error');
          return;
        }

        if (!response.ok || !data?.message) {
          announce('We could not confirm your submission. Your details are still here; please try again.', 'error');
          return;
        }

        form.reset();
        if (data.submission_id && form.elements.namedItem('submission_id')) {
          const token = form.elements.namedItem('submission_id');
          token.value = data.submission_id;
          token.defaultValue = data.submission_id;
        }
        announce(data.message, 'success');
      } catch (error) {
        announce(error.name === 'AbortError'
          ? 'The request timed out. Your details are still here; please try again.'
          : 'Unable to connect. Check your connection and try again. Your details have been kept.', 'error');
      } finally {
        window.clearTimeout(timeout);
        pending = false;
        button.disabled = false;
        form.removeAttribute('aria-busy');
      }
    });
  });
})();
