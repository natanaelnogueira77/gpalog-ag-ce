<?php 
    $this->layout("themes/black-screen/_theme", [
        'title' => sprintf(_('Carregamento | %s'), $appData['app_name']),
        'message' => $message
    ]);
?>

<p><?= _('Carregamento') ?></p>

<form action="<?= $router->route('user.conference.loading') ?>" method="post">
    <input type="hidden" name="step" value="<?= $nextStep ?>">

    <div>
        <?php if(!$CLF->hasSeparationId()): ?>
        <input type="submit" value="<?= _('Carregar') ?>">
        <?php elseif(!$CLF->hasCompletion()): ?>
        <input type="submit" value="<?= _('Confirmar') ?>">
        <?php endif; ?>
        <input type="button" value="<?= _('Voltar') ?>"
            onclick="window.location.href='<?= $router->route('user.conference.index') ?>'">
    </div>

    <table>
        <tbody>
            <tr>
                <td><?= _('ID de Separação') ?></td>
                <td>
                    <?php if(!$CLF->hasSeparationId()): ?>
                    <input type="text" name="sep_id" value="<?= $CLF->sep_id ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $CLF->hasError('sep_id') ? $CLF->getFirstError('sep_id') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="sep_id" value="<?= $CLF->sep_id ?>">
                    <?= $CLF->sep_id ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><?= _('Placa do Carregamento') ?></td>
                <td>
                    <?php if(!$CLF->hasSeparationId()): ?>
                    <input type="text" name="plate" value="<?= $CLF->plate ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $CLF->hasError('plate') ? $CLF->getFirstError('plate') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="plate" value="<?= $CLF->plate ?>">
                    <?= $CLF->plate ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><?= _('Doca') ?></td>
                <td>
                    <?php if(!$CLF->hasSeparationId()): ?>
                    <input type="text" name="dock" value="<?= $CLF->dock ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $CLF->hasError('dock') ? $CLF->getFirstError('dock') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="dock" value="<?= $CLF->dock ?>">
                    <?= $CLF->dock ?>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
</form>