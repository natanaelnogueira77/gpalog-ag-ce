<?php 
    $this->layout("themes/error/_theme", [
        'code' => $code
    ]); 
?>
<div class="wrapper">
    <div class="box">
        <h1><?= $code ?></h1>
        <p><?= $message ?></p>
        <p>
            <a href="<?= $router->route('home.index') ?>"><?= _('Voltar') ?></a>
        </p>
    </div>
</div>