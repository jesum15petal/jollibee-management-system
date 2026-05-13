<?php
require_once 'db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin  = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $admin['id'];
            $_SESSION['admin_name']      = $admin['full_name'];
            $_SESSION['admin_role']      = $admin['role'];
            header("Location: index.php");
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jollibee EMS – Admin Login</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Nunito', sans-serif;
    background: #DA251C;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-image: radial-gradient(circle at 20% 50%, rgba(255,180,0,0.15) 0%, transparent 50%),
                      radial-gradient(circle at 80% 20%, rgba(255,255,255,0.05) 0%, transparent 40%);
  }

  .login-wrapper {
    display: flex;
    width: 900px;
    max-width: 95vw;
    min-height: 560px;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(0,0,0,0.35);
  }

  /* Left branding panel */
  .brand-panel {
    flex: 0 0 320px;
    background: #B71C1C;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 32px;
    position: relative;
    overflow: hidden;
  }
  .brand-panel::before {
    content: '';
    position: absolute;
    width: 300px; height: 300px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
    bottom: -80px; right: -80px;
  }
  .brand-panel::after {
    content: '';
    position: absolute;
    width: 200px; height: 200px;
    background: rgba(255,180,0,0.07);
    border-radius: 50%;
    top: -60px; left: -60px;
  }
  .brand-logo {
    width: 130px;
    margin-bottom: 20px;
    filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
    position: relative; z-index: 1;
  }
  .brand-title {
    color: #fff;
    font-size: 28px;
    font-weight: 900;
    letter-spacing: 1px;
    position: relative; z-index: 1;
  }
  .brand-subtitle {
    color: rgba(255,255,255,0.65);
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-top: 8px;
    position: relative; z-index: 1;
  }
  .brand-badge {
    margin-top: 32px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 12px;
    padding: 14px 20px;
    text-align: center;
    position: relative; z-index: 1;
  }
  .brand-badge span {
    color: rgba(255,255,255,0.9);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
  }

  /* Right form panel */
  .form-panel {
    flex: 1;
    background: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 56px 48px;
  }
  .form-panel h2 {
    font-size: 26px;
    font-weight: 900;
    color: #1a1a1a;
    margin-bottom: 6px;
  }
  .form-panel p.subtitle {
    color: #888;
    font-size: 14px;
    margin-bottom: 36px;
  }

  .form-group {
    margin-bottom: 20px;
  }
  .form-group label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #444;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .form-group input {
    width: 100%;
    padding: 13px 16px;
    border: 2px solid #eee;
    border-radius: 12px;
    font-family: 'Nunito', sans-serif;
    font-size: 15px;
    color: #333;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .form-group input:focus {
    border-color: #DA251C;
    box-shadow: 0 0 0 4px rgba(218,37,28,0.08);
  }

  .error-msg {
    background: #fff0f0;
    border: 1px solid #ffcdd2;
    border-radius: 10px;
    padding: 12px 16px;
    color: #c62828;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .btn-login {
    width: 100%;
    padding: 14px;
    background: #DA251C;
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 16px;
    font-weight: 800;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
    margin-top: 8px;
    letter-spacing: 0.5px;
  }
  .btn-login:hover {
    background: #b71c1c;
    box-shadow: 0 6px 20px rgba(218,37,28,0.3);
    transform: translateY(-1px);
  }
  .btn-login:active { transform: translateY(0); }

  .hint {
    margin-top: 24px;
    text-align: center;
    font-size: 12px;
    color: #aaa;
  }

  @media (max-width: 640px) {
    .brand-panel { display: none; }
    .form-panel { padding: 40px 28px; }
  }
</style>
</head>
<body>

<div class="login-wrapper">
  <!-- Brand Panel -->
  <div class="brand-panel">
    <img src="https://upload.wikimedia.org/wikipedia/en/thumb/8/84/Jollibee_2011_logo.svg/1200px-Jollibee_2011_logo.svg.png"
         alt="Jollibee Logo" class="brand-logo"
         onerror="this.style.display='none'">
    <div class="brand-title">Jollibee</div>
    <div class="brand-subtitle">Employee Management</div>
    <div class="brand-badge">
      <span>🔒 Admin Portal Only</span>
    </div>
  </div>

  <!-- Form Panel -->
  <div class="form-panel">
    <h2>Welcome Back! 👋</h2>
    <p class="subtitle">Sign in to access the admin dashboard</p>

    <?php if ($error): ?>
      <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               placeholder="Enter your username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               required autofocus>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn-login">Sign In to Dashboard</button>
    </form>
    
  </div>
</div>

</body>
</html>