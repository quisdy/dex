<?php
session_start();
require_once 'db.php';

// Проверка авторизации и роли администратора
if (!isset($_SESSION['auth']) || $_SESSION['user_login'] !== 'Admin26') {
	header('Location: login.php');
	exit;
}

$success_msg = '';
$error_msg = '';

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
	$order_id = (int) $_POST['order_id'];
	$new_status = (int) $_POST['new_status'];
	if ($new_status >= 1 && $new_status <= 3) {
		$update = mysqli_query($link, "UPDATE orders SET status_id = $new_status WHERE id_order = $order_id");
		if ($update) {
			$success_msg = 'Статус заявки успешно изменён.';
		} else {
			$error_msg = 'Ошибка при изменении статуса.';
		}
	} else {
		$error_msg = 'Некорректный статус.';
	}
}

// Параметры фильтрации, сортировки, пагинации
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$room_filter = isset($_GET['room']) ? $_GET['room'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

// Базовый запрос
$where = [];
if ($status_filter !== 'all') {
	$status_map = ['new' => 1, 'assigned' => 2, 'completed' => 3];
	$status_id = $status_map[$status_filter] ?? 0;
	if ($status_id)
		$where[] = "o.status_id = $status_id";
}
if ($room_filter !== 'all') {
	$where[] = "o.room = '$room_filter'";
}
$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Сортировка
switch ($sort) {
	case 'date_asc':
		$order_sql = "ORDER BY o.date ASC";
		break;
	case 'status':
		$order_sql = "ORDER BY o.status_id";
		break;
	case 'room':
		$order_sql = "ORDER BY o.room";
		break;
	default:
		$order_sql = "ORDER BY o.date DESC";
}

// Общее количество записей для пагинации
$count_result = mysqli_query($link, "SELECT COUNT(*) as cnt FROM orders o $where_sql");
$total_rows = mysqli_fetch_assoc($count_result)['cnt'];
$total_pages = ceil($total_rows / $per_page);

// Основной запрос с данными пользователей
$query = "SELECT o.*, s.name as status_name, u.full_name, u.login as user_login 
          FROM orders o
          LEFT JOIN status s ON o.status_id = s.id
          LEFT JOIN users u ON o.user_id = u.user_id
          $where_sql
          $order_sql
          LIMIT $offset, $per_page";
$result = mysqli_query($link, $query);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
	<title>Панель администратора</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<header class="navbar navbar-expand-md">
		<div class="container">
			<a class="navbar-brand text-light fw-bold" href="#">Конференции.РФ | Админ</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="adminNavbar">
				<ul class="navbar-nav ms-auto">
					<li class="nav-item"><a class="nav-link active" href="admin.php">Панель управления</a></li>
					<li class="nav-item"><a class="nav-link" href="logout.php">Выйти</a></li>
				</ul>
			</div>
		</div>
	</header>

	<main class="container">
		<?php if ($success_msg): ?>
			<div class="alert alert-success d-flex justify-content-between align-items-center p-3 mb-4" role="alert">
				<span>
					<?= htmlspecialchars($success_msg) ?>
				</span>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Закрыть"></button>
			</div>
		<?php elseif ($error_msg): ?>
			<div class="alert alert-danger d-flex justify-content-between align-items-center p-3 mb-4" role="alert">
				<span>
					<?= htmlspecialchars($error_msg) ?>
				</span>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
			</div>
		<?php endif; ?>

		<div class="filter-bar">
			<form method="GET" action="admin.php" id="filterForm">
				<div class="row g-3 align-items-end">
					<div class="col-md-4">
						<label for="statusFilter" class="form-label text-secondary">Фильтр по статусу</label>
						<select class="form-select" id="statusFilter" name="status" onchange="this.form.submit()">
							<option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Все статусы</option>
							<option value="new" <?= $status_filter == 'new' ? 'selected' : '' ?>>Новая</option>
							<option value="assigned" <?= $status_filter == 'assigned' ? 'selected' : '' ?>>Мероприятие назначено</option>
							<option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Мероприятие завершено
							</option>
						</select>
					</div>
					<div class="col-md-4">
						<label for="roomFilter" class="form-label text-secondary">Фильтр по помещению</label>
						<select class="form-select" id="roomFilter" name="room" onchange="this.form.submit()">
							<option value="all" <?= $room_filter == 'all' ? 'selected' : '' ?>>Все помещения</option>
							<option value="Аудитория" <?= $room_filter == 'Аудитория' ? 'selected' : '' ?>>Аудитория</option>
							<option value="Коворкинг" <?= $room_filter == 'Коворкинг' ? 'selected' : '' ?>>Коворкинг</option>
							<option value="Кино-зал" <?= $room_filter == 'Кино-зал' ? 'selected' : '' ?>>Кино-зал</option>
						</select>
					</div>
					<div class="col-md-4">
						<label for="sortOrder" class="form-label text-secondary">Сортировка</label>
						<select class="form-select" id="sortOrder" name="sort" onchange="this.form.submit()">
							<option value="date_asc" <?= $sort == 'date_asc' ? 'selected' : '' ?>>По дате (сначала старые)</option>
							<option value="date_desc" <?= $sort == 'date_desc' ? 'selected' : '' ?>>По дате (сначала новые)</option>
							<option value="status" <?= $sort == 'status' ? 'selected' : '' ?>>По статусу</option>
							<option value="room" <?= $sort == 'room' ? 'selected' : '' ?>>По помещению</option>
						</select>
					</div>
				</div>
				<div class="mt-3 text-end">
					<button type="submit" class="btn btn-outline-secondary btn-sm">Применить фильтры</button>
				</div>
			</form>
		</div>

		<div class="row g-4">
			<?php if (mysqli_num_rows($result) > 0): ?>
				<?php while ($row = mysqli_fetch_assoc($result)):
					$status_class = '';
					if ($row['status_id'] == 1)
						$status_class = 'status-new';
					elseif ($row['status_id'] == 2)
						$status_class = 'status-assigned';
					else
						$status_class = 'status-completed';
					$status_text = htmlspecialchars($row['status_name']);
					$room = htmlspecialchars($row['room']);
					$date = date('d.m.Y', strtotime($row['date']));
					$payment = htmlspecialchars($row['payment_type']);
					$applicant = htmlspecialchars($row['full_name'] . ' (' . $row['user_login'] . ')');
					?>
					<div class="col-md-6 col-lg-4">
						<div class="card admin-card bg-dark text-light border-secondary h-100">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-start">
									<h5 class="card-title">
										<?= $room ?>
									</h5>
									<span class="status-badge <?= $status_class ?>">
										<?= $status_text ?>
									</span>
								</div>
								<p class="card-text text-secondary mt-2">
									<strong>Дата:</strong>
									<?= $date ?><br>
									<strong>Оплата:</strong>
									<?= $payment ?><br>
									<strong>Заявитель:</strong>
									<?= $applicant ?>
								</p>
								<div class="mt-3">
									<label class="form-label text-secondary small">Изменить статус</label>
									<form method="POST" action="admin.php" class="d-flex gap-2">
										<input type="hidden" name="order_id" value="<?= $row['id_order'] ?>">
										<select class="form-select status-select" name="new_status">
											<option value="1" <?= $row['status_id'] == 1 ? 'selected' : '' ?>>Новая</option>
											<option value="2" <?= $row['status_id'] == 2 ? 'selected' : '' ?>>Мероприятие назначено</option>
											<option value="3" <?= $row['status_id'] == 3 ? 'selected' : '' ?>>Мероприятие завершено</option>
										</select>
										<button type="submit" name="update_status" class="btn btn-sm btn-primary">Обновить</button>
									</form>
								</div>
							</div>
						</div>
					</div>
				<?php endwhile; ?>
			<?php else: ?>
				<p class="text-secondary">Нет заявок, соответствующих критериям.</p>
			<?php endif; ?>
		</div>

		<?php if ($total_pages > 1): ?>
			<nav aria-label="Навигация по страницам" class="mt-5">
				<ul class="pagination justify-content-center">
					<?php if ($page > 1): ?>
						<li class="page-item"><a class="page-link"
								href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Назад</a></li>
					<?php else: ?>
						<li class="page-item disabled"><span class="page-link">Назад</span></li>
					<?php endif; ?>

					<?php for ($i = 1; $i <= $total_pages; $i++): ?>
						<li class="page-item <?= $i == $page ? 'active' : '' ?>">
							<a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
								<?= $i ?>
							</a>
						</li>
					<?php endfor; ?>

					<?php if ($page < $total_pages): ?>
						<li class="page-item"><a class="page-link"
								href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Вперёд</a></li>
					<?php else: ?>
						<li class="page-item disabled"><span class="page-link">Вперёд</span></li>
					<?php endif; ?>
				</ul>
			</nav>
		<?php endif; ?>
	</main>

	<footer class="footer">
		<div class="container">
			<p class="text-center small">&copy; 2026 Все права защищены. Конференции.РФ</p>
		</div>
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>