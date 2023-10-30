<?php 
    $theme->title = sprintf(_('Administrador | %s'), $appData['app_name']);
    $this->layout("themes/architect-ui/_theme", ['theme' => $theme]);

    $this->insert('themes/architect-ui/_components/title', [
        'title' => _('Painel do Administrador'),
        'subtitle' => _('Relatórios e gerenciamento do sistema'),
        'icon' => 'pe-7s-home',
        'icon_color' => 'bg-malibu-beach'
    ]);
?>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow mb-3 br-15">
            <div class="card-header-tab card-header-tab-animation card-header brt-15">    
                <div class="card-header-title">
                    <i class="header-icon icofont-gear icon-gradient bg-night-sky"> </i>
                    <?= _('Informações da Aplicação') ?>
                </div>
            </div>

            <div class="card-body">
                <div class="card-text"><?= sprintf(_('Versão: <strong>%s</strong>'), $appData['app_version']) ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="main-card mb-3 card br-15">
            <?php if($userTypes): ?>
            <ul class="list-group list-group-flush">
                <?php foreach($userTypes as $userType): ?>
                <li class="list-group-item">
                    <div class="widget-content p-0">
                        <div class="widget-content-outer">
                            <div class="widget-content-wrapper mb-2">
                                <div class="widget-content-left">
                                    <div class="widget-heading"><?= $userType->name_plur ?></div>
                                    <div class="widget-subheading">
                                        <?= sprintf(_("%s do sistema"), $userType->name_plur) ?>
                                    </div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers text-success">
                                        <?= $usersCount[$userType->id] ?? 0 ?>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-content-wrapper">
                                <div class="widget-content-right">
                                    <button class="btn btn-lg btn-success" data-info="users" data-id="<?= $userType->id ?>">
                                        <?= sprintf(_("Ver %s"), $userType->name_plur) ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="panels_top"></div>

<div class="card shadow mb-4 panels br-15" id="panel_users" style="display: none;">
    <div class="card-header-tab card-header-tab-animation card-header brt-15">    
        <div class="card-header-title">
            <i class="header-icon icofont-investigator icon-gradient bg-night-sky"> </i>
            <?= _('Usuários') ?>
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

<div class="card shadow mb-4 br-15">
    <div class="card-header-tab card-header-tab-animation card-header brt-15">    
        <div class="card-header-title">
            <i class="header-icon icofont-gear icon-gradient bg-night-sky"> </i>
            <?= _('Configurações do Sistema') ?>
        </div>
    </div>

    <form id="system" action="<?= $router->route('admin.system') ?>" method="put">
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="style">
                        <?= _('Tema') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Escolha o tema de cores do sistema.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <select id="style" name="style" class="form-control">
                        <option value=""><?= _('Escolha o tema de cores do sistema...') ?></option>
                        <option value="light" <?= $configMetas['style'] == 'light' ? 'selected' : '' ?>>
                            <?= _('Tema Claro') ?>
                        </option>
                        <option value="dark" <?= $configMetas['style'] == 'dark' ? 'selected' : '' ?>>
                            <?= _('Tema Escuro') ?>
                        </option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group col-md-6">
                    <label for="login_img">
                        <?= _('Imagem de Fundo (Login)') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Escolha a imagem que ficará de fundo na página de login.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <div id="login-img-area"></div>
                    <small class="text-danger" data-error="login_img"></small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="logo">
                        <?= _('Logo') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Escolha a imagem que ficará como logo do sistema.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <div id="logo-area"></div>
                    <small class="text-danger" data-error="logo"></small>
                </div>

                <div class="form-group col-md-6">
                    <label for="logo_icon">
                        <?= _('Ícone (Tamanho Recomendado: 512 x 512)') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Escolha a imagem que ficará como ícone do sistema.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <div id="logo-icon-area"></div>
                    <small class="text-danger" data-error="logo_icon"></small>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-around brb-15">
            <input type="submit" class="btn btn-md btn-success btn-block" value="<?= _('Salvar Configurações') ?>">
        </div>
    </form>
</div>

<?php 
    $this->start('scripts'); 
    $this->insert('admin/_scripts/index.js', [
        'configMetas' => $configMetas
    ]);
    $this->end(); 

    $this->start('modals');
    $this->insert('_components/media-library', ['v' => $this]);
    $this->end();
?>