<?php 
    $theme->title = sprintf(_('Usuários | %s'), $appData['app_name']);
    $this->layout("themes/architect-ui/_theme", ['theme' => $theme]);
    
    $this->insert('themes/architect-ui/_components/title', [
        'title' => _('Lista de Usuários'),
        'subtitle' => _('Segue abaixo a lista de usuários do sistema'),
        'icon' => 'pe-7s-users',
        'icon_color' => 'bg-malibu-beach'
    ]);
?>

<div class="card shadow mb-4 br-15">
    <div class="card-header-tab card-header-tab-animation card-header brt-15">    
        <div class="card-header-title">
            <i class="header-icon icofont-users icon-gradient bg-info"> </i>
            <?= _('Usuários') ?>
        </div>

        <div class="btn-actions-pane-right">
            <div role="group" class="btn-group-sm btn-group">
                <a class="btn btn-lg btn-primary" href="<?= $router->route('admin.users.create') ?>">
                    <?= _('Cadastrar Usuário') ?>
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form id="filters">
            <?php $this->insert('_components/data-table-filters', ['formId' => 'filters']); ?>
            <div class="form-row"> 
                <div class="form-group col-md-4 col-sm-6">
                    <label><?= _('Nível de Usuário') ?></label>
                    <select name="user_type" class="form-control">
                        <option value=""><?= _('Todos os Usuários') ?></option>
                        <?php 
                            if($userTypes) {
                                foreach($userTypes as $userType) {
                                    echo "<option value=\"{$userType->id}\">{$userType->name_plur}</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
            </div>
        </form>

        <div id="users" data-action="<?= $router->route('admin.users.list') ?>">
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
    $this->insert('admin/users/_scripts/index.js');
    $this->end(); 
?>