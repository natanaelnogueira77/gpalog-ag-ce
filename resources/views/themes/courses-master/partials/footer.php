<footer>
    <div class="footer-wrappper footer-bg">
        <!-- Footer Start-->
        <div class="footer-area footer-padding">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-xl-4 col-lg-5 col-md-4 col-sm-6">
                        <div class="single-footer-caption mb-50">
                            <div class="single-footer-caption mb-30">
                                <div class="footer-logo mb-25">
                                    <a href="#"><img src="<?= $logo ?>" alt="" height="120px"></a>
                                </div>
                                <div class="footer-tittle">
                                    <div class="footer-pera"></div>
                                </div>
                                <?php if($socials): ?>
                                <div class="footer-social">
                                    <?php foreach($socials as $social): ?>
                                    <a href="<?= $social['url'] ?>"><i class="fab <?= $social['icon'] ?>"></i></a>
                                    <?php endforeach ?>
                                </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-5">
                        <div class="single-footer-caption mb-50">
                            <div class="footer-tittle">
                                <h4><?= _('Menu') ?></h4>
                                <ul>
                                    <?php foreach($items as $menuItem): ?>   
                                    <li><a href="<?= $menuItem->getURL() ?>"><?= $menuItem->getText() ?></a></li>
                                    <?php endforeach ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- footer-bottom area -->
        <div class="footer-bottom-area">
            <div class="container">
                <div class="footer-border">
                    <div class="row d-flex align-items-center">
                        <div class="col-xl-12 ">
                            <div class="footer-copy-right text-center text-white">
                                Copyright &copy;<script>document.write(new Date().getFullYear());</script> 
                                <?= _('Todos os direitos reservados.') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer End-->
    </div>
</footer> 
<div id="back-top">
    <a title="<?= _('Subir') ?>" href="#"> <i class="fas fa-level-up-alt"></i></a>
</div>