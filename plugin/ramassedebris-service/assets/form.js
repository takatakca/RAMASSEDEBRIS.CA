(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.rdb-form').forEach(function (form) {
      var details = form.querySelector('textarea[name="rdb_details"]');
      var counter = form.querySelector('#rdb-counter');
      var submit = form.querySelector('button[type="submit"]');
      var endpoint = form.getAttribute('data-rdb-nonce-endpoint');
      var submitting = false;

      if (details && counter) {
        var update = function () { counter.textContent = String(details.value.length); };
        details.addEventListener('input', update);
        update();
      }

      function pending(value) {
        submitting = value;
        if (submit) {
          submit.disabled = value;
          submit.textContent = value ? 'Envoi en cours…' : 'Envoyer ma demande →';
        }
      }

      function showError() {
        var existing = form.querySelector('.rdb-nonce-error');
        if (existing) return;
        var message = document.createElement('p');
        message.className = 'rdb-notice rdb-notice--error rdb-nonce-error';
        message.setAttribute('role', 'alert');
        message.textContent = 'La vérification de sécurité est indisponible. Actualisez la page et réessayez; la demande n’a pas été envoyée.';
        var actions = form.querySelector('.rdb-actions');
        form.insertBefore(message, actions || form.firstChild);
      }

      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) return;
        if (submitting) {
          event.preventDefault();
          return;
        }

        // Progressive enhancement: when fetch is unavailable the original
        // WordPress form + nonce still works without JavaScript.
        if (!endpoint || typeof fetch !== 'function') {
          pending(true);
          return;
        }

        event.preventDefault();
        pending(true);
        var body = 'action=rdb_refresh_nonce';
        fetch(endpoint, {
          method: 'POST',
          credentials: 'same-origin',
          cache: 'no-store',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: body
        })
          .then(function (response) {
            if (!response.ok) throw new Error('Nonce service unavailable');
            return response.json();
          })
          .then(function (payload) {
            if (!payload || payload.success !== true ||
                !payload.data || typeof payload.data.nonce !== 'string' ||
                !payload.data.nonce) {
              throw new Error('Invalid nonce response');
            }
            var nonce = form.elements['rdb_nonce'];
            if (!nonce) throw new Error('Missing nonce field');
            nonce.value = payload.data.nonce;
            // Native submit avoids triggering this same handler a second time.
            form.submit();
          })
          .catch(function () {
            pending(false);
            showError();
          });
      });
    });
  });
}());
