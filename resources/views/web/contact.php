<?php 
    $this->layout("themes/courses-master/_theme", [
        'title' => sprintf(_('Contato | %s'), $appData['app_name'])
    ]);
?>

<?php 
    $this->insert('themes/courses-master/components/title', [
        'bg_color' => '#6DB3F2',
        'title' => [
            'text' => _('Contato'),
            'animation' => ['effect' => 'bounceIn', 'delay' => '.2s']
        ],
        'subtitle' => [
            'text' => _('Para entrar em contato conosco para maiores esclarecimentos, preencha o formulário abaixo com seu 
                nome, email, assunto e mensagem e clique em "Enviar". Seu feedback é altamente apreciado.'),
            'animation' => ['effect' => 'bounceIn', 'delay' => '.5s']
        ]
    ]);
?>

<main>
    <section class="contact-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="contact-title"><?= _('Entre em Contato') ?></h2>
                </div>

                <div class="col-lg-8">
                    <form id="contact-form" class="form-contact" action="<?= $router->route('contact.index') ?>" method="post">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <input class="form-control <?= $contactForm->hasError('subject') ? 'is-invalid' : '' ?>" 
                                        name="subject" id="subject" type="text" 
                                        onfocus="this.placeholder = ''" 
                                        onblur="this.placeholder = '<?= _('Qual é o assunto?') ?>'" 
                                        placeholder="<?= _('Qual é o assunto?') ?>" value="<?= $contactForm->subject ?>">
                                    <div class="invalid-feedback">
                                        <?= $contactForm->hasError('subject') ? $contactForm->getFirstError('subject') : '' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <input class="form-control <?= $contactForm->hasError('name') ? 'is-invalid' : '' ?>" 
                                        name="name" id="name" type="text" 
                                        onfocus="this.placeholder = ''" 
                                        onblur="this.placeholder = '<?= _('Digite seu nome') ?>'" 
                                        placeholder="<?= _('Digite seu nome') ?>" 
                                        value="<?= isset($user) ? $user->name : $contactForm->name ?>">
                                    <div class="invalid-feedback">
                                        <?= $contactForm->hasError('name') ? $contactForm->getFirstError('name') : '' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <input class="form-control <?= $contactForm->hasError('email') ? 'is-invalid' : '' ?>" 
                                        name="email" id="email" type="email" 
                                        onfocus="this.placeholder = ''" 
                                        onblur="this.placeholder = '<?= _('Digite seu email') ?>'" 
                                        placeholder="<?= _('Digite seu email') ?>" 
                                        value="<?= isset($user) ? $user->email : $contactForm->email ?>">
                                    <div class="invalid-feedback">
                                        <?= $contactForm->hasError('email') ? $contactForm->getFirstError('email') : '' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <textarea class="form-control w-100 <?= $contactForm->hasError('body') ? 'is-invalid' : '' ?>" 
                                        name="body" id="body" cols="30" rows="9" 
                                        onfocus="this.placeholder = ''" 
                                        onblur="this.placeholder = '<?= _('Digite sua mensagem') ?>'" 
                                        placeholder=" <?= _('Digite sua mensagem') ?>"><?= $contactForm->body ?></textarea>
                                    <div class="invalid-feedback">
                                        <?= $contactForm->hasError('body') ? $contactForm->getFirstError('body') : '' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
                            <input type="submit" data-sitekey="<?= $appData['recaptcha']['site_key'] ?>"
                                data-callback='onSubmit' data-action='submit' 
                                class="g-recaptcha button button-contactForm boxed-btn" value="<?= _('Enviar') ?>">
                        </div>
                    </form>
                </div>

                <div class="col-lg-3 offset-lg-1">
                    <div class="media contact-info">
                        <span class="contact-info__icon"><i class="ti-email"></i></span>
                        <div class="media-body">
                            <p><?= _('Envie-nos sua dúvida a qualquer hora!') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php $this->start('scripts'); ?>
<script>
    function onSubmit(token) {
        document.getElementById("contact-form").submit();
    }
</script>
<?php $this->end(); ?>