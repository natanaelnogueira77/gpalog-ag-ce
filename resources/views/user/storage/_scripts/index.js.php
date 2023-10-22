<script>
    $(function () {
        const app = new App();
        const pallets_list_modal = $("#pallets-list-modal");

        $("[data-act=check-pallets]").click(function () {
            const data = $(this).data();

            app.callAjax({
                url: data.action,
                type: data.method,
                success: function (response) {
                    pallets_list_modal.find('.modal-body').children().remove();
                    pallets_list_modal.find('.modal-body').append(response.content);

                    pallets_list_modal.find('[modal-info=title]').html(
                        <?php echo json_encode(sprintf(_('Rua %s - Pallets'), '{street_number}')) ?>
                        .replace('{street_number}', data.streetNumber)
                    );

                    pallets_list_modal.modal('show');
                }
            });
        });
    });
</script>