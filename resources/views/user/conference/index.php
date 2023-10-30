<?php 
    $theme->title = sprintf(_('Operação | %s'), $appData['app_name']);
    $this->layout("themes/black-screen/_theme", [
        'theme' => $theme,
        'message' => $message
    ]);
?>

<p><?= _('Operação') ?></p>

<div>
    <input type="button" value="<?= _('Entrada') ?>" onclick="window.location.href='<?= $router->route('user.conference.input') ?>'">
    <br>
    <input type="button" value="<?= _('Separação') ?>" onclick="window.location.href='<?= $router->route('user.conference.separation') ?>'">
    <br>
    <input type="button" value="<?= _('Conferência de Expedição') ?>" onclick="window.location.href='<?= $router->route('user.conference.expedition') ?>'">
    <br>
    <input type="button" value="<?= _('Carregamento') ?>" onclick="window.location.href='<?= $router->route('user.conference.loading') ?>'">
    <br>
    <input type="button" value="<?= _('Sair') ?>" onclick="window.location.href='<?= $router->route('auth.logout') ?>'">
</div>