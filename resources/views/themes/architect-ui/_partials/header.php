<div class="app-header header-shadow <?= $theme->header['color'] ? $theme->header['color'] : "bg-heavy-rain header-text-dark" ?>">
    <div class="app-header__logo">
        <img class="logo-src" src="<?= $theme->logo ?>" style="height: 60px; width: auto;">
        <?php if($theme->header['left']): ?>
        <div class="header__pane ml-auto">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
        <?php endif ?>
    </div>
    <?php if($theme->header['left']): ?>
    <div class="app-header__mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>
    <?php endif ?>
    <div class="app-header__menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
            </button>
        </span>
    </div>
    <div class="app-header__content">
        <div class="app-header-left">
            <ul class="header-menu nav">
                <?php 
                if($theme->header && $theme->header['menu']):
                    foreach($theme->header['menu'] as $menuItem):
                    ?>
                    <li class="btn-group nav-item">
                        <a href="<?= $menuItem->getURL() ?>" class="nav-link">
                            <i class="<?= $menuItem->getIcon() ?>"></i>
                            <?= $menuItem->getText() ?>
                        </a>
                    </li>
                    <?php 
                    endforeach;
                endif;
                ?>
            </ul>        
        </div>

        <?php if($theme->header['right']["show"]): ?>
        <div class="app-header-right">
            <?php if($theme->header['right']['bell']): ?>
            <div class="header-btn-lg pr-2">
                <div class="widget-content p-0">
                    <div class="widget-content-wrapper">
                        <div class="widget-content-left">
                            <div class="btn-group">
                                <a id="bell-notifications" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" 
                                    class="p-0 btn">
                                    <?php if($theme->header['right']['bell']['notifications_count']): ?>
                                    <div class="badge badge-pill badge-danger position-absolute p-1 ml-0">
                                        <?= $theme->header['right']['bell']['notifications_count'] ?>
                                    </div>
                                    <?php endif; ?>
                                    <i class="icofont-alarm" style="font-size: 2.4rem;"></i>
                                </a>
                                <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                    <h6 tabindex="-1" class="dropdown-header"><?= $theme->header['right']['bell']['title'] ?></h6>
                                    <?php 
                                    if($theme->header['right']['bell']['notifications']):
                                        foreach($theme->header['right']['bell']['notifications'] as $notification):
                                        ?>
                                        <div tabindex="-1" class="dropdown-divider my-0 <?= $notification->wasRead() ? 'bg-light' : '' ?>"></div>
                                        <p class="px-3 py-2 mb-0 <?= $notification->wasRead() ? 'bg-light' : '' ?>">
                                            <?= $notification->getContent() ?>
                                        </p>
                                        <?php 
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if($theme->header['right']['languages']): ?>
            <div class="header-btn-lg pr-2">
                <div class="widget-content p-0">
                    <div class="widget-content-wrapper">
                        <div class="widget-content-left">
                            <div class="btn-group">
                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                    <img width="42" class="rounded-circle" src="<?= $theme->header['right']['languages']['curr_img'] ?>" alt="">
                                </a>
                                <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                    <h6 tabindex="-1" class="dropdown-header"><?= $theme->header['right']['languages']['heading'] ?></h6>
                                    <div tabindex="-1" class="dropdown-divider"></div>
                                    <?php 
                                    if($theme->header['right']["languages"]['items']):
                                        foreach($theme->header['right']["languages"]['items'] as $language):
                                        ?>
                                        <a href="<?= $language['url'] ?>" tabindex="0" class="dropdown-item">
                                            <?= $language["desc"] ?>
                                        </a>
                                        <?php 
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if($theme->header['right']['avatar']): ?>
            <div class="header-btn-lg pr-0">
                <div class="widget-content p-0">
                    <div class="widget-content-wrapper">
                        <div class="widget-content-left">
                            <div class="btn-group">
                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                    <img width="42" class="rounded-circle" src="<?= $theme->header['right']["avatar"] ?>" alt="user">
                                    <i class="fa fa-angle-down ml-2 opacity-8"></i>
                                </a>
                                <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                    <?php 
                                    if($theme->header['right']["items"]):
                                        foreach($theme->header['right']["items"] as $menuItem):
                                            if(!isset($menuItem->getMetadata()['divider'])): 
                                            ?>
                                            <a href="<?= $menuItem->getURL() ?>" tabindex="0" class="dropdown-item">
                                                <?= $menuItem->getText() ?>
                                            </a>
                                            <?php else: ?>
                                            <div tabindex="-1" class="dropdown-divider"></div>
                                            <?php 
                                            endif; 
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="widget-content-left  ml-3 header-user-info">
                            <div class="widget-heading">
                                <?= $theme->header['right']["avatar_title"] ?>
                            </div>
                            <div class="widget-subheading">
                                <?= $theme->header['right']["avatar_subtitle"] ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>