(function () {
  'use strict';

  var cfg = window.BB_CONFIG || {};
  var fmt = function (n) { return new Intl.NumberFormat('fr-FR').format(n); };

  var state = {
    step: 1,
    amount: 5000,
    custom: '',
    name: '',
    email: '',
    anonymous: false,
    method: 'mtn',
    submitting: false,
  };

  function effectiveAmount() {
    var c = parseInt(String(state.custom).replace(/\D/g, ''), 10);
    return (state.custom && !isNaN(c) && c > 0) ? c : state.amount;
  }

  function updateAmountLabels() {
    var eff = effectiveAmount();
    document.querySelectorAll('[data-amount-label]').forEach(function (el) {
      el.textContent = fmt(eff);
    });
  }

  function refreshChipStyles() {
    document.querySelectorAll('[data-chip-group]').forEach(function (group) {
      var light = group.getAttribute('data-style') === 'light';
      group.querySelectorAll('[data-chip]').forEach(function (btn) {
        var active = !state.custom && parseInt(btn.getAttribute('data-amount'), 10) === state.amount;
        if (light) {
          btn.style.background = active ? '#e6f5ee' : '#f6f5ef';
          btn.style.borderColor = active ? '#008651' : '#e6e3d7';
          btn.style.color = active ? '#008651' : '#14160F';
        } else {
          btn.style.background = active ? 'rgba(255,255,255,.22)' : 'rgba(255,255,255,.06)';
          btn.style.borderColor = active ? '#fff' : 'rgba(255,255,255,.18)';
        }
      });
    });
  }

  function selectChip(amount) {
    state.amount = amount;
    state.custom = '';
    document.querySelectorAll('[data-custom-amount]').forEach(function (input) { input.value = ''; });
    refreshChipStyles();
    updateAmountLabels();
  }

  function onCustomInput(e) {
    var digits = e.target.value.replace(/\D/g, '');
    state.custom = digits;
    document.querySelectorAll('[data-custom-amount]').forEach(function (input) {
      if (input !== e.target) input.value = digits;
    });
    refreshChipStyles();
    updateAmountLabels();
  }

  document.querySelectorAll('[data-chip]').forEach(function (btn) {
    btn.addEventListener('click', function () { selectChip(parseInt(btn.getAttribute('data-amount'), 10)); });
  });
  document.querySelectorAll('[data-custom-amount]').forEach(function (input) {
    input.addEventListener('input', onCustomInput);
  });

  // ---- modal open/close/steps ----
  var modal = document.getElementById('donation-modal');
  if (!modal) return; // donations toggled off in the backoffice — modal markup isn't rendered

  function setBodyScrollLocked(locked) {
    document.body.style.overflow = locked ? 'hidden' : '';
  }

  function goToStep(n) {
    state.step = n;
    modal.querySelectorAll('[data-step-panel]').forEach(function (panel) {
      panel.hidden = parseInt(panel.getAttribute('data-step-panel'), 10) !== n;
    });
    modal.querySelectorAll('[data-step-tab]').forEach(function (tab) {
      tab.style.color = parseInt(tab.getAttribute('data-step-tab'), 10) <= n ? '#008651' : '#bdbdb4';
    });
    var bar = modal.querySelector('[data-progress-bar]');
    if (bar) bar.style.width = (Math.min(100, (Math.min(n, 3) / 3) * 100)) + '%';
  }

  function openModal() {
    modal.style.display = 'flex';
    setBodyScrollLocked(true);
    clearPayError();
    goToStep(1);
  }

  function closeModal() {
    modal.style.display = 'none';
    setBodyScrollLocked(false);
    state.submitting = false;
    goToStep(1);
  }

  document.querySelectorAll('[data-open-modal]').forEach(function (btn) {
    btn.addEventListener('click', openModal);
  });
  modal.querySelectorAll('[data-close-modal]').forEach(function (btn) {
    btn.addEventListener('click', closeModal);
  });
  modal.addEventListener('click', function (e) {
    if (!e.target.closest('[data-modal-card]')) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.style.display !== 'none') closeModal();
  });

  modal.querySelectorAll('[data-next]').forEach(function (btn) {
    btn.addEventListener('click', function () { goToStep(Math.min(3, state.step + 1)); });
  });
  modal.querySelectorAll('[data-back]').forEach(function (btn) {
    btn.addEventListener('click', function () { clearPayError(); goToStep(Math.max(1, state.step - 1)); });
  });

  // ---- step 2: donor info ----
  var nextStep2Btn = modal.querySelector('[data-step-panel="2"] [data-next]');

  function validateStep2() {
    var valid = state.name.trim().length > 0 && state.email.trim().length > 0;
    if (nextStep2Btn) {
      nextStep2Btn.disabled = !valid;
      nextStep2Btn.style.background = valid ? '#008651' : '#e9c4c2';
      nextStep2Btn.style.cursor = valid ? 'pointer' : 'not-allowed';
    }
    return valid;
  }

  modal.querySelectorAll('[data-field]').forEach(function (input) {
    input.addEventListener('input', function () {
      state[input.getAttribute('data-field')] = input.value;
      validateStep2();
    });
  });

  var anonBtn = modal.querySelector('[data-toggle-anon]');
  if (anonBtn) {
    anonBtn.addEventListener('click', function () {
      state.anonymous = !state.anonymous;
      anonBtn.style.borderColor = state.anonymous ? '#008651' : '#cfcdc2';
      anonBtn.style.background = state.anonymous ? '#008651' : '#fff';
      anonBtn.textContent = state.anonymous ? '✓' : '';
    });
  }

  // ---- step 3: payment method selection (visual only — FedaPay shows its own picker) ----
  modal.querySelectorAll('[data-method]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      state.method = btn.getAttribute('data-method-id');
      modal.querySelectorAll('[data-method]').forEach(function (b) {
        var active = b === btn;
        b.style.background = active ? '#e6f5ee' : '#fff';
        b.style.borderColor = active ? '#008651' : '#e6e3d7';
        var radio = b.querySelector('[data-method-radio]');
        if (radio) {
          radio.style.borderColor = active ? '#008651' : '#cfcdc2';
          radio.style.background = active ? 'radial-gradient(circle,#008651 0 5px,transparent 6px)' : 'transparent';
        }
      });
    });
  });

  // ---- FAQ accordion ----
  document.querySelectorAll('[data-faq-item]').forEach(function (item) {
    var toggle = item.querySelector('[data-faq-toggle]');
    var body = item.querySelector('[data-faq-body]');
    var sign = item.querySelector('[data-faq-sign]');
    toggle.addEventListener('click', function () {
      var open = body.style.maxHeight && body.style.maxHeight !== '0px';
      body.style.maxHeight = open ? '0' : '220px';
      sign.style.background = open ? '#f4f2ea' : '#008651';
      sign.style.color = open ? '#14160F' : '#fff';
      sign.style.transform = open ? 'rotate(0deg)' : 'rotate(45deg)';
    });
  });

  // ---- payment ----
  var payBtn = modal.querySelector('[data-pay]');
  var payErrorEl = modal.querySelector('[data-pay-error]');

  function clearPayError() {
    if (payErrorEl) { payErrorEl.hidden = true; payErrorEl.textContent = ''; }
  }

  function showPayError(msg) {
    if (payErrorEl) { payErrorEl.hidden = false; payErrorEl.textContent = msg; }
  }

  function setSubmitting(submitting) {
    state.submitting = submitting;
    if (payBtn) {
      payBtn.disabled = submitting;
      payBtn.textContent = submitting ? cfg.processingLabel : (cfg.payLabel + ' · ' + fmt(effectiveAmount()) + ' FCFA');
    }
  }

  function openFedaPay(donationId) {
    var eff = effectiveAmount();
    var donorName = state.name.trim() || cfg.donorFallback;
    var description = 'Don – Bénin Bouge';
    if (donationId) description = 'Don #' + donationId + ' – Bénin Bouge';

    var widget = FedaPay.init({
      public_key: cfg.fedapayPublicKey,
      transaction: {
        amount: eff,
        description: description,
      },
      customer: {
        firstname: donorName,
        email: state.email.trim(),
      },
      currency: { iso: 'XOF' },
      onComplete: function (resp) {
        if (resp.reason === FedaPay.CHECKOUT_COMPLETED) {
          var params = new URLSearchParams({ amount: String(eff) });
          if (!state.anonymous && state.name.trim()) params.set('name', state.name.trim());
          window.location.href = cfg.merciUrl + '?' + params.toString();
        } else {
          setSubmitting(false);
        }
      },
    });
    widget.open();
  }

  if (payBtn) {
    payBtn.addEventListener('click', function () {
      if (typeof FedaPay === 'undefined') {
        showPayError(cfg.loadErrorMessage);
        return;
      }
      clearPayError();
      setSubmitting(true);

      fetch('/api/donations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          amount: effectiveAmount(),
          name: state.anonymous ? '' : state.name.trim(),
          email: state.email.trim(),
          anonymous: state.anonymous,
          method: state.method,
        }),
      })
        .then(function (res) { return res.ok ? res.json() : null; })
        .then(function (data) { openFedaPay(data && data.id); })
        .catch(function () { openFedaPay(null); });
    });
  }

  updateAmountLabels();
  refreshChipStyles();
  validateStep2();
})();
