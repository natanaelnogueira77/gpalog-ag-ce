<?php 
    $this->layout("themes/architect-ui/_theme", [
        'title' => sprintf(_('Fornecedores | %s'), $appData['app_name'])
    ]);
?>

<?php 
    $this->insert('themes/architect-ui/components/title', [
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
            <?php $this->insert('components/data-table-filters', ['formId' => 'filters']); ?>
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

<?php $this->start('scripts'); ?>
<script>
    $(function () {
        const app = new App();
        const table = $("#providers");
        const filters_form = $("#filters");

        const save_provider_form = $("#save-provider");
        const save_provider_modal = $("#save-provider-modal");
        const create_provider_btn = $("#create-provider");

        const import_csv_btn = $("#import-csv");
        const import_providers_form = $("#import-providers");
        const import_providers_modal = $("#import-providers-modal");

        const dataTable = app.table(table, table.data('action'));
        dataTable.defaultParams(app.objectifyForm(filters_form)).filtersForm(filters_form)
        .setMsgFunc((msg) => app.showMessage(msg.message, msg.type)).loadOnChange().addAction((table) => {
            table.find("[data-act=delete]").click(function () {
                var data = $(this).data();

                if(confirm(<?php echo json_encode(_('Deseja realmente excluir este fornecedor?')) ?>)) {
                    app.callAjax({
                        url: data.action,
                        type: data.method,
                        success: function (response) {
                            dataTable.load();
                        }
                    });
                }
            });
        }).addAction((table) => {
            table.find("[data-act=edit]").click(function () {
                const data = $(this).data();

                app.callAjax({
                    url: data.action,
                    type: data.method,
                    success: function (response) {
                        save_provider_form.attr('action', response.save.action);
                        save_provider_form.attr('method', response.save.method);

                        app.cleanForm(save_provider_form);

                        if(response.content) {
                            app.populateForm(save_provider_form, response.content, 'name');
                        }

                        save_provider_modal.find("[modal-info=title]").text(
                            <?php echo json_encode(sprintf(_('Editar Fornecedor - %s'), '{provider_name}')) ?>
                            .replace('{provider_name}', data.providerName)
                        );

                        save_provider_modal.modal('show');
                    }
                });
            });
        }).load();

        create_provider_btn.click(function () {
            var data = $(this).data();

            app.cleanForm(save_provider_form);

            save_provider_form.attr("action", data.action);
            save_provider_form.attr("method", data.method);
            
            save_provider_modal.find("[modal-info=title]").text(<?php echo json_encode(_('Cadastrar Fornecedor')) ?>);
            save_provider_modal.modal("show");
        });

        import_csv_btn.click(function () {
            var data = $(this).data();

            import_providers_form.attr('action', data.action);
            import_providers_form.attr('method', data.method);
            import_providers_modal.modal('show');
        });

        app.form(save_provider_form, function (response) {
            dataTable.load();
            save_provider_modal.modal("toggle");
        });
    });
</script>
<?php $this->end(); ?>

<?php 
$this->start('modals'); 
$this->insert('user/providers/components/save-modal');
$this->insert('user/providers/components/import-modal');
$this->end(); 
?>