<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit();
}

// Подключение к базе данных
$dsn = 'mysql:host=localhost;dbname=u68691;charset=utf8';
$username = 'u68691';
$password = '9388506';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Обработка добавления объявления
if (isset($_POST['add_car'])) {    
    $title = $_POST['title'];
    $description = $_POST['description'];
    $year = $_POST['year'];
    $engine = $_POST['engine'];
    $power = $_POST['power'];
    $price = $_POST['price'];
    $user_id = $_SESSION['user_id'];
    
    // Обработка загрузки изображения
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO cars (user_id, title, description, year, engine, power, price, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $description, $year, $engine, $power, $price, $image_path]);
    
    header("Location: profile.php");
    exit();
}

// Получение объявлений пользователя
$stmt = $pdo->prepare("SELECT * FROM cars WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$user_cars = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>T&B - Продажа эксклюзивных автомобилей</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
        }
        
        header {
            background-color: #000;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            flex-grow: 1;
            text-align: center;
        }
        
        .auth-buttons {
            display: flex;
            gap: 15px;
        }
        
        .auth-btn {
            background-color: transparent;
            color: white;
            border: 1px solid white;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .auth-btn:hover {
            background-color: white;
            color: black;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 200;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .close-btn {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }
        
        h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        input, textarea, select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .submit-btn {
            background-color: #000;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #333;
        }
        
        .error {
            color: red;
            font-size: 14px;
            margin-top: -10px;
            margin-bottom: 10px;
        }
        
        .cars-container {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .car-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .car-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .car-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .car-info {
            padding: 20px;
        }
        
        .car-title {
            font-size: 22px;
            margin-bottom: 10px;
            color: #222;
        }
        
        .car-description {
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .car-specs {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .spec-item {
            text-align: center;
        }
        
        .spec-value {
            font-weight: bold;
            color: #000;
        }
        
        .spec-label {
            font-size: 12px;
            color: #888;
        }
        
        .user-photos {
            padding: 40px 20px;
            background-color: #eee;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        .user-photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .user-photo {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            transition: transform 0.3s;
        }
        
        .user-photo:hover {
            transform: scale(1.03);
        }
        
        .profile-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .add-car-btn {
            background-color: #000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .add-car-btn:hover {
            background-color: #333;
        }
        
        .add-car-form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .price {
            color: #d00;
            font-weight: bold;
            font-size: 20px;
        }
        
        .author {
            color: #666;
            font-style: italic;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">T&B</div>
        <div class="auth-buttons">
            <?php if (isset($_SESSION['logged_in'])): ?>
                <a href="profile.php" class="auth-btn">Профиль</a>
                <a href="?logout" class="auth-btn">Выйти</a>
            <?php else: ?>
                <button class="auth-btn" id="loginBtn">Вход</button>
                <button class="auth-btn" id="registerBtn">Регистрация</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- Модальное окно входа -->
    <div class="modal" id="loginModal">
        <div class="modal-content">
            <span class="close-btn" id="closeLogin">&times;</span>
            <h2>Вход</h2>
            <?php if (isset($login_error)): ?>
                <div class="error"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form id="loginForm" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <button type="submit" name="login" class="submit-btn">Войти</button>
            </form>
        </div>
    </div>

    <!-- Модальное окно регистрации -->
    <div class="modal" id="registerModal">
        <div class="modal-content">
            <span class="close-btn" id="closeRegister">&times;</span>
            <h2>Регистрация</h2>
            <?php if (isset($register_error)): ?>
                <div class="error"><?php echo $register_error; ?></div>
            <?php endif; ?>
            <form id="registerForm" method="POST">
                <input type="text" name="name" placeholder="Имя" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <input type="password" name="confirm_password" placeholder="Подтвердите пароль" required>
                <button type="submit" name="register" class="submit-btn">Зарегистрироваться</button>
            </form>
        </div>
    </div>

    <?php if (basename($_SERVER['PHP_SELF']) == 'profile.php'): ?>
        <!-- Страница профиля -->
        <div class="profile-container">
            <div class="profile-header">
                <h1>Ваши объявления</h1>
                <button class="add-car-btn" id="showAddCarForm">Добавить автомобиль</button>
            </div>
            
            <div class="add-car-form" id="addCarForm" style="display: none;">
                <h2>Добавить новое объявление</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Название автомобиля</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Описание</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="year">Год выпуска</label>
                        <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="engine">Двигатель</label>
                        <input type="text" id="engine" name="engine" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="power">Мощность (л.с.)</label>
                        <input type="number" id="power" name="power" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Цена ($)</label>
                        <input type="number" id="price" name="price" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Фотография автомобиля</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </div>
                    
                    <button type="submit" name="add_car" class="submit-btn">Опубликовать</button>
                </form>
            </div>
            
            <div class="car-grid">
                <?php foreach ($user_cars as $car): ?>
                    <div class="car-card">
                        <?php if ($car['image_path']): ?>
                            <img src="<?php echo $car['image_path']; ?>" alt="<?php echo htmlspecialchars($car['title']); ?>" class="car-image">
                        <?php else: ?>
                            <div class="car-image" style="background-color: #eee; display: flex; align-items: center; justify-content: center;">
                                <span>Нет изображения</span>
                            </div>
                        <?php endif; ?>
                        <div class="car-info">
                            <h3 class="car-title"><?php echo htmlspecialchars($car['title']); ?></h3>
                            <div class="price">$<?php echo number_format($car['price'], 0, '.', ' '); ?></div>
                            <p class="car-description"><?php echo htmlspecialchars($car['description']); ?></p>
                            <div class="car-specs">
                                <div class="spec-item">
                                    <div class="spec-value"><?php echo $car['year']; ?></div>
                                    <div class="spec-label">Год выпуска</div>
                                </div>
                                <div class="spec-item">
                                    <div class="spec-value"><?php echo htmlspecialchars($car['engine']); ?></div>
                                    <div class="spec-label">Двигатель</div>
                                </div>
                                <div class="spec-item">
                                    <div class="spec-value"><?php echo $car['power']; ?> HP</div>
                                    <div class="spec-label">Мощность</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($user_cars)): ?>
                    <p>У вас пока нет объявлений. Нажмите "Добавить автомобиль", чтобы создать первое.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
            document.getElementById('showAddCarForm').addEventListener('click', function() {
                const form = document.getElementById('addCarForm');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            });
        </script>
    <?php else: ?>
    <?php endif; ?>

    <script>
        // Обработчики для модальных окон
        const loginBtn = document.getElementById('loginBtn');
        const registerBtn = document.getElementById('registerBtn');
        const loginModal = document.getElementById('loginModal');
        const registerModal = document.getElementById('registerModal');
        const closeLogin = document.getElementById('closeLogin');
        const closeRegister = document.getElementById('closeRegister');
        
        if (loginBtn) loginBtn.addEventListener('click', () => {
            loginModal.style.display = 'flex';
        });
        
        if (registerBtn) registerBtn.addEventListener('click', () => {
            registerModal.style.display = 'flex';
        });
        
        if (closeLogin) closeLogin.addEventListener('click', () => {
            loginModal.style.display = 'none';
        });
        
        if (closeRegister) closeRegister.addEventListener('click', () => {
            registerModal.style.display = 'none';
        });
        
        // Закрытие модальных окон при клике вне их области
        window.addEventListener('click', (e) => {
            if (e.target === loginModal) {
                loginModal.style.display = 'none';
            }
            if (e.target === registerModal) {
                registerModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>
