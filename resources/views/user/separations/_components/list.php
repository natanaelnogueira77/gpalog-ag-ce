<table class="table table-bordered table-striped table-hover">
    <thead>
        <th class="align-middle"><?= _('EAN') ?></th>
        <th class="align-middle"><?= _('Tipo de Quantidade') ?></th>
        <th class="align-middle"><?= _('Quantidade') ?></th>
        <th class="align-middle"><?= _('Número do Pallet') ?></th>
        <th class="align-middle"><?= _('Rua') ?></th>
        <th class="align-middle"><?= _('Posição') ?></th>
        <th class="align-middle"><?= _('Altura') ?></th>
    </thead>
    <tbody>
        <?php foreach($dbSeparationItems as $dbSeparationItem): ?>
        <tr>
            <td class="align-middle"><?= $dbSeparationItem->product->ean ?></td>
            <td class="align-middle"><?= $dbSeparationItem->getAmountType() ?></td>
            <td class="align-middle"><?= $dbSeparationItem->amount ?></td>
            <td class="align-middle">
                <?= $dbSeparationItem->pallet->code ?>
                <?php 
                    if($dbSeparationItem->pallets) {
                        foreach($dbSeparationItem->pallets as $pallet) {
                            echo "<br>{$pallet->code}";
                        }
                    }
                ?>
            </td>
            <td class="align-middle">
                <?= $dbSeparationItem->pallet->street_number ?>
                <?php 
                    if($dbSeparationItem->pallets) {
                        foreach($dbSeparationItem->pallets as $pallet) {
                            echo "<br>{$pallet->street_number}";
                        }
                    }
                ?>
            </td>
            <td class="align-middle">
                <?= $dbSeparationItem->pallet->position ?>
                <?php 
                    if($dbSeparationItem->pallets) {
                        foreach($dbSeparationItem->pallets as $pallet) {
                            echo "<br>{$pallet->position}";
                        }
                    }
                ?>
            </td>
            <td class="align-middle">
                <?= $dbSeparationItem->pallet->height ?>
                <?php 
                    if($dbSeparationItem->pallets) {
                        foreach($dbSeparationItem->pallets as $pallet) {
                            echo "<br>{$pallet->height}";
                        }
                    }
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>