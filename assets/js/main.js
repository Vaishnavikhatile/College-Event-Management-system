/**
 * COCSIT College Event Management System - Interactive Frontend Engine
 */
(function () {
  'use strict';

  // ==========================================
  // 1. Toast Notification System
  // ==========================================
  window.showToast = function (message, type = 'success') {
    var container = document.getElementById('toast-container');
    if (!container) return;

    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML = '<span class="toast-icon">' + (type === 'success' ? '✓' : 'ℹ') + '</span><span>' + message + '</span>';
    container.appendChild(toast);

    setTimeout(function () {
      toast.classList.add('show');
    }, 10);

    setTimeout(function () {
      toast.classList.remove('show');
      setTimeout(function () {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
      }, 300);
    }, 3500);
  };

  // ==========================================
  // 2. Mobile Menu Navigation
  // ==========================================
  var navToggle = document.querySelector('.nav-toggle');
  var navMenu = document.getElementById('nav');
  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
      var isOpen = navMenu.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', isOpen);
      navToggle.classList.toggle('active', isOpen);
    });
  }

  // ==========================================
  // 3. Real-Time Countdown Engine
  // ==========================================
  function initCountdowns() {
    var elements = document.querySelectorAll('[data-countdown]');
    if (!elements.length) return;

    function update() {
      var now = new Date().getTime();
      elements.forEach(function (el) {
        var targetStr = el.dataset.countdown;
        if (!targetStr) return;

        // Parse target date/time
        var targetDate = new Date(targetStr.replace(' ', 'T')).getTime();
        var diff = targetDate - now;

        var dEl = el.querySelector('.cd-days');
        var hEl = el.querySelector('.cd-hours');
        var mEl = el.querySelector('.cd-mins');
        var sEl = el.querySelector('.cd-secs');

        if (diff <= 0) {
          if (dEl) dEl.textContent = '00';
          if (hEl) hEl.textContent = '00';
          if (mEl) mEl.textContent = '00';
          if (sEl) sEl.textContent = '00';
          return;
        }

        var days = Math.floor(diff / (1000 * 60 * 60 * 24));
        var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        var secs = Math.floor((diff % (1000 * 60)) / 1000);

        if (dEl) dEl.textContent = days < 10 ? '0' + days : days;
        if (hEl) hEl.textContent = hours < 10 ? '0' + hours : hours;
        if (mEl) mEl.textContent = mins < 10 ? '0' + mins : mins;
        if (sEl) sEl.textContent = secs < 10 ? '0' + secs : secs;
      });
    }

    update();
    setInterval(update, 1000);
  }
  initCountdowns();

  // ==========================================
  // 4. Hero Live Stats Animated Count-Up
  // ==========================================
  function initStatsCountUp() {
    var statNumbers = document.querySelectorAll('.hero-stat-item .stat-number');
    statNumbers.forEach(function (el) {
      var target = parseInt(el.dataset.count, 10);
      if (isNaN(target)) return;
      var current = 0;
      var increment = Math.ceil(target / 25) || 1;
      var timer = setInterval(function () {
        current += increment;
        if (current >= target) {
          current = target;
          clearInterval(timer);
        }
        el.textContent = current + (el.dataset.count.indexOf('%') > -1 ? '%' : (target > 50 ? '+' : ''));
      }, 40);
    });
  }
  initStatsCountUp();

  // ==========================================
  // 5. Interactive Event Filtering & Search Engine
  // ==========================================
  var grid = document.getElementById('events-grid');
  var searchInput = document.getElementById('live-search-input');
  var searchClearBtn = document.getElementById('search-clear-btn');
  var catPills = document.querySelectorAll('.cat-pill');
  var countText = document.getElementById('results-count-text');
  var noMatchBox = document.getElementById('no-filter-match');
  var resetBtn = document.getElementById('reset-filters-btn');
  var sortSelect = document.getElementById('quick-sort-select');

  var currentCategory = 'all';

  function filterCards() {
    if (!grid) return;
    var cards = grid.querySelectorAll('.ticket');
    var query = (searchInput ? searchInput.value : '').toLowerCase().trim();
    var visibleCount = 0;

    cards.forEach(function (card) {
      var cardCat = (card.dataset.cardCategory || '').toLowerCase();
      var cardName = (card.dataset.cardName || '').toLowerCase();
      var cardVenue = (card.dataset.cardVenue || '').toLowerCase();
      var cardOrg = (card.dataset.cardOrganizer || '').toLowerCase();
      var cardText = card.textContent.toLowerCase();

      var matchCat = currentCategory === 'all' || cardCat === currentCategory.toLowerCase();
      var matchQuery = !query || cardName.indexOf(query) > -1 || cardVenue.indexOf(query) > -1 || cardOrg.indexOf(query) > -1 || cardText.indexOf(query) > -1;

      if (matchCat && matchQuery) {
        card.style.display = '';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (countText) {
      countText.textContent = 'Showing ' + visibleCount + ' matching event' + (visibleCount === 1 ? '' : 's');
    }

    if (noMatchBox) {
      noMatchBox.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    if (searchClearBtn && searchInput) {
      searchClearBtn.style.display = searchInput.value ? 'block' : 'none';
    }
  }

  // Category pill click handlers
  catPills.forEach(function (pill) {
    pill.addEventListener('click', function () {
      catPills.forEach(function (p) { p.classList.remove('active'); });
      pill.classList.add('active');
      currentCategory = pill.dataset.filter || 'all';
      filterCards();
    });
  });

  // Search input typing handler
  if (searchInput) {
    searchInput.addEventListener('input', filterCards);
  }

  // Clear search button
  if (searchClearBtn && searchInput) {
    searchClearBtn.addEventListener('click', function () {
      searchInput.value = '';
      filterCards();
      searchInput.focus();
    });
  }

  // Reset filters button
  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      if (searchInput) searchInput.value = '';
      currentCategory = 'all';
      catPills.forEach(function (p) {
        p.classList.toggle('active', p.dataset.filter === 'all');
      });
      filterCards();
    });
  }

  // Sort dropdown handler
  if (sortSelect && grid) {
    sortSelect.addEventListener('change', function () {
      var val = sortSelect.value;
      var cards = Array.from(grid.querySelectorAll('.ticket'));
      cards.sort(function (a, b) {
        if (val === 'date-asc') {
          return (a.dataset.cardDate || '').localeCompare(b.dataset.cardDate || '');
        } else if (val === 'date-desc') {
          return (b.dataset.cardDate || '').localeCompare(a.dataset.cardDate || '');
        } else if (val === 'name-asc') {
          return (a.dataset.cardName || '').localeCompare(b.dataset.cardName || '');
        }
        return 0;
      });
      cards.forEach(function (card) { grid.appendChild(card); });
    });
  }

  // ==========================================
  // 6. Interactive Quick View Modal
  // ==========================================
  var qvModal = document.getElementById('quick-view-modal');
  var qvTimer = null;

  function openQuickView(data) {
    if (!qvModal || !data) return;

    var catSlug = (data.category || 'technical').toLowerCase().replace(/[^a-z0-9]+/g, '-');
    var catEl = document.getElementById('qv-category');
    if (catEl) {
      catEl.textContent = data.category;
      catEl.className = 'category-badge cat-' + catSlug;
    }

    var urgEl = document.getElementById('qv-urgency');
    if (urgEl) {
      urgEl.textContent = data.is_past ? 'Completed' : (data.is_mine ? 'Registered ✓' : 'Upcoming');
      urgEl.className = 'status-pill ' + (data.is_past ? 'status-past' : (data.is_mine ? 'status-registered' : 'status-urgent'));
    }

    document.getElementById('qv-title').textContent = data.title;
    document.getElementById('qv-organizer').textContent = 'Organized by ' + data.organizer;
    document.getElementById('qv-date').textContent = data.date_fmt;
    document.getElementById('qv-time').textContent = data.time_fmt;
    document.getElementById('qv-venue').textContent = data.venue;
    document.getElementById('qv-description').textContent = data.description;

    var actionBtn = document.getElementById('qv-action-btn');
    if (actionBtn) {
      actionBtn.href = data.url;
      actionBtn.textContent = data.is_mine ? 'View Your Pass →' : 'View Full Details & Register →';
    }

    // Google Calendar URL
    var gcal = document.getElementById('qv-gcal-link');
    if (gcal) {
      var dStr = (data.date || '').replace(/-/g, '');
      var tStr = (data.time || '10:00:00').replace(/:/g, '');
      var start = dStr + 'T' + tStr;
      gcal.href = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=' + encodeURIComponent(data.title) + '&dates=' + start + '/' + start + '&details=' + encodeURIComponent(data.description) + '&location=' + encodeURIComponent(data.venue + ', COCSIT Latur');
    }

    // Share link button
    var shareBtn = document.getElementById('qv-copy-share');
    if (shareBtn) {
      shareBtn.onclick = function () {
        var fullUrl = window.location.origin + data.url;
        if (navigator.clipboard) {
          navigator.clipboard.writeText(fullUrl).then(function () {
            showToast('Event link copied to clipboard!');
          });
        } else {
          showToast('Link: ' + fullUrl);
        }
      };
    }

    // Countdown inside modal
    var cdBox = document.getElementById('qv-countdown-box');
    var cdBanner = document.getElementById('qv-countdown-banner');
    if (cdBox && cdBanner) {
      if (data.is_past) {
        cdBanner.style.display = 'none';
      } else {
        cdBanner.style.display = 'block';
        if (qvTimer) clearInterval(qvTimer);
        var target = new Date(data.date + 'T' + data.time).getTime();
        function tick() {
          var diff = target - new Date().getTime();
          if (diff <= 0) {
            cdBox.querySelector('.cd-days').textContent = '00';
            cdBox.querySelector('.cd-hours').textContent = '00';
            cdBox.querySelector('.cd-mins').textContent = '00';
            cdBox.querySelector('.cd-secs').textContent = '00';
            return;
          }
          var days = Math.floor(diff / (1000 * 60 * 60 * 24));
          var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          var mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
          var secs = Math.floor((diff % (1000 * 60)) / 1000);
          cdBox.querySelector('.cd-days').textContent = days < 10 ? '0' + days : days;
          cdBox.querySelector('.cd-hours').textContent = hours < 10 ? '0' + hours : hours;
          cdBox.querySelector('.cd-mins').textContent = mins < 10 ? '0' + mins : mins;
          cdBox.querySelector('.cd-secs').textContent = secs < 10 ? '0' + secs : secs;
        }
        tick();
        qvTimer = setInterval(tick, 1000);
      }
    }

    qvModal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeQuickView() {
    if (qvModal) {
      qvModal.classList.remove('active');
      document.body.style.overflow = '';
      if (qvTimer) clearInterval(qvTimer);
    }
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.js-quick-view');
    if (btn) {
      e.preventDefault();
      try {
        var data = JSON.parse(btn.dataset.event);
        openQuickView(data);
      } catch (err) {
        console.error('Invalid event data', err);
      }
    }
    if (e.target.matches('.modal-backdrop') || e.target.closest('.modal-close')) {
      closeQuickView();
      closePassModal();
    }
  });

  // ==========================================
  // 7. Interactive Digital Event Pass Modal
  // ==========================================
  var passModal = document.getElementById('pass-modal');

  function openPassModal(dataset) {
    if (!passModal || !dataset) return;

    var catSlug = (dataset.category || 'technical').toLowerCase().replace(/[^a-z0-9]+/g, '-');
    var catTag = document.getElementById('pass-cat-tag');
    if (catTag) {
      catTag.textContent = dataset.category;
      catTag.className = 'category-badge cat-' + catSlug;
    }

    document.getElementById('pass-event-name').textContent = dataset.eventName;
    document.getElementById('pass-date').textContent = dataset.date;
    document.getElementById('pass-time').textContent = dataset.time;
    document.getElementById('pass-venue').textContent = dataset.venue;
    document.getElementById('pass-student-name').textContent = dataset.studentName;
    document.getElementById('pass-student-id').textContent = dataset.studentId;
    document.getElementById('pass-reg-id').textContent = dataset.regId;
    document.getElementById('pass-reg-date').textContent = dataset.regDate;
    document.getElementById('pass-code-text').textContent = dataset.regId + '-COCSIT';

    passModal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closePassModal() {
    if (passModal) {
      passModal.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  document.addEventListener('click', function (e) {
    var passBtn = e.target.closest('.js-open-pass-btn');
    if (passBtn) {
      e.preventDefault();
      openPassModal(passBtn.dataset);
    }
  });

  // Escape key closes modals
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeQuickView();
      closePassModal();
    }
  });

  // ==========================================
  // 8. 1-Click Demo Login Autofill
  // ==========================================
  document.querySelectorAll('.js-demo-fill').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var email = btn.dataset.email;
      var pass = btn.dataset.pass;
      var emailInput = document.getElementById('email');
      var passInput = document.getElementById('password');
      if (emailInput && passInput) {
        emailInput.value = email;
        passInput.value = pass;
        emailInput.classList.add('field-highlight');
        passInput.classList.add('field-highlight');
        setTimeout(function () {
          emailInput.classList.remove('field-highlight');
          passInput.classList.remove('field-highlight');
        }, 800);
        showToast('Demo credentials filled! Click Log in.');
      }
    });
  });

  // ==========================================
  // 9. Share Button Handler
  // ==========================================
  document.querySelectorAll('.js-share-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var url = window.location.origin + (btn.dataset.url || window.location.pathname);
      if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function () {
          showToast('Event link copied to clipboard!');
        });
      } else {
        prompt('Copy event link:', url);
      }
    });
  });

  // ==========================================
  // 10. Existing Standard Helpers (Confirm, Filter, Mark All)
  // ==========================================
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (ev) {
      if (!confirm(f.dataset.confirm)) ev.preventDefault();
    });
  });

  document.querySelectorAll('input[name="confirm"]').forEach(function (c) {
    var p = c.form.querySelector('input[name="password"], input[name="new_password"]');
    if (!p) return;
    c.form.addEventListener('submit', function (ev) {
      if (c.value !== p.value) {
        ev.preventDefault();
        alert('Passwords do not match.');
        c.focus();
      }
    });
  });

  document.querySelectorAll('input[data-filter]').forEach(function (i) {
    i.addEventListener('input', function () {
      var q = i.value.toLowerCase();
      document.querySelectorAll(i.dataset.filter + ' tbody tr').forEach(function (r) {
        r.style.display = r.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
      });
    });
  });

  document.querySelectorAll('[data-mark-all]').forEach(function (b) {
    b.addEventListener('click', function () {
      document.querySelectorAll('input[type=radio][value="' + b.dataset.markAll + '"]').forEach(function (r) {
        r.checked = true;
      });
      showToast('Marked all students as ' + b.dataset.markAll);
    });
  });

})();
