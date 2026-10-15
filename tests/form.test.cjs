'use strict';
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');

const source = fs.readFileSync('plugin/ramassedebris-service/assets/form.js', 'utf8');

async function exercise(scenario) {
  let handleSubmit;
  let calls = 0;
  let prevented = 0;
  let submitted = 0;
  let message = null;
  const nonce = { value: 'old-cached-token' };
  const counter = { textContent: '' };
  const details = {
    value: 'Un canapé',
    addEventListener(name, callback) {
      assert.equal(name, 'input');
      this.update = callback;
    }
  };
  const button = { disabled: false, textContent: 'Envoyer ma demande →' };
  const actions = {};
  const form = {
    elements: { rdb_nonce: nonce },
    getAttribute(name) {
      assert.equal(name, 'data-rdb-nonce-endpoint');
      return '/wp-admin/admin-ajax.php';
    },
    querySelector(selector) {
      return ({
        'textarea[name="rdb_details"]': details,
        '#rdb-counter': counter,
        'button[type="submit"]': button,
        '.rdb-actions': actions,
        '.rdb-nonce-error': message
      })[selector] || null;
    },
    checkValidity() { return scenario !== 'invalid'; },
    addEventListener(name, callback) {
      assert.equal(name, 'submit');
      handleSubmit = callback;
    },
    submit() { submitted++; },
    insertBefore(node, anchor) {
      assert.equal(anchor, actions);
      message = node;
    }
  };
  const document = {
    addEventListener(name, callback) {
      assert.equal(name, 'DOMContentLoaded');
      callback();
    },
    querySelectorAll(selector) {
      assert.equal(selector, '.rdb-form');
      return [form];
    },
    createElement(type) {
      assert.equal(type, 'p');
      return { className: '', textContent: '', setAttribute() {} };
    }
  };
  const fetchMock = (url, options) => {
    calls++;
    assert.equal(url, '/wp-admin/admin-ajax.php');
    assert.equal(options.method, 'POST');
    assert.equal(options.credentials, 'same-origin');
    assert.equal(options.cache, 'no-store');
    assert.equal(options.body, 'action=rdb_refresh_nonce');
    return Promise.resolve({
      ok: scenario !== 'network-error',
      json: () => Promise.resolve(scenario === 'invalid-json'
        ? { success: true, data: { nonce: '' } }
        : { success: true, data: { nonce: 'fresh-token' } })
    });
  };

  const context = { document };
  if (scenario !== 'no-fetch') context.fetch = fetchMock;
  vm.runInNewContext(source, context, { filename: 'form.js' });
  assert.equal(counter.textContent, '9', 'description count initialized');
  details.value = 'abc';
  details.update();
  assert.equal(counter.textContent, '3', 'description count changes');

  const submitEvent = () => handleSubmit({ preventDefault() { prevented++; } });
  submitEvent();
  if (scenario === 'double') submitEvent();

  await new Promise((resolve) => setImmediate(resolve));
  await new Promise((resolve) => setImmediate(resolve));

  if (scenario === 'success' || scenario === 'double') {
    assert.equal(prevented, scenario === 'double' ? 2 : 1);
    assert.equal(calls, 1, 'one nonce refresh');
    assert.equal(submitted, 1, 'form submitted once');
    assert.equal(nonce.value, 'fresh-token');
    assert.equal(button.disabled, true);
  } else if (scenario === 'no-fetch') {
    assert.equal(prevented, 0, 'native fallback not prevented');
    assert.equal(calls, 0);
  } else if (scenario === 'invalid') {
    assert.equal(prevented, 0);
    assert.equal(submitted, 0);
    assert.equal(calls, 0);
  } else {
    assert.equal(submitted, 0, 'never submit without a fresh nonce');
    assert.equal(button.disabled, false, 're-enable on failure');
    assert.ok(message && message.textContent.includes('n’a pas été envoyée'));
  }
}

(async () => {
  for (const scenario of ['success', 'double', 'no-fetch', 'invalid', 'network-error', 'invalid-json']) {
    await exercise(scenario);
  }
  process.stdout.write('PASS: dynamic nonce refresh, double-submit, offline fallback and error states\n');
})().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
