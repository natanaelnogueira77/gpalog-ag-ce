<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="<?= $icon ?> icon-gradient <?= $icon_color ?>"></i>
            </div>
            <div>
                <?= $title ?>
                <div class="page-title-subheading">
                    <?= $subtitle ?>
                </div>
            </div>
        </div>
        <div class="page-title-actions">
            <?php 
            if(isset($buttons)): 
                foreach($buttons as $button):
                    if($button['type'] == 'tooltip'):
                    ?>
                    <button type="button" data-toggle="tooltip" title="<?= $button['tooltip'] ?>" 
                        data-placement="bottom" class="<?= $button['class'] ?>">
                        <?php echo $button['html'] ?>
                    </button>
                    <?php elseif($button['type'] == 'link'): ?>
                    <a href="<?= $button['url'] ?>" class="<?= $button['class'] ?>">
                        <?= $button['desc'] ?>
                    </a>
                    <?php elseif($button['type'] == 'dropdown'): ?>
                    <div class="d-inline-block dropdown">
                        <button type="button" data-toggle="dropdown" aria-haspopup="true" 
                            aria-expanded="false" class="<?= $button['class'] ?> dropdown-toggle">
                            <?php if($button['icon']): ?>
                            <span class="btn-icon-wrapper pr-2 opacity-7">
                                <i class="<?= $button['icon'] ?>"></i>
                            </span>
                            <?php endif ?>
                            <?= $button['text'] ?>
                        </button>
                        <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                            <ul class="nav flex-column">
                                <?php 
                                if($button['items']): 
                                    foreach($button['items'] as $item):
                                    ?>
                                    <li class="nav-item">
                                        <a href="<?= $item['url'] ?>" class="nav-link">
                                            <?= $item['content'] ?>
                                        </a>
                                    </li>
                                    <?php
                                    endforeach;
                                endif;
                                ?>
                            </ul>
                        </div>
                    </div>
                    <?php 
                    endif;
                endforeach;
            endif;    
            ?>
        </div>    
    </div>
</div>