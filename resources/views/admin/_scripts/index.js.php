<script>
    $(function () {
        const app = new App();
        const table = $("#users");
        const filters_form = $("#filters");

        const mediaLibrary = new MediaLibrary();
        const FSLogo = (new FileSelector(
            '#logo-area', 
            mediaLibrary.setFileTypes(['jpg', 'jpeg', 'png'])
        ))
        <?php if($configMetas['logo']): ?>
        .loadFiles({
            uri: <?php echo json_encode($configMetas['logo']) ?>,
            url: <?php echo json_encode(url($configMetas['logo'])) ?>
        })
        <?php endif; ?>
        .render();

        const FSLogoIcon = (new FileSelector(
            '#logo-icon-area', 
            mediaLibrary.setFileTypes(['jpg', 'jpeg', 'png'])
        ))
        <?php if($configMetas['logo_icon']): ?>
        .loadFiles({
            uri: <?php echo json_encode($configMetas['logo_icon']) ?>,
            url: <?php echo json_encode(url($configMetas['logo_icon'])) ?>
        })
        <?php endif; ?>
        .render();

        const FSLoginImg = (new FileSelector(
            '#login-img-area', 
            mediaLibrary.setFileTypes(['jpg', 'jpeg', 'png'])
        ))
        <?php if($configMetas['login_img']): ?>
        .loadFiles({
            uri: <?php echo json_encode($configMetas['login_img']) ?>,
            url: <?php echo json_encode(url($configMetas['login_img'])) ?>
        })
        <?php endif; ?>
        .render();

        const dataTable = app.table(table, table.data('action'));
        dataTable.defaultParams(app.objectifyForm(filters_form)).filtersForm(filters_form)
        .setMsgFunc((msg) => app.showMessage(msg.message, msg.type)).loadOnChange().addAction((table) => {
            table.find("[data-act=delete]").click(function () {
                var data = $(this).data();

                if(confirm(<?php echo json_encode(_('Deseja realmente excluir este usuário?')) ?>)) {
                    app.callAjax({
                        url: data.action,
                        type: data.method,
                        success: function (response) {
                            dataTable.load();
                        }
                    });
                }
            });
        }).load();

        $("#system").submit(function (e) {
            e.preventDefault();

            const form = $(this);
            var formData = app.objectifyForm(form);
            formData['logo'] = FSLogo.getURIList();
            formData['logo_icon'] = FSLogoIcon.getURIList();
            formData['login_img'] = FSLoginImg.getURIList();

            app.callAjax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: formData,
                success: function (response) {
                    window.location.reload();
                }, 
                error: function (response) {
                    var errors = [];
                    if(response.errors) {
                        errors = response.errors;
                    }

                    app.showFormErrors(form, errors, 'name');
                }
            });
        });

        $("[data-info=users]").click(function() {
            var data = $(this).data();
            $("#panel_users").show('fast');
            
            dataTable.params({ user_type: data.id }).load();

            $('html,body').animate({ scrollTop: $("#panels_top").offset().top }, 'slow');
        });
    });
</script>