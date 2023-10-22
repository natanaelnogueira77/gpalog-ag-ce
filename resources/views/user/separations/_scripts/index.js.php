<script>
    $(function () {
        const app = new App();
        const table = $("#separation-items");
        const filters_form = $("#filters");
        
        const separations_table = $("#separations");
        const separations_filters_form = $("#separations-filters");

        const save_separation_item_form = $("#save-separation-item");
        const save_separation_item_modal = $("#save-separation-item-modal");
        const create_separation_item_btn = $("#create-separation-item");

        const generate_separation_list_btn = $("#generate-separation-list");
        const send_separation_item_list_modal = $("#send-separation-item-list-modal");
        const send_separation_item_list_form = $("#send-separation-item-list-form");

        const bond_pallets_table = $("#bond-pallets");
        const bond_pallets_filters_form = $("#bond-pallets-filters");
        const bond_pallets_modal = $("#bond-pallets-modal");
        
        const separation_item_list_modal = $("#separation-item-list-modal");

        const DTBondPallets = app.table(bond_pallets_table, bond_pallets_table.data('action'));
        DTBondPallets.defaultParams(app.objectifyForm(bond_pallets_filters_form)).filtersForm(bond_pallets_filters_form)
        .setMsgFunc((msg) => app.showMessage(msg.message, msg.type)).loadOnChange().addAction((table) => {
            table.find("[data-act=pallet-action]").click(function () {
                var data = $(this).data();

                app.callAjax({
                    url: data.action,
                    type: data.method,
                    success: function (response) {
                        DTBondPallets.load();
                    }
                });
            });
        });

        const DTSeparationItems = app.table(table, table.data('action'));
        DTSeparationItems.defaultParams(app.objectifyForm(filters_form)).filtersForm(filters_form)
        .setMsgFunc((msg) => app.showMessage(msg.message, msg.type)).loadOnChange().addAction((table) => {
            table.find("[data-act=delete]").click(function () {
                var data = $(this).data();

                if(confirm(<?php echo json_encode(_('Deseja realmente excluir esta separação?')) ?>)) {
                    app.callAjax({
                        url: data.action,
                        type: data.method,
                        success: function (response) {
                            DTSeparationItems.load();
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
                        save_separation_item_form.attr('action', response.save.action);
                        save_separation_item_form.attr('method', response.save.method);

                        app.cleanForm(save_separation_item_form);

                        if(response.content) {
                            app.populateForm(save_separation_item_form, response.content, 'name');
                        }

                        save_separation_item_modal.find("[modal-info=title]").text(
                            <?php echo json_encode(sprintf(_('Editar EAN - %s'), '{separation_ean}')) ?>
                            .replace('{separation_ean}', data.separationEan)
                        );
                        save_separation_item_modal.modal('show');
                    }
                });
            });
        }).addAction((table) => {
            table.find("[data-act=pallets]").click(function () {
                var data = $(this).data();

                bond_pallets_modal.find("[modal-info=title]").text(
                    <?php echo json_encode(sprintf(_('Fazer De Para - %s'), '{separation_ean}')) ?>
                    .replace('{separation_ean}', data.separationEan)
                );

                DTBondPallets.setBaseUrl(data.action).load();
                bond_pallets_modal.modal('show');
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
                            separation_item_list_modal.find('.modal-body').html(response.content);
                        }

                        separation_item_list_modal.find("[modal-info=title]").text(
                            <?php echo json_encode(sprintf(_('Separação - %s'), '{separation_id}')) ?>
                            .replace('{separation_id}', data.separationId)
                        );
                        separation_item_list_modal.modal('show');
                    }
                });
            });
        }).load();

        create_separation_item_btn.click(function () {
            var data = $(this).data();

            save_separation_item_form.attr('action', data.action);
            save_separation_item_form.attr('method', data.method);

            app.cleanForm(save_separation_item_form);
            save_separation_item_modal.find("[modal-info=title]").text(
                <?php echo json_encode(sprintf(_('Fazer Separação'))) ?>
            );

            save_separation_item_modal.modal('show');
        });

        generate_separation_list_btn.click(function () {
            const data = $(this).data();
            app.callAjax({
                url: data.action,
                type: data.method,
                success: function (response) {
                    send_separation_item_list_form.attr('action', response.save.action);
                    send_separation_item_list_form.attr('method', response.save.method);
                    
                    send_separation_item_list_modal.find('.modal-body').html(response.content);
                    send_separation_item_list_modal.modal('show');
                }
            });
        });

        app.form(send_separation_item_list_form, function (response) {
            DTSeparationItems.load();
            DTSeparations.load();
            send_separation_item_list_modal.modal("toggle");
        });

        app.form(save_separation_item_form, function (response) {
            DTSeparationItems.load();
            save_separation_item_modal.modal("toggle");
        });
    });
</script>