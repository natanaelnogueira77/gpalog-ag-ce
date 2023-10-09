<?php 
    $this->layout("themes/architect-ui/_theme", [
        'title' => sprintf(_('Operações | %s'), $appData['app_name'])
    ]);
?>

<?php 
    $this->insert('themes/architect-ui/components/title', [
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

                <a class="btn btn-lg btn-outline-success" href="<?= $router->route('user.operations.export') ?>"
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

        <div id="operations" data-action="<?= $router->route('user.operations.list') ?>">
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
        const table = $("#operations");
        const filters_form = $("#filters");

        const save_operation_form = $("#save-operation");
        const save_operation_area = $("#save-operation-area");
        const save_operation_modal = $("#save-operation-modal");
        const create_operation_btn = $("#create-operation");

        const save_provider_form = $("#save-provider");
        const save_provider_area = $("#save-provider-area");
        const save_provider_return_btn = $("#save-provider-return");
        const create_provider_btn = $("#create-provider");

        const dataTable = app.table(table, table.data('action'));
        dataTable.defaultParams(app.objectifyForm(filters_form)).filtersForm(filters_form)
        .setMsgFunc((msg) => app.showMessage(msg.message, msg.type)).loadOnChange().addAction((table) => {
            table.find("[data-act=delete]").click(function () {
                var data = $(this).data();

                if(confirm(<?php echo json_encode(_('Deseja realmente excluir esta operação?')) ?>)) {
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
                        save_operation_form.attr('action', response.save.action);
                        save_operation_form.attr('method', response.save.method);

                        app.cleanForm(save_operation_form);

                        if(response.content) {
                            app.populateForm(save_operation_form, response.content, 'name');
                        }

                        save_operation_modal.find("[modal-info=title]").text(
                            <?php echo json_encode(sprintf(_('Editar Operação - %s'), '{operation_id}')) ?>
                            .replace('{operation_id}', data.operationId)
                        );

                        save_provider_area.hide();
                        save_operation_area.show();
                        save_operation_modal.modal('show');
                    }
                });
            });
        }).addAction((table) => {
            table.find("[data-act=create-conference]").click(function () {
                var data = $(this).data();

                app.callAjax({
                    url: data.action,
                    type: data.method,
                    success: function (response) {
                        dataTable.load();
                    }
                });
            });
        }).load();

        create_operation_btn.click(function () {
            var data = $(this).data();

            save_operation_form.attr('action', data.action);
            save_operation_form.attr('method', data.method);

            app.cleanForm(save_operation_form);
            save_operation_modal.find("[modal-info=title]").text(
                <?php echo json_encode(sprintf(_('Dar Entrada'))) ?>
            );

            save_operation_modal.modal('show');
        });

        create_provider_btn.click(function () {
            var data = $(this).data();

            save_provider_form.attr('action', data.action);
            save_provider_form.attr('method', data.method);

            app.cleanForm(save_provider_form);

            save_operation_area.hide();
            save_provider_area.show();
        });

        save_provider_return_btn.click(function () {
            save_provider_area.hide();
            save_operation_area.show();
        });

        app.form(save_provider_form, function (response) {
            if(response.content) {
                save_operation_form.find("[name=for_id]").append(`
                    <option value="${response.content.id}">${response.content.name}</option>
                `);
                save_operation_form.find("[name=for_id]").val(response.content.id);
            }

            save_provider_area.hide();
            save_operation_area.show();
        });

        app.form(save_operation_form, function (response) {
            dataTable.load();
            save_operation_modal.modal("toggle");
        });
    });
</script>
<?php $this->end(); ?>

<?php 
    $this->start('modals');
    $this->insert('user/operations/components/save-modal', [
        'dbProviders' => $dbProviders, 
        'serviceTypes' => $serviceTypes
    ]);
    $this->end();
?>