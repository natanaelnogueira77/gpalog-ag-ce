<?php 
    $this->layout("themes/architect-ui/_theme", [
        'title' => sprintf(_('Histórico de Entradas e Separação | %s'), $appData['app_name'])
    ]);

    $this->insert('themes/architect-ui/_components/title', [
        'title' => _('Histórico de Entrada e Separação'),
        'subtitle' => _('Veja abaixo o histórico de operação de entradas e separação. 
            Você pode gerar o PDF das etiquetas de entrada ou de separação.'),
        'icon' => 'pe-7s-date',
        'icon_color' => 'bg-malibu-beach'
    ]);
?>

<div class="card shadow mb-4 br-15">
    <div class="card-header-tab card-header-tab-animation card-header brt-15">    
        <div class="card-header-title">
            <i class="header-icon icofont-calendar icon-gradient bg-info"> </i>
            <?= _('Conferências') ?>
        </div>

        <div class="btn-actions-pane-right">
            <div role="group" class="btn-group-sm btn-group">
                <button type="button" id="export-excel" class="btn btn-outline-success btn-lg" 
                    data-action="<?= $router->route('user.inputOutputHistory.export') ?>" data-method="get">
                    <i class="icofont-file-excel"></i>
                    <?= _('Exportar Excel') ?>
                </button>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form id="filters">
            <?php $this->insert('_components/data-table-filters', ['formId' => 'filters']); ?>
            <div class="form-row"> 
                <div class="form-group col-md-4 col-sm-6">
                    <label><?= _('Ordem de Serviço') ?></label>
                    <input type="text" name="order_number" class="form-control" 
                        placeholder="<?= _('Digite a ordem de serviço...') ?>">
                </div>
            </div>
        </form>

        <div id="input-output-history" data-action="<?= $router->route('user.inputOutputHistory.list') ?>">
            <div class="d-flex justify-content-around p-5">
                <div class="spinner-grow text-secondary" role="status">
                    <span class="visually-hidden"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
    $this->start('scripts'); 
    $this->insert('user/input-output-history/_scripts/index.js');
    $this->end();
    
    $this->start('modals');
    $this->insert('user/input-output-history/_components/export-modal');
    $this->end();
?>