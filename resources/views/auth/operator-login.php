<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="<?= url($shortcutIcon) ?>" type="image/png">
    <title><?= $title ?></title>
</head>
<body style="height: 100vh; width: 100%; background-color: black;">
    <?php 
        if($session->getFlash('error')) {
            $message = [
                'type' => 'error',
                'message' => $session->getFlash('error')
            ];
        } elseif($session->getFlash('success')) {
            $message = [
                'type' => 'success',
                'message' => $session->getFlash('success')
            ];
        }
    ?>
    <div style="color: white; margin-left: 10px; height: 100%;">
        <?php if($message): ?>
        <p style="color: <?= $message['type'] == 'error' ? 'red' : 'lime' ?>;"><strong><?= $message['message'] ?></strong></p>
        <?php endif; ?>

        <form action="<?= $router->route('operatorLogin.index') ?>" method="post">
            <div>
                <label for="registration_number"><?= _('Número da Matrícula') ?></label>
                <br>
                <input type="text" id="registration_number" name="registration_number" 
                    placeholder="<?= _('Digite o número da matrícula') ?>" value="<?= $operatorLoginForm->registration_number ?>" required>
                <br>
                <small style="color: red;">
                    <?= $operatorLoginForm->hasError('registration_number') ? $operatorLoginForm->getFirstError('registration_number') : '' ?>
                </small>
            </div>

            <div>
                <label for="password"><?= _('Senha') ?></label>
                <br>
                <input type="password" id="password" name="password" placeholder="<?= _('Digite sua senha') ?>" required>
                <br>
                <small style="color: red;">
                    <?= $operatorLoginForm->hasError('password') ? $operatorLoginForm->getFirstError('password') : '' ?>
                </small>
            </div>

            <br>
            <input type="submit" value="<?= _('Entrar') ?>">
        </form>
    </div>
</body>
</html>