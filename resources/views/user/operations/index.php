<?php 
    $theme->title = sprintf(_('Operações | %s'), $appData['app_name']);
    $this->layout("themes/architect-ui/_theme", ['theme' => $theme]);
    
    $this->insert('themes/architect-ui/_components/title', [
        'title' => _('Lista de Operações'),
        'subtitle' => _('Segue abaixo a lista de operações do sistema'),
        'icon' => 'pe-7s-server',
        'icon_color' => 'bg-malibu-beach'
    ]);
?>

<div class="card shadow mb-4 br-15">
    <div class="card-header-tab card-header-tab-animation card-header brt-15">    
        <div class="card-header-title">
            <i class="header-icon icofont-database icon-gradient bg-info"> </i>
            <?= _('Operações') ?>
        </div>

        <div class="btn-actions-pane-right">
            <div role="group" class="btn-group-sm btn-group">
                <button type="button" id="create-operation" class="btn btn-lg btn-primary" 
                    data-action="<?= $router->route('user.operations.store') ?>" data-method="post">
                    <?= _('Dar Entrada') ?>
                </button>

                <a class="btn btn-lg btn-success" href="<?= $router->route('user.operations.export') ?>"
                    target="_blank">
                    <i class="icofont-file-excel"></i>
                    <?= _('Exportar Excel') ?>
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form id="filters">
            <?php $this->insert('_components/data-table-filters', ['formId' => 'filters']); ?>
        </form>

        <div id="operations" data-action="<?= $router->route('user.operations.list') ?>">
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
    $this->insert('user/operations/_scripts/index.js');
    $this->end(); 

    $this->start('modals');
    $this->insert('user/operations/_components/save-modal', [
        'v' => $this,
        'dbProviders' => $dbProviders, 
        'serviceTypes' => $serviceTypes
    ]);
    $this->end();
?>