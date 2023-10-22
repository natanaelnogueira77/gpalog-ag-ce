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