<?php 
    $theme->title = sprintf(_('Separação | %s'), $appData['app_name']);
    $this->layout("themes/architect-ui/_theme", ['theme' => $theme]);
    
    $this->insert('themes/architect-ui/_components/title', [
        'title' => _('Separação'),
        'subtitle' => _('Gere a lista de separação dos produtos.'),
        'icon' => 'pe-7s-next-2',
        'icon_color' => 'bg-malibu-beach'
    ]);
?>

<div class="card shadow mb-4 br-15">
    <div class="card-header-tab card-header-tab-animation card-header brt-15">    
        <div class="card-header-title">
            <i class="header-icon icofont-logout icon-gradient bg-info"> </i>
            <?= _('Nova Lista de Separação') ?>
        </div>

        <div class="btn-actions-pane-right">
            <div role="group" class="btn-group-sm btn-group">
                <button type="button" id="create-separation-item" class="btn btn-sm btn-primary" 
                    data-action="<?= $router->route('user.separationItems.store') ?>" data-method="post">
                    <i class="icofont-plus"></i>
                    <?= _('Adicionar Produto') ?>
                </button>

                <button type="button" id="import-csv" class="btn btn-sm btn-info" data-method="post" 
                    data-action="<?= $router->route('user.separationItems.import') ?>">
                    <i class="icofont-file-excel"></i>
                    <?= _('Importar') ?>
                </button>
                
                <button type="button" id="generate-separation-list" class="btn btn-sm btn-danger" 
                    data-action="<?= $router->route('user.separations.getSeparationTable') ?>" data-method="get">
                    <i class="icofont-paper-plane"></i>
                    <?= _('Enviar Para Separação') ?>
                </button>
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

        <div id="separation-items" data-action="<?= $router->route('user.separationItems.list') ?>">
            <div class="d-flex justify-content-around p-5">
                <div class="spinner-grow text-secondary" role="status">
                    <span class="visually-hidden"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4 br-15">
    <div class="card-header-tab card-header-tab-animation card-header brt-15">    
        <div class="card-header-title">
            <i class="header-icon icofont-logout icon-gradient bg-info"> </i>
            <?= _('Listas de Separação') ?>
        </div>
    </div>

    <div class="card-body">
        <form id="separations-filters">
            <?php $this->insert('_components/data-table-filters', ['formId' => 'separations-filters']); ?>
            <div class="form-row"> 
                <div class="form-group col-md-4 col-sm-6">
                    <label><?= _('Status') ?></label>
                    <select name="separation_status" class="form-control">
                        <option value=""><?= _('Todos os Status') ?></option>
                        <?php 
                            if($states) {
                                foreach($states as $stId => $status) {
                                    echo "<option value=\"{$stId}\">{$status}</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
            </div>
        </form>

        <div id="separations" data-action="<?= $router->route('user.separations.list') ?>">
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
    $this->insert('user/separations/_scripts/index.js');
    $this->end(); 
    
    $this->start('modals'); 
    $this->insert('user/separation-items/_components/save-modal', [
        'v' => $this,
        'amountTypes' => $amountTypes
    ]);
    $this->insert('user/separations/_components/send-list-modal');
    $this->insert('user/separation-items/_components/bond-pallets-modal', ['v' => $this]);
    $this->insert('user/separations/_components/list-modal');
    $this->insert('user/separation-items/_components/import-modal');
    $this->end();
?>