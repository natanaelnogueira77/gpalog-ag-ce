<?php 
    $this->layout("themes/architect-ui/_theme", [
        'title' => sprintf(_('Produtos | %s'), $appData['app_name'])
    ]);
?>

<?php 
    $this->insert('themes/architect-ui/components/title', [
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
                    <?= _('Cadastrar Produto') ?>
                </button>

                <button type="button" id="import-csv" class="btn btn-lg btn-outline-info" data-method="post" 
                    data-action="<?= $router->route('user.products.import') ?>">
                    <i class="icofont-file-excel"></i>
                    <?= _('Importar Produtos') ?>
                </button>

                <a class="btn btn-lg btn-outline-success" href="<?= $router->route('user.products.export') ?>"
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

        <div id="products" data-action="<?= $router->route('user.products.list') ?>">
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
        const table = $("#products");
        const filters_form = $("#filters");

        const save_product_form = $("#save-product");
        const save_product_modal = $("#save-product-modal");
        const create_product_btn = $("#create-product");

        const import_csv_btn = $("#import-csv");
        const import_products_form = $("#import-products");
        const import_products_modal = $("#import-products-modal");

        const dataTable = app.table(table, table.data('action'));
        dataTable.defaultParams(app.objectifyForm(filters_form)).filtersForm(filters_form)
        .setMsgFunc((msg) => app.showMessage(msg.message, msg.type)).loadOnChange().addAction((table) => {
            table.find("[data-act=delete]").click(function () {
                var data = $(this).data();

                if(confirm(<?php echo json_encode(_('Deseja realmente excluir este produto?')) ?>)) {
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
                        save_product_form.attr('action', response.save.action);
                        save_product_form.attr('method', response.save.method);

                        app.cleanForm(save_product_form);

                        if(response.content) {
                            app.populateForm(save_product_form, response.content, 'name');
                        }

                        save_product_modal.find("[modal-info=title]").text(
                            <?php echo json_encode(sprintf(_('Editar Produto - %s'), '{product_name}')) ?>
                            .replace('{product_name}', data.productName)
                        );

                        save_product_modal.modal('show');
                    }
                });
            });
        }).load();

        create_product_btn.click(function () {
            var data = $(this).data();

            app.cleanForm(save_product_form);

            save_product_form.attr("action", data.action);
            save_product_form.attr("method", data.method);
            
            save_product_modal.find("[modal-info=title]").text(<?php echo json_encode(_('Cadastrar Produto')) ?>);
            save_product_modal.modal("show");
        });

        import_csv_btn.click(function () {
            var data = $(this).data();

            import_products_form.attr('action', data.action);
            import_products_form.attr('method', data.method);
            import_products_modal.modal('show');
        });

        app.form(save_product_form, function (response) {
            dataTable.load();
            save_product_modal.modal("toggle");
        });
    });
</script>
<?php $this->end(); ?>

<?php 
$this->start('modals'); 
$this->insert('user/products/components/save-modal');
$this->insert('user/products/components/import-modal');
$this->end(); 
?>