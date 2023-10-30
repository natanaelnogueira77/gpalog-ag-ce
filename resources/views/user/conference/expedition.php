<?php 
    $theme->title = sprintf(_('Conferência de Expedição | %s'), $appData['app_name']);
    $this->layout("themes/black-screen/_theme", [
        'theme' => $theme,
        'message' => $message
    ]);
?>

<p><?= _('Conferência de Expedição') ?></p>

<form action="<?= $router->route('user.conference.expedition') ?>" method="post">
    <input type="hidden" name="step" value="<?= $nextStep ?>">

    <div>
        <?php if(!$CEF->hasEAN()): ?>
        <input type="submit" value="<?= _('Próximo') ?>">
        <?php elseif(!$CEF->hasAmount()): ?>
        <input type="submit" value="<?= _('Finalizar Conferência') ?>">
        <?php elseif(!$CEF->hasCompletion()): ?>
        <input type="submit" value="<?= _('Confirmar') ?>">
        <?php endif; ?>
        <input type="button" value="<?= _('Voltar') ?>"
            onclick="window.location.href='<?= !$CEF->hasEAN() ? $router->route('user.conference.index') : $router->route('user.conference.expedition', ['step' => $previousStep] + ($CEF->hasAmount() ? ['sep_id' => $CEF->sep_id, 'ean' => $CEF->ean] : [])) ?>'">
    </div>

    <table>
        <tbody>
            <tr>
                <td><?= _('ID de Separação') ?></td>
                <td>
                    <?php if(!$CEF->hasEAN()): ?>
                    <input type="number" name="sep_id" value="<?= $CEF->sep_id ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $CEF->hasError('sep_id') ? $CEF->getFirstError('sep_id') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="sep_id" value="<?= $CEF->sep_id ?>">
                    <?= $CEF->sep_id ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><?= _('EAN') ?></td>
                <td>
                    <?php if(!$CEF->hasEAN()): ?>
                    <input type="text" name="ean" value="<?= $CEF->ean ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $CEF->hasError('ean') ? $CEF->getFirstError('ean') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="ean" value="<?= $CEF->ean ?>">
                    <?= $CEF->ean ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if($CEF->hasEAN()): ?>
            <tr>
                <td><?= _('Número do Pedido') ?></td>
                <td><?= $CEF->separationItem->order_number ?></td>
            </tr>
            <tr>
                <td><?= $CEF->separationItem->isBoxesType() ? _('Quantidade de Caixas') : _('Quantidade de Unidades') ?></td>
                <td>
                    <?php if(!$CEF->hasAmount()): ?>
                    <input type="number" name="amount" value="<?= $CEF->amount ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $CEF->hasError('amount') ? $CEF->getFirstError('amount') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="amount" value="<?= $CEF->amount ?>">
                    <?= $CEF->amount ?> (<?= $CEF->amount == $CEF->separationItem->separation_amount ? _('OK') : _('Diferença') ?>)
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</form>