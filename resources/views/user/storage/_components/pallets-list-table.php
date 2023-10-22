<div class="responsive-table-lg">
    <table class="table table-bordered table-striped table-bordered align-middle m-0">
        <thead>
            <th class="align-middle"><?= _('Número do Pallet') ?></th>
            <th class="align-middle"><?= _('Produto') ?></th>
            <th class="align-middle"><?= _('EAN') ?></th>
            <th class="align-middle"><?= _('Posição') ?></th>
            <th class="align-middle"><?= _('Altura') ?></th>
            <th class="align-middle"><?= _('Embalagem') ?></th>
            <th class="align-middle"><?= _('Caixas') ?></th>
            <th class="align-middle"><?= _('Unidades') ?></th>
            <th class="align-middle"><?= _('Serviço') ?></th>
        </thead>
        <tbody>
            <?php if($pallets): ?>
            <?php foreach($pallets as $pallet): ?>
                <tr>
                    <td class="align-middle"><?= $pallet->code ?></td>
                    <td class="align-middle"><?= $pallet->product->name ?></td>
                    <td class="align-middle"><?= $pallet->product->ean ?></td>
                    <td class="align-middle"><?= $pallet->position ?></td>
                    <td class="align-middle"><?= $pallet->height ?></td>
                    <td class="align-middle"><?= $pallet->package ?></td>
                    <td class="align-middle"><?= $pallet->boxes_amount ?></td>
                    <td class="align-middle"><?= $pallet->units_amount ?></td>
                    <td class="align-middle"><?= $pallet->getServiceType() ?></td>
                </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <td class="align-middle text-center" colspan="9">
                <?= _('Nenhum pallet foi encontrado!') ?>
            </td>
            <?php endif; ?>
        </tbody>
    </table>
</div>