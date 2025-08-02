<section class="content">
    <div class="login-container container">
        <h1><?= $lang("Вход в систему") ?></h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group mb-4">
                <label for="login"><?= $lang("Логин") ?>:</label>
                <input type="text" class="form-control" id="login" name="login"
                       placeholder="<?= $lang("Введите логин") ?>" required>
            </div>
            <div class="form-group mb-4">
                <label for="password"><?= $lang("Пароль") ?>:</label>
                <input type="password" class="form-control" id="password" name="password"
                       placeholder="<?= $lang("Введите пароль") ?>" required>
            </div>
            <button type="submit" class="btn btn-primary"><?= $lang("Войти") ?></button>
        </form>
    </div>
</section>
