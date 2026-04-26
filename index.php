<?php
// ============================================================
//  UPI INTENT PAYMENT PAGE
//  Upload this file to htdocs/ on InfinityFree
//  URL: https://yourname.infinityfreeapp.com/pay.php
// ============================================================

// ---------- CONFIG — EDIT THESE ----------
$VPA          = "paytm.s1h4uwq@pty";   // Your UPI VPA
$MERCHANT     = "Paytm";             // Your merchant/shop name
$AMOUNT       = "1.00";                 // Amount in INR
$NOTE         = "Test Payment";         // Transaction note
// -----------------------------------------

// Generate a unique transaction reference every load
// This is CRITICAL to avoid UPI risk policy errors
$TXN_REF = "TXN" . date("YmdHis") . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

// Build base UPI params
$upi_params = http_build_query([
    "pa" => $VPA,
    "pn" => $MERCHANT,
    "am" => $AMOUNT,
    "cu" => "INR",
    "tn" => $NOTE,
    "tr" => $TXN_REF,
]);

// Generic UPI deep link (works on iOS + some Android)
$upi_url = "upi://pay?" . $upi_params;

// Android Intent URLs (bypass browser, open app directly)
$paytm_intent   = "intent://pay?" . $upi_params . "#Intent;scheme=upi;package=net.one97.paytm;end";
$phonepe_intent = "intent://pay?" . $upi_params . "#Intent;scheme=upi;package=com.phonepe.app;end";
$gpay_intent    = "intent://pay?" . $upi_params . "#Intent;scheme=upi;package=com.google.android.apps.nbu.paisa.user;end";
$bhim_intent    = "intent://pay?" . $upi_params . "#Intent;scheme=upi;package=in.org.npci.upiapp;end";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Pay ₹<?= $AMOUNT ?> — <?= htmlspecialchars($MERCHANT) ?></title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    min-height: 100vh;
    background: #0f0f13;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', system-ui, sans-serif;
    padding: 16px;
  }

  .card {
    background: #1a1a22;
    border: 1px solid #2a2a38;
    border-radius: 20px;
    padding: 32px 24px;
    width: 100%;
    max-width: 360px;
    text-align: center;
    box-shadow: 0 24px 64px rgba(0,0,0,0.5);
  }

  .merchant-icon {
    width: 64px; height: 64px;
    background: linear-gradient(135deg, #6c63ff, #a78bfa);
    border-radius: 18px;
    margin: 0 auto 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
  }

  .merchant-name {
    color: #aaa;
    font-size: 13px;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 6px;
  }

  .amount {
    color: #fff;
    font-size: 42px;
    font-weight: 700;
    letter-spacing: -1px;
    margin-bottom: 4px;
  }

  .note {
    color: #666;
    font-size: 13px;
    margin-bottom: 28px;
  }

  .txn-ref {
    background: #111118;
    border: 1px solid #222;
    border-radius: 8px;
    padding: 8px 12px;
    color: #555;
    font-size: 11px;
    font-family: monospace;
    margin-bottom: 28px;
  }

  .divider {
    color: #333;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 16px;
    position: relative;
  }
  .divider::before, .divider::after {
    content: '';
    display: inline-block;
    width: 60px;
    height: 1px;
    background: #2a2a38;
    vertical-align: middle;
    margin: 0 10px;
  }

  .btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: none;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    color: #fff;
    margin-bottom: 10px;
    transition: opacity 0.15s, transform 0.1s;
    -webkit-tap-highlight-color: transparent;
  }
  .btn:active { opacity: 0.8; transform: scale(0.98); }

  .btn-paytm   { background: #00b9f1; }
  .btn-phonepe { background: #5f259f; }
  .btn-gpay    { background: #1a73e8; }
  .btn-bhim    { background: #f47721; }
  .btn-any     {
    background: transparent;
    border: 1px solid #2a2a38;
    color: #aaa;
    font-size: 13px;
    margin-top: 6px;
  }

  .btn svg { flex-shrink: 0; }

  .status {
    margin-top: 20px;
    padding: 12px;
    border-radius: 10px;
    font-size: 13px;
    display: none;
  }
  .status.success { background: #0a2a1a; color: #4caf50; border: 1px solid #1a4a2a; }
  .status.error   { background: #2a0a0a; color: #f44336; border: 1px solid #4a1a1a; }
  .status.info    { background: #0a1a2a; color: #64b5f6; border: 1px solid #1a2a4a; }

  .upi-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 24px;
    color: #444;
    font-size: 11px;
  }
  .upi-badge img { height: 18px; opacity: 0.4; }

  /* iOS notice */
  .ios-notice {
    background: #1a1a10;
    border: 1px solid #333;
    border-radius: 10px;
    padding: 14px;
    color: #999;
    font-size: 12px;
    line-height: 1.6;
    margin-bottom: 20px;
    display: none;
    text-align: left;
  }
  .ios-notice b { color: #ddd; }

  #android-buttons { display: none; }
  #generic-button  { display: none; }
</style>
</head>
<body>
<div class="card">
  <div class="merchant-icon">🛍️</div>
  <p class="merchant-name"><?= htmlspecialchars($MERCHANT) ?></p>
  <p class="amount">₹<?= $AMOUNT ?></p>
  <p class="note"><?= htmlspecialchars($NOTE) ?></p>
  <div class="txn-ref">Ref: <?= $TXN_REF ?></div>

  <div class="divider">Pay via UPI</div>

  <!-- iOS Notice -->
  <div class="ios-notice" id="ios-notice">
    <b>iOS Users:</b> Tap the button below. If your UPI app doesn't open automatically, open <b>Paytm / PhonePe / GPay</b> manually and use the <b>Scan & Pay</b> or <b>Pay via UPI ID</b> option.<br><br>UPI ID: <b><?= $VPA ?></b>
  </div>

  <!-- Android: App-specific intent buttons -->
  <div id="android-buttons">
    <a class="btn btn-paytm"   id="btn-paytm"   href="<?= htmlspecialchars($paytm_intent) ?>">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
      Pay with Paytm
    </a>
    <a class="btn btn-phonepe" id="btn-phonepe" href="<?= htmlspecialchars($phonepe_intent) ?>">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/></svg>
      Pay with PhonePe
    </a>
    <a class="btn btn-gpay"    id="btn-gpay"    href="<?= htmlspecialchars($gpay_intent) ?>">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/></svg>
      Pay with Google Pay
    </a>
    <a class="btn btn-bhim"    id="btn-bhim"    href="<?= htmlspecialchars($bhim_intent) ?>">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/></svg>
      Pay with BHIM
    </a>
    <button class="btn btn-any" onclick="openAnyUPI()">Open Any UPI App</button>
  </div>

  <!-- iOS / fallback: generic upi:// -->
  <div id="generic-button">
    <a class="btn btn-paytm" href="<?= htmlspecialchars($upi_url) ?>" id="btn-ios-pay">
      Open UPI App to Pay
    </a>
  </div>

  <div class="status" id="status-box"></div>

  <div class="upi-badge">
    🔒 Secured by UPI &nbsp;|&nbsp; BHIM UPI
  </div>
</div>

<script>
// ---- Device Detection ----
const isAndroid = /android/i.test(navigator.userAgent);
const isIOS     = /iphone|ipad|ipod/i.test(navigator.userAgent);
const isMobile  = isAndroid || isIOS;

// Show correct buttons
if (isAndroid) {
  document.getElementById('android-buttons').style.display = 'block';
} else if (isIOS) {
  document.getElementById('generic-button').style.display = 'block';
  document.getElementById('ios-notice').style.display = 'block';
} else {
  // Desktop: show UPI ID to copy
  document.getElementById('generic-button').style.display = 'block';
  showStatus('info', 'Open UPI app on your phone. Pay to UPI ID: <?= $VPA ?>');
}

// ---- Intent launcher with fallback detection ----
function launchIntent(btn) {
  const href = btn.getAttribute('href');
  const start = Date.now();

  // On Android, intent:// URLs open the app directly
  // If the app is not installed, the browser stays — detect that
  window.location.href = href;

  // After 2.5s, if user is still on page, app wasn't installed
  setTimeout(function() {
    if (Date.now() - start < 3000) {
      showStatus('error', 'App not installed or could not open. Try another app below.');
    }
  }, 2500);

  return false;
}

// Attach intent launcher to Android buttons
document.querySelectorAll('#android-buttons .btn').forEach(function(btn) {
  if (btn.id !== 'btn-bhim') {
    btn.addEventListener('click', function(e) {
      // Let the href do its work naturally — no preventDefault
      // Just show a pending message
      setTimeout(function() {
        showStatus('info', 'Returning from app? Verify payment in your UPI app history.');
      }, 1500);
    });
  }
});

// "Any UPI app" button — uses generic upi:// scheme
function openAnyUPI() {
  window.location.href = "<?= $upi_url ?>";
  setTimeout(function() {
    showStatus('info', 'If no app opened, copy UPI ID manually: <?= $VPA ?>');
  }, 2000);
}

function showStatus(type, msg) {
  const box = document.getElementById('status-box');
  box.className = 'status ' + type;
  box.textContent = msg;
  box.style.display = 'block';
}
</script>
</body>
</html>
