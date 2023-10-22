<?php 
    $this->layout("themes/architect-ui/_theme", [
        'title' => sprintf(_('Fornecedores | %s'), $appData['app_name'])
    ]);

    $this->insert('themes/architect-ui/_components/title', [
        'title' => _('Lista de Fornecedores'),
        'subtitle' => _('Segue abaixo a lista de fornecedores do sistema'),
        'icon' => 'pe-7s-users',
        'icon_color' => 'bg-malibu-beach'
    ]);
?>

<div class="card shadow mb-4 br-15">
    <div class="card-header-tab card-header-tab-animation card-header brt-15">    
        <div class="card-header-title">
            <i class="header-icon icofont-users icon-gradient bg-info"> </i>
            <?= _('Fornecedores') ?>
        </div>

        <div class="btn-actions-pane-right">
            <div role="group" class="btn-group-sm btn-group">
                <button type="button" id="create-provider" class="btn btn-lg btn-primary" 
                    data-action="<?= $router->route('user.providers.store') ?>" data-method="post">
                    <?= _('Cadastrar Fornecedor') ?>
                </button>

                <button type="button" id="import-csv" class="btn btn-lg btn-outline-info" data-method="post" 
                    data-action="<?= $router->route('user.providers.import') ?>">
                    <i class="icofont-file-excel"></i>
                    <?= _('Importar Fornecedores') ?>
                </button>

                <a class="btn btn-lg btn-outline-success" href="<?= $router->route('user.providers.export') ?>"
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

        <div id="providers" data-action="<?= $router->route('user.providers.list') ?>">
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
    $this->insert('user/providers/_scripts/index.js');
    $this->end(); 

    $this->start('modals'); 
    $this->insert('user/providers/_components/save-modal', ['v' => $this]);
    $this->insert('user/providers/_components/import-modal');
    $this->end(); 
?>