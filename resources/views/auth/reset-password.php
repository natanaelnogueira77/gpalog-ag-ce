<?php 
    $this->layout("themes/courses-master/_theme", [
        'title' => sprintf(_('Redefinir Senha | %s'), $appData['app_name']),
        'noHeader' => true,
        'noFooter' => true,
        'shortcutIcon' => $shortcutIcon,
        'preloader' => ['shortcutIcon' => $shortcutIcon]
    ]);
?>

<main class="login-body" data-vide-bg="<?= $background ?>">
    <div class="login-form mt-5">
        <div class="logo-login">
            <a href="#">
                <img src="<?= $shortcutIcon ?>" alt="">
            </a>
        </div>

        <h2><?= _('Redefinir Senha') ?></h2>

        <?php if(!isset($code)): ?>
        <form id="redefine-password-form" class="form-default" action="<?= $router->route('resetPassword.index') ?>" method="post">
            <div class="form-input">
                <label for="email"><?= _('Email') ?></label>
                <input type="email" id="email" name="email" placeholder="<?= _('Digite seu email') ?>" 
                    class="form-control <?= $forgotPasswordForm->hasError('email') ? 'is-invalid' : '' ?>" 
                    value="<?= $forgotPasswordForm->email ?>" required>
                <div class="invalid-feedback">
                    <?= $forgotPasswordForm->hasError('email') ? $forgotPasswordForm->getFirstError('email') : '' ?>
                </div>
            </div>

            <div class="form-input pt-30">
                <input type="submit" class="g-recaptcha" data-sitekey="<?= $appData['recaptcha']['site_key'] ?>"
                    data-callback='onSubmit' data-action='submit' value="<?= _('Enviar') ?>">
            </div>
        </form>
        <?php else: ?>
        <form id="redefine-password-form" class="form-default" 
            action="<?= $router->route('resetPassword.verify', ['code' => $code]) ?>" method="post">
            <div class="form-input">
                <label for="password"><?= _('Nova Senha') ?></label>
                <input type="password" id="password" name="password" placeholder="<?= _('Digite sua nova senha') ?>" 
                    class="form-control <?= $resetPasswordForm->hasError('password') ? 'is-invalid' : '' ?>" required>
                <div class="invalid-feedback">
                    <?= $resetPasswordForm->hasError('password') ? $resetPasswordForm->getFirstError('password') : '' ?>
                </div>
            </div>

            <div class="form-input">
                <label for="password_confirm"><?= _('Confirmar Nova Senha') ?></label>
                <input type="password" id="password_confirm" name="password_confirm" 
                    class="form-control <?= $resetPasswordForm->hasError('password_confirm') ? 'is-invalid' : '' ?>"
                    placeholder="<?= _('Digite novamente a nova senha') ?>" required>
                <div class="invalid-feedback">
                    <?= $resetPasswordForm->hasError('password_confirm') ? $resetPasswordForm->getFirstError('password_confirm') : '' ?>
                </div>
            </div>

            <div class="form-input pt-30">
                <input type="submit" value="<?= _('Redefinir') ?>">
            </div>
        </form>
        <?php endif; ?>
    </div>
</main>

<?php $this->start('scripts'); ?>
<script>
    function onSubmit(token) {
        document.getElementById("redefine-password-form").submit();
    }
</script>
<?php $this->end(); ?>