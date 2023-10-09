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
    <div style="color: white; margin-left: 10px; height: 100%;">
        <?php if($message): ?>
        <p style="color: <?= $message['type'] == 'error' ? 'red' : 'lime' ?>;"><strong><?= $message['message'] ?></strong></p>
        <?php endif; ?>

        <?= $this->section("content"); ?>
    </div>
</body>
</html>