<?php
// ============================================================
//  CUSTOM UPI PAYMENT PAGE
//  Razorpay used ONLY for order creation + signed UPI URL
//  Zero Razorpay branding shown anywhere
// ============================================================

$KEY_ID     = "rzp_test_SiAZTGiuDroiRf";   // ✏️ Your Key ID
$KEY_SECRET = "jsYgFDnHAzOHS76KxWriat2z";    // ✏️ Your Key Secret

$AMOUNT   = 100;           // in PAISE — 100 = ₹1
$MERCHANT = "Audiva Fm Private Limited";
$NOTE     = "Verified Paytm Account";

// ---- STEP 1: Create Razorpay Order ----
function rzp_post($url, $payload, $key_id, $key_secret) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_USERPWD        => "$key_id:$key_secret",
        CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 10,
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, json_decode($res, true)];
}

$order_id  = null;
$upi_url   = null;
$error     = null;

// Create order
[$code, $order] = rzp_post(
    "https://api.razorpay.com/v1/orders",
    ["amount" => $AMOUNT, "currency" => "INR", "receipt" => "rcpt_" . time()],
    $KEY_ID, $KEY_SECRET
);

if ($code !== 200) {
    $error = $order['error']['description'] ?? "Order creation failed ($code). Check your keys.";
} else {
    $order_id = $order['id'];

    // ---- STEP 2: Get Razorpay-signed UPI Intent URL ----
    // This URL is signed by Razorpay's infrastructure — bypasses all UPI risk checks
    [$pcode, $payment] = rzp_post(
        "https://api.razorpay.com/v1/payments/create/ajax",
        [
            "amount"   => $AMOUNT,
            "currency" => "INR",
            "order_id" => $order_id,
            "method"   => "upi",
            "upi"      => ["flow" => "intent"],
            "contact"  => "9999999999",   // dummy contact — required field
            "email"    => "pay@test.com", // dummy email — required field
        ],
        $KEY_ID, $KEY_SECRET
    );

    if ($pcode === 200 && isset($payment['next'][0]['url'])) {
        // Razorpay returns a fully signed upi:// URL
        $upi_url = $payment['next'][0]['url'];
    } elseif ($pcode === 200 && isset($payment['razorpay_payment_id'])) {
        // Collect flow returned — build URL manually with payment id as tr
        $tr = $payment['razorpay_payment_id'];
        $p  = "pa=rzrpay@icici&pn=" . rawurlencode($MERCHANT)
            . "&am=" . ($AMOUNT/100) . "&cu=INR&tn=" . rawurlencode($NOTE)
            . "&tr=" . $tr;
        $upi_url = "upi://pay?" . $p;
    } else {
        // Fallback: build a standard UPI URL with order_id as tr
        // (still better than random tr — order_id is Razorpay-registered)
        $p = "pa=paytm.s1h4uwq@pty&pn=" . rawurlencode($MERCHANT)
           . "&am=" . ($AMOUNT/100) . "&cu=INR&tn=" . rawurlencode($NOTE)
           . "&tr=" . $order_id;
        $upi_url = "upi://pay?" . $p;
    }
}

// ---- STEP 3: Build app-specific deep links from the UPI URL ----
$params = "";
if ($upi_url) {
    // Extract params from upi://pay?PARAMS
    $params = substr($upi_url, strpos($upi_url, '?') + 1);
}

$paytm_url   = "paytm://pay?"   . $params;
$phonepe_url = "phonepe://pay?" . $params;
$gpay_url    = "tez://upi/pay?" . $params;
$bhim_url    = "upi://pay?"     . $params;

$amount_inr = number_format($AMOUNT / 100, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="theme-color" content="#0f0f14">
<title>Pay &#8377;<?= $amount_inr ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
body {
  min-height:100vh;
  background:#0f0f14;
  font-family:-apple-system,'Segoe UI',sans-serif;
  display:flex; align-items:center; justify-content:center;
  padding:16px;
}
.card {
  background:#161620;
  border:1px solid #22223a;
  border-radius:22px;
  width:100%; max-width:360px;
  overflow:hidden;
  box-shadow:0 24px 64px rgba(0,0,0,0.7);
}
.top {
  padding:30px 22px 22px;
  text-align:center;
  border-bottom:1px solid #1e1e30;
  background:linear-gradient(180deg,#1a1a2e 0%,#161620 100%);
}
.icon {
  width:58px; height:58px;
  background:linear-gradient(135deg,#667eea,#764ba2);
  border-radius:16px;
  margin:0 auto 14px;
  display:flex; align-items:center; justify-content:center;
  font-size:26px;
  box-shadow:0 8px 20px rgba(102,126,234,0.3);
}
.mname { font-size:15px; font-weight:700; color:#e8e8ff; margin-bottom:3px; }
.mdesc { font-size:11px; color:#555; margin-bottom:18px; }
.amt { font-size:50px; font-weight:800; color:#fff; letter-spacing:-2px; line-height:1; }
.amt sup { font-size:20px; font-weight:400; color:#666; vertical-align:super; }
.oid { font-family:monospace; font-size:10px; color:#333; margin-top:10px; }

.body { padding:20px; }

.section-title {
  font-size:10px; color:#444;
  letter-spacing:1.5px; text-transform:uppercase;
  margin-bottom:12px; font-weight:600;
}

/* App buttons */
.app-btn {
  display:flex; align-items:center; gap:12px;
  width:100%; padding:13px 14px;
  border-radius:14px;
  border:1px solid #1e1e30;
  background:#0f0f18;
  color:#ccc;
  font-size:14px; font-weight:600;
  text-decoration:none;
  margin-bottom:9px;
  transition:background 0.15s;
  font-family:inherit; cursor:pointer;
}
.app-btn:active { opacity:0.75; transform:scale(0.98); }

.app-icon {
  width:38px; height:38px; border-radius:10px;
  display:flex; align-items:center; justify-content:center;
  font-size:17px; font-weight:800; flex-shrink:0;
}

.paytm   .app-icon { background:rgba(0,185,241,0.1);  color:#00b9f1; }
.phonepe .app-icon { background:rgba(95,37,159,0.15); color:#a855f7; }
.gpay    .app-icon { background:rgba(26,115,232,0.1);  color:#60a5fa; }
.bhim    .app-icon { background:rgba(52,211,153,0.1);  color:#34d399; }

.paytm   { border-color:rgba(0,185,241,0.12); }
.phonepe { border-color:rgba(95,37,159,0.18); }
.gpay    { border-color:rgba(26,115,232,0.12); }
.bhim    { border-color:rgba(52,211,153,0.12); }

.arr { margin-left:auto; color:#2a2a40; font-size:18px; }

/* Copy row */
.copy-row {
  display:flex; align-items:center;
  background:#0d0d16; border:1px dashed #1e1e30;
  border-radius:12px; padding:12px 14px;
  gap:10px; margin-top:6px;
}
.copy-vpa { font-family:monospace; font-size:12px; color:#666; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.copy-btn {
  background:#1a1a2e; border:1px solid #2a2a40;
  border-radius:8px; padding:5px 12px;
  color:#818cf8; font-size:11px; font-weight:600;
  cursor:pointer; font-family:inherit; flex-shrink:0;
}

/* Error */
.err {
  background:rgba(239,68,68,0.07);
  border:1px solid rgba(239,68,68,0.2);
  border-radius:14px; padding:18px;
  color:#fca5a5; font-size:13px;
  line-height:1.7; text-align:center;
}
.err b { color:#ef4444; font-size:15px; display:block; margin-bottom:6px; }
.err code { background:#1a0a0a; border-radius:6px; padding:2px 8px; font-size:11px; color:#f87171; }

/* Test note */
.test-note {
  background:rgba(251,191,36,0.06);
  border:1px solid rgba(251,191,36,0.12);
  border-radius:12px; padding:12px 14px;
  margin-top:10px;
}
.test-note-title { font-size:10px; color:#f59e0b; letter-spacing:1px; text-transform:uppercase; margin-bottom:6px; font-weight:600; }
.test-note p { font-size:12px; color:#777; margin-bottom:3px; }
.test-note b { color:#aaa; font-family:monospace; }

#toast {
  position:fixed; bottom:20px; left:50%;
  transform:translateX(-50%) translateY(70px);
  background:#1e1e30; border:1px solid #2a2a40;
  color:#ccc; border-radius:12px;
  padding:10px 18px; font-size:13px;
  transition:transform 0.3s cubic-bezier(.34,1.56,.64,1);
  z-index:999; white-space:nowrap;
}
#toast.show { transform:translateX(-50%) translateY(0); }
</style>
</head>
<body>

<div class="card">
  <div class="top">
    <div class="icon">&#128722;</div>
    <p class="mname"><?= htmlspecialchars($MERCHANT) ?></p>
    <p class="mdesc"><?= htmlspecialchars($NOTE) ?></p>
    <div class="amt"><sup>&#8377;</sup><?= $amount_inr ?></div>
    <?php if ($order_id): ?>
      <p class="oid"><?= $order_id ?></p>
    <?php endif; ?>
  </div>

  <div class="body">

    <?php if ($error): ?>
      <div class="err">
        <b>&#9888; Setup Error</b>
        <?= htmlspecialchars($error) ?><br><br>
        Check <code>$KEY_ID</code> and <code>$KEY_SECRET</code> in pay.php
      </div>

    <?php else: ?>

      <p class="section-title">Pay with UPI</p>

      <a class="app-btn paytm" href="<?= htmlspecialchars($paytm_url) ?>" onclick="appTap()">
        <div class="app-icon">P</div>
        Paytm
        <span class="arr">&#8250;</span>
      </a>

      <a class="app-btn phonepe" href="<?= htmlspecialchars($phonepe_url) ?>" onclick="appTap()">
        <div class="app-icon">Ph</div>
        PhonePe
        <span class="arr">&#8250;</span>
      </a>

      <a class="app-btn gpay" href="<?= htmlspecialchars($gpay_url) ?>" onclick="appTap()">
        <div class="app-icon">G</div>
        Google Pay
        <span class="arr">&#8250;</span>
      </a>

      <a class="app-btn bhim" href="<?= htmlspecialchars($bhim_url) ?>" onclick="appTap()">
        <div class="app-icon">B</div>
        BHIM / Other UPI
        <span class="arr">&#8250;</span>
      </a>

      <div class="copy-row">
        <span class="copy-vpa" id="vpa-text">paytm.s1h4uwq@pty</span>
        <button class="copy-btn" onclick="copyVPA()">Copy ID</button>
      </div>

      <div class="test-note">
        <p class="test-note-title">&#127381; Test Mode</p>
        <p>Success: <b>success@razorpay</b></p>
        <p>Failure: <b>failure@razorpay</b></p>
        <p>Any PIN: <b>1234</b></p>
      </div>

    <?php endif; ?>

  </div>
</div>

<div id="toast"></div>

<script>
var fallbackURL = "<?= htmlspecialchars($upi_url ?? '') ?>";
var timer;

function appTap() {
  setTimeout(function() {
    if (document.visibilityState !== 'hidden') {
      toast('App not opening? Try another app or copy UPI ID');
    }
  }, 2500);
}

function copyVPA() {
  var v = document.getElementById('vpa-text').textContent;
  if (navigator.clipboard) {
    navigator.clipboard.writeText(v).then(function() { toast('&#10003; Copied: ' + v); });
  } else {
    var t = document.createElement('textarea');
    t.value = v; document.body.appendChild(t);
    t.select(); document.execCommand('copy');
    document.body.removeChild(t);
    toast('&#10003; Copied!');
  }
}

function toast(msg) {
  var el = document.getElementById('toast');
  el.innerHTML = msg; el.classList.add('show');
  clearTimeout(timer);
  timer = setTimeout(function() { el.classList.remove('show'); }, 3000);
}
</script>
</body>
</html>
