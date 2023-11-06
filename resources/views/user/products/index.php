<?php 
    $theme->title = sprintf(_('Produtos | %s'), $appData['app_name']);
    $this->layout("themes/architect-ui/_theme", ['theme' => $theme]);

    $this->insert('themes/architect-ui/_components/title', [
        'title' => _('Lista de Produtos'),
        'subtitle' => _('Segue abaixo a lista de produtos do sistema'),
        'icon' => 'pe-7s-server',
        'icon_color' => 'bg-malibu-beach'
    ]);
?>

<div class="card shadow mb-4 br-15">
    <div class="card-header-tab card-header-tab-animation card-header brt-15">    
        <div class="card-header-title">
            <i class="header-icon icofont-database icon-gradient bg-info"> </i>
            <?= _('Produtos') ?>
        </div>

        <div class="btn-actions-pane-right">
            <div role="group" class="btn-group-sm btn-group">
                <button type="button" id="create-product" class="btn btn-lg btn-primary" 
                    data-action="<?= $router->route('user.products.store') ?>" data-method="post">
                    <i class="icofont-plus"></i>
                    <?= _('Cadastrar Produto') ?>
                </button>

                <button type="button" id="import-csv" class="btn btn-lg btn-info" data-method="post" 
                    data-action="<?= $router->route('user.products.import') ?>">
                    <i class="icofont-file-excel"></i>
                    <?= _('Importar Produtos') ?>
                </button>

                <a class="btn btn-lg btn-success" href="<?= $router->route('user.products.export') ?>"
                    target="_blank">
                    <i class="icofont-file-excel"></i>
                    <?= _('Exportar Excel') ?>
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <?php if($importErrors): ?>
        <div class="alert alert-danger">
            <?php 
                foreach($importErrors as $index => $error) {
                    echo sprintf(_('Linha %s: %s'), end(explode('_', $index)) + 1, $error) . '<br>';
                }
            ?>
        </div>
        <?php endif; ?>

        <form id="filters">
            <?php $this->insert('_components/data-table-filters', ['formId' => 'filters']); ?>
        </form>

        <div id="products" data-action="<?= $router->route('user.products.list') ?>">
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
    $this->insert('user/products/_scripts/index.js');
    $this->end(); 
    
    $this->start('modals'); 
    $this->insert('user/products/_components/save-modal', ['v' => $this]);
    $this->insert('user/products/_components/import-modal');
    $this->end(); 
?>