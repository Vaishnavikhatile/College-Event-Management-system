</main>

<footer class="site-footer">
  <div class="wrap footer-content">
    <div class="footer-brand">
      <span class="brand-mark small">C</span>
      <div>
        <strong>COCSIT Event Management System</strong>
        <p>BCA Capstone Project 2026&ndash;27 &bull; College of Computer Science &amp; Information Technology, Latur</p>
      </div>
    </div>
    <div class="footer-links">
      <a href="<?= BASE_URL ?>/index.php">Home</a>
      <a href="<?= BASE_URL ?>/events.php">Browse Events</a>
      <a href="<?= BASE_URL ?>/login.php">Student Portal</a>
      <a href="<?= BASE_URL ?>/admin/login.php">Admin Panel</a>
    </div>
  </div>
</footer>

<!-- Interactive Quick View Modal -->
<div id="quick-view-modal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-box">
    <button type="button" class="modal-close" aria-label="Close modal">&times;</button>
    <div class="modal-header">
      <div class="modal-tags">
        <span id="qv-category" class="category-badge cat-technical">Technical</span>
        <span id="qv-urgency" class="status-pill status-urgent">Upcoming</span>
      </div>
      <h2 id="qv-title" class="modal-title">Event Title</h2>
      <p id="qv-organizer" class="modal-organizer">By Organizer</p>
    </div>
    
    <!-- Modal Live Countdown -->
    <div id="qv-countdown-banner" class="modal-countdown">
      <div class="cd-title">Starts In:</div>
      <div class="countdown-box" id="qv-countdown-box">
        <div class="countdown-unit"><span class="cd-val cd-days">00</span><span class="cd-lbl">Days</span></div>
        <span class="cd-colon">:</span>
        <div class="countdown-unit"><span class="cd-val cd-hours">00</span><span class="cd-lbl">Hours</span></div>
        <span class="cd-colon">:</span>
        <div class="countdown-unit"><span class="cd-val cd-mins">00</span><span class="cd-lbl">Mins</span></div>
        <span class="cd-colon">:</span>
        <div class="countdown-unit"><span class="cd-val cd-secs">00</span><span class="cd-lbl">Secs</span></div>
      </div>
    </div>

    <div class="modal-body">
      <div class="modal-facts">
        <div class="modal-fact-item">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <span id="qv-date">24 Oct 2026</span>
        </div>
        <div class="modal-fact-item">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <span id="qv-time">10:00 AM</span>
        </div>
        <div class="modal-fact-item">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
          <span id="qv-venue">Campus Auditorium</span>
        </div>
      </div>
      <p id="qv-description" class="modal-desc">Description text...</p>
    </div>

    <div class="modal-footer">
      <div class="modal-footer-tools">
        <a id="qv-gcal-link" href="#" target="_blank" rel="noopener" class="btn btn-tiny btn-ghost">Add to G-Cal</a>
        <button type="button" id="qv-copy-share" class="btn btn-tiny btn-ghost">Copy Link</button>
      </div>
      <a id="qv-action-btn" href="#" class="btn btn-gold">View Full Details &rarr;</a>
    </div>
  </div>
</div>

<!-- Interactive Digital Event Pass Modal -->
<div id="pass-modal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-box pass-modal-box">
    <button type="button" class="modal-close" aria-label="Close pass">&times;</button>
    
    <div class="print-actions-top no-print">
      <button type="button" class="btn btn-small btn-gold" onclick="window.print()">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print Entry Pass
      </button>
      <span class="muted small">Or take a screenshot for mobile entry</span>
    </div>

    <!-- Official Styled Digital Ticket Pass -->
    <div class="digital-pass" id="printable-pass">
      <div class="pass-header">
        <div class="pass-college">
          <span class="pass-mark">COCSIT</span>
          <div>
            <h3>COLLEGE OF COMPUTER SCIENCE &amp; IT, LATUR</h3>
            <p>Official Student Event Entry Pass &bull; Session 2026-27</p>
          </div>
        </div>
        <div class="pass-status-stamp">VERIFIED ENTRY</div>
      </div>

      <div class="pass-main">
        <div class="pass-event-details">
          <span id="pass-cat-tag" class="category-badge cat-technical">Technical</span>
          <h2 id="pass-event-name">Event Title</h2>
          <div class="pass-grid">
            <div class="pass-field">
              <span class="field-lbl">Date</span>
              <strong id="pass-date">27 Oct 2026</strong>
            </div>
            <div class="pass-field">
              <span class="field-lbl">Time</span>
              <strong id="pass-time">09:00 AM</strong>
            </div>
            <div class="pass-field full">
              <span class="field-lbl">Venue</span>
              <strong id="pass-venue">Advanced Computer Lab 3</strong>
            </div>
            <div class="pass-field">
              <span class="field-lbl">Student Name</span>
              <strong id="pass-student-name">Rohit Shinde</strong>
            </div>
            <div class="pass-field">
              <span class="field-lbl">Student ID</span>
              <strong id="pass-student-id">COCSIT-STU-0001</strong>
            </div>
            <div class="pass-field">
              <span class="field-lbl">Registration #</span>
              <strong id="pass-reg-id">REG-00012</strong>
            </div>
            <div class="pass-field">
              <span class="field-lbl">Issued On</span>
              <strong id="pass-reg-date">24 Oct 2026</strong>
            </div>
          </div>
        </div>

        <!-- Simulated QR & Barcode Section -->
        <div class="pass-qr-stub">
          <div class="qr-box">
            <!-- Dynamic SVG simulated QR Code -->
            <svg class="qr-svg" viewBox="0 0 100 100" width="90" height="90">
              <rect width="100" height="100" fill="#fff"/>
              <!-- Corner 1 -->
              <rect x="5" y="5" width="26" height="26" fill="#14253A"/>
              <rect x="9" y="9" width="18" height="18" fill="#fff"/>
              <rect x="13" y="13" width="10" height="10" fill="#14253A"/>
              <!-- Corner 2 -->
              <rect x="69" y="5" width="26" height="26" fill="#14253A"/>
              <rect x="73" y="9" width="18" height="18" fill="#fff"/>
              <rect x="77" y="13" width="10" height="10" fill="#14253A"/>
              <!-- Corner 3 -->
              <rect x="5" y="69" width="26" height="26" fill="#14253A"/>
              <rect x="9" y="73" width="18" height="18" fill="#fff"/>
              <rect x="13" y="77" width="10" height="10" fill="#14253A"/>
              <!-- Pattern blocks -->
              <rect x="36" y="8" width="6" height="14" fill="#14253A"/>
              <rect x="46" y="14" width="12" height="6" fill="#14253A"/>
              <rect x="10" y="38" width="14" height="6" fill="#14253A"/>
              <rect x="38" y="38" width="24" height="24" fill="#14253A"/>
              <rect x="44" y="44" width="12" height="12" fill="#fff"/>
              <rect x="48" y="48" width="4" height="4" fill="#14253A"/>
              <rect x="70" y="40" width="8" height="14" fill="#14253A"/>
              <rect x="84" y="48" width="10" height="6" fill="#14253A"/>
              <rect x="38" y="70" width="14" height="6" fill="#14253A"/>
              <rect x="58" y="74" width="8" height="18" fill="#14253A"/>
              <rect x="72" y="72" width="18" height="18" fill="#14253A"/>
            </svg>
          </div>
          <span class="qr-label">SCAN AT ENTRANCE</span>
          <div class="barcode-sim">
            <span class="bar b1"></span><span class="bar b2"></span><span class="bar b1"></span><span class="bar b3"></span>
            <span class="bar b2"></span><span class="bar b1"></span><span class="bar b3"></span><span class="bar b1"></span>
            <span class="bar b2"></span><span class="bar b3"></span><span class="bar b1"></span><span class="bar b2"></span>
          </div>
          <small class="pass-code" id="pass-code-text">COCSIT-PASS-VALID</small>
        </div>
      </div>

      <div class="pass-footer">
        <span>&bull; Please present this pass alongside your student ID card at the venue gate.</span>
        <span>&bull; Non-transferable admission badge.</span>
      </div>
    </div>
  </div>
</div>

<!-- Floating Toast Notification System -->
<div id="toast-container" class="toast-container" aria-live="polite"></div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
