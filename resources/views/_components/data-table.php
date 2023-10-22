<div class="table-responsive-lg" style="overflow-y: visible;">
    <table class="align-middle mb-0 table table-borderless table-striped table-hover">
        <thead>
            <?php 
            if($headers):
                foreach($headers as $info => $head): 
                ?>
                <th class="align-middle <?= $head['classes'] ?>">
                    <?php if($head['sort']): ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="mb-0"><?= $head["text"] ?></p>
                        <div class="d-flex flex-column">
                            <i class="icofont-arrow-up text-<?= $order['selected'] == $info && $order['type'] == "ASC" ? "secondary" : "light" ?>" 
                                data-order="<?= $info ?>" data-order-type="ASC" style="cursor: pointer;"></i>
                            <i class="icofont-arrow-down text-<?= $order['selected'] == $info && $order['type'] == "DESC" ? "secondary" : "light" ?>" 
                                data-order="<?= $info ?>" data-order-type="DESC" style="cursor: pointer;"></i>
                        </div>
                    </div>
                    <?php else: ?>
                    <?= $head['text'] ?>
                    <?php endif; ?>
                </th>
                <?php 
                endforeach;
            endif;
            ?>
        </thead>
        <tbody>
            <?php if($data): ?>
            <?php foreach($data as $row): ?>
            <tr>
                <?php foreach($headers as $info => $head): ?>
                <td class="align-middle"><?= $row[$info] ?></td>
                <?php endforeach ?>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <td class="align-middle text-center" colspan="<?= is_array($headers) ? count($headers) : 0 ?>">
                <?= _('Nenhum dado correspondente foi encontrado!') ?>
            </td>
            <?php endif; ?>
        </tbody>
    </table>
</div>