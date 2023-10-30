<div class="header-area header-transparent" style="background: linear-gradient(to bottom, #4949FF 0%, #7879FF 100%);">
    <div class="main-header">
        <div class="header-bottom  header-sticky" style="background: linear-gradient(to bottom, #4949FF 0%, #7879FF 100%);">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-xl-2 col-lg-2">
                        <div class="logo">
                            <a href="#"><img src="<?= $theme->logo ?>" alt="" height="60px"></a>
                        </div>
                    </div>
                    <div class="col-xl-10 col-lg-10">
                        <div class="menu-wrapper d-flex align-items-center justify-content-end">
                            <div class="main-menu d-none d-lg-block">
                                <nav>
                                    <ul id="navigation">
                                        <?php 
                                        if($theme->header && $theme->header['menu']):
                                            foreach($theme->header['menu'] as $menuItem): 
                                            ?>
                                            <li>
                                                <a href="<?= $menuItem->getURL() ?>"><?= $menuItem->getText() ?></a>
                                            </li>
                                            <?php 
                                            endforeach; 
                                        endif;
                                        ?>

                                        <?php if($theme->header && $theme->header['right']['languages']): ?>
                                        <li>
                                            <a href="#">
                                                <img width="42" class="rounded-circle" src="<?= $right['languages']['curr_img'] ?>" alt="">
                                            </a>
                                            <?php if($theme->header['right']['languages']['items']): ?>
                                            <ul class="submenu">
                                                <li><?= $theme->header['right']['languages']['heading'] ?></li>
                                                <?php foreach($theme->header['right']['languages']['items'] as $language): ?>
                                                <li>
                                                    <a href="<?= $language['url'] ?>"><?= $language["desc"] ?></a>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <?php endif; ?>
                                        </li>
                                        <?php endif; ?>

                                        <?php 
                                        if($theme->header['right']['items']):
                                            foreach($theme->header['right']['items'] as $menuItem):
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