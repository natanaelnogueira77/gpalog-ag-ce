<script>
    $(function () {
        const app = new App();
        <?php if($dbNotifications): ?>
        $("#bell-notifications").click(function () {
            $.ajax({
                url: <?php echo json_encode($router->route('user.notifications.markAllAsRead')) ?>,
                type: 'patch',
                data: {},
                dataType: 'json'
            });
        });
        <?php endif; ?>
    });
</script>