(function () {
  const root = document.documentElement;
  const savedTheme = localStorage.getItem('memolog_theme') || 'dark';
  const savedDevice = localStorage.getItem('memolog_device') || 'pc';
  const savedLang = localStorage.getItem('memolog_lang') || 'en';
  root.setAttribute('data-theme', savedTheme);
  root.setAttribute('data-device', savedDevice);
  root.setAttribute('data-lang', savedLang);

  const I18N_FIL = {
    'Overview': 'Buod',
    'Deliveries': 'Mga Hatid',
    'My Deliveries': 'Aking mga Hatid',
    'Riders': 'Mga Rider',
    'Riders & Applications': 'Mga Rider at Aplikasyon',
    'Customers': 'Mga Customer',
    'Reports': 'Mga Ulat',
    'Reports & Earnings': 'Mga Ulat at Kita',
    'My Earnings': 'Aking Kita',
    'My Earnings & Quota': 'Aking Kita at Quota',
    'Settings': 'Mga Setting',
    'Operations Settings': 'Mga Setting ng Operasyon',
    'Sign Out': 'Mag-logout',
    'Collapse': 'Itago',
    'Sign In': 'Mag-sign In',
    'Apply': 'Mag-apply',
    'Apply as a Rider': 'Mag-apply bilang Rider',
    'Submit Application': 'Ipasa ang Aplikasyon',
    'Show Info': 'Ipakita',
    'Email Address': 'Email Address',
    'Gmail Address *': 'Gmail Address *',
    'Password': 'Password',
    'Password *': 'Password *',
    'Confirm Password *': 'Kumpirmahin ang Password *',
    'Full Name *': 'Buong Pangalan *',
    'Mobile Number *': 'Numero ng Mobile *',
    'License / ID Number *': 'Numero ng Lisensya / ID *',
    'Vehicle Type *': 'Uri ng Sasakyan *',
    'Use 6+ characters': 'Gumamit ng 6+ na karakter',
    'Enter your email and password to continue.': 'Ilagay ang iyong email at password upang magpatuloy.',
    'Enter your details below to apply.': 'Ilagay ang iyong mga detalye sa ibaba.',
    'Want to become a rider?': 'Gusto mo bang maging rider?',
    'Apply and get notified on Gmail.': 'Mag-apply at makatanggap ng abiso sa Gmail.',
    'Track deliveries, verify items, and monitor your daily quota in one clean workspace.': 'Subaybayan ang mga hatid, i-verify ang mga item, at tingnan ang iyong pang-araw-araw na quota.',
    'Apply with your Gmail. Once approved, you can verify deliveries and track your daily quota.': 'Mag-apply gamit ang iyong Gmail. Kapag naaprubahan, maaari ka nang mag-verify ng mga hatid at subaybayan ang iyong quota.',
    'RATE / DROP': 'BAYAD / HATID',
    'DAILY QUOTA': 'QUOTA / ARAW',
    'QUICK DEMO LOGIN': 'MABILISANG DEMO LOGIN',
    'Fill out your rider details': 'Punan ang iyong mga detalye',
    'Get approved via Gmail': 'Maaprubahan sa pamamagitan ng Gmail',
    'Verify items and hit your quota': 'I-verify ang mga item at abutin ang quota',
    'Fill Date Today': 'Punan Petsa Ngayon',
    'Filter': 'Salain',
    'Reset': 'I-reset',
    'Clear': 'Burahin',
    'Cancel': 'Kanselahin',
    'Confirm': 'Kumpirmahin',
    'Delivery History': 'Kasaysayan ng Hatid',
    'Date & Time': 'Petsa at Oras',
    'Status': 'Katayuan',
    'Updated By': 'In-update ni',
    'Note': 'Tala',
    'No updates yet.': 'Wala pang mga update.',
    'Operations & verification summary': 'Buod ng operasyon at beripikasyon',
    'Your assigned deliveries & verified earnings': 'Mga nakatalagang hatid at kinita',
    'Manage parcels, rider assignments, and proof verification': 'Pamahalaan ang mga parsela at rider',
    'Update assigned deliveries and submit proof': 'I-update ang mga hatid at i-verify ang item',
    'Track your daily quota and verified delivery payouts': 'Subaybayan ang iyong quota at kinita',
    'Review rider applications, send Gmail approval notices, and manage accounts': 'Suriin ang mga aplikasyon ng rider at pamahalaan ang mga account',
    'Configure per-delivery payout rate, daily rider quota, and Gmail notifications': 'Itakda ang bayad kada hatid, pang-araw-araw na quota, at Gmail'
  };

  const I18N_HTML_EN = {
    login_hero: 'Audit every parcel.<br><span>Verify every peso.</span>',
    reg_hero: 'Join the fleet.<br><span>Earn per drop.</span>'
  };

  const I18N_HTML_FIL = {
    login_hero: 'Suriin ang bawat parsela.<br><span>Tiyakin ang bawat kita.</span>',
    reg_hero: 'Sumali sa fleet.<br><span>Kumita kada hatid.</span>'
  };

  document.addEventListener('DOMContentLoaded', function () {
    const loader = document.getElementById('pageLoader');
    const loaderStatus = document.getElementById('loaderStatus');
    let isSubmittingWithLoader = false;

    function runActionLoader(statusText, doneCallback) {
      if (!loader) {
        doneCallback();
        return;
      }
      const lang = root.getAttribute('data-lang') || 'en';
      const processingLabel = lang === 'fil' ? 'Pinoproseso...' : (statusText || 'Processing...');
      const doneLabel = lang === 'fil' ? 'Tapos na' : 'Confirmed';

      loader.classList.remove('is-done', 'is-hidden');
      if (loaderStatus) loaderStatus.textContent = processingLabel;

      const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      const spinMs = reduced ? 80 : 380;
      const checkMs = reduced ? 120 : 420;

      setTimeout(function () {
        loader.classList.add('is-done');
        if (loaderStatus) loaderStatus.textContent = doneLabel;
        setTimeout(function () {
          doneCallback();
        }, checkMs);
      }, spinMs);
    }

    // Instant Language Switcher (English / Filipino)
    const langBtns = document.querySelectorAll('[data-set-lang]');
    function applyLanguage(lang) {
      root.setAttribute('data-lang', lang);
      localStorage.setItem('memolog_lang', lang);

      langBtns.forEach(function (btn) {
        btn.classList.toggle('on', btn.getAttribute('data-set-lang') === lang);
      });

      document.querySelectorAll('[data-i18n-text]').forEach(function (el) {
        const enText = el.getAttribute('data-i18n-text');
        if (!enText) return;
        el.textContent = (lang === 'fil' && I18N_FIL[enText]) ? I18N_FIL[enText] : enText;
      });

      document.querySelectorAll('[data-i18n-html]').forEach(function (el) {
        const key = el.getAttribute('data-i18n-html');
        if (!key) return;
        if (lang === 'fil' && I18N_HTML_FIL[key]) {
          el.innerHTML = I18N_HTML_FIL[key];
        } else if (I18N_HTML_EN[key]) {
          el.innerHTML = I18N_HTML_EN[key];
        }
      });
    }

    applyLanguage(savedLang);
    langBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        applyLanguage(btn.getAttribute('data-set-lang') || 'en');
      });
    });

    // Device mode switcher (Mobile / Tablet / PC)
    const deviceBtns = document.querySelectorAll('[data-set-device]');
    function syncDeviceButtons(device) {
      deviceBtns.forEach(function (btn) {
        btn.classList.toggle('on', btn.getAttribute('data-set-device') === device);
      });
    }
    syncDeviceButtons(savedDevice);
    deviceBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        const d = btn.getAttribute('data-set-device');
        root.setAttribute('data-device', d);
        localStorage.setItem('memolog_device', d);
        syncDeviceButtons(d);
      });
    });

    // Theme switcher
    const themeBtns = document.querySelectorAll('[data-set-theme]');
    function syncThemeButtons(theme) {
      themeBtns.forEach(function (btn) {
        btn.classList.toggle('on', btn.getAttribute('data-set-theme') === theme);
      });
    }
    syncThemeButtons(savedTheme);
    themeBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        const t = btn.getAttribute('data-set-theme');
        root.setAttribute('data-theme', t);
        localStorage.setItem('memolog_theme', t);
        syncThemeButtons(t);
      });
    });

    // Sidebar collapse
    const shell = document.getElementById('appShell');
    const toggleBtn = document.getElementById('toggleSidebar');
    if (shell && localStorage.getItem('memolog_side_collapsed') === '1') {
      shell.classList.add('c');
    }
    if (shell && toggleBtn) {
      toggleBtn.addEventListener('click', function () {
        shell.classList.toggle('c');
        localStorage.setItem('memolog_side_collapsed', shell.classList.contains('c') ? '1' : '0');
      });
    }

    // Animated quota progress bars
    document.querySelectorAll('.quota-fill[data-progress]').forEach(function (bar) {
      const target = Math.min(100, Math.max(0, parseInt(bar.getAttribute('data-progress'), 10) || 0));
      requestAnimationFrame(function () {
        setTimeout(function () {
          bar.style.width = target + '%';
        }, 80);
      });
    });

    // Smooth numeric stat count-up
    const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!prefersReduced) {
      document.querySelectorAll('[data-count-to]').forEach(function (el) {
        const target = parseInt(el.getAttribute('data-count-to'), 10);
        if (isNaN(target) || target <= 0) return;
        const duration = 500;
        const start = performance.now();
        function step(now) {
          const progress = Math.min(1, (now - start) / duration);
          const eased = 1 - Math.pow(1 - progress, 3);
          el.textContent = String(Math.round(target * eased));
          if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
      });
    }

    // "Fill Date Today" buttons
    function getLocalTodayISO() {
      const now = new Date();
      const y = now.getFullYear();
      const m = String(now.getMonth() + 1).padStart(2, '0');
      const d = String(now.getDate()).padStart(2, '0');
      return y + '-' + m + '-' + d;
    }

    document.querySelectorAll('[data-fill-today]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const form = btn.closest('form') || document;
        const today = getLocalTodayISO();
        form.querySelectorAll('input[type="date"]').forEach(function (input) {
          input.value = today;
          input.classList.remove('date-pulsed');
          void input.offsetWidth;
          input.classList.add('date-pulsed');
        });
        const span = btn.querySelector('span');
        if (span) {
          const prev = span.textContent;
          span.textContent = (root.getAttribute('data-lang') === 'fil') ? 'Napunan na!' : 'Filled!';
          setTimeout(function () { span.textContent = prev; }, 1000);
        }
      });
    });

    // Toggle auth overview panel (smooth close / reopen)
    const authSplit = document.querySelector('.auth-split');
    if (authSplit) {
      if (localStorage.getItem('memolog_auth_aside_hidden') === '1') {
        authSplit.classList.add('aside-hidden');
      }
      document.querySelectorAll('[data-toggle-aside]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const isHidden = authSplit.classList.toggle('aside-hidden');
          localStorage.setItem('memolog_auth_aside_hidden', isHidden ? '1' : '0');
        });
      });
    }

    // Interactive vehicle selector cards
    document.querySelectorAll('.vehicle-tile input[type="radio"]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        document.querySelectorAll('.vehicle-tile').forEach(function (tile) {
          tile.classList.remove('selected');
        });
        const parent = radio.closest('.vehicle-tile');
        if (parent) parent.classList.add('selected');
      });
    });

    // Password show/hide toggle
    document.querySelectorAll('[data-toggle-pw]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const input = document.getElementById(btn.getAttribute('data-toggle-pw'));
        if (!input) return;
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.textContent = isHidden ? 'Hide' : 'Show';
      });
    });

    // Live password strength meter
    const pwInput = document.querySelector('[data-pw-strength]');
    const pwBar = document.getElementById('pwStrengthBar');
    const pwText = document.getElementById('pwStrengthText');
    if (pwInput && pwBar && pwText) {
      pwInput.addEventListener('input', function () {
        const v = pwInput.value;
        let score = 0;
        if (v.length >= 6) score++;
        if (v.length >= 10) score++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
        if (/[0-9]/.test(v) || /[^A-Za-z0-9]/.test(v)) score++;

        const levels = [
          { w: '0%', c: 'transparent', t: 'Use 6+ characters' },
          { w: '28%', c: '#ef4444', t: 'Weak' },
          { w: '55%', c: '#f59e0b', t: 'Fair' },
          { w: '80%', c: '#e85d22', t: 'Good' },
          { w: '100%', c: '#22c55e', t: 'Strong' }
        ];
        const lvl = v.length === 0 ? levels[0] : levels[Math.min(4, Math.max(1, score))];
        pwBar.style.width = lvl.w;
        pwBar.style.background = lvl.c;
        pwText.textContent = lvl.t;
      });
    }

    // One-click demo credentials fill on Login page
    document.querySelectorAll('[data-demo-email]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const emailEl = document.getElementById('login_email');
        const passEl = document.getElementById('login_password');
        if (emailEl) {
          emailEl.value = btn.getAttribute('data-demo-email') || '';
          emailEl.classList.remove('date-pulsed');
          void emailEl.offsetWidth;
          emailEl.classList.add('date-pulsed');
        }
        if (passEl) {
          passEl.value = btn.getAttribute('data-demo-pass') || '';
        }
        if (emailEl) emailEl.focus();
      });
    });

    // DataTables initialization
    if (window.jQuery && jQuery.fn.DataTable) {
      jQuery('.js-datatable').each(function () {
        jQuery(this).DataTable({
          pageLength: 10,
          order: [],
          language: {
            search: '',
            searchPlaceholder: 'Search...'
          }
        });
      });
    }

    // Client-side validation
    function validateForm(form) {
      let valid = true;
      form.querySelectorAll('.js-client-error').forEach(function (el) { el.remove(); });
      form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });

      const fields = form.querySelectorAll('input, select, textarea');
      fields.forEach(function (field) {
        if (field.disabled || field.type === 'hidden') return;
        let errorMsg = '';

        if (field.type === 'checkbox') {
          if (field.hasAttribute('required') && !field.checked) {
            errorMsg = 'Please check this box.';
          }
        } else {
          const val = field.value.trim();
          if (field.hasAttribute('required') && val === '') {
            errorMsg = (field.getAttribute('data-label') || 'This field') + ' is required.';
          } else if (field.type === 'email' && val !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            errorMsg = 'Enter a valid email address.';
          } else if (field.hasAttribute('pattern') && val !== '') {
            const rx = new RegExp('^' + field.getAttribute('pattern') + '$');
            if (!rx.test(val)) {
              errorMsg = field.getAttribute('data-pattern-msg') || 'Invalid format.';
            }
          } else if (field.hasAttribute('minlength') && val !== '') {
            const minLen = parseInt(field.getAttribute('minlength'), 10);
            if (val.length < minLen) {
              errorMsg = 'Must be at least ' + minLen + ' characters.';
            }
          } else if (field.type === 'number' && val !== '' && field.hasAttribute('min')) {
            const minVal = parseFloat(field.getAttribute('min'));
            if (isNaN(parseFloat(val)) || parseFloat(val) < minVal) {
              errorMsg = 'Value must be at least ' + minVal + '.';
            }
          }
        }

        if (errorMsg !== '') {
          valid = false;
          field.classList.add('is-invalid');
          const span = document.createElement('span');
          span.className = 'field-error js-client-error';
          span.textContent = errorMsg;
          field.parentNode.appendChild(span);
        }
      });

      return valid;
    }

    // Bootstrap confirmation modal + action-only loader
    const modalEl = document.getElementById('confirmModal');
    const confirmMsgEl = document.getElementById('confirmModalMessage');
    const confirmTitleEl = document.getElementById('confirmModalTitle');
    const confirmOkBtn = document.getElementById('confirmModalOk');
    const bsModal = (modalEl && window.bootstrap) ? new bootstrap.Modal(modalEl) : null;
    let pendingForm = null;

    document.querySelectorAll('form[data-validate="true"], form[data-confirm], form[data-action-loader]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        if (form.dataset.confirmed === '1' || isSubmittingWithLoader) {
          return;
        }

        if (form.getAttribute('data-validate') === 'true' && !validateForm(form)) {
          e.preventDefault();
          return;
        }

        const confirmMessage = form.getAttribute('data-confirm');
        if (confirmMessage && bsModal) {
          e.preventDefault();
          pendingForm = form;
          confirmTitleEl.textContent = form.getAttribute('data-confirm-title') || 'Confirm Action';
          confirmMsgEl.textContent = confirmMessage;
          bsModal.show();
          return;
        }

        const loaderMsg = form.getAttribute('data-action-loader');
        if (loaderMsg) {
          e.preventDefault();
          isSubmittingWithLoader = true;
          runActionLoader(loaderMsg, function () {
            form.dataset.confirmed = '1';
            form.submit();
          });
        }
      });
    });

    if (confirmOkBtn) {
      confirmOkBtn.addEventListener('click', function () {
        if (pendingForm) {
          const targetForm = pendingForm;
          pendingForm = null;
          if (bsModal) bsModal.hide();
          isSubmittingWithLoader = true;
          runActionLoader('Confirming...', function () {
            targetForm.dataset.confirmed = '1';
            targetForm.submit();
          });
        }
      });
    }
  });
})();
