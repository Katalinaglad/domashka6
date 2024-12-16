<?php
$host = 'localhost';
$dbname = 'std';
$user = 'root'; 
$password = 'root'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user'], $_POST['work_type'])) {
    $username = htmlspecialchars($_POST['user']);
    $workType = (int)$_POST['work_type'];
    $stmt = $pdo->prepare("INSERT INTO works (user, work_type, action_date) VALUES (?, ?, NOW())");
    $stmt->execute([$username, $workType]);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $editId = (int)$_POST['edit_id'];
    $username = htmlspecialchars($_POST['edit_user']);
    $workType = (int)$_POST['edit_work_type'];
    $stmt = $pdo->prepare("UPDATE works SET user = ?, work_type = ? WHERE id = ?");
    $stmt->execute([$username, $workType, $editId]);
}
$works = $pdo->query("SELECT w.id, w.user, wl.worktype, w.action_date 
                      FROM works w 
                      JOIN workslist wl ON w.work_type = wl.id 
                      ORDER BY w.id DESC")->fetchAll(PDO::FETCH_ASSOC);

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
            <input type="text" name="user" required>
        </label>
        <label>
            Вид работы:
            <select name="work_type" required>
                <?php foreach ($workTypes as $type): ?>
                    <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['worktype']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Добавить</button>
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
                        <input type="hidden" name="edit_id" value="<?= $work['id'] ?>">
                        <input type="text" name="edit_user" value="<?= htmlspecialchars($work['user']) ?>">
                    </td>
                    <td>
                        <select name="edit_work_type">
                            <?php foreach ($workTypes as $type): ?>
                                <option value="<?= $type['id'] ?>" <?= $type['worktype'] === $work['worktype'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['worktype']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><?= $work['action_date'] ?></td>
                    <td><button type="submit">Сохранить</button></td>
                </form>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
