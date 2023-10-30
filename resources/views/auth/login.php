<?php 
    use Src\Components\Theme;
    $this->layout("themes/courses-master/_theme", [
        'theme' => (new Theme())->loadData([
            'title' => sprintf(_('Entrar | %s'), $appData['app_name']),
            'has_header' => false,
            'has_footer' => false,
            'logo_icon' => $shortcutIcon
        ])
    ]);
?>

<main class="login-body" data-vide-bg="<?= $background ?>">
    <form id="main-login-form" class="form-default" action="<?= $router->route('auth.index') ?>" method="post">
        <?php if($redirect): ?>
        <input type="hidden" name="redirect" value="<?= $redirect ?>">
        <?php endif; ?>
        <div class="login-form mt-5">
            <div class="logo-login">
                <a href="#">
                    <img src="<?= $shortcutIcon ?>" alt="">
                </a>
            </div>

            <h2><?= _('Entrar') ?></h2>

            <div class="form-input d-block text-center">
                <a href="<?= $router->route('operatorLogin.index') ?>" class="btn btn-primary">
                   <?= _('Área de Operação') ?>
                </a>
            </div>

            <div class="form-input">
                <input type="email" id="email" name="email" 
                    placeholder="<?= _('Digite seu email') ?>" value="<?= $loginForm->email ?>" required>
                <div class="invalid-feedback">
                    <?= $loginForm->hasError('email') ? $loginForm->getFirstError('email') : '' ?>
                </div>
            </div>

            <div class="form-input">
                <input type="password" id="password" name="password" 
                    placeholder="<?= _('Digite sua senha') ?>" required>
                <div class="invalid-feedback">
                    <?= $loginForm->hasError('password') ? $loginForm->getFirstError('password') : '' ?>
                </div>
            </div>

            <div class="form-input pt-10">
                <input type="submit" class="g-recaptcha" data-sitekey="<?= $appData['recaptcha']['site_key'] ?>"
                    data-callback='onSubmit' data-action='submit' value="<?= _('Entrar') ?>">
            </div>

            <a href="<?= $router->route('resetPassword.index') ?>" class="forget">
                <?= _('Esqueceu a senha?') ?>
            </a>
        </div>
    </form>
</main>

<?php $this->start('scripts'); ?>
<script>
    function onSubmit(token) {
        document.getElementById("main-login-form").submit();
    }
</script>
<?php $this->end(); ?>