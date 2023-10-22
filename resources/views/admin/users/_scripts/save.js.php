<script>
    $(function () {
        const app = new App();
        const form = $("#save-user");
        const update_password = $("input[name$='update_password']");
        const password_area = $("#password");

        const user_type = $("select[name$='utip_id']");
        const registration_number_area = $("#registration-number-area");

        update_password.change(function () {
            if($('#update_password1').is(':checked')) {
                password_area.show('fast');
            }

            if($('#update_password2').is(':checked')) {
                password_area.hide('fast');
            }
        });

        user_type.change(function () {
            if($(this).val() == 3) {
                registration_number_area.show('fast');
            } else {
                registration_number_area.hide('fast');
            }
        });

        app.form(form, function (response) {
            if(response.link) window.location.href = response.link;
        });
    });
</script>