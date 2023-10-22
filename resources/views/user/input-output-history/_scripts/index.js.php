<script>
    $(function () {
        const app = new App();
        const table = $("#input-output-history");
        const filters_form = $("#filters");

        const export_excel_btn = $("#export-excel");
        const export_history_form = $("#export-history");
        const export_history_modal = $("#export-history-modal");

        const dataTable = app.table(table, table.data('action'));
        dataTable.defaultParams(app.objectifyForm(filters_form)).filtersForm(filters_form)
        .setMsgFunc((msg) => app.showMessage(msg.message, msg.type)).loadOnChange().load();

        export_excel_btn.click(function () {
            var data = $(this).data();

            export_history_form.attr('action', data.action);
            export_history_form.attr('method', data.method);
            export_history_modal.modal('show');
        });
    });
</script>