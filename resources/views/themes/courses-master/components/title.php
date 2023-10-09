<section class="slider-area slider-area2">
    <div class="slider-active">
        <div class="single-slider slider-height2" 
            style="<?= $bg_color ? "background-color: {$bg_color}" : '' ?>; <?= $bg_img ? "background-image: url('{$bg_img}');" : '' ?>">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8 col-lg-11 col-md-12">
                        <div class="hero__caption hero__caption2">
                            <h1 data-animation="<?= $title["animation"]["effect"] ?>" 
                                data-delay="<?= $title["animation"]["delay"]  ?>">
                                <?= $title["text"] ?>
                            </h1>
                            <p data-animation="<?= $subtitle["animation"]["effect"] ?>" 
                                data-delay="<?= $subtitle["animation"]["delay"]  ?>">
                                <?= $subtitle["text"] ?>
                            </p>

                            <?php if($cta_button): ?>
                            <a href="<?= $cta_button['url'] ?>" class="btn hero-btn" 
                                data-animation="<?= $cta_button['animation']['effect'] ?>" 
                                data-delay="<?= $cta_button['animation']['delay'] ?>">
                                <?= $cta_button['text'] ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>