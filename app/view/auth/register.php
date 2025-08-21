<section class="content">
    <div class="login-container container">
        <h1><?= $lang("Регистрация") ?></h1>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-danger" role="alert"><?= $error ?></div>
        <?php endif; ?>

        <form id="registerForm" action="" method="POST" novalidate>
            <div class="form-group mb-3">
                <label for="login" class="d-flex align-items-center gap-2">
                    <span><?= $lang("Логин") ?>:</span>
                    <span class="text-muted d-inline-flex align-items-center" role="button" tabindex="0"
                          data-bs-toggle="tooltip" data-bs-placement="right" data-bs-trigger="hover focus"
                          data-bs-container="body" title="<?= $lang('Разрешены латинские буквы, цифры, . _ -') ?>"></span>
                </label>

                <input type="text" class="form-control" id="login" name="login"
                       value="<?= isset($old['login']) ? htmlspecialchars($old['login']) : '' ?>"
                       placeholder="<?= $lang("Введите логин") ?>" autocomplete="username"
                       autocapitalize="off" spellcheck="false" pattern="[A-Za-z0-9._-]{3,}" maxlength="64" required>
                <div class="invalid-feedback">
                    <?= $lang("Логин должен быть не короче 3 символов и содержать только латиницу, цифры, . _ -") ?>
                </div>
            </div>

            <?php
            $idPrefix   = 'reg';
            $nameNew    = 'password';
            $nameConfirm= 'password_confirm';
            $labelNew   = $lang("Пароль");
            $labelConfirm=$lang("Повторите пароль");
            $minLength  = 6;
            $autofill   = 'new-password';
            include __DIR__ . '/../site/partials/password_fields.php';
            ?>

            <button id="submitBtn" type="submit" class="btn btn-primary mt-3" disabled>
                <?= $lang("Зарегистрироваться") ?>
            </button>
        </form>

        <div class="mt-3">
            <p class="mb-0"><?= $lang("Уже есть аккаунт?") ?> <a href="/login"><?= $lang("Войти") ?></a></p>
        </div>
    </div>
</section>

<script>
    (function(){
        const form = document.getElementById('registerForm');
        const loginInput = document.getElementById('login');
        const submitBtn = document.getElementById('submitBtn');

        if (window.bootstrap && typeof bootstrap.Tooltip === 'function') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        }

        const allowedRe = /^[A-Za-z0-9._-]+$/;
        function validateLogin(){
            const val = loginInput.value || '';
            const ok = val.length>=3 && allowedRe.test(val);
            loginInput.classList.toggle('is-invalid', !ok && val.length>0);
            loginInput.classList.toggle('is-valid', ok);
            return ok;
        }

        let pwdOK = false;
        form.addEventListener('pwd-valid-change', (e)=>{ pwdOK = !!(e.detail && e.detail.valid); updateBtn(); });
        ['input','change','keyup','blur','paste'].forEach(ev=>loginInput.addEventListener(ev, ()=>{ validateLogin(); updateBtn(); }));

        function updateBtn(){ submitBtn.disabled = !(validateLogin() && pwdOK); }
        updateBtn();
    })();
</script>
