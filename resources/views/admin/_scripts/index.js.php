<script>
    $(function () {
        const app = new App();
        const table = $("#users");
        const filters_form = $("#filters");

        const mediaLibrary = new MediaLibrary();
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

        $("#logo_upload").click(function () {
            mediaLibrary.setFileTypes(['jpg', 'jpeg', 'png']).setSuccess(function (path) {
                $("#logo").val(path);
                $("img#logo_view").attr("src", `${mediaLibrary.path}/${path}`);
            }).open();
        });

        $("#logo_remove").each(function () {
            $(this).click(function () {
                $(this).parent().children("#logo").val('');
                $(this).parent().parent().find("#logo_view").attr("src", '');
            });
        });

        $("#logo_icon_upload").click(function () {
            mediaLibrary.setFileTypes(['jpg', 'jpeg', 'png']).setSuccess(function (path) {
                $("#logo_icon").val(path);
                $("img#logo_icon_view").attr("src", `${mediaLibrary.path}/${path}`);
            }).open();
        });

        $("#logo_icon_remove").each(function () {
            $(this).click(function () {
                $(this).parent().children("#logo_icon").val('');
                $(this).parent().parent().find("#logo_icon_view").attr("src", '');
            });
        });

        $("#login_img_upload").click(function () {
            mediaLibrary.setFileTypes(['jpg', 'jpeg', 'png']).setSuccess(function (path) {
                $("#login_img").val(path);
                $("img#login_img_view").attr("src", `${mediaLibrary.path}/${path}`);
            }).open();
        });

        $("#login_img_remove").each(function () {
            $(this).click(function () {
                $(this).parent().children("#login_img").val('');
                $(this).parent().parent().find("#login_img_view").attr("src", '');
            });
        });

        app.form($("#system"), function (response) { });

        $("[data-info=users]").click(function() {
            var data = $(this).data();
            $("#panel_users").show('fast');
            
            dataTable.params({
                user_type: data.id
            }).load();

            $('html,body').animate({
                scrollTop: $("#panels_top").offset().top},
                'slow');
        });
    });
</script>