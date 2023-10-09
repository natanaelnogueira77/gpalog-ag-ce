<?php 
    $this->layout("themes/black-screen/_theme", [
        'title' => sprintf(_('Operação | %s'), $appData['app_name']),
        'message' => $message
    ]);
?>

<p><?= _('Operação') ?></p>
<div>
    <input type="button" value="<?= _('Entrada') ?>" onclick="window.location.href='<?= $router->route('user.conference.input') ?>'">
    <input type="button" value="<?= _('Separação') ?>" onclick="window.location.href='<?= $router->route('user.conference.separation') ?>'">
    <input type="button" value="<?= _('Conferência de Expedição') ?>" onclick="window.location.href='<?= $router->route('user.conference.expedition') ?>'">
    <input type="button" value="<?= _('Carregamento') ?>" onclick="window.location.href='<?= $router->route('user.conference.loading') ?>'">
    <input type="button" value="<?= _('Sair') ?>" onclick="window.location.href='<?= $router->route('auth.logout') ?>'">
</div>