<?php
// ============================================================
//  RAZORPAY UPI CHECKOUT — Single File
//  Works on Railway / any PHP host
//  No risk errors — Razorpay is whitelisted by all UPI apps
// ============================================================

// ✏️ PASTE YOUR KEYS HERE
$KEY_ID     = "rzp_test_SiAZTGiuDroiRf";   // Your Razorpay Test Key ID
$KEY_SECRET = "jsYgFDnHAzOHS76KxWriat2z";    // Your Razorpay Test Key Secret

$AMOUNT      = 100;           // Amount in PAISE (100 = ₹1)
$CURRENCY    = "INR";
$MERCHANT    = "Audiva Fm Private Limited";
$DESCRIPTION = "Test Payment";

// ---- Create Razorpay Order via API ----
$order = null;
$error = null;

try {
    $ch = curl_init("https://api.razorpay.com/v1/orders");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_USERPWD        => "$KEY_ID:$KEY_SECRET",
        CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS     => json_encode([
            "amount"   => $AMOUNT,
            "currency" => $CURRENCY,
            "receipt"  => "rcpt_" . time(),
        ]),
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $order = json_decode($response, true);
    } else {
        $decoded = json_decode($response, true);
        $error = $decoded['error']['description'] ?? "API error ($httpCode)";
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

$amount_display = number_format($AMOUNT / 100, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="theme-color" content="#0a0a0f">
<title>Pay &#8377;<?= $amount_display ?> &mdash; <?= htmlspecialchars($MERCHANT) ?></title>

<!-- Razorpay Checkout JS — this is the magic that bypasses UPI risk errors -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<style>
* { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }

body {
  min-height: 100vh;
  background: #0a0a0f;
  font-family: -apple-system, 'Segoe UI', sans-serif;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.card {
  background: #13131a;
  border: 1px solid #1e1e2e;
  border-radius: 24px;
  width: 100%;
  max-width: 360px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.6);
}

.card-top {
  padding: 32px 24px 24px;
  text-align: center;
  border-bottom: 1px solid #1e1e2e;
}

.avatar {
  width: 60px; height: 60px;
  background: linear-gradient(135deg, #1a73e8, #4f6ef7);
  border-radius: 18px;
  margin: 0 auto 16px;
  display: flex; align-items: center; justify-content: center;
  font-size: 26px;
  box-shadow: 0 8px 24px rgba(79,110,247,0.25);
}

.merchant { font-size: 15px; font-weight: 700; color: #e0e0ff; margin-bottom: 4px; }
.desc     { font-size: 12px; color: #555; margin-bottom: 22px; }

.amount {
  font-size: 54px; font-weight: 800;
  color: #fff; letter-spacing: -2px; line-height: 1;
}
.amount sup { font-size: 22px; font-weight: 400; color: #666; vertical-align: super; }

.order-id {
  font-family: monospace; font-size: 10px;
  color: #333; margin-top: 12px;
}

.card-body { padding: 24px; }

/* Pay button */
.pay-btn {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #1a73e8, #4f6ef7);
  border: none;
  border-radius: 16px;
  color: #fff;
  font-size: 16px;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  letter-spacing: 0.3px;
  box-shadow: 0 8px 24px rgba(79,110,247,0.3);
  transition: opacity 0.15s, transform 0.1s;
  display: flex; align-items: center; justify-content: center; gap: 10px;
}
.pay-btn:active { opacity: 0.85; transform: scale(0.98); }
.pay-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Test hint */
.test-hint {
  margin-top: 16px;
  background: rgba(245,158,11,0.07);
  border: 1px solid rgba(245,158,11,0.15);
  border-radius: 12px;
  padding: 12px 14px;
}
.test-hint-title { font-size: 10px; color: #f59e0b; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 6px; font-weight: 600; }
.test-hint-row { font-size: 12px; color: #888; margin-bottom: 4px; }
.test-hint-row b { color: #bbb; font-family: monospace; }

/* Error box */
.error-box {
  background: rgba(239,68,68,0.07);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 14px;
  padding: 16px;
  color: #fca5a5;
  font-size: 13px;
  line-height: 1.6;
  text-align: center;
}
.error-box .err-title { font-size: 15px; font-weight: 700; color: #ef4444; margin-bottom: 8px; }

/* Footer */
.footer {
  margin-top: 20px;
  text-align: center;
  color: #333;
  font-size: 11px;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}

/* Spinner */
@keyframes spin { to { transform: rotate(360deg); } }
.spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: none;
}
.pay-btn.loading .spinner { display: block; }
.pay-btn.loading .btn-text { opacity: 0.7; }
</style>
</head>
<body>

<div class="card">
  <div class="card-top">
    <div class="avatar">&#128722;</div>
    <p class="merchant"><?= htmlspecialchars($MERCHANT) ?></p>
    <p class="desc"><?= htmlspecialchars($DESCRIPTION) ?></p>
    <div class="amount"><sup>&#8377;</sup><?= $amount_display ?></div>
    <?php if ($order): ?>
      <p class="order-id">Order: <?= $order['id'] ?></p>
    <?php endif; ?>
  </div>

  <div class="card-body">

    <?php if ($error): ?>
      <!-- API Error -->
      <div class="error-box">
        <p class="err-title">&#9888; API Error</p>
        <p><?= htmlspecialchars($error) ?></p>
        <p style="margin-top:10px; font-size:11px; color:#666;">
          Check your Key ID and Secret in pay.php
        </p>
      </div>

    <?php else: ?>
      <!-- Pay Button -->
      <button class="pay-btn" id="pay-btn" onclick="startPayment()">
        <div class="spinner"></div>
        <span class="btn-text">&#9889; Pay with UPI</span>
      </button>

      <!-- Test credentials hint -->
      <div class="test-hint">
        <p class="test-hint-title">&#127381; Test Mode — Use These</p>
        <p class="test-hint-row">Success UPI ID: <b>success@razorpay</b></p>
        <p class="test-hint-row">Failure UPI ID: <b>failure@razorpay</b></p>
        <p class="test-hint-row">Any UPI PIN: <b>1234</b></p>
      </div>
    <?php endif; ?>

  </div>
</div>

<div class="footer">
  &#128274; Secured by Razorpay &nbsp;&bull;&nbsp; BHIM UPI
</div>

<?php if ($order): ?>
<script>
var ORDER_ID  = "<?= $order['id'] ?>";
var KEY_ID    = "<?= $KEY_ID ?>";
var AMOUNT    = <?= $AMOUNT ?>;
var MERCHANT  = "<?= addslashes($MERCHANT) ?>";

function startPayment() {
  var btn = document.getElementById('pay-btn');
  btn.classList.add('loading');
  btn.disabled = true;

  var options = {
    key:         KEY_ID,
    amount:      AMOUNT,
    currency:    "INR",
    name:        MERCHANT,
    description: "<?= addslashes($DESCRIPTION) ?>",
    order_id:    ORDER_ID,

    // ---- Force UPI as default method ----
    method: {
      upi:        true,
      card:       false,
      netbanking: false,
      wallet:     false,
      emi:        false,
    },

    // Pre-select UPI intent on mobile
    config: {
      display: {
        blocks: {
          upi: { name: "Pay via UPI", instruments: [{ method: "upi" }] }
        },
        sequence: ["block.upi"],
        preferences: { show_default_blocks: false }
      }
    },

    handler: function(response) {
      // Payment successful
      document.body.innerHTML = `
        <div style="min-height:100vh;background:#0a0a0f;display:flex;align-items:center;justify-content:center;padding:20px;">
          <div style="background:#13131a;border:1px solid #1e2e1e;border-radius:24px;padding:40px 24px;max-width:360px;width:100%;text-align:center;">
            <div style="font-size:56px;margin-bottom:16px;">&#10004;&#65039;</div>
            <p style="color:#10b981;font-size:20px;font-weight:700;margin-bottom:8px;">Payment Successful!</p>
            <p style="color:#555;font-size:12px;margin-bottom:20px;">&#8377;${AMOUNT/100} paid successfully</p>
            <div style="background:#0d0d16;border-radius:12px;padding:14px;text-align:left;">
              <p style="font-family:monospace;font-size:11px;color:#444;margin-bottom:4px;">Payment ID</p>
              <p style="font-family:monospace;font-size:12px;color:#888;word-break:break-all;">${response.razorpay_payment_id}</p>
            </div>
          </div>
        </div>`;
    },

    modal: {
      ondismiss: function() {
        btn.classList.remove('loading');
        btn.disabled = false;
      }
    },

    theme: { color: "#4f6ef7" },

    // Prefill (optional)
    prefill: { name: "", email: "", contact: "" }
  };

  var rzp = new Razorpay(options);

  rzp.on('payment.failed', function(response) {
    btn.classList.remove('loading');
    btn.disabled = false;
    alert("Payment failed: " + response.error.description);
  });

  rzp.open();
}
</script>
<?php endif; ?>

</body>
</html>
