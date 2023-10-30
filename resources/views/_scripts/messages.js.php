<script>
    $(function () {
        const app = new App();
        <?php 
        foreach(['success', 'error', 'info'] as $type):
            if($session->getFlash($type)): 
            ?>
            app.showMessage(
                <?php echo json_encode($session->getFlash($type)) ?>, 
                <?php echo json_encode($type) ?>,
                5000, 
                5000, 
                5000, 
                'toast-bottom-right'
            );
            <?php 
            endif;  
        endforeach;
        ?>
    });
</script>