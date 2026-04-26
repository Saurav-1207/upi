<?php
$VPA      = "paytm.s1h4uwq@pty";
$MERCHANT = "Audiva Fm Private Limited";
$AMOUNT   = "1.00";
$NOTE     = "Test";
$TXN_REF  = "ORD" . date("YmdHis") . rand(100, 999);

$p = "pa=" . rawurlencode($VPA)
   . "&pn=" . rawurlencode($MERCHANT)
   . "&am=" . $AMOUNT
   . "&cu=INR"
   . "&tn=" . rawurlencode($NOTE)
   . "&tr=" . $TXN_REF;

$paytm_url   = "paytm://pay?" . $p;
$phonepe_url = "phonepe://pay?" . $p;
$gpay_url    = "tez://upi/pay?" . $p;
$bhim_url    = "upi://pay?" . $p;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Pay Rs.<?= $AMOUNT ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
body { min-height:100vh; background:#0a0a0f; font-family:-apple-system,'Segoe UI',sans-serif; display:flex; align-items:center; justify-content:center; padding:16px; }
.card { background:#13131a; border:1px solid #1e1e2e; border-radius:20px; width:100%; max-width:360px; overflow:hidden; }
.card-header { padding:28px 20px 20px; text-align:center; border-bottom:1px solid #1e1e2e; }
.merchant-name { font-size:16px; font-weight:700; color:#e0e0ff; margin-bottom:4px; }
.vpa { font-size:12px; color:#555; font-family:monospace; margin-bottom:20px; }
.amount { font-size:52px; font-weight:800; color:#fff; letter-spacing:-2px; line-height:1; margin-bottom:6px; }
.amount sup { font-size:22px; font-weight:400; color:#666; vertical-align:super; }
.txn-id { font-size:10px; font-family:monospace; color:#444; margin-top:10px; }
.card-body { padding:20px; }
.label { font-size:10px; color:#555; letter-spacing:1.5px; text-transform:uppercase; margin-bottom:12px; }
.btn { display:flex; align-items:center; gap:14px; width:100%; padding:14px 16px; border-radius:14px; border:1px solid #1e1e2e; background:#0f0f18; color:#ddd; font-size:14px; font-weight:600; text-decoration:none; margin-bottom:10px; cursor:pointer; font-family:inherit; }
.btn:active { opacity:0.75; }
.btn-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.btn-paytm .btn-icon   { background:rgba(0,185,241,0.12); }
.btn-phonepe .btn-icon { background:rgba(95,37,159,0.2); }
.btn-gpay .btn-icon    { background:rgba(26,115,232,0.12); }
.btn-bhim .btn-icon    { background:rgba(239,68,68,0.12); }
.btn-paytm   { border-color:rgba(0,185,241,0.2); }
.btn-phonepe { border-color:rgba(95,37,159,0.25); }
.btn-gpay    { border-color:rgba(26,115,232,0.2); }
.btn-bhim    { border-color:rgba(239,68,68,0.2); }
.btn-arrow { margin-left:auto; color:#333; font-size:18px; }
.manual-box { background:#0d0d16; border:1px dashed #222; border-radius:12px; padding:14px; margin-top:6px; }
.manual-label { font-size:10px; color:#444; letter-spacing:1px; text-transform:uppercase; margin-bottom:6px; }
.manual-row { display:flex; align-items:center; justify-content:space-between; gap:8px; }
.manual-vpa { font-family:monospace; font-size:12px; color:#888; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.copy-btn { background:#1a1a28; border:1px solid #2a2a40; border-radius:8px; padding:6px 12px; color:#7c9eff; font-size:11px; font-weight:600; cursor:pointer; font-family:inherit; flex-shrink:0; }
#toast { position:fixed; bottom:20px; left:50%; transform:translateX(-50%) translateY(80px); background:#1e1e2e; border:1px solid #2a2a40; color:#ccc; border-radius:12px; padding:10px 20px; font-size:13px; transition:transform 0.3s; z-index:999; }
#toast.show { transform:translateX(-50%) translateY(0); }
</style>
</head>
<body>
<div class="card">
  <div class="card-header">
    <p class="merchant-name"><?= htmlspecialchars($MERCHANT) ?></p>
    <p class="vpa"><?= htmlspecialchars($VPA) ?></p>
    <div class="amount"><sup>&#8377;</sup><?= $AMOUNT ?></div>
    <p class="txn-id">Ref: <?= $TXN_REF ?></p>
  </div>
  <div class="card-body">
    <p class="label">Tap to pay with</p>
    <a class="btn btn-paytm" href="<?= htmlspecialchars($paytm_url) ?>">
      <div class="btn-icon">P</div>Paytm<span class="btn-arrow">&#8250;</span>
    </a>
    <a class="btn btn-phonepe" href="<?= htmlspecialchars($phonepe_url) ?>">
      <div class="btn-icon">Ph</div>PhonePe<span class="btn-arrow">&#8250;</span>
    </a>
    <a class="btn btn-gpay" href="<?= htmlspecialchars($gpay_url) ?>">
      <div class="btn-icon">G</div>Google Pay<span class="btn-arrow">&#8250;</span>
    </a>
    <a class="btn btn-bhim" href="<?= htmlspecialchars($bhim_url) ?>">
      <div class="btn-icon">B</div>BHIM / Other<span class="btn-arrow">&#8250;</span>
    </a>
    <div class="manual-box">
      <p class="manual-label">Or pay manually via UPI ID</p>
      <div class="manual-row">
        <span class="manual-vpa"><?= htmlspecialchars($VPA) ?></span>
        <button class="copy-btn" onclick="copyID()">Copy</button>
      </div>
    </div>
  </div>
</div>
<div id="toast"></div>
<script>
document.querySelectorAll('.btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    setTimeout(function() {
      if (document.visibilityState !== 'hidden') {
        showToast('App not installed? Try another or copy UPI ID below');
      }
    }, 2200);
  });
});
function copyID() {
  var id = "<?= $VPA ?>";
  if (navigator.clipboard) {
    navigator.clipboard.writeText(id).then(function() { showToast('Copied: ' + id); });
  } else {
    var t = document.createElement('textarea');
    t.value = id; document.body.appendChild(t);
    t.select(); document.execCommand('copy');
    document.body.removeChild(t);
    showToast('Copied!');
  }
}
var timer;
function showToast(msg) {
  var t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  clearTimeout(timer);
  timer = setTimeout(function() { t.classList.remove('show'); }, 3000);
}
</script>
</body>
</html>
