<?php 
    $this->layout("themes/architect-ui/_theme", [
        'title' => sprintf(_('Administrador | %s'), $appData['app_name'])
    ]);
?>

<?php 
    $this->insert('themes/architect-ui/components/title', [
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
            <?php $this->insert('components/data-table-filters', ['formId' => 'filters']); ?>
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
                    <div class="d-flex justify-content-around">
                        <img id="login_img_view" style="max-height: 100px; max-width: 100%;" 
                            src="<?= url($configMetas['login_img']) ?>">
                    </div>

                    <div class="d-block text-center mt-2">
                        <input type="hidden" id="login_img" name="login_img" value="<?= $configMetas['login_img'] ?>">
                        <button type="button" class="btn btn-outline-primary btn-md" id="login_img_upload">
                            <i class="icofont-upload-alt"></i> <?= _('Escolher Imagem') ?>
                        </button>
                        
                        <button type="button" class="btn btn-outline-danger btn-md" id="login_img_remove">
                            <i class="icofont-close"></i> <?= _('Remover Imagem') ?>
                        </button>
                    </div>
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
                    <div class="d-flex justify-content-around">
                        <img id="logo_view" style="max-height: 100px; max-width: 100%;" 
                            src="<?= url($configMetas['logo']) ?>">
                    </div>

                    <div class="d-block text-center mt-2">
                        <input type="hidden" id="logo" name="logo" value="<?= $configMetas['logo'] ?>">
                        <button type="button" class="btn btn-outline-primary btn-md" id="logo_upload">
                            <i class="icofont-upload-alt"></i> <?= _('Escolher Imagem') ?>
                        </button>

                        <button type="button" class="btn btn-outline-danger btn-md" id="logo_remove">
                            <i class="icofont-close"></i> <?= _('Remover Imagem') ?>
                        </button>
                    </div>
                    <small class="text-danger" data-error="logo"></small>
                </div>

                <div class="form-group col-md-6">
                    <label for="logo_icon">
                        <?= _('Ícone (Tamanho Recomendado: 512 x 512)') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Escolha a imagem que ficará como ícone do sistema.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <div class="d-flex justify-content-around">
                        <img id="logo_icon_view" style="max-height: 100px; max-width: 100%;" 
                            src="<?= url($configMetas['logo_icon']) ?>">
                    </div>

                    <div class="d-block text-center mt-2">
                        <input type="hidden" id="logo_icon" name="logo_icon" value="<?= $configMetas['logo_icon'] ?>">
                        <button type="button" class="btn btn-outline-primary btn-md" id="logo_icon_upload">
                            <i class="icofont-upload-alt"></i> <?= _('Escolher Imagem') ?>
                        </button>
                        
                        <button type="button" class="btn btn-outline-danger btn-md" id="logo_icon_remove">
                            <i class="icofont-close"></i> <?= _('Remover Imagem') ?>
                        </button>
                    </div>
                    <small class="text-danger" data-error="logo_icon"></small>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-around brb-15">
            <input type="submit" class="btn btn-md btn-success btn-block" value="<?= _('Salvar Configurações') ?>">
        </div>
    </form>
</div>

<?php $this->start('scripts'); ?>
<script>
    $(function () {
        const app = new App();
        const table = $("#users");
        const filters_form = $("#filters");

        const mediaLibrary = new MediaLibrary();
        const dataTable = app.table(table, table.data('action'));
        dataTable.defaultParams(app.objectifyForm(filters_form)).filtersForm(filters_form)
        .setMsgFunc((msg) => app.showMessage(msg.message, msg.type)).loadOnChange().addAction((table) => {
            table.find("[data-act=delete]").click(function () {
                var data = $(this).data();

                if(confirm(<?php echo json_encode(_('Deseja realmente excluir este usuário?')) ?>)) {
                    app.callAjax({
                        url: data.action,
                        type: data.method,
                        success: function (response) {
                            dataTable.load();
                        }
                    });
                }
            });
        }).load();

        $("#logo_upload").click(function () {
            mediaLibrary.setFileTypes(['jpg', 'jpeg', 'png']).setSuccess(function (path) {
                $("#logo").val(path);
                $("img#logo_view").attr("src", `${mediaLibrary.path}/${path}`);
            }).open();
        });

        $("#logo_remove").each(function () {
            $(this).click(function () {
                $(this).parent().children("#logo").val('');
                $(this).parent().parent().find("#logo_view").attr("src", '');
            });
        });

        $("#logo_icon_upload").click(function () {
            mediaLibrary.setFileTypes(['jpg', 'jpeg', 'png']).setSuccess(function (path) {
                $("#logo_icon").val(path);
                $("img#logo_icon_view").attr("src", `${mediaLibrary.path}/${path}`);
            }).open();
        });

        $("#logo_icon_remove").each(function () {
            $(this).click(function () {
                $(this).parent().children("#logo_icon").val('');
                $(this).parent().parent().find("#logo_icon_view").attr("src", '');
            });
        });

        $("#login_img_upload").click(function () {
            mediaLibrary.setFileTypes(['jpg', 'jpeg', 'png']).setSuccess(function (path) {
                $("#login_img").val(path);
                $("img#login_img_view").attr("src", `${mediaLibrary.path}/${path}`);
            }).open();
        });

        $("#login_img_remove").each(function () {
            $(this).click(function () {
                $(this).parent().children("#login_img").val('');
                $(this).parent().parent().find("#login_img_view").attr("src", '');
            });
        });

        app.form($("#system"), function (response) { });

        $("[data-info=users]").click(function() {
            var data = $(this).data();
            $("#panel_users").show('fast');
            
            dataTable.params({
                user_type: data.id
            }).load();

            $('html,body').animate({
                scrollTop: $("#panels_top").offset().top},
                'slow');
        });
    });
</script>
<?php $this->end(); ?>