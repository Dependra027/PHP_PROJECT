<?php
include('config.php');
include('function.php');
$msg = "";

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle Login
if (isset($_POST['login']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $username = get_safe_value($_POST['username']);
    $password = get_safe_value($_POST['password']);

    $res = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        if (password_verify($password, $row['password'])) {
            $_SESSION['UID'] = $row['id'];
            $_SESSION['UNAME'] = $row['username'];
            $_SESSION['EMAIL'] = $row['email'];
            $_SESSION['UROLE'] = $row['role'];
            redirect($_SESSION['UROLE'] == 'User' ? 'dashboard.php' : 'category.php');
        } else {
            $msg = "Invalid password.";
        }
    } else {
        $msg = "Invalid username.";
    }
}

// Handle Registration
if (isset($_POST['register']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $username = get_safe_value($_POST['username']);
    $email = get_safe_value($_POST['email']);
    $password = get_safe_value($_POST['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $msg = "Password must be at least 8 characters long.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $res = mysqli_query($con, "SELECT * FROM users WHERE username='$username' OR email='$email'");
        if (mysqli_num_rows($res) > 0) {
            $msg = "Username or email already exists.";
        } else {
            mysqli_query($con, "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', 'User')");
            $msg = "Registration successful. Please login.";
            

        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login & Register</title>
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">
    <link href="css/theme.css" rel="stylesheet" media="all">
    <style>
        .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
    <script>
        // Toggle between Login and Register
        function toggleForm(showLogin) {
            document.getElementById('loginForm').style.display = showLogin ? 'block' : 'none';
            document.getElementById('registerForm').style.display = showLogin ? 'none' : 'block';
            document.getElementById('msg').innerText = ''; // Clear messages
        }

        // Toggle Password Visibility
        function togglePassword(inputId, toggleIcon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                toggleIcon.textContent = '🙈';
            } else {
                input.type = 'password';
                toggleIcon.textContent = '👁️';
            }
        }
    </script>
</head>

<body class="animsition">
    <div class="page-wrapper">
        <div class="page-content--bge5">
            <div class="container">
                <div class="login-wrap">
                    <div class="login-content">
                        <div class="login-logo">
                            <a href="#">
                                <img src="images/icon/imagePhp-removebg.png" alt="Logo" style="width: 150px;">
                            </a>
                        </div>
                        <div id="msg" style="color: red; text-align: center;"><?= $msg ?></div>

                        <!-- Login Form -->
                        <div id="loginForm">
                            <form action="" method="post">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input class="au-input au-input--full" type="text" name="username" placeholder="Username" required>
                                </div>
                                <div class="form-group position-relative">
                                    <label>Password</label>
                                    <input id="loginPassword" class="au-input au-input--full" type="password" name="password" placeholder="Password" required>
                                    <span class="toggle-password" onclick="togglePassword('loginPassword', this)">👁️</span>
                                </div>
                                <button class="au-btn au-btn--block au-btn--green m-b-20" type="submit" name="login">Sign In</button>
                                <p>Don't have an account? <a href="#" onclick="toggleForm(false)">Register Here</a></p>
                            </form>
                        </div>

                        <!-- Registration Form -->
                        <div id="registerForm" style="display: none;">
                            <form action="" method="post">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input class="au-input au-input--full" type="text" name="username" placeholder="Username" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input class="au-input au-input--full" type="email" name="email" placeholder="Email" required>
                                </div>
                                <div class="form-group position-relative">
                                    <label>Password</label>
                                    <input id="registerPassword" class="au-input au-input--full" type="password" name="password" placeholder="Password" required>
                                    <span class="toggle-password" onclick="togglePassword('registerPassword', this)">👁️</span>
                                </div>
                                <button class="au-btn au-btn--block au-btn--green m-b-20" type="submit" name="register">Register</button>
                                <p>Already have an account? <a href="#" onclick="toggleForm(true)">Login Here</a></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>
