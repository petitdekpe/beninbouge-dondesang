(function () {
  'use strict';

  var cfg = window.BB_MERCI || {};
  var palette = ['#3A8556', '#0E3F51', '#E1251B', '#295D3C'];
  var root = document.getElementById('confetti-root');

  if (root) {
    for (var i = 0; i < 18; i++) {
      var left = (i * 5.6 + (i % 3) * 4) % 100;
      var color = palette[i % palette.length];
      var delay = ((i % 6) * 0.45 + (i % 3) * 0.2).toFixed(2);
      var dur = (4.2 + (i % 5) * 0.7).toFixed(2);
      var w = 7 + (i % 3) * 3;
      var h = (i % 2 === 0) ? w : Math.round(w * 1.8);
      var round = (i % 4 === 0) ? '50%' : '2px';

      var span = document.createElement('span');
      span.style.cssText = 'position:absolute;top:-6vh;left:' + left + '%;width:' + w + 'px;height:' + h + 'px;background:' + color + ';border-radius:' + round + ';opacity:0;animation:bbConf ' + dur + 's linear ' + delay + 's infinite';
      root.appendChild(span);
    }
  }

  function openShare(url) {
    try { window.open(url, '_blank', 'noopener,noreferrer'); } catch (e) {}
  }

  var shareUrl = window.location.href;

  var whatsappBtn = document.querySelector('[data-share="whatsapp"]');
  if (whatsappBtn) whatsappBtn.addEventListener('click', function () {
    openShare('https://wa.me/?text=' + encodeURIComponent(cfg.shareText + ' ' + shareUrl));
  });

  var facebookBtn = document.querySelector('[data-share="facebook"]');
  if (facebookBtn) facebookBtn.addEventListener('click', function () {
    openShare('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl));
  });

  var xBtn = document.querySelector('[data-share="x"]');
  if (xBtn) xBtn.addEventListener('click', function () {
    openShare('https://twitter.com/intent/tweet?text=' + encodeURIComponent(cfg.shareText) + '&url=' + encodeURIComponent(shareUrl));
  });

  var copyBtn = document.querySelector('[data-copy-link]');
  if (copyBtn) copyBtn.addEventListener('click', function () {
    try {
      navigator.clipboard.writeText(shareUrl);
      copyBtn.textContent = cfg.copiedLabel;
      setTimeout(function () { copyBtn.textContent = cfg.copyLabel; }, 2000);
    } catch (e) {}
  });
})();
