<?php
// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'std';
$user = 'root'; 
$password = 'root'; 

try {
    // Устанавливаем соединение с базой данных через PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Устанавливаем режим обработки ошибок
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage()); // Если ошибка, прерываем выполнение и выводим сообщение
}

// Обработка добавления новой работы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user'], $_POST['work_type'])) {
    $username = htmlspecialchars($_POST['user']); // Получаем имя пользователя и экранируем спецсимволы
    $workType = (int)$_POST['work_type']; // Получаем ID типа работы и приводим к целому числу
    $stmt = $pdo->prepare("INSERT INTO works (user, work_type, action_date) VALUES (?, ?, NOW())"); // Подготавливаем SQL-запрос
    $stmt->execute([$username, $workType]); // Выполняем запрос с параметрами
}

// Обработка редактирования существующей работы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $editId = (int)$_POST['edit_id']; // Получаем ID работы
    $username = htmlspecialchars($_POST['edit_user']); // Получаем обновленное имя пользователя
    $workType = (int)$_POST['edit_work_type']; // Получаем обновленный тип работы
    $stmt = $pdo->prepare("UPDATE works SET user = ?, work_type = ? WHERE id = ?"); // Подготавливаем SQL-запрос
    $stmt->execute([$username, $workType, $editId]); // Выполняем запрос
}

// Получаем список работ с названиями типов работ
$works = $pdo->query("SELECT w.id, w.user, wl.worktype, w.action_date 
                      FROM works w 
                      JOIN workslist wl ON w.work_type = wl.id 
                      ORDER BY w.id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Получаем список всех типов работ для выпадающего списка
$workTypes = $pdo->query("SELECT * FROM workslist")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление Работами</title>
</head>
<body>
    <h1>Форма добавления работы</h1>
    <form method="post">
        <label>
            Имя пользователя:
            <input type="text" name="user" required> <!-- Поле ввода имени пользователя -->
        </label>
        <label>
            Вид работы:
            <select name="work_type" required> <!-- Выпадающий список типов работ -->
                <?php foreach ($workTypes as $type): ?>
                    <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['worktype']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Добавить</button> <!-- Кнопка отправки формы -->
    </form>

    <h2>Список работ</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Имя пользователя</th>
            <th>Вид работы</th>
            <th>Дата действия</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($works as $work): ?>
            <tr>
                <form method="post">
                    <td><?= $work['id'] ?></td>
                    <td>
                        <input type="hidden" name="edit_id" value="<?= $work['id'] ?>"> <!-- Скрытое поле с ID работы -->
                        <input type="text" name="edit_user" value="<?= htmlspecialchars($work['user']) ?>"> <!-- Поле редактирования имени -->
                    </td>
                    <td>
                        <select name="edit_work_type"> <!-- Выпадающий список для редактирования типа работы -->
                            <?php foreach ($workTypes as $type): ?>
                                <option value="<?= $type['id'] ?>" <?= $type['worktype'] === $work['worktype'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['worktype']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><?= $work['action_date'] ?></td> <!-- Отображение даты действия -->
                    <td><button type="submit">Сохранить</button></td> <!-- Кнопка сохранения изменений -->
                </form>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
