<?php 
    $this->layout("themes/architect-ui/_theme", [
        'title' => sprintf(_('Separação | %s'), $appData['app_name'])
    ]);
?>

<?php 
    $this->insert('themes/architect-ui/components/title', [
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
            <?= _('Nova Separação') ?>
        </div>

        <div class="btn-actions-pane-right">
            <div role="group" class="btn-group-sm btn-group">
                <button type="button" id="create-separation-ean" class="btn btn-lg btn-primary" 
                    data-action="<?= $router->route('user.separationEANs.store') ?>" data-method="post">
                    <?= _('Adicionar Produto à Lista') ?>
                </button>
                
                <button type="button" id="generate-separation-list" class="btn btn-lg btn-danger" 
                    data-action="<?= $router->route('user.separations.getSeparationTable') ?>" data-method="get">
                    <?= _('Enviar Para Separação') ?>
                </button>

                <a class="btn btn-lg btn-outline-success" href="<?= $router->route('user.separations.export') ?>"
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

        <div id="separation-eans" data-action="<?= $router->route('user.separationEANs.list') ?>">
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
            <?php $this->insert('components/data-table-filters', ['formId' => 'separations-filters']); ?>
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

<?php $this->start('scripts'); ?>
<script>
    $(function () {
        const app = new App();
        const table = $("#separation-eans");
        const filters_form = $("#filters");
        
        const separations_table = $("#separations");
        const separations_filters_form = $("#separations-filters");

        const save_separation_ean_form = $("#save-separation-ean");
        const save_separation_ean_modal = $("#save-separation-ean-modal");
        const create_separation_ean_btn = $("#create-separation-ean");

        const generate_separation_list_btn = $("#generate-separation-list");
        const send_separation_ean_list_modal = $("#send-separation-ean-list-modal");
        const send_separation_ean_list_form = $("#send-separation-ean-list-form");
        
        const separation_ean_list_modal = $("#separation-ean-list-modal");

        const DTSeparationEANs = app.table(table, table.data('action'));
        DTSeparationEANs.defaultParams(app.objectifyForm(filters_form)).filtersForm(filters_form)
        .setMsgFunc((msg) => app.showMessage(msg.message, msg.type)).loadOnChange().addAction((table) => {
            table.find("[data-act=delete]").click(function () {
                var data = $(this).data();

                if(confirm(<?php echo json_encode(_('Deseja realmente excluir esta separação?')) ?>)) {
                    app.callAjax({
                        url: data.action,
                        type: data.method,
                        success: function (response) {
                            DTSeparationEANs.load();
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
                        save_separation_ean_form.attr('action', response.save.action);
                        save_separation_ean_form.attr('method', response.save.method);

                        app.cleanForm(save_separation_ean_form);

                        if(response.content) {
                            app.populateForm(save_separation_ean_form, response.content, 'name');
                        }

                        save_separation_ean_modal.find("[modal-info=title]").text(
                            <?php echo json_encode(sprintf(_('Editar EAN - %s'), '{separation_ean}')) ?>
                            .replace('{separation_ean}', data.separationEan)
                        );
                        save_separation_ean_modal.modal('show');
                    }
                });
            });
        }).load();

        const DTSeparations = app.table(separations_table, separations_table.data('action'));
        DTSeparations.defaultParams(app.objectifyForm(separations_filters_form)).filtersForm(separations_filters_form)
        .setMsgFunc((msg) => app.showMessage(msg.message, msg.type)).loadOnChange().addAction((table) => {
            table.find("[data-act=delete]").click(function () {
                var data = $(this).data();

                if(confirm(<?php echo json_encode(_('Deseja realmente excluir esta lista de separação?')) ?>)) {
                    app.callAjax({
                        url: data.action,
                        type: data.method,
                        success: function (response) {
                            DTSeparations.load();
                        }
                    });
                }
            });
        }).addAction((table) => {
            table.find("[data-act=show]").click(function () {
                const data = $(this).data();

                app.callAjax({
                    url: data.action,
                    type: data.method,
                    success: function (response) {
                        if(response.content) {
                            separation_ean_list_modal.find('.modal-body').html(response.content);
                        }

                        separation_ean_list_modal.find("[modal-info=title]").text(
                            <?php echo json_encode(sprintf(_('Separação - %s'), '{separation_id}')) ?>
                            .replace('{separation_id}', data.separationId)
                        );
                        separation_ean_list_modal.modal('show');
                    }
                });
            });
        }).load();

        create_separation_ean_btn.click(function () {
            var data = $(this).data();

            save_separation_ean_form.attr('action', data.action);
            save_separation_ean_form.attr('method', data.method);

            app.cleanForm(save_separation_ean_form);
            save_separation_ean_modal.find("[modal-info=title]").text(
                <?php echo json_encode(sprintf(_('Fazer Separação'))) ?>
            );

            save_separation_ean_modal.modal('show');
        });

        generate_separation_list_btn.click(function () {
            const data = $(this).data();
            app.callAjax({
                url: data.action,
                type: data.method,
                success: function (response) {
                    send_separation_ean_list_form.attr('action', response.save.action);
                    send_separation_ean_list_form.attr('method', response.save.method);
                    
                    send_separation_ean_list_modal.find('.modal-body').html(response.content);
                    send_separation_ean_list_modal.modal('show');
                }
            });
        });

        app.form(send_separation_ean_list_form, function (response) {
            DTSeparationEANs.load();
            DTSeparations.load();
            send_separation_ean_list_modal.modal("toggle");
        });

        app.form(save_separation_ean_form, function (response) {
            DTSeparationEANs.load();
            save_separation_ean_modal.modal("toggle");
        });
    });
</script>
<?php $this->end(); ?>

<?php 
$this->start('modals'); 
$this->insert('user/separation-eans/components/save-modal', [
    'v' => $this,
    'amountTypes' => $amountTypes
]);
$this->insert('user/separations/components/send-list-modal');
$this->insert('user/separations/components/list-modal');
$this->end();
?>