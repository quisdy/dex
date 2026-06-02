<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['auth'])) {
	header('Location: login.php');
	exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
	$room = $_POST['course'];
	$date = $_POST['date'];
	$payment = $_POST['payment'];

	// Преобразование помещения и оплаты в читаемый вид
	$room_map = ['web' => 'Аудитория', 'python' => 'Коворкинг', 'sql' => 'Кино-зал'];
	$payment_map = ['cash' => 'Наличные', 'card' => 'Банковская карта', 'transfer' => 'Перевод по номеру телефона'];

	if (!isset($room_map[$room]))
		$error = 'Выберите помещение';
	elseif (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date))
		$error = 'Дата в формате ДД.ММ.ГГГГ';
	else {
		$parts = explode('.', $date);
		if (!checkdate($parts[1], $parts[0], $parts[2]))
			$error = 'Некорректная дата';
		else
			$date_db = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
	}
	if (!isset($payment_map[$payment]))
		$error = 'Выберите способ оплаты';

	if (empty($error)) {
		$user_id = $_SESSION['user_id'];
		$room_text = $room_map[$room];
		$payment_text = $payment_map[$payment];
		$query = "INSERT INTO orders (user_id, room, date, payment_type, status_id) 
                  VALUES ('$user_id', '$room_text', '$date_db', '$payment_text', 1)";
		if (mysqli_query($link, $query)) {
			$success = 'Заявка успешно создана! Статус: Новая.';
			// Очистка полей формы (опционально)
			$_POST = [];
		} else {
			$error = 'Ошибка БД: ' . mysqli_error($link);
		}
	}
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
	<title>Оформление заявки</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<header class="navbar navbar-expand-md">
		<div class="container">
			<a class="navbar-brand text-light fw-bold" href="#">Конференции.РФ</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="userNavbar">
				<ul class="navbar-nav ms-auto">
					<li class="nav-item"><a class="nav-link text-secondary" href="application_form.php">Оформить заявку</a></li>
					<li class="nav-item"><a class="nav-link text-secondary" href="my_lk.php">Личный кабинет</a></li>
					<li class="nav-item"><a class="nav-link text-secondary" href="logout.php">Выйти</a></li>
				</ul>
			</div>
		</div>
	</header>

	<main class="container">
		<div class="row g-4">
			<div class="col-md-6">
				<div id="carouselExample" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
					<div class="carousel-indicators">
						<button type="button" data-bs-target="#carouselExample" data-bs-slide-to="0" class="active"
							aria-current="true" aria-label="Слайд 1"></button>
						<button type="button" data-bs-target="#carouselExample" data-bs-slide-to="1" aria-label="Слайд 2"></button>
						<button type="button" data-bs-target="#carouselExample" data-bs-slide-to="2" aria-label="Слайд 3"></button>
						<button type="button" data-bs-target="#carouselExample" data-bs-slide-to="3" aria-label="Слайд 4"></button>
					</div>

					<div class="carousel-inner">
						<div class="carousel-item active">
							<div class="d-block w-100 carousel-item-bg">
								<div class="carousel-caption">
									<h5>Система управления мероприятиями</h5>
									<p>Выберите помещение, дату и способ оплаты. Отслеживайте статус
										заявки в личном кабинете.</p>
								</div>
							</div>
						</div>

						<div class="carousel-item">
							<div class="d-block w-100 carousel-item-bg"></div>
							<div class="carousel-caption">
								<h5>Аудитория</h5>
							</div>
						</div>

						<div class="carousel-item">
							<div class="d-block w-100 carousel-item-bg"></div>
							<div class="carousel-caption">
								<h5>Коворкинг</h5>
							</div>
						</div>

						<div class="carousel-item">
							<div class="d-block w-100 carousel-item-bg"></div>
							<div class="carousel-caption">
								<h5>Кино-зал</h5>
							</div>
						</div>
					</div>

					<button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
						<span class="carousel-control-prev-icon" aria-hidden="true"></span>
						<span class="visually-hidden">Предыдущий</span>
					</button>
					<button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
						<span class="carousel-control-next-icon" aria-hidden="true"></span>
						<span class="visually-hidden">Следующий</span>
					</button>
				</div>
			</div>

			<div class="form-card col-md-6">
				<div class="card-body p-4 md-5">
					<h2 class="h3 mb-1 text-light text-center">Заявка</h2>
					<p class="text-secondary mb-4 text-center">Формирование заявки</p>

					<?php if ($error): ?>
						<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
					<?php endif; ?>
					<?php if ($success): ?>
						<div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
					<?php endif; ?>

					<form method="POST">
						<div class="mb-3">
							<label for="course" class="form-label text-light">Помещение</label>
							<select class="form-select" id="course" name="course" required>
								<option selected disabled value>Выберите помещение</option>
								<option value="web">Аудитория</option>
								<option value="python">Коворкинг</option>
								<option value="sql">Кино-зал</option>
							</select>
						</div>
						<div class="mb-3">
							<label for="date" class="form-label text-light">Желаемая дата начала мероприятия</label>
							<input type="text" class="form-control" id="date" name="date" placeholder="ДД.ММ.ГГГГ"
								pattern="\d{2}\.\d{2}\.\d{4}" required>
							<div class="form-text text-secondary">Пример: 25.12.2026</div>
						</div>
						<div class="mb-4">
							<label for="payment" class="form-label text-light">Способ оплаты</label>
							<select class="form-select" id="payment" name="payment" required>
								<option selected disabled value>Выберите способ</option>
								<option value="cash">Наличные</option>
								<option value="card">Банковская карта</option>
								<option value="transfer">Перевод по номеру телефона</option>
							</select>
						</div>
						<button type="submit" name="submit" class="btn btn-primary w-100 py-2">Отправить заявку</button>
					</form>
				</div>
			</div>
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