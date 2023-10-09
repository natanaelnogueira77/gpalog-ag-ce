<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/bootstrap.min.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/owl.carousel.min.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/slicknav.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/flaticon.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/progressbar_barfiller.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/gijgo.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/animate.min.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/animated-headline.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/magnific-popup.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/fontawesome-all.min.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/themify-icons.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/slick.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/nice-select.css") ?>">
    <link rel="stylesheet" href="<?= url("public/themes/courses-master/assets/css/style.css") ?>">
    <link rel="stylesheet" href="<?= url("public/assets/css/icofont.min.css") ?>">
    <link rel="stylesheet" href="<?= url("public/assets/css/toastr.min.css") ?>">
    <link rel="stylesheet" href="<?= url("public/assets/css/custom.css") ?>">
    <?= $this->section("css"); ?>
    <link rel="shortcut icon" href="<?= $shortcutIcon ?>" type="image/png">
    <title><?= $title ?></title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <?php 
        $this->insert("themes/courses-master/partials/preloader", $preloader);
        if(!$noHeader) {
            $this->insert("themes/courses-master/partials/header", $header);
        }
    ?>

    <?= $this->section("content"); ?>

    <?php 
        if(!$noFooter) {
            $this->insert("themes/courses-master/partials/footer", $footer);
        }
    ?>

    <!-- JS here -->
    <script src="<?= url("public/themes/courses-master/assets/js/vendor/modernizr-3.5.0.min.js") ?>"></script>
    <!-- Jquery, Popper, Bootstrap -->
    <script src="<?= url("public/themes/courses-master/assets/js/vendor/jquery-1.12.4.min.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/popper.min.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/bootstrap.min.js") ?>"></script>
    <!-- Jquery Mobile Menu -->
    <script src="<?= url("public/themes/courses-master/assets/js/jquery.slicknav.min.js") ?>"></script>

    <script src="<?= url("public/themes/courses-master/assets/js/jquery.vide.js") ?>"></script>

    <!-- Jquery Slick , Owl-Carousel Plugins -->
    <script src="<?= url("public/themes/courses-master/assets/js/owl.carousel.min.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/slick.min.js") ?>"></script>
    <!-- One Page, Animated-HeadLin -->
    <script src="<?= url("public/themes/courses-master/assets/js/wow.min.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/animated.headline.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/jquery.magnific-popup.js") ?>"></script>

    <!-- Date Picker -->
    <script src="<?= url("public/themes/courses-master/assets/js/gijgo.min.js") ?>"></script>
    <!-- Nice-select, sticky -->
    <script src="<?= url("public/themes/courses-master/assets/js/jquery.nice-select.min.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/jquery.sticky.js") ?>"></script>
    <!-- Progress -->
    <script src="<?= url("public/themes/courses-master/assets/js/jquery.barfiller.js") ?>"></script>

    <!-- counter , waypoint,Hover Direction -->
    <script src="<?= url("public/themes/courses-master/assets/js/jquery.counterup.min.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/waypoints.min.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/jquery.countdown.min.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/hover-direction-snake.min.js") ?>"></script>

    <!-- contact js -->
    <script src="<?= url("public/themes/courses-master/assets/js/contact.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/jquery.form.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/jquery.validate.min.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/mail-script.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/jquery.ajaxchimp.min.js") ?>"></script>

    <!-- Jquery Plugins, main Jquery -->	
    <script src="<?= url("public/themes/courses-master/assets/js/plugins.js") ?>"></script>
    <script src="<?= url("public/themes/courses-master/assets/js/main.js") ?>"></script>
    <script src="<?= url("public/assets/js/tinymce.min.js") ?>"></script>
    <script src="<?= url("public/assets/js/toastr.min.js") ?>"></script>
    <script src="<?= url("public/assets/js/app.js") ?>"></script>
    <script>
        $(function () {
            const app = new App();
            <?php 
            foreach(['success', 'error', 'info'] as $type):
                if($session->getFlash($type)): 
                ?>
                app.showMessage(
                    <?php echo json_encode($session->getFlash($type)) ?>, 
                    <?php echo json_encode($type) ?>,
                    5000, 
                    5000, 
                    5000, 
                    'toast-bottom-right'
                );
                <?php 
                endif;  
            endforeach;
            ?>
        });
    </script>
    <?php $this->insert("components/tinymce"); ?>

    <?= $this->section("scripts"); ?>
    <?= $this->section("modals"); ?>
</body>
</html>