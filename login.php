<?php
// Configure session cookie path for subdirectory installation
$cookiePath = dirname($_SERVER['PHP_SELF']) . '/';
if ($cookiePath === '//') $cookiePath = '/';
session_set_cookie_params(['path' => $cookiePath]);
session_start(); // Start the session to track user login

header('Content-Type: text/html; charset=utf-8');

include "config.php"; // Include database connection

function ensure_users_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `email` VARCHAR(255) NOT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `role` VARCHAR(50) NOT NULL DEFAULT 'user',
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `is_online` TINYINT(1) NOT NULL DEFAULT 0,
            `last_seen` DATETIME NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uniq_users_email` (`email`),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $hasHash = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'password_hash'");
    $hasHashOk = ($hasHash && mysqli_num_rows($hasHash) > 0);
    if (!$hasHashOk) {
        @mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `password_hash` VARCHAR(255) NOT NULL DEFAULT ''");
        $hasLegacyPassword = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'password'");
        if ($hasLegacyPassword && mysqli_num_rows($hasLegacyPassword) > 0) {
            @mysqli_query($con, "UPDATE `users` SET `password_hash`=`password` WHERE (`password_hash` IS NULL OR `password_hash`='') AND `password` IS NOT NULL AND `password`<>''");
        }
    }

    $hasRole = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'role'");
    if (!($hasRole && mysqli_num_rows($hasRole) > 0)) {
        @mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'user'");
    }
    $hasStatus = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'status'");
    if (!($hasStatus && mysqli_num_rows($hasStatus) > 0)) {
        @mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `status` TINYINT(1) NOT NULL DEFAULT 1");
    }
    $hasIsOnline = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'is_online'");
    if (!($hasIsOnline && mysqli_num_rows($hasIsOnline) > 0)) {
        @mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `is_online` TINYINT(1) NOT NULL DEFAULT 0");
    }
    $hasLastSeen = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'last_seen'");
    if (!($hasLastSeen && mysqli_num_rows($hasLastSeen) > 0)) {
        @mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `last_seen` DATETIME NULL");
    }
    $hasCreatedAt = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'created_at'");
    if (!($hasCreatedAt && mysqli_num_rows($hasCreatedAt) > 0)) {
        @mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }
}

function users_has_legacy_password_column(mysqli $con): bool
{
    $res = @mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'password'");
    return ($res && mysqli_num_rows($res) > 0);
}

if(isset($_POST['submit'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
        $error = "Database connection failed";
    } else {

    $emailEsc = mysqli_real_escape_string($con, $email);
    $passwordEsc = mysqli_real_escape_string($con, $password);

    // 1) Superadmin login (legacy admin table, plain password)
    $resultAdmin = mysqli_query($con, "SELECT email,password FROM `admin` WHERE `email` = '$emailEsc' LIMIT 1");
    if ($resultAdmin && mysqli_num_rows($resultAdmin) > 0) {
        $adminRow = mysqli_fetch_assoc($resultAdmin);
        $adminPass = isset($adminRow['password']) ? (string)$adminRow['password'] : '';
        if ($adminPass !== (string)$password) {
            $error = "E-poçt və ya şifrə yanlışdır!";
        } else {
            session_regenerate_id(true);
            $_SESSION['loggedin'] = true;
            $_SESSION['email'] = $adminRow['email'];
            $_SESSION['role'] = 'superadmin';
            $_SESSION['user_id'] = 'superadmin';

            session_write_close();

            $redirectPath = dirname($_SERVER['PHP_SELF']) . '/';
            header("Location: " . $redirectPath);
            exit;
        }
    }

    // 2) Regular users (users table, hashed password)
    ensure_users_table($con);
    $hasLegacyPasswordCol = users_has_legacy_password_column($con);
    $selectCols = $hasLegacyPasswordCol
        ? "id,email,password_hash,role,status,`password`"
        : "id,email,password_hash,role,status";
    $res2 = mysqli_query($con, "SELECT $selectCols FROM users WHERE email='$emailEsc' LIMIT 1");
    if ($res2 === false) {
        $error = "DB error: " . mysqli_error($con);
    } else {
    $u = $res2 ? mysqli_fetch_assoc($res2) : null;
    if (!$u) {
        $error = "E-poçt və ya şifrə yanlışdır!";
    } else {
        $statusRaw = $u['status'] ?? 0;
        $statusStr = strtolower(trim((string)$statusRaw));
        $isActive = ((string)$statusRaw === '1' || (int)$statusRaw === 1 || $statusStr === 'active' || $statusStr === 'aktiv');
        if (!$isActive) {
            $error = "User deaktivdir";
        } else {
            $hash = (string)($u['password_hash'] ?? '');
            $legacyPass = ($hasLegacyPasswordCol && isset($u['password'])) ? (string)$u['password'] : '';

            $okPass = false;
            $uidTmp = (int)($u['id'] ?? 0);
            if ($hash !== '') {
                $info = password_get_info($hash);
                $isRealHash = is_array($info) && isset($info['algo']) && (int)$info['algo'] !== 0;
                if ($isRealHash) {
                    $okPass = password_verify($password, $hash);
                } elseif (hash_equals($hash, (string)$password)) {
                    $okPass = true;
                    $newHash = password_hash((string)$password, PASSWORD_DEFAULT);
                    $newHashEsc = mysqli_real_escape_string($con, $newHash);
                    if ($uidTmp > 0) {
                        @mysqli_query($con, "UPDATE users SET password_hash='$newHashEsc' WHERE id=$uidTmp");
                    }
                }
            } elseif ($legacyPass !== '' && hash_equals($legacyPass, (string)$password)) {
                $okPass = true;
                $newHash = password_hash((string)$password, PASSWORD_DEFAULT);
                $newHashEsc = mysqli_real_escape_string($con, $newHash);
                if ($uidTmp > 0) {
                    @mysqli_query($con, "UPDATE users SET password_hash='$newHashEsc' WHERE id=$uidTmp");
                }
            }

            if (!$okPass) {
                $error = "E-poçt və ya şifrə yanlışdır!";
            } else {
                $role = (string)($u['role'] ?? 'user');
                if ($role !== 'admin' && $role !== 'user' && $role !== 'superadmin') {
                    $role = 'user';
                }
                $uid = (int)($u['id'] ?? 0);

                session_regenerate_id(true);
                $_SESSION['loggedin'] = true;
                $_SESSION['email'] = (string)$u['email'];
                $_SESSION['role'] = $role;
                $_SESSION['user_id'] = $uid;

                @mysqli_query($con, "UPDATE users SET is_online=1, last_seen=NOW() WHERE id=$uid");

                session_write_close();

                $redirectPath = dirname($_SERVER['PHP_SELF']) . '/';
                header("Location: " . $redirectPath);
                exit;
            }
        }
    }
    }
    }
}
?>

<!DOCTYPE html>
<html lang="az">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Giriş | GRI</title>
    <link rel="shortcut icon" href="/hisabat.in/icon.png" />
  </head>
  <style>
    :root{
      --gri-accent:#fdb714;
      --gri-dark:#0f172a;
      --gri-ink:#0b1220;
      --gri-text:#0f172a;
      --gri-muted:#64748b;
      --gri-border:rgba(148,163,184,.35);
    }
    *{box-sizing:border-box;}
    html,body{height:100%;}
    body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Arial,"Noto Sans","Liberation Sans",sans-serif;background:radial-gradient(1200px 600px at 20% 0%, rgba(124,58,237,.35), transparent 55%), radial-gradient(900px 520px at 90% 20%, rgba(253,183,20,.25), transparent 55%), linear-gradient(180deg, #0b1220 0%, #0f172a 100%);}
    .login-shell{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px;}
    .login-card{width:min(1040px,100%);border-radius:22px;overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.06);backdrop-filter:blur(10px);}
    .login-grid{display:grid;grid-template-columns:1fr 420px;}
    @media (max-width: 980px){.login-grid{grid-template-columns:1fr;}.login-left{display:none;}}

    .login-left{position:relative;padding:46px 46px;background:linear-gradient(135deg, rgba(124,58,237,.92) 0%, rgba(99,102,241,.88) 35%, rgba(253,183,20,.62) 100%);}
    .login-left:before{content:'';position:absolute;inset:-120px -120px auto auto;width:360px;height:360px;border-radius:999px;background:rgba(255,255,255,.18);filter:blur(0px);}
    .login-left:after{content:'';position:absolute;inset:auto -140px -160px auto;width:520px;height:520px;border-radius:999px;background:rgba(15,23,42,.25);}
    .login-left-inner{position:relative;z-index:1;color:#fff;}
    .login-brand{display:flex;align-items:center;gap:12px;margin-bottom:40px;}
    .login-mark{width:46px;height:46px;border-radius:14px;background:rgba(15,23,42,.22);border:1px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-weight:800;letter-spacing:.4px;}
    .login-brand-title{font-weight:800;font-size:18px;line-height:1.1;}
    .login-brand-sub{font-size:12px;opacity:.9;}
    .login-hero-title{font-size:38px;font-weight:900;line-height:1.05;margin:0 0 14px 0;letter-spacing:-.3px;}
    .login-hero-text{margin:0;font-size:14px;max-width:460px;opacity:.95;line-height:1.6;}
    .login-badges{margin-top:26px;display:flex;flex-wrap:wrap;gap:10px;}
    .login-badge{padding:10px 12px;border-radius:14px;background:rgba(15,23,42,.18);border:1px solid rgba(255,255,255,.22);font-weight:700;font-size:12px;backdrop-filter:blur(6px);}
    .login-badge span{opacity:.95;}

    .login-right{background:rgba(255,255,255,.92);padding:44px 44px;}
    @media (max-width: 980px){.login-right{border-radius:22px;}}
    .login-right h3{margin:0 0 6px 0;font-size:18px;font-weight:900;color:var(--gri-text);}
    .login-right p{margin:0 0 18px 0;color:var(--gri-muted);font-weight:600;font-size:13px;}
    .login-alert{border-radius:16px;padding:12px 14px;font-weight:700;font-size:13px;margin-bottom:14px;border:1px solid rgba(244,63,94,.25);background:rgba(244,63,94,.08);color:#b91c1c;}

    .login-form{margin-top:14px;}
    .login-field{margin-bottom:14px;}
    .pw-wrap{position:relative;}
    .login-label{display:block;color:var(--gri-muted);font-weight:800;font-size:12px;margin-bottom:7px;}
    .login-input{width:100%;border-radius:14px;border:1px solid var(--gri-border);padding:12px 14px;font-weight:700;font-size:13px;line-height:20px;min-height:44px;color:var(--gri-text);background:#fff;outline:none;transition:all .15s ease;}
    .login-input.with-toggle{padding-right:48px;}
    .login-input:focus{border-color:rgba(124,58,237,.55);box-shadow:0 0 0 4px rgba(124,58,237,.12);}
    .pw-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);width:38px;height:38px;border-radius:12px;border:1px solid rgba(148,163,184,.35);background:rgba(248,250,252,.9);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .12s ease;}
    .pw-toggle:hover{background:#fff;box-shadow:0 8px 16px rgba(15,23,42,.08);}
    .pw-toggle svg{width:18px;height:18px;fill:none;stroke:#475569;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
    .login-actions{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:10px 0 18px 0;}
    .login-help{color:var(--gri-muted);font-weight:700;font-size:12px;}
    .login-btn{width:100%;border-radius:14px;min-height:44px;padding:12px 14px;border:none;background:linear-gradient(90deg, var(--gri-accent) 0%, #ffd56a 60%, var(--gri-accent) 100%);color:#111827;font-weight:900;letter-spacing:.3px;box-shadow:0 14px 30px rgba(253,183,20,.24);cursor:pointer;transition:transform .12s ease, box-shadow .12s ease;}
    .login-btn:hover{transform:translateY(-1px);box-shadow:0 16px 34px rgba(253,183,20,.32);}
    .login-foot{margin-top:14px;color:var(--gri-muted);font-size:12px;font-weight:700;text-align:center;}
  </style>
  <body>
    <div class="login-shell">
      <div class="login-card">
        <div class="login-grid">
          <div class="login-left">
            <div class="login-left-inner">
              <div class="login-brand">
                <div class="login-mark">GRI</div>
                <div>
                  <div class="login-brand-title">Hesabat Sistemi</div>
                  <div class="login-brand-sub">Giriş və idarəetmə paneli</div>
                </div>
              </div>

              <h1 class="login-hero-title">Xoş gəlmisiniz</h1>
              <p class="login-hero-text">
                Hesabat sisteminə daxil olun və işlərin, müştərilərin, ödənişlərin və anbarın idarəsini sürətli şəkildə aparın.
              </p>

              <div class="login-badges">
                <div class="login-badge"><span>Təhlükəsiz giriş</span></div>
                <div class="login-badge"><span>Rol əsaslı icazələr</span></div>
                <div class="login-badge"><span>Sürətli hesabatlar</span></div>
              </div>
            </div>
          </div>

          <div class="login-right">
            <h3>Hesaba daxil ol</h3>
            <p>E-poçt və şifrənizi daxil edin</p>

            <?php if (isset($error)): ?>
                <div class="login-alert"><?php echo $error; ?></div>
            <?php endif; ?>

            <form class="login-form" action="login.php" method="post">
              <div class="login-field">
                <label class="login-label" for="exampleInputEmail1">E-poçt</label>
                <input type="email" class="login-input" id="exampleInputEmail1" name="email" placeholder="məs: nigar@hesabat.in" required>
              </div>

              <div class="login-field">
                <label class="login-label" for="exampleInputPassword1">Şifrə</label>
                <div class="pw-wrap">
                  <input type="password" class="login-input with-toggle" id="exampleInputPassword1" name="password" placeholder="Şifrənizi daxil edin" required>
                  <button type="button" class="pw-toggle" data-pw-toggle="1" aria-label="Şifrəni göstər">
                    <svg viewBox="0 0 24 24" data-eye="open">
                      <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path>
                      <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"></path>
                    </svg>
                    <svg viewBox="0 0 24 24" data-eye="closed" style="display:none">
                      <path d="M3 3l18 18"></path>
                      <path d="M10.6 10.6a2.5 2.5 0 0 0 3.54 3.54"></path>
                      <path d="M9.88 5.1A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a18.86 18.86 0 0 1-4.2 5.2"></path>
                      <path d="M6.1 6.1C3.6 8.2 2 12 2 12s3.5 7 10 7a10.9 10.9 0 0 0 4.1-.8"></path>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="login-actions">
                <div class="login-help">Daxil olmaq üçün davam edin</div>
              </div>

              <input type="submit" class="login-btn" name="submit" value="Daxil ol">

              <div class="login-foot">© <?php echo date('Y'); ?> GRI</div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script>
      (function(){
        var btn = document.querySelector('[data-pw-toggle]');
        var input = document.getElementById('exampleInputPassword1');
        if (!btn || !input) return;
        btn.addEventListener('click', function(){
          var isHidden = input.type === 'password';
          input.type = isHidden ? 'text' : 'password';
          var openIcon = btn.querySelector('[data-eye="open"]');
          var closedIcon = btn.querySelector('[data-eye="closed"]');
          if (openIcon && closedIcon) {
            openIcon.style.display = isHidden ? 'none' : 'block';
            closedIcon.style.display = isHidden ? 'block' : 'none';
          }
          btn.setAttribute('aria-label', isHidden ? 'Şifrəni gizlət' : 'Şifrəni göstər');
          input.focus();
        });
      })();
    </script>
  </body>
</html>
