<?php 
    $this->layout("themes/architect-ui/_theme", [
        'title' => sprintf(_('Ruas | %s'), $appData['app_name'])
    ]);
?>

<?php 
    $this->insert('themes/architect-ui/components/title', [
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

                <a class="btn btn-lg btn-outline-success" href="<?= $router->route('user.streets.export') ?>"
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

        <div id="streets" data-action="<?= $router->route('user.streets.list') ?>">
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
        const table = $("#streets");
        const filters_form = $("#filters");

        const save_street_form = $("#save-street");
        const save_street_modal = $("#save-street-modal");
        const create_street_btn = $("#create-street");

        const is_limitless_checkbox = $("#is_limitless");
        const has_limit_areas = $("[data-condition=has-limit]");

        const dataTable = app.table(table, table.data('action'));
        dataTable.defaultParams(app.objectifyForm(filters_form)).filtersForm(filters_form)
        .setMsgFunc((msg) => app.showMessage(msg.message, msg.type)).loadOnChange().addAction((table) => {
            table.find("[data-act=delete]").click(function () {
                var data = $(this).data();

                if(confirm(<?php echo json_encode(_('Deseja realmente excluir esta rua?')) ?>)) {
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
                        save_street_form.attr('action', response.save.action);
                        save_street_form.attr('method', response.save.method);

                        app.cleanForm(save_street_form);

                        if(response.content) {
                            app.populateForm(save_street_form, response.content, 'name');
                            if(response.content.is_limitless) {
                                has_limit_areas.hide();
                            } else {
                                has_limit_areas.show();
                            }
                        }

                        save_street_modal.find("[modal-info=title]").text(
                            <?php echo json_encode(sprintf(_('Editar Produto - %s'), '{street_number}')) ?>
                            .replace('{street_number}', data.streetNumber)
                        );

                        save_street_modal.modal('show');
                    }
                });
            });
        }).load();

        create_street_btn.click(function () {
            var data = $(this).data();

            app.cleanForm(save_street_form);
            has_limit_areas.show();
            
            save_street_form.attr("action", data.action);
            save_street_form.attr("method", data.method);
            
            save_street_modal.find("[modal-info=title]").text(<?php echo json_encode(_('Cadastrar Rua')) ?>);
            save_street_modal.modal("show");
        });

        is_limitless_checkbox.change(function () {
            if($(this).is(":checked")) {
                has_limit_areas.hide('fast');
            } else {
                has_limit_areas.show('fast');
            }
        });

        app.form(save_street_form, function (response) {
            dataTable.load();
            save_street_modal.modal("toggle");
        });
    });
</script>
<?php $this->end(); ?>

<?php 
$this->start('modals');
$this->insert('user/streets/components/save-modal');
$this->end();
?>