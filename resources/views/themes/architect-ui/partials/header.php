<div class="app-header header-shadow <?= $color ? $color : "bg-heavy-rain header-text-dark" ?>">
    <div class="app-header__logo">
        <img class="logo-src" src="<?= $logo ?>" style="height: 60px; width: auto;">
        <?php if($left): ?>
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
    <?php if($left): ?>
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
                if($menu):
                    foreach($menu as $menuItem):
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

        <?php if($right["show"]): ?>
        <div class="app-header-right">
            <?php if($right['bell']): ?>
            <div class="header-btn-lg pr-2">
                <div class="widget-content p-0">
                    <div class="widget-content-wrapper">
                        <div class="widget-content-left">
                            <div class="btn-group">
                                <a id="bell-notifications" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" 
                                    class="p-0 btn">
                                    <?php if($right['bell']['notifications_count']): ?>
                                    <div class="badge badge-pill badge-danger position-absolute p-1 ml-0">
                                        <?= $right['bell']['notifications_count'] ?>
                                    </div>
                                    <?php endif; ?>
                                    <i class="icofont-alarm" style="font-size: 2.4rem;"></i>
                                </a>
                                <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                    <h6 tabindex="-1" class="dropdown-header"><?= $right['bell']['title'] ?></h6>
                                    <?php 
                                    if($right["bell"]['notifications']):
                                        foreach($right["bell"]['notifications'] as $notification):
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

            <?php if($right['languages']): ?>
            <div class="header-btn-lg pr-2">
                <div class="widget-content p-0">
                    <div class="widget-content-wrapper">
                        <div class="widget-content-left">
                            <div class="btn-group">
                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                    <img width="42" class="rounded-circle" src="<?= $right['languages']['curr_img'] ?>" alt="">
                                </a>
                                <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                    <h6 tabindex="-1" class="dropdown-header"><?= $right['languages']['heading'] ?></h6>
                                    <div tabindex="-1" class="dropdown-divider"></div>
                                    <?php 
                                    if($right["languages"]['items']):
                                        foreach($right["languages"]['items'] as $language):
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

            <?php if($right['avatar']): ?>
            <div class="header-btn-lg pr-0">
                <div class="widget-content p-0">
                    <div class="widget-content-wrapper">
                        <div class="widget-content-left">
                            <div class="btn-group">
                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                    <img width="42" class="rounded-circle" src="<?= $right["avatar"] ?>" alt="user">
                                    <i class="fa fa-angle-down ml-2 opacity-8"></i>
                                </a>
                                <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                    <?php 
                                    if($right["items"]):
                                        foreach($right["items"] as $menuItem):
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
                                <?= $right["avatar_title"] ?>
                            </div>
                            <div class="widget-subheading">
                                <?= $right["avatar_subtitle"] ?>
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