<?php 
    $theme->title = sprintf(_('Ruas | %s'), $appData['app_name']);
    $this->layout("themes/architect-ui/_theme", ['theme' => $theme]);

    $this->insert('themes/architect-ui/_components/title', [
        'title' => _('Lista de Ruas'),
        'subtitle' => _('Segue abaixo a lista de ruas do sistema'),
        'icon' => 'pe-7s-server',
        'icon_color' => 'bg-malibu-beach'
    ]);
?>

<div class="card shadow mb-4 br-15">
    <div class="card-header-tab card-header-tab-animation card-header brt-15">    
        <div class="card-header-title">
            <i class="header-icon icofont-database icon-gradient bg-info"> </i>
            <?= _('Ruas') ?>
        </div>

        <div class="btn-actions-pane-right">
            <div role="group" class="btn-group-sm btn-group">
                <button type="button" id="create-street" class="btn btn-lg btn-primary" 
                    data-action="<?= $router->route('user.streets.store') ?>" data-method="post">
                    <?= _('Cadastrar Rua') ?>
                </button>

                <a class="btn btn-lg btn-success" href="<?= $router->route('user.streets.export') ?>"
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

        <div id="streets" data-action="<?= $router->route('user.streets.list') ?>">
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
    $this->insert('user/streets/_scripts/index.js');
    $this->end(); 

    $this->start('modals');
    $this->insert('user/streets/_components/save-modal', ['v' => $this]);
    $this->end();
?>