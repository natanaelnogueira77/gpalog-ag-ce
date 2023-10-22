<script>
    $(function () {
        $("[card-link]").mouseover(function () {
            $(this).addClass("border border-primary");
        });

        $("[card-link]").mouseleave(function () {
            $(this).removeClass("border border-primary");
        });
    });
</script>