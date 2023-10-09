<?php 
    $this->layout("themes/black-screen/_theme", [
        'title' => sprintf(_('Entrada | %s'), $appData['app_name']),
        'message' => $message
    ]);
?>

<p><?= _('Entrada') ?></p>

<table>
    <thead>
        <th><?= _('ID') ?></th>
        <th><?= _('OC') ?></th>
        <th><?= _('Placa') ?></th>
        <!-- <th><?php //_('Fornecedor') ?></th> -->
        <th><?= _('Data') ?></th>
    </thead>
    <tbody>
        <tr>
            <td><?= $dbConference->id ?></td>
            <td><?= $dbOperation->order_number ?></td>
            <td><?= $dbOperation->plate ?></td>
            <!-- <td><?php //$dbOperation->provider->name ?></td> -->
            <td><?= $dbConference->created_at ?></td>
        </tr>
    </tbody>
</table>
<br>

<?php if(!$conferenceInputForm->hasStarted()): ?>
<form action="<?= $router->route('user.conference.singleInput', ['conference_id' => $dbConference->id]) ?>" 
    <?= $dbConferenceInputs ? "onSubmit=\"return confirm('" . _('Você tem certeza que deseja finalizar?') . "');\"" : '' ?> 
    method="post">
    <input type="button" value="<?= _('Incluir Produto') ?>" 
        onclick="window.location.href='<?= $router->route('user.conference.singleInput', ['conference_id' => $dbConference->id, 'include_product' => true]) ?>'">
    <input type="button" value="<?= _('Ver Produtos') ?>" 
        onclick="window.location.href='<?= $router->route('user.conference.inputProducts', ['conference_id' => $dbConference->id]) ?>'">
    <?php if($dbConferenceInputs): ?>
    <input type="hidden" name="finish_conference">
    <input type="submit" value="<?= _('Finalizar Conferência') ?>">
    <?php endif; ?>
</form>
<?php else: ?>
<form method="<?= $conferenceInputForm->hasProduct() ? 'post' : 'get' ?>" 
    action="<?= $router->route('user.conference.create', ['conference_id' => $dbConference->id]) ?>">
    <div>
        <?php if(!$conferenceInputForm->hasProduct()): ?>
        <input type="hidden" name="search_product">
        <input type="submit" value="<?= _('Buscar') ?>">
        <?php elseif(!$conferenceInputForm->isCompleted()): ?>
        <input type="submit" value="<?= _('Enviar') ?>">
        <?php else: ?>
        <input type="hidden" name="is_completed">
        <input type="submit" value="<?= _('Confirmar') ?>">
        <?php endif; ?>

        <input type="button" value="<?= _('Voltar') ?>" onclick="window.location.href='<?= $return ?>'">
    </div>

    <table>
        <tbody>
            <tr>
                <td><?= _('Código de Barras') ?></td>
                <td>
                    <?php if(!$conferenceInputForm->hasProduct()): ?>
                    <input type="text" name="barcode" value="<?= $conferenceInputForm->barcode ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $conferenceInputForm->hasError('barcode') ? $conferenceInputForm->getFirstError('barcode') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="barcode" value="<?= $conferenceInputForm->barcode ?>">
                    <?= $conferenceInputForm->barcode ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if($conferenceInputForm->hasProduct()): ?>
            <?php if(!$conferenceInputForm->isCompleted()): ?>
            <tr>
                <td><?= _('Nome do Produto') ?></td>
                <td><?= $dbProduct->name ?></td>
            </tr>
            <?php endif; ?>
            <!-- <tr>
                <td><?php //_('Nome do Fornecedor') ?></td>
                <td><?php //$dbProduct->prov_name ?></td>
            </tr> -->
            <tr>
                <td><?= _('Embalagem') ?></td>
                <td><?= $dbProduct->emb_fb ?></td>
            </tr>
            <tr>
                <td><?= _('Qtde. CX Físico') ?></td>
                <td>
                    <?php if(!$conferenceInputForm->isCompleted()): ?>
                    <input type="number" id="physic_boxes_amount" name="physic_boxes_amount" 
                        value="<?= $conferenceInputForm->physic_boxes_amount ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $conferenceInputForm->hasError('physic_boxes_amount') ? $conferenceInputForm->getFirstError('physic_boxes_amount') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="physic_boxes_amount" value="<?= $conferenceInputForm->physic_boxes_amount ?>">
                    <?= $conferenceInputForm->physic_boxes_amount ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><?= _('Qtde. Unidade') ?></td>
                <td><?= $conferenceInputForm->isCompleted() ? $conferenceInputForm->physic_boxes_amount * $dbProduct->emb_fb : '' ?></td>
            </tr>
            <tr>
                <td><?= _('Qtde. Plts Fechados') ?></td>
                <td>
                    <?php if(!$conferenceInputForm->isCompleted()): ?>
                    <input type="number" name="closed_plts_amount" value="<?= $conferenceInputForm->closed_plts_amount ?>" style="max-width: 100px;">
                    <br>
                    <small style="color: red;">
                        <?= $conferenceInputForm->hasError('closed_plts_amount') ? $conferenceInputForm->getFirstError('closed_plts_amount') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="closed_plts_amount" value="<?= $conferenceInputForm->closed_plts_amount ?>">
                    <?= $conferenceInputForm->closed_plts_amount ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><?= _('Serviço') ?></td>
                <td>
                    <?php if(!$conferenceInputForm->isCompleted()): ?>
                    <select name="service_type" style="max-width: 100px;">
                        <option value=""><?= _('Selecionar...') ?></option>
                        <?php 
                            if($serviceTypes) {
                                foreach($serviceTypes as $stId => $serviceType) {
                                    $selected = $conferenceInputForm->service_type == $stId ? 'selected' : '';
                                    echo "<option value=\"{$stId}\" {$selected}>{$serviceType}</option>";
                                }
                            }
                        ?>
                    </select>
                    <br>
                    <small style="color: red;">
                        <?= $conferenceInputForm->hasError('service_type') ? $conferenceInputForm->getFirstError('service_type') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="service_type" value="<?= $conferenceInputForm->service_type ?>">
                    <?= $serviceTypes[$conferenceInputForm->service_type] ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><?= _('Altura Plt') ?></td>
                <td>
                    <?php if(!$conferenceInputForm->isCompleted()): ?>
                    <select name="pallet_height" style="max-width: 100px;">
                        <?php 
                            foreach([1.40, 2.20] as $height) {
                                $selected = $conferenceInputForm->pallet_height == $height ? 'selected' : '';
                                echo "<option value=\"{$height}\" {$selected}>{$height}</option>";
                            }
                        ?>
                    </select>
                    <br>
                    <small style="color: red;">
                        <?= $conferenceInputForm->hasError('pallet_height') ? $conferenceInputForm->getFirstError('pallet_height') : '' ?>
                    </small>
                    <?php else: ?>
                    <input type="hidden" name="pallet_height" value="<?= $conferenceInputForm->pallet_height ?>">
                    <?= $conferenceInputForm->pallet_height ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</form>
<?php endif; ?>