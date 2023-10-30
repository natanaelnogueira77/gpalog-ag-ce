<?php 
    $theme->title = sprintf(_('Separação | %s'), $appData['app_name']);
    $this->layout("themes/black-screen/_theme", [
        'theme' => $theme,
        'message' => $message
    ]);
?>

<p><?= $CSF->hasEAN() ? sprintf(_('Separação - ID %s'), $CSF->separation->id) : _('Separação') ?></p>

<form action="<?= $router->route('user.conference.separation') ?>" method="<?= $CSF->hasAmount() ? 'post' : 'get' ?>" 
    <?= $CSF->hasEAN() ? "onSubmit=\"return document.getElementById('amount').value != {$CSF->separationItem->amount} ? confirm('" . _('A quantidade colocada não corresponde à quantidade solicitada! Deseja continuar mesmo assim?') . "') : null;\"" : '' ?>>
    <input type="hidden" name="step" value="<?= $nextStep ?>">

    <div>
        <?php if(!$CSF->hasEAN()): ?>
        <input type="submit" value="<?= _('Próximo') ?>">
        <?php elseif(!$CSF->hasAmount()): ?>
        <input type="submit" value="<?= _('Finalizar Separação') ?>">
        <?php elseif(!$CSF->hasDock()): ?>
        <input type="submit" value="<?= _('Finalizar') ?>">
        <?php endif; ?>
        <input type="button" value="<?= _('Voltar') ?>"
            onclick="window.location.href='<?= !$CSF->hasEAN() ? $router->route('user.conference.index') : $router->route('user.conference.separation', ['step' => $previousStep] + ($CSF->hasAmount() ? ['address' => $CSF->address, 'ean' => $CSF->ean] : []) + ($CSF->hasDock() ? ['amount' => $CSF->amount] : [])) ?>'">
    </div>

    <table>
        <tbody>
            <tr>
                <td><?= _('Endereçamento') ?></td>
                <td>
                    <?php if(!$CSF->hasEAN()): ?>
                    <input type="text" name="address" value="<?= $CSF->address ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $CSF->hasError('address') ? $CSF->getFirstError('address') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="address" value="<?= $CSF->address ?>">
                    <?= $CSF->address ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><?= _('EAN') ?></td>
                <td>
                    <?php if(!$CSF->hasEAN()): ?>
                    <input type="text" name="ean" value="<?= $CSF->ean ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $CSF->hasError('ean') ? $CSF->getFirstError('ean') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="ean" value="<?= $CSF->ean ?>">
                    <?= $CSF->ean ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if($CSF->hasEAN()): ?>
            <tr>
                <td><?= _('Número do Pedido') ?></td>
                <td><?= $CSF->separationItem->order_number ?></td>
            </tr>
            <tr>
                <td><?= $CSF->separationItem->isBoxesType() ? _('Quantidade de Caixas') : _('Quantidade de Unidades') ?></td>
                <td>
                    <?php if(!$CSF->hasAmount()): ?>
                    <input type="number" id="amount" name="amount" value="<?= $CSF->amount ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $CSF->hasError('amount') ? $CSF->getFirstError('amount') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="amount" value="<?= $CSF->amount ?>">
                    <?= $CSF->amount ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php if($CSF->hasAmount()): ?>
            <tr>
                <td><?= _('Doca de Despacho') ?></td>
                <td>
                    <input type="text" name="dispatch_dock" value="<?= $CSF->dispatch_dock ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $CSF->hasError('dispatch_dock') ? $CSF->getFirstError('dispatch_dock') : '' ?>
                    </small>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</form>

<?php if(!$CSF->hasAmount()): ?>
<script>
    const amount_field = document.getElementById('amount');
    const checkAmount = function () {
        return amount_field.value != <?php echo json_encode($CSF->separationItem->amount) ?> 
            ? confirm(<?php echo json_encode(_('A quantidade colocada não corresponde à quantidade solicitada! Deseja continuar mesmo assim?')) ?>) 
            : null;
    };
</script>
<?php endif; ?>