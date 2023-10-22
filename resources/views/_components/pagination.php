<div class="row">
    <div class="col-sm-12 col-md-4">
        <div>
            <?= 
                sprintf(
                    _('Mostrando %s à %s de %s resultado(s)'), 
                    $results ? $limit * ($currPage - 1) + 1 : 0, 
                    $currPage < $pages ? $limit * $currPage : $results, 
                    $results
                )
            ?>
        </div>
    </div>

    <div class="col-sm-12 col-md-8">
        <nav>
            <ul class="pagination justify-content-end flex-wrap">
                <li class="page-item <?= $currPage > 1 ? '' : 'disabled' ?>">
                    <a class="page-link" data-page="<?= ($currPage - 1) ?>"><?= _('Anterior') ?></a>
                </li>

                <?php 
                if($pages): 
                    for(
                        $i = $currPage - 5 >= 1 
                        ? (
                            $currPage >= $pages - 5 
                            ? ($pages > 10 ? $pages - 10 : 1)
                            : $currPage - 5
                        ) : 1; 
                        $i <= $pages 
                            && $i >= $currPage - ($currPage >= $pages - 5 ? 10 - ($pages - $currPage) : 5) 
                            && $i <= $currPage + ($currPage <= 5 ? 10 - $currPage : 5);
                        $i++
                    ):
                    ?>
                    <li class="page-item <?= $i == $currPage ? 'active' : '' ?>">
                        <a class="page-link" data-page="<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php 
                    endfor;
                endif;
                ?>

                <li class="page-item <?= $currPage < $pages ? '' : 'disabled' ?>">
                    <a class="page-link" data-page="<?= ($currPage + 1) ?>"><?= _('Próxima') ?></a>
                </li>
            </ul>
        </nav>
    </div>
</div>