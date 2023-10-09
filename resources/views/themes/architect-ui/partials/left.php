<div class="app-sidebar sidebar-shadow <?= $color ? $color : "bg-heavy-rain sidebar-text-dark" ?>">
    <div class="app-header__logo">
        <div class="logo-src"></div>
        <div class="header__pane ml-auto">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>

    <div class="app-header__mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>

    <div class="app-header__menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
            </button>
        </span>
    </div>
    
    <div class="scrollbar-container"></div>
    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">
                <?php $nextItem = 0; ?>
                <?php foreach($menu as $menuItem): ?>
                <?php $nextItem++; ?>
                <?php if($menuItem->isHeading()): ?>
                <li class="app-sidebar__heading"><?= $menuItem->getText() ?></li>
                <?php if($menuItem->getLevel() > $menu[$nextItem]->getLevel() && $menuItem->getLevel() != 1): ?>
                <?php for($i = 0; $i < $menuItem->getLevel() - $menu[$nextItem]->getLevel(); $i++): ?>
                </ul>
                </li>
                <?php endfor ?>
                <?php endif ?>
                <?php elseif($menuItem->isItem()): ?>
                <li>
                    <a href="<?= strpos($menuItem->getURL(), "http") !== false ? $menuItem->getURL() : url($menuItem->getURL()) ?>" 
                        class="<?= $active == url($menuItem->getURL()) ? 'mm-active' : '' ?>">
                        <i class="<?= $menuItem->getIcon() ?>"></i>
                        <?= $menuItem->getText() ?>
                        <?php if(isset($menu[$nextItem]) && $menuItem->getLevel() < $menu[$nextItem]->getLevel()): ?>
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        <?php endif ?>
                    </a>
                    <?php if(isset($menu[$nextItem]) && $menuItem->getLevel() < $menu[$nextItem]->getLevel()): ?>
                    <ul>
                    <?php elseif(isset($menu[$nextItem]) && $menuItem->getLevel() > $menu[$nextItem]->getLevel() && $menuItem->getLevel() != 1): ?>
                    </li>
                    <?php for($i = 0; $i < $menuItem->getLevel() - $menu[$nextItem]->getLevel(); $i++): ?>
                    </ul>
                    </li>
                    <?php endfor ?>
                    <?php else: ?>
                    </li>
                    <?php endif ?>
                <?php endif ?>
                <?php endforeach ?>
            </ul>
        </div>
    </div>
</div>