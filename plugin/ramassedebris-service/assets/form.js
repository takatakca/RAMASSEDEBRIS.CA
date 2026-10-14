
(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.rdb-form').forEach(function (form) {
      var details = form.querySelector('textarea[name="rdb_details"]');
      var counter = form.querySelector('#rdb-counter');
      var submit = form.querySelector('button[type="submit"]');
      if (details && counter) {
        var update = function () { counter.textContent = String(details.value.length); };
        details.addEventListener('input', update);
        update();
      }
      form.addEventListener('submit', function () {
        if (!form.checkValidity()) return;
        if (submit) {
          submit.disabled = true;
          submit.textContent = 'Envoi en cours…';
        }
      });
    });
  });
}());
