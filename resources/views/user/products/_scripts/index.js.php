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