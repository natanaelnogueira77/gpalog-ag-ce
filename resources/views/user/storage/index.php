<?php 
    $this->layout("themes/architect-ui/_theme", [
        'title' => sprintf(_('Armazenagem | %s'), $appData['app_name'])
    ]);
    
    $this->insert('themes/architect-ui/_components/title', [
        'title' => _('Armazenagem'),
        'subtitle' => _('Veja abaixo como está o armazenamento atualmente'),
        'icon' => 'pe-7s-server',
        'icon_color' => 'bg-malibu-beach'
    ]);
?>

<div class="card shadow mb-4 br-15">
    <div class="card-header-tab card-header-tab-animation card-header brt-15">    
        <div class="card-header-title">
            <i class="header-icon icofont-database icon-gradient bg-info"> </i>
            <?= _('Armazenagem') ?>
        </div>

        <div class="btn-actions-pane-right">
            <div role="group" class="btn-group-sm btn-group">
                <a class="btn btn-lg btn-outline-success" href="<?= $router->route('user.storage.export') ?>"
                    target="_blank">
                    <i class="icofont-file-excel"></i>
                    <?= _('Exportar Excel') ?>
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive-lg">
            <table class="align-middle mb-0 table table-bordered">
                <thead class="bg-info text-white">
                    <th class="text-center align-middle"><?= _('Ver') ?></th>
                    <th class="text-center align-middle"><?= _('Ruas') ?></th>
                    <th class="text-center align-middle" colspan="25"><?= _('Armazenamento') ?></th>
                    <th class="text-center align-middle"><?= _('Livre') ?></th>
                    <th class="text-center align-middle"><?= _('Ocupado') ?></th>
                    <th class="text-center align-middle"><?= _('Total') ?></th>
                </thead>
                <tbody>
                    <?php foreach($dbStreets as $dbStreet): ?>
                    <tr>
                        <td class="text-center align-middle" rowspan="3">
                            <button type="button" class="btn btn-sm btn-primary" 
                                data-action="<?= $router->route('user.storage.getStreetPallets', ['street_id' => $dbStreet->id]) ?>" 
                                data-method="get" data-act="check-pallets" data-street-number="<?= $dbStreet->street_number ?>">
                                <i class="icofont-eye"></i>
                            </button>
                        </td>
                        <td class="text-center align-middle" rowspan="3">
                            <strong><?= sprintf(_('Rua %s'), $dbStreet->street_number) ?></strong>
                        </td>
                        <?php for($i = 0.02; $i < 0.51; $i += 0.02): ?>
                        <td class="<?= !$dbStreet->isLimitless() && $dbStreet->allocateds >= floor($dbStreet->max_pallets * $i) 
                            ? 'bg-danger' : 'bg-success' ?>"></td>
                        <?php endfor; ?>
                        <td class="text-center align-middle" rowspan="3">
                            <?= $dbStreet->isLimitless() ? '---' : $dbStreet->max_pallets - $dbStreet->allocateds ?>
                        </td>
                        <td class="text-center align-middle" rowspan="3"><?= $dbStreet->allocateds ?? 0 ?></td>
                        <td class="text-center align-middle" rowspan="3">
                            <?= $dbStreet->isLimitless() ? '---' : ($dbStreet->max_pallets ?? 0) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center align-middle" colspan="25">
                            <strong>
                                <?= sprintf(_('Capacidade Total: %s'), $dbStreet->isLimitless() ? _('Bloqueio') : $dbStreet->max_pallets) ?>
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <?php for($i = 0.52; $i < 1.01; $i += 0.02): ?>
                        <td class="<?= !$dbStreet->isLimitless() && $dbStreet->allocateds >= floor($dbStreet->max_pallets * $i) 
                            ? 'bg-danger' : 'bg-success' ?>"></td>
                        <?php endfor; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-secondary text-white">
                    <tr>
                        <td class="text-center align-middle"></td>
                        <td class="text-center align-middle"><?= _('Totais') ?></td>
                        <td class="text-center align-middle" colspan="25">
                            <h3><strong><?= sprintf(_('Total Para Armazenagem: %s'), $storageCapacity) ?></strong></h3>
                        </td>
                        <td class="text-center align-middle"><?= $freeAmount ?></td>
                        <td class="text-center align-middle"><?= $allocatedAmount ?></td>
                        <td class="text-center align-middle"><?= $storageCapacity ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php 
    $this->start('scripts');
    $this->insert('user/storage/_scripts/index.js');
    $this->end();
    
    $this->start('modals');
    $this->insert('user/storage/_components/pallets-list-modal');
    $this->end();
?>