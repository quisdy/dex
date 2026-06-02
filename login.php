<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['auth'])) {
	// Если уже авторизован, перенаправляем в зависимости от роли
	if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 2) {
		header('Location: admin.php');
	} else {
		header('Location: my_lk.php');
	}
	exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
	$login = trim($_POST['login']);
	$password = $_POST['password'];

	if (!empty($login) && !empty($password)) {
		$query = "SELECT user_id, login, full_name, email, role_id, password 
                  FROM users 
                  WHERE login = '$login' OR email = '$login'";
		$result = mysqli_query($link, $query);

		if (mysqli_num_rows($result) == 0) {
			$error = 'Неверный логин или пароль';
		} else {
			$user = mysqli_fetch_assoc($result);
			if (password_verify($password, $user['password'])) {
				$_SESSION['user_id'] = $user['user_id'];
				$_SESSION['user_login'] = $user['login'];
				$_SESSION['user_full_name'] = $user['full_name'];
				$_SESSION['user_email'] = $user['email'];
				$_SESSION['user_role'] = $user['role_id'];
				$_SESSION['auth'] = true;

				// Перенаправление в зависимости от роли
				if ($user['role_id'] == 2) {
					header('Location: admin.php');
				} else {
					header('Location: my_lk.php');
				}
				exit;
			} else {
				$error = 'Неверный логин или пароль';
			}
		}
	} else {
		$error = 'Заполните все поля';
	}
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
	<title>Авторизация</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<header class="navbar navbar-expand-md">
		<div class="container">
			<a class="navbar-brand" href="#">Конференции.РФ</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#guestNavbar">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="guestNavbar">
				<ul class="navbar-nav ms-auto">
					<li class="nav-item"><a class="nav-link" href="login.php">Вход</a></li>
					<li class="nav-item"><a class="nav-link active" href="registration.php">Регистрация</a></li>
				</ul>
			</div>
		</div>
	</header>

	<main class="card p-4 p-md-5">
		<div class="card-body">
			<h2 class="card-title text-center mb-4">Вход в систему</h2>
			<p class="text-center text-secondary mb-4">Добро пожаловать! Войдите, чтобы продолжить.</p>

			<?php if ($error != ''): ?>
				<div class="alert alert-danger alert-dismissible fade show" role="alert">
					<?php echo htmlspecialchars($error); ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			<?php endif; ?>

			<form method="POST" action="login.php">
				<div class="mb-3">
					<label for="login" class="form-label">Логин или Email</label>
					<input type="text" class="form-control" id="login" name="login" placeholder="ivan2024 или name@example.com"
						required>
					<div class="form-text">Логин или email, указанные при регистрации</div>
				</div>

				<div class="mb-4">
					<label for="password" class="form-label">Пароль</label>
					<input type="password" class="form-control" id="password" name="password" placeholder="Введите пароль"
						required>
				</div>

				<button type="submit" name="submit" class="btn btn-primary w-100 py-2">Войти</button>

				<div class="text-center mt-4">
					<a href="registration.php" class="link-light-custom">Ещё не зарегистрированы? Создать аккаунт</a>
				</div>
			</form>
		</div>
	</main>

	<footer class="footer">
		<div class="container">
			<p class="text-center small">&copy; 2026 Все права защищены. Конференции.РФ</p>
		</div>
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>