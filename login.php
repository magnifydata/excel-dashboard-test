<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Login</title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: #1e293b; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4); width: 350px; }
        h2 { color: #f1f5f9; text-align: center; margin-bottom: 25px; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; color: #94a3b8; font-weight: 600; margin-bottom: 5px; font-size: 14px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #334155; border-radius: 6px; background: #334155; color: #f1f5f9; font-size: 16px; box-sizing: border-box; }
        .login-btn { width: 100%; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 16px; transition: background 0.3s; }
        .login-btn:hover { background: #2563eb; }
        .error-msg { color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 6px; text-align: center; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>SIS Dashboard Login</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <p class="error-msg">Invalid Username or Password.</p>
        <?php endif; ?>
        
        <form action="auth.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">Log In</button>
        </form>
    </div>
</body>
</html>