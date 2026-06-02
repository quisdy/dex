<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['auth'])) {
	header('Location: login.php');
	exit;
}

$user_id = $_SESSION['user_id'];
$msg = '';

// Добавление отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
	$order_id = (int) $_POST['order_id'];
	$comment = trim($_POST['comment']);
	if (!empty($comment)) {
		// Проверяем, что заявка принадлежит пользователю и имеет статус 3 (завершено)
		$check = mysqli_query($link, "SELECT id_order FROM orders WHERE id_order = $order_id AND user_id = $user_id AND status_id = 3");
		if (mysqli_num_rows($check) == 1) {
			// Проверяем, нет ли уже отзыва
			$check_review = mysqli_query($link, "SELECT id_review FROM reviews WHERE order_id = $order_id");
			if (mysqli_num_rows($check_review) == 0) {
				$comment = mysqli_real_escape_string($link, $comment);
				mysqli_query($link, "INSERT INTO reviews (user_id, order_id, comment) VALUES ($user_id, $order_id, '$comment')");
				$msg = '<div class="alert alert-success">Спасибо! Отзыв добавлен.</div>';
			} else {
				$msg = '<div class="alert alert-warning">Отзыв для этой заявки уже оставлен.</div>';
			}
		} else {
			$msg = '<div class="alert alert-danger">Некорректная заявка или статус не позволяет оставить отзыв.</div>';
		}
	} else {
		$msg = '<div class="alert alert-danger">Отзыв не может быть пустым.</div>';
	}
}

// Выборка заявок пользователя
$result = mysqli_query($link, "SELECT o.*, s.name as status_name 
                               FROM orders o 
                               LEFT JOIN status s ON o.status_id = s.id 
                               WHERE o.user_id = $user_id 
                               ORDER BY o.date DESC");
?>
<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
	<title>Личный кабинет</title>
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
					<li class="nav-item"><a class="nav-link active text-secondary" href="my_lk.php">Личный кабинет</a></li>
					<li class="nav-item"><a class="nav-link text-secondary" href="logout.php">Выйти</a></li>
				</ul>
			</div>
		</div>
	</header>

	<main class="container">
		<h2 class="text-light mb-3">Мои заявки</h2>
		<?= $msg ?>

		<div class="applications-grid">
			<?php if (mysqli_num_rows($result) > 0): ?>
				<?php while ($row = mysqli_fetch_assoc($result)): ?>
					<div class="app-card">
						<div class="d-flex justify-content-between align-items-start mb-2">
							<h5 class="text-light mb-0"><?= htmlspecialchars($row['room']) ?></h5>
							<span
								class="status-badge 
														<?= $row['status_id'] == 1 ? 'status-new' : ($row['status_id'] == 2 ? 'status-assigned' : 'status-completed') ?>">
								<?= htmlspecialchars($row['status_name']) ?>
							</span>
						</div>
						<p class="text-secondary mb-1"><strong>Дата:</strong> <?= date('d.m.Y', strtotime($row['date'])) ?></p>
						<p class="text-secondary mb-2"><strong>Оплата:</strong> <?= htmlspecialchars($row['payment_type']) ?></p>

						<?php if ($row['status_id'] == 3): ?>
							<?php
							// Проверяем, есть ли уже отзыв
							$rev_check = mysqli_query($link, "SELECT id_review FROM reviews WHERE order_id = {$row['id_order']}");
							if (mysqli_num_rows($rev_check) == 0): ?>
								<form method="POST" class="review-form mt-2">
									<input type="hidden" name="order_id" value="<?= $row['id_order'] ?>">
									<textarea class="form-control" name="comment" rows="2" placeholder="Ваш отзыв о мероприятии..."
										required></textarea>
									<button type="submit" name="submit_review" class="btn btn-outline-comment w-100 mt-2">Оставить отзыв</button>
								</form>
							<?php else: ?>
								<button class="btn btn-sm btn-outline-secondary w-100 mt-2" disabled>Отзыв уже оставлен</button>
							<?php endif; ?>
						<?php else: ?>
							<button class="btn btn-sm btn-outline-secondary w-100 mt-2" disabled>
								<?= $row['status_id'] == 1 ? 'Ожидает подтверждения' : 'Мероприятие назначено' ?>
							</button>
						<?php endif; ?>
					</div>
				<?php endwhile; ?>
			<?php else: ?>
				<p class="text-secondary">У вас пока нет заявок. <a href="application_form.php">Создайте первую заявку</a>.</p>
			<?php endif; ?>
		</div>

		<div class="text-center mt-5">
			<a href="application_form.php" class="btn btn-primary btn-create w-100 py-2">Создать новую заявку</a>
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