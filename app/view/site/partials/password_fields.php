<?php
$labelNew = $labelNew ?? $lang("Пароль");
$labelConfirm = $labelConfirm ?? $lang("Повторите пароль");
$minLength = isset($minLength) ? (int)$minLength : 6;
$autofill = $autofill ?? "new-password";

$pwdId = $idPrefix . "_pwd";
$pwd2Id = $idPrefix . "_pwd2";
$barId = $idPrefix . "_pwdbar";
$labelId = $idPrefix . "_pwdlabel";
$capsId = $idPrefix . "_caps";
$eye1 = $idPrefix . "_eye1";
$eye2 = $idPrefix . "_eye2";
?>
<div class="row g-3">
    <div class="col-12">
        <label for="<?= $pwdId ?>" class="form-label"><?= htmlspecialchars($labelNew) ?></label>
        <input
                type="password"
                class="form-control"
                id="<?= $pwdId ?>"
                name="<?= htmlspecialchars($nameNew) ?>"
                placeholder="<?= $lang('Введите пароль (мин. ' . $minLength . ' символов)') ?>"
                autocomplete="<?= htmlspecialchars($autofill) ?>"
                minlength="<?= $minLength ?>"
                required
        >
        <div class="form-text text-warning d-none" id="<?= $capsId ?>"><?= $lang("Внимание: включён Caps Lock") ?></div>
        <div class="mt-2">
            <div class="progress" style="height:6px;">
                <div id="<?= $barId ?>" class="progress-bar" role="progressbar" style="width:0%"></div>
            </div>
            <small id="<?= $labelId ?>" class="text-muted"><?= $lang("Надёжность пароля:") ?>
                — <?= $lang("пусто") ?></small>
        </div>
    </div>

    <div class="col-12">
        <label for="<?= $pwd2Id ?>" class="form-label"><?= htmlspecialchars($labelConfirm) ?></label>
        <input
                type="password"
                class="form-control"
                id="<?= $pwd2Id ?>"
                name="<?= htmlspecialchars($nameConfirm) ?>"
                placeholder="<?= $lang('Повторите пароль') ?>"
                autocomplete="<?= htmlspecialchars($autofill) ?>"
                minlength="<?= $minLength ?>"
                required
        >
        <div class="invalid-feedback"><?= $lang("Пароли не совпадают") ?></div>
    </div>
</div>

<script>
    (function () {
        const id = <?= json_encode($idPrefix, JSON_UNESCAPED_UNICODE) ?>;
        const pwd = document.getElementById(id + "_pwd");
        const pwd2 = document.getElementById(id + "_pwd2");
        const bar = document.getElementById(id + "_pwdbar");
        const lab = document.getElementById(id + "_pwdlabel");
        const caps = document.getElementById(id + "_caps");
        const eye1 = document.getElementById(id + "_eye1");
        const eye2 = document.getElementById(id + "_eye2");
        const minL = <?= (int)$minLength ?>;

        if (!pwd || !pwd2 || !bar || !lab) return;

        function scorePassword(pw) {
            let s = 0;
            if (!pw) return 0;
            if (pw.length >= minL) s += 10;
            if (pw.length >= 8) s += 10;
            if (pw.length >= 12) s += 10;
            if (/[a-z]/.test(pw)) s += 10;
            if (/[A-Z]/.test(pw)) s += 10;
            if (/\d/.test(pw)) s += 10;
            if (/[^A-Za-z0-9]/.test(pw)) s += 10;
            const classes = [/[a-z]/, /[A-Z]/, /\d/, /[^A-Za-z0-9]/].reduce((c, r) => c + (r.test(pw) ? 1 : 0), 0);
            if (classes >= 3) s += 10;
            if (classes === 4) s += 10;
            if (/([a-zA-Z0-9])\1{2,}/.test(pw)) s -= 10;
            if (/^(?:1234|qwer|asdf|zxcv|password|qwerty|admin)/i.test(pw)) s -= 20;
            return Math.max(0, Math.min(100, s));
        }

        const SCORE_MAX = scorePassword('Aa1!Aa1!Aa1!Aa1!');

        function updateStrength() {
            const raw = scorePassword(pwd.value);
            const s = Math.max(0, Math.min(100, Math.round((raw / (SCORE_MAX || 100)) * 100)));
            bar.style.width = s + '%';
            bar.classList.remove('bg-danger', 'bg-warning', 'bg-success');

            let txt = '<?= $lang("пусто") ?>';
            if (!pwd.value || s === 0) {
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

            lab.textContent = '<?= $lang("Надёжность пароля:") ?> — ' + txt;
        }

        function capsCheck(e) {
            if (typeof e.getModifierState === 'function') {
                const c = e.getModifierState('CapsLock');
                if (caps) caps.classList.toggle('d-none', !c);
            }
        }

        function matchCheck() {
            const okLen = pwd.value.length >= minL;
            const match = pwd2.value.length > 0 && pwd2.value === pwd.value;
            pwd.classList.toggle('is-invalid', !okLen && pwd.value.length > 0);
            pwd.classList.toggle('is-valid', okLen);
            pwd2.classList.toggle('is-invalid', pwd2.value.length > 0 && !match);
            pwd2.classList.toggle('is-valid', pwd2.value.length > 0 && match);

            const valid = okLen && match;
            const ev = new CustomEvent('pwd-valid-change', {bubbles: true, detail: {prefix: id, valid}});
            (pwd.closest('form') || document).dispatchEvent(ev);
        }

        function toggleEye(btn, input) {
            if (!btn || !input) return;
            btn.addEventListener('click', () => {
                const t = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', t);
                btn.classList.toggle('active', t === 'text');
            });
        }

        ['input', 'keyup', 'change', 'blur', 'paste'].forEach(ev => {
            pwd.addEventListener(ev, (e) => {
                capsCheck(e);
                updateStrength();
                matchCheck();
            });
            pwd2.addEventListener(ev, (e) => {
                capsCheck(e);
                matchCheck();
            });
        });
        toggleEye(eye1, pwd);
        toggleEye(eye2, pwd2);
        updateStrength();
        matchCheck();
    })();
</script>
