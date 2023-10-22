<div class="header-area header-transparent" style="background: linear-gradient(to bottom, #4949FF 0%, #7879FF 100%);">
    <div class="main-header">
        <div class="header-bottom  header-sticky" style="background: linear-gradient(to bottom, #4949FF 0%, #7879FF 100%);">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-xl-2 col-lg-2">
                        <div class="logo">
                            <a href="#"><img src="<?= $logo ?>" alt="" height="60px"></a>
                        </div>
                    </div>
                    <div class="col-xl-10 col-lg-10">
                        <div class="menu-wrapper d-flex align-items-center justify-content-end">
                            <div class="main-menu d-none d-lg-block">
                                <nav>
                                    <ul id="navigation">
                                        <?php 
                                        if($menu):
                                            foreach($menu as $menuItem): 
                                            ?>                                                                                  
                                            <li>
                                                <a href="<?= $menuItem->getURL() ?>"><?= $menuItem->getText() ?></a>
                                            </li>
                                            <?php 
                                            endforeach; 
                                        endif;
                                        ?>

                                        <?php if($right['languages']): ?>
                                        <li>
                                            <a href="#">
                                                <img width="42" class="rounded-circle" src="<?= $right['languages']['curr_img'] ?>" alt="">
                                            </a>
                                            <?php if($right["languages"]['items']): ?>
                                            <ul class="submenu">
                                                <li><?= $right['languages']['heading'] ?></li>
                                                <?php foreach($right["languages"]['items'] as $language): ?>
                                                <li>
                                                    <a href="<?= $language['url'] ?>"><?= $language["desc"] ?></a>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <?php endif; ?>
                                        </li>
                                        <?php endif; ?>

                                        <?php 
                                        if($right["items"]):
                                            foreach($right["items"] as $menuItem):
                                            ?>
                                            <li class="button-header">
                                                <a href="<?= $menuItem->getURL() ?>" class="btn btn3">
                                                    <?= $menuItem->getText() ?>
                                                </a>
                                            </li>
                                            <?php 
                                            endforeach;
                                        endif;
                                        ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mobile_menu d-block d-lg-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Header End -->