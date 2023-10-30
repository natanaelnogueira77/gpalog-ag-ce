<script>
    $(function () {
        const app = new App();

        const login_modal = $("#login-modal");
        const login_form = $("#login-form");

        var is_session_expired = false;

        function check_session() 
        {
            app.callAjax({
                url: <?php echo json_encode($router->route('auth.expired')) ?>,
                type: 'post',
                success: function (response) {
                    if(response.success) {
                        login_modal.modal("show");
                        is_session_expired = true;
                    }
                },
                noLoad: true
            });
        }
        
        var count_interval = setInterval(function () {
            check_session();
            if(is_session_expired == true) {
                clearInterval(count_interval);
            }
        }, 10000);

        app.form(login_form, function (response) {
            login_modal.modal('toggle');
        });
    });
</script>