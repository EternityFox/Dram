<section class="content">
    <div class="container">
        <h1>Вход</h1>

        <?php
        if (isset($error)) {
            ?>
            <div class="alert alert-danger" role="alert">
                <?= $error ?>
            </div>
            <?php
        }
        ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Логин:</label>
                <input type="text" class="form-control" name="login" placeholder="login">
            </div>
            <div class="form-group">
                <label>Пароль:</label>
                <input type="password" class="form-control" name="password" placeholder="password">
            </div>
            <button class="btn btn-primary">Войти</button>
        </form>


    </div>
</section>
