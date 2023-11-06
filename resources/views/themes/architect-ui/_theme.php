<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="<?= url('public/themes/architect-ui/main.css') ?>">
    <link rel="stylesheet" href="<?= url('public/assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= url('public/assets/css/icofont.min.css') ?>">
    <link rel="stylesheet" href="<?= url('public/assets/css/toastr.min.css') ?>">
    <?php if($theme->has_full_background): ?>
    <style>
        .full-background {
            background-image: url('<?= $theme->full_background ?>');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
            position: fixed;
            opacity: 0.7;
            width: 100%;
            height: 100%;
        }
    </style>
    <?php endif; ?>
    <link rel="stylesheet" href="https//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <?= $this->section('css'); ?>
    <link rel="shortcut icon" href="<?= $theme->logo_icon ?>" type="image/png">
    <title><?= $theme->title ?></title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="ajax_load">
        <div class="ajax_load_box">
            <div class="ajax_load_box_circle">
                <div class="ajax_rotation"></div>
                <img src="<?= $theme->logo_icon ?>" alt="">
            </div>
            <div class="ajax_load_box_title"><?= $theme->loading_text ?></div>
        </div>
    </div>

    <div class="app-container app-theme-white body-tabs-shadow <?= $theme->has_left ? 'fixed-sidebar' : '' ?> fixed-header">
        <?php 
            if($theme->has_header) {
                $this->insert('themes/architect-ui/_partials/header', ['theme' => $theme]);
            }
        ?>
        <div class="app-main">
            <?php 
                if($theme->has_left) {
                    $this->insert('themes/architect-ui/_partials/left', ['theme' => $theme]); 
                }
            ?>
            <div class="app-main__outer">
                <?php if($theme->has_full_background): ?>
                <div class="full-background"></div>
                <?php endif; ?>
                <div class="app-main__inner">
                    <?= $this->section('content'); ?>
                </div>
                <?php 
                    if($theme->has_footer) {
                        $this->insert('themes/architect-ui/_partials/footer', ['theme' => $theme]);
                    }
                ?>
            </div>
        </div>
    </div>

    <script src="<?= url('public/assets/js/jquery-3.0.0.min.js') ?>"></script>
    <script src="<?= url('public/assets/js/bootstrap.min.js') ?>"></script>
    <script src="<?= url('public/assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= url('public/themes/architect-ui/assets/scripts/main.js') ?>"></script>
    <script src="<?= url('public/assets/js/toastr.min.js') ?>"></script>
    <script src="<?= url('public/assets/js/jquery-ui.js') ?>"></script>
    <script src="<?= url('public/assets/js/jquery.ui.touch-punch.js') ?>"></script>
    <script src="<?= url('public/assets/js/jquery.maskedinput.min.js') ?>"></script>
    <script src="<?= url('public/assets/js/tinymce.min.js') ?>"></script>
    <script src="<?= url('public/assets/js/data-table.js') ?>"></script>
    <script src="<?= url('public/assets/js/app.js') ?>"></script>
    <?php 
        $this->insert('_scripts/messages.js');
        $this->insert('_scripts/file-selector.js');
        if($theme->header['right']['bell']['notifications']) {
            $this->insert('_scripts/notifications.js', [
                'dbNotifications' => $theme->header['right']['bell']['notifications']
            ]);
        }
        $this->insert('_components/tinymce', ['v' => $this]);

        echo $this->section('scripts');
        echo $this->section('modals');

        if($session->getAuth()) {
            $this->insert('_components/expired-session', ['v' => $this]);
        }
    ?>
</body>
</html>