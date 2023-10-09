<table class="table table-bordered table-striped table-hover">
    <thead>
        <th class="align-middle"><?= _('EAN') ?></th>
        <th class="align-middle"><?= _('Tipo de Quantidade') ?></th>
        <th class="align-middle"><?= _('Quantidade') ?></th>
    </thead>
    <tbody>
        <?php foreach($dbSeparationEANs as $dbSeparationEAN): ?>
        <tr>
            <td class="align-middle"><?= $dbSeparationEAN->product->ean ?></td>
            <td class="align-middle"><?= $dbSeparationEAN->getAmountType() ?></td>
            <td class="align-middle"><?= $dbSeparationEAN->amount ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>