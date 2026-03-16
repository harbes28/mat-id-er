<?php

session_start();

$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$activeForm = $_SESSION['active_form'] ?? 'login';

session_unset();

function showError($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}

?>

<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggning</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="login-container">
        <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
            <form action="login_register.php" method="post">
                <h2>Logga in</h2>
                <?= showError($errors['login']) ?>
                <input type="email" name="email" placeholder="E-post" required>
                <input type="password" name="password" placeholder="Lösenord" required>
                <button type="submit" name="login">Logga in</button>
                <p>Har du inget konto? <a href="#" onclick="showForm('register-form')">Registrera dig här</a></p>
            </form>
        </div>

        <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
            <form action="login_register.php" method="post">
                <h2>Registrera</h2>
                <?= showError($errors['register']) ?>
                <input type="text" name="namn" placeholder="Namn" required>
                <input type="email" name="email" placeholder="E-post" required>
                <input type="password" name="password" placeholder="Lösenord" required>
                <select name="role" required>
                    <option value="">Välj roll</option>
                    <option value="user">Användare</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" name="register">Registrera</button>
                <p>Har du redan ett konto? <a href="#" onclick="showForm('login-form')">Logga in</a></p>
            </form>
        </div>
    </div>
    <script>
        function showForm(formId) {
            document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
            document.getElementById(formId).classList.add("active");
        }
    </script>
</body>
