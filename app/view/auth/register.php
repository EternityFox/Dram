<section class="content">
    <div class="login-container container">
        <h1><?= $lang("Регистрация") ?></h1>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form id="registerForm" action="" method="POST" novalidate>
            <div class="form-group mb-3">
                <label for="login" class="d-flex align-items-center gap-2">
                    <span><?= $lang("Логин") ?>:</span>
                    <span
                            class="text-muted d-inline-flex align-items-center"
                            role="button"
                            tabindex="0"
                            data-bs-toggle="tooltip"
                            data-bs-placement="right"
                            data-bs-trigger="hover focus"
                            data-bs-container="body"
                            title="<?= $lang('Разрешены латинские буквы, цифры, . _ -') ?>">
                    </span>
                </label>

                <input
                        type="text"
                        class="form-control"
                        id="login"
                        name="login"
                        value="<?= isset($old['login']) ? htmlspecialchars($old['login']) : '' ?>"
                        placeholder="<?= $lang("Введите логин") ?>"
                        autocomplete="username"
                        autocapitalize="off"
                        spellcheck="false"
                        pattern="[A-Za-z0-9._-]{3,}"
                        maxlength="64"
                        required
                >
                <div class="invalid-feedback">
                    <?= $lang("Логин должен быть не короче 3 символов и содержать только латиницу, цифры, . _ -") ?>
                </div>
            </div>

            <!-- ПАРОЛЬ -->
            <div class="form-group mb-3">
                <label for="password"><?= $lang("Пароль") ?>:</label>
                <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="<?= $lang("Введите пароль (мин. 6 символов)") ?>"
                        autocomplete="new-password"
                        minlength="6"
                        required
                >
                <div class="form-text text-warning d-none"
                     id="capsWarning"><?= $lang("Внимание: включён Caps Lock") ?></div>

                <!-- Индикатор силы -->
                <div class="mt-2">
                    <div class="progress" style="height: 6px;">
                        <div id="pwdStrengthBar" class="progress-bar" role="progressbar" style="width:0%;"></div>
                    </div>
                    <small id="pwdStrengthLabel" class="text-muted"><?= $lang("Надёжность пароля:") ?>
                        — <?= $lang("пусто") ?></small>
                </div>
            </div>

            <!-- ПОВТОР ПАРОЛЯ -->
            <div class="form-group mb-3">
                <label for="password_confirm"><?= $lang("Повторите пароль") ?>:</label>
                <input
                        type="password"
                        class="form-control"
                        id="password_confirm"
                        name="password_confirm"
                        placeholder="<?= $lang("Повторите пароль") ?>"
                        autocomplete="new-password"
                        required
                >
                <div class="invalid-feedback" id="confirmFeedback"><?= $lang("Пароли не совпадают") ?></div>
            </div>

            <button id="submitBtn" type="submit" class="btn btn-primary" disabled>
                <?= $lang("Зарегистрироваться") ?>
            </button>
        </form>

        <div class="mt-3">
            <p class="mb-0">
                <?= $lang("Уже есть аккаунт?") ?>
                <a href="/login"><?= $lang("Войти") ?></a>
            </p>
        </div>
    </div>
</section>

<script>
    (function () {
        const form = document.getElementById('registerForm');
        const loginInput = document.getElementById('login');
        const passInput = document.getElementById('password');
        const pass2Input = document.getElementById('password_confirm');
        const submitBtn = document.getElementById('submitBtn');
        const capsWarning = document.getElementById('capsWarning');

        const bar = document.getElementById('pwdStrengthBar');
        const label = document.getElementById('pwdStrengthLabel');

        // Bootstrap tooltip init (bundle должен быть подключен)
        if (window.bootstrap && typeof bootstrap.Tooltip === 'function') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        }

        // ====== Жёсткая фильтрация логина (live) ======
        const allowedRe = /^[A-Za-z0-9._-]+$/;

        function filterLoginValue(val) {
            return val.replace(/[^A-Za-z0-9._-]/g, '');
        }

        function validateLogin() {
            // чистим любые недопустимые символы на лету
            const cleaned = filterLoginValue(loginInput.value);
            if (cleaned !== loginInput.value) {
                const pos = loginInput.selectionStart;
                loginInput.value = cleaned;
                // восстановим курсор примерно на то же место
                if (typeof pos === 'number') {
                    loginInput.setSelectionRange(Math.min(pos - 1, cleaned.length), Math.min(pos - 1, cleaned.length));
                }
            }
            const ok = cleaned.length >= 3 && allowedRe.test(cleaned);
            loginInput.classList.toggle('is-invalid', !ok && cleaned.length > 0);
            loginInput.classList.toggle('is-valid', ok);
            return ok;
        }

        // ====== Надёжность пароля ======
        function scorePassword(pw) {
            let score = 0;
            if (!pw) return 0;
            if (pw.length >= 6) score += 10;
            if (pw.length >= 8) score += 10;
            if (pw.length >= 12) score += 10;
            if (/[a-z]/.test(pw)) score += 10;
            if (/[A-Z]/.test(pw)) score += 10;
            if (/\d/.test(pw)) score += 10;
            if (/[^A-Za-z0-9]/.test(pw)) score += 10;
            const classes = [/[a-z]/, /[A-Z]/, /\d/, /[^A-Za-z0-9]/].reduce((c, r) => c + (r.test(pw) ? 1 : 0), 0);
            if (classes >= 3) score += 10;
            if (classes === 4) score += 10;
            if (/([a-zA-Z0-9])\1{2,}/.test(pw)) score -= 10;
            if (/^(?:1234|qwer|asdf|zxcv|password|qwerty|admin)/i.test(pw)) score -= 20;
            return Math.max(0, Math.min(100, score));
        }

        function updateStrength() {
            const s = scorePassword(passInput.value);
            bar.style.width = s + '%';
            bar.classList.remove('bg-danger', 'bg-warning', 'bg-success');
            let txt = '<?= $lang("пусто") ?>';
            if (s === 0) {
                bar.classList.add('bg-danger');
                txt = '<?= $lang("пусто") ?>';
            } else if (s <= 30) {
                bar.classList.add('bg-danger');
                txt = '<?= $lang("слабый") ?>';
            } else if (s <= 70) {
                bar.classList.add('bg-warning');
                txt = '<?= $lang("средний") ?>';
            } else {
                bar.classList.add('bg-success');
                txt = '<?= $lang("сильный") ?>';
            }
            label.textContent = '<?= $lang("Надёжность пароля:") ?> — ' + txt;
        }

        // ====== Валидация паролей ======
        function passwordsMatch() {
            return pass2Input.value.length > 0 && pass2Input.value === passInput.value;
        }

        function validatePasswords() {
            const passOk = passInput.value.length >= 6;
            passInput.classList.toggle('is-invalid', !passOk && passInput.value.length > 0);
            passInput.classList.toggle('is-valid', passOk);

            const match = passwordsMatch();
            const touched = pass2Input.value.length > 0;
            pass2Input.classList.toggle('is-invalid', touched && !match);
            pass2Input.classList.toggle('is-valid', touched && match);
            return passOk && match;
        }

        // ====== CapsLock предупреждение ======
        function onKeyEvent(e) {
            if (typeof e.getModifierState === 'function') {
                const caps = e.getModifierState('CapsLock');
                capsWarning.classList.toggle('d-none', !caps);
            }
        }

        function refreshSubmitState() {
            const ok = validateLogin() && validatePasswords();
            submitBtn.disabled = !ok;
        }

        // Слушатели
        ['input', 'blur', 'change', 'keyup', 'paste'].forEach(ev => loginInput.addEventListener(ev, () => {
            validateLogin();
            refreshSubmitState();
        }));
        ['input', 'blur', 'change', 'keyup'].forEach(ev => passInput.addEventListener(ev, (e) => {
            onKeyEvent(e);
            updateStrength();
            refreshSubmitState();
        }));
        ['input', 'blur', 'change', 'keyup'].forEach(ev => pass2Input.addEventListener(ev, (e) => {
            onKeyEvent(e);
            refreshSubmitState();
        }));

        form.addEventListener('submit', function (e) {
            refreshSubmitState();
            if (submitBtn.disabled) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        // init
        if (loginInput.value) validateLogin();
        updateStrength();
        refreshSubmitState();
    })();
</script>