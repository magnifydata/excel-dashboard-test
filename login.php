<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magnify Data Analytics Login</title>
    <style>
        /* Modern Split-Screen Design Based on Sample Image */
        
        /* Global Styles */
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
            margin: 0; 
            height: 100vh; 
            display: flex;
            overflow: hidden; /* Prevent scroll on the main body */
        }

        /* --- LEFT PANEL: ILLUSTRATION (White Background) --- */
        .left-panel {
            width: 50%;
            background-color: #ffffff; /* White side */
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: #1f2937;
        }

        /* Placeholder for the Illustration */
        .illustration-placeholder {
            /* You would replace this with an <img> tag to load your illustration */
            width: 80%;
            height: 60%; 
            background: #f0f4f8; /* Light gray box as a visual stand-in */
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 14px;
            color: #4b5563;
        }
        
        /* Green Heading for the whole page, outside the form */
        .page-title {
            color: #10b981; /* Green Heading */
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 20px;
        }

        /* --- RIGHT PANEL: LOGIN FORM (Blue Background) --- */
        .right-panel {
            width: 50%;
            background-color: #1976D2; /* Deep Blue Background (Similar to sample) */
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* Login Card Container (White Card on Blue Background) */
        .login-container { 
            background: #ffffff; /* White card */
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15); 
            width: 100%;
            max-width: 350px; 
            box-sizing: border-box;
            text-align: left; /* Align text within the card */
        }
        
        /* Card Heading Styles (Similar to sample) */
        .card-heading h2 {
            color: #1f2937;
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 5px 0;
        }
        .card-heading p {
            color: #4b5563;
            font-size: 16px;
            margin: 0 0 30px 0;
        }
        
        /* Form Groups and Inputs */
        .form-group { margin-bottom: 20px; }

        /* Input Wrappers for Icons */
        .input-wrapper {
            display: flex;
            align-items: center;
            border: 1px solid #d1d5db;
            border-radius: 25px; /* Pill shape for inputs */
            padding: 10px 15px;
            background: #f9fafb;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-wrapper:focus-within {
            border-color: #3b82f6; 
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .input-icon {
            color: #9ca3af; /* Gray icon color */
            margin-right: 10px;
            font-size: 18px; /* Placeholder for a pseudo-icon */
        }
        
        input[type="text"], 
        input[type="password"] { 
            flex-grow: 1;
            padding: 0; 
            border: none; /* Remove individual input border */
            background: transparent; 
            color: #1f2937; 
            font-size: 16px; 
            outline: none;
        }
        
        /* Button (Primary Blue) */
        .login-btn { 
            width: 100%; 
            padding: 14px; 
            background: #3b82f6; /* Blue button (matching the right panel color family) */
            color: white; 
            border: none; 
            border-radius: 25px; /* Pill shape for button */
            font-weight: 600; 
            cursor: pointer; 
            font-size: 16px; 
            transition: background 0.3s, opacity 0.2s; 
            margin-top: 15px;
        }
        
        .login-btn:hover:not([disabled]) { 
            background: #2563eb; 
        }
        
        /* Disabled Button Style */
        .login-btn:disabled {
            background: #9ca3af; 
            cursor: not-allowed;
            opacity: 0.8;
        }

        /* Error Message (Inside white card) */
        .error-msg { 
            color: #991b1b; 
            background: #fee2e2; 
            padding: 12px; 
            border-radius: 8px; 
            text-align: center; 
            margin-bottom: 20px; 
            border: 1px solid #fca5a5;
            font-size: 14px;
        }
        
        /* Footer Link */
        .forgot-password {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #4b5563;
            font-size: 14px;
            text-decoration: none;
        }

        /* --- MEDIA QUERY: MOBILE/TABLET RESTRICTION --- */
        @media (max-width: 768px) {
            body { 
                flex-direction: column; /* Stack panels vertically */
                overflow-y: auto;
            }
            .left-panel, 
            .right-panel {
                width: 100% !important;
                height: 100%;
                min-height: 400px;
                padding: 20px 0;
            }

            /* Hide everything but the restriction message */
            .left-panel,
            .page-title,
            .right-panel {
                display: none !important;
            }
            .restriction-message {
                display: flex !important; /* Force show restriction message */
                justify-content: center;
                align-items: center;
                position: absolute; /* Take over the screen */
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: #f7f8fa;
                flex-direction: column;
            }
        }
        /* ----------------------------------------------- */
    </style>
</head>
<body>
    
    <!-- RESTRICTION MESSAGE (Visible only on small screens) -->
    <div class="restriction-message" style="display:none;">
        <h2>Access Restricted</h2>
        <p>System only accessible via Desktop currently.</p>
        <p style="font-size: 12px; margin-top: 20px; color: #9ca3af;">Please switch to a desktop or laptop computer to log in.</p>
    </div>

    <!-- MAIN CONTENT - Split Screen Layout -->
    
    <!-- LEFT PANEL (White with Illustration/Heading) -->
    <div class="left-panel">
        <h1 class="page-title">Magnify Data Analytics</h1>
        <div class="illustration-placeholder">
            <!-- PLACEHOLDER: Insert Illustration Image Here -->
            Illustration Area
        </div>
    </div>

    <!-- RIGHT PANEL (Blue with Login Form Card) -->
    <div class="right-panel">
        <div class="login-container">
            <div class="card-heading">
                <h2>Hello!</h2>
                <p>Sign In to Get Started</p>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <p class="error-msg">Invalid Username or Password.</p>
            <?php endif; ?>
            
            <form action="auth.php" method="POST">
                
                <!-- Username Field with Icon -->
                <div class="form-group">
                    <div class="input-wrapper">
                        <!-- Font Awesome/SVG Icon Placeholder - Use a character as a simple placeholder -->
                        <span class="input-icon">&#9993;</span> 
                        <input type="text" id="username" name="username" placeholder="Username" onpaste="return false;" required>
                    </div>
                </div>
                
                <!-- Password Field with Icon -->
                <div class="form-group">
                    <div class="input-wrapper">
                        <!-- Font Awesome/SVG Icon Placeholder - Use a character as a simple placeholder -->
                        <span class="input-icon">&#128274;</span> 
                        <input type="password" id="password" name="password" placeholder="Password" onpaste="return false;" required>
                    </div>
                </div>

                <!-- Login Button -->
                <button type="submit" class="login-btn" id="loginBtn" disabled>Log In</button>
                
                <!-- Footer Link (Placeholder) -->
                <a href="#" class="forgot-password">Forgot Password</a>
            </form>
        </div>
    </div>

    <script>
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const loginBtn = document.getElementById('loginBtn');
        const restrictionMessage = document.querySelector('.restriction-message');
        const pageTitle = document.querySelector('.page-title');
        const leftPanel = document.querySelector('.left-panel');
        const rightPanel = document.querySelector('.right-panel');

        function checkInputs() {
            const isUsernameFilled = usernameInput.value.trim().length > 0;
            const isPasswordFilled = passwordInput.value.trim().length > 0;

            if (isUsernameFilled && isPasswordFilled) {
                loginBtn.disabled = false;
            } else {
                loginBtn.disabled = true;
            }
        }
        
        // --- Mobile Restriction JS Handler (Optional but robust) ---
        function checkScreenSize() {
            if (window.innerWidth <= 768) {
                // Show restriction, hide page content
                restrictionMessage.style.display = 'flex';
                pageTitle.style.display = 'none';
                leftPanel.style.display = 'none';
                rightPanel.style.display = 'none';
            } else {
                // Hide restriction, show page content
                restrictionMessage.style.display = 'none';
                pageTitle.style.display = 'block';
                leftPanel.style.display = 'flex';
                rightPanel.style.display = 'flex';
            }
        }
        // -----------------------------------------------------------

        // Attach listeners
        usernameInput.addEventListener('input', checkInputs);
        passwordInput.addEventListener('input', checkInputs);

        // Initial checks
        document.addEventListener('DOMContentLoaded', () => {
            checkInputs();
            checkScreenSize();
        });
        window.addEventListener('resize', checkScreenSize);
    </script>
</body>
</html>