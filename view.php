<?php
/**
 * view.php — private viewer, only for you.
 * -----------------------------------------------------------
 * Open this at yoursite.com/view.php and enter the password
 * below. IMPORTANT: change SECRET_KEY to your own value before
 * you upload this anywhere — the one here is a random default.
 * -----------------------------------------------------------
 */

define('SECRET_KEY', 'NETpKWZLfiVkVimmjvMd'); // <-- CHANGE THIS

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$providedKey = $_GET['key'] ?? ($_POST['key'] ?? '');
$authorized = hash_equals(SECRET_KEY, (string)$providedKey);

if (!$authorized) {
    ?>
    <!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="robots" content="noindex, nofollow">
      <title>ورود</title>
      <style>
        body{font-family:Tahoma,Arial,sans-serif;background:#2b0f1a;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}
        form{background:#3d1626;padding:32px;border-radius:16px;text-align:center;width:260px;}
        input{padding:10px 14px;border-radius:8px;border:none;font-size:16px;width:100%;box-sizing:border-box;}
        button{margin-top:12px;padding:10px 24px;border-radius:8px;border:none;background:#ff4d78;color:#fff;font-size:15px;cursor:pointer;display:block;width:100%;}
      </style>
    </head>
    <body>
      <form method="get">
        <p>رمز عبور:</p>
        <input type="password" name="key" autofocus>
        <button type="submit">ورود</button>
      </form>
    </body>
    </html>
    <?php
    exit;
}

$file = __DIR__ . '/data/submissions.json';
$entries = [];
if (file_exists($file)) {
    $entries = json_decode(file_get_contents($file), true);
    if (!is_array($entries)) { $entries = []; }
}
$entries = array_reverse($entries); // newest first
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>خلاصه‌ی جواب‌ها</title>
<style>
  body{font-family:Tahoma,Arial,sans-serif;background:#2b0f1a;color:#fdeef2;margin:0;padding:30px 16px;}
  h1{color:#ff8fac;text-align:center;margin-bottom:4px;}
  .count{text-align:center;opacity:.7;font-size:13px;margin-bottom:26px;}
  .empty{text-align:center;opacity:.7;margin-top:60px;}
  .card{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:20px 22px;max-width:640px;margin:0 auto 18px;}
  .card .when{font-size:12px;opacity:.6;margin-bottom:10px;}
  table{width:100%;border-collapse:collapse;}
  td{padding:6px 8px;border-bottom:1px solid rgba(255,255,255,.08);vertical-align:top;}
  td:first-child{font-weight:bold;white-space:nowrap;color:#ffb3c6;width:110px;}
  .stats{margin-top:10px;font-size:13px;opacity:.85;}
</style>
</head>
<body>
<h1>خلاصه‌ی جواب‌ها 💌</h1>
<p class="count"><?php echo count($entries); ?> ثبت</p>
<?php if (empty($entries)): ?>
  <p class="empty">هنوز کسی تا آخر نرفته...</p>
<?php else: foreach ($entries as $e): ?>
  <div class="card">
    <div class="when"><?php echo h($e['receivedAt'] ?? ''); ?></div>
    <table>
      <tr><td>تاریخ 📅</td><td><?php echo h(($e['date'] ?? '') !== '' ? $e['date'] : '—'); ?></td></tr>
      <tr><td>ساعت ⏰</td><td><?php echo h(($e['time'] ?? '') !== '' ? $e['time'] : '—'); ?></td></tr>
      <tr><td>مکان 📍</td><td><?php echo h(($e['location'] ?? '') !== '' ? $e['location'] : '—'); ?></td></tr>
      <tr><td>آدرس</td><td><?php echo h(($e['address'] ?? '') !== '' ? $e['address'] : '—'); ?></td></tr>
    </table>
    <div class="stats">
      دفعات «نه» زدن: مرحله ۱ = <?php echo (int)($e['noCount1'] ?? 0); ?>،
      مرحله ۲ = <?php echo (int)($e['noCount2'] ?? 0); ?>،
      مرحله ۳ = <?php echo (int)($e['noCount3'] ?? 0); ?> —
      <b>مجموع: <?php echo (int)($e['total'] ?? 0); ?></b>
    </div>
  </div>
<?php endforeach; endif; ?>
</body>
</html>
