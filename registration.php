<?php
session_start();
require_once 'db.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
	$login = trim($_POST['login']);
	$password = $_POST['password'];
	$full_name = trim($_POST['full_name']);
	$phone = trim($_POST['phone']);
	$email = trim($_POST['email']);

	// 1. Логин (латиница + цифры, мин 6)
	if (!preg_match('/^[A-Za-z0-9]{6,}$/', $login)) {
		$error = 'Логин должен содержать только латинские буквы и цифры (минимум 6 символов)';
	}
	// 2. Пароль (минимум 8 символов)
	elseif (strlen($password) < 8) {
		$error = 'Пароль должен быть не менее 8 символов';
	}
	// 3. ФИО (три слова, каждое с заглавной буквы)
	elseif (!preg_match('/^[А-ЯЁ][а-яё]+(?:\s[А-ЯЁ][а-яё]+){2}$/u', $full_name)) {
		$error = 'ФИО должно состоять из трёх слов, каждое с заглавной буквы (пример: Иванов Иван Иванович)';
	}
	// 4. Телефон (формат 8(XXX)XXX-XX-XX)
	elseif (!preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
		$error = 'Телефон должен быть в формате 8(XXX)XXX-XX-XX';
	}
	// 5. Email
	elseif (!preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email)) {
		$error = 'Некорректный формат email. Пример: name@domain.ru';
	} else {
		// Проверка уникальности логина и email
		$query = "SELECT user_id FROM users WHERE login = '$login'";
		$res = mysqli_query($link, $query);
		if (mysqli_num_rows($res) > 0) {
			$error = 'Логин уже занят';
		} else {
			$query = "SELECT user_id FROM users WHERE email = '$email'";
			$res = mysqli_query($link, $query);
			if (mysqli_num_rows($res) > 0) {
				$error = 'Email уже зарегистрирован';
			} else {
				$hashed = password_hash($password, PASSWORD_DEFAULT);
				$query = "INSERT INTO users (login, password, full_name, phone, email) 
                          VALUES ('$login', '$hashed', '$full_name', '$phone', '$email')";
				if (mysqli_query($link, $query)) {
					$success = true;
				} else {
					$error = 'Ошибка при регистрации: ' . mysqli_error($link);
				}
			}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
	<title>Регистрация</title>
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
			<h2 class="card-title text-center mb-4">Регистрация</h2>
			<p class="text-center text-secondary mb-4">Добро пожаловать! Зарегистрируйтесь, чтобы продолжить.</p>

			<?php if ($error != ''): ?>
				<div class="alert alert-danger alert-dismissible fade show" role="alert">
					<?php echo htmlspecialchars($error); ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			<?php endif; ?>

			<?php if ($success): ?>
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					Регистрация прошла успешна! Теперь вы можете <a href="login.php" class="link-light-custom">войти</a>.
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			<?php endif; ?>

			<form method="POST">
				<div class="mb-3">
					<label for="login" class="form-label">Логин</label>
					<input type="text" class="form-control" id="login" name="login" placeholder="ivan2024"
						pattern="[A-Za-z0-9]{6,}" title="Только латинские буквы и цифры, минимум 6 символов"
						value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>" required>
					<div class="form-text">Латинские буквы и цифры, от 6 символов.</div>
				</div>

				<div class="mb-3">
					<label for="password" class="form-label">Пароль</label>
					<input type="password" class="form-control" id="password" name="password" placeholder="Введите пароль"
						minlength="8" title="Минимум 8 символов" required>
					<div class="form-text">Не менее 8 символов.</div>
				</div>

				<div class="mb-3">
					<label for="full_name" class="form-label">ФИО</label>
					<input type="text" class="form-control" id="full_name" name="full_name" placeholder="Иванов Иван Иванович"
						pattern="[А-Яа-яЁё\s]+" title="Только русские буквы и пробелы"
						value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
				</div>

				<div class="mb-3">
					<label for="phone" class="form-label">Телефон</label>
					<input type="tel" class="form-control" id="phone" name="phone" placeholder="8(999)123-45-67"
						pattern="8\(\d{3}\)\d{3}-\d{2}-\d{2}" title="Формат: 8(XXX)XXX-XX-XX"
						value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
					<div class="form-text">Пример: 8(999)123-45-67</div>
				</div>

				<div class="mb-4">
					<label for="email" class="form-label">Электронная почта</label>
					<input type="email" class="form-control" id="email" name="email" placeholder="name@example.com"
						value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
				</div>

				<button type="submit" name="submit" class="btn btn-primary w-100 py-2">Зарегистрироваться</button>

				<div class="text-center mt-4">
					<a href="login.php" class="link-light-custom">Уже есть аккаунт? Войти</a>
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