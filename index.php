<?php
session_start();

$dsn = 'mysql:host=localhost;dbname=u68691;charset=utf8';
$username = 'u68691';
$password = '9388506';
try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

$errors = isset($_SESSION['form_errors']) ? $_SESSION['form_errors'] : [];
$values = isset($_SESSION['form_values']) ? $_SESSION['form_values'] : [];
$credentials = isset($_SESSION['credentials']) ? $_SESSION['credentials'] : null;

unset($_SESSION['form_errors']);
unset($_SESSION['form_values']);

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users6 WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT pl.name FROM user_languages6 ul JOIN programming_languages6 pl ON ul.language_id = pl.id WHERE ul.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_languages = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $values = array_merge($user, ['languages' => $user_languages]);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>АвтоПродажа - Продажа автомобилей</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <h1>АвтоПродажа</h1>
        <p>Лучшие автомобили по выгодным ценам</p>
    </header>

    <main>
        <?php if (isset($_SESSION['user_id'])): ?>
            <section class="user-section">
                <h2>Личный кабинет</h2>
                <p><a href="save.php?action=logout">Выйти</a></p>
                
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <div class="success-box">Данные успешно обновлены!</div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-box">
                        <?php foreach ($errors as $field => $message): ?>
                            <p>Ошибка в поле '<?=$field?>': <?=$message?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form action="save.php?action=update" method="POST">
                    <!-- Поля формы остаются такими же, но с автомобильной тематикой -->
                    <div class="form-group">
                        <label for="fio">ФИО:</label>
                        <input type="text" id="fio" name="fio" value="<?= htmlspecialchars($values['fio'] ?? '') ?>">
                    </div>
                    <!-- Остальные поля формы -->
                </form>
            </section>

        <?php elseif (isset($_SESSION['admin_id'])): ?>
            <section class="admin-section">
                <h2>Панель администратора</h2>
                <p><a href="save.php?action=logout">Выйти</a></p>
                <p><a href="admin.php">Управление клиентами</a></p>
            </section>

        <?php else: ?>
            <section class="login-section">
                <h2>Вход для клиентов</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-box">
                        <?php foreach ($errors as $message): ?>
                            <p><?= htmlspecialchars($message) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form action="save.php?action=login" method="POST">
                    <!-- Форма входа -->
                </form>
            </section>

            <section class="cars-section">
                <h2>Наши автомобили</h2>
                <div class="car-list">
                    <div class="car-item">
                        <h3>BMW X5</h3>
                        <p>Год: 2020 | Пробег: 50 000 км</p>
                        <p>Цена: 4 500 000 ₽</p>
                    </div>
                    <!-- Другие автомобили -->
                </div>
            </section>
        <?php endif; ?>
    </main>

    <?php if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])): ?>
        <section class="registration-section">
            <h2>Регистрация нового клиента</h2>
            
            <?php if ($credentials): ?>
                <div class="success-box">
                    <p>Ваши учетные данные:</p>
                    <p>Логин: <?= htmlspecialchars($credentials['login']) ?></p>
                    <p>Пароль: <?= htmlspecialchars($credentials['password']) ?></p>
                </div>
            <?php endif; ?>
            
            <form action="save.php?action=register" method="POST">
                <!-- Форма регистрации (аналогичная оригиналу) -->
            </form>
        </section>
    <?php endif; ?>

    <footer>
        <p>© 2023 АвтоПродажа. Все права защищены.</p>
    </footer>
</body>
</html>
