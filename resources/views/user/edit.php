<?php 
    $this->layout("themes/architect-ui/_theme", [
        'title' => sprintf(_('Editar Conta | %s'), $appData['app_name'])
    ]);
?>

<?php 
    $this->insert('themes/architect-ui/components/title', [
        'title' => _('Editar Conta'),
        'subtitle' => _('Edite os detalhes de sua conta logo abaixo'),
        'icon' => 'pe-7s-user',
        'icon_color' => 'bg-malibu-beach'
    ]);
?>

<form id="save-user" action="<?= $router->route('user.edit.update') ?>" method="put">
    <div class="card shadow mb-4 br-15">
        <div class="card-header-tab card-header-tab-animation card-header brt-15">    
            <div class="card-header-title">
                <i class="header-icon icofont-user icon-gradient bg-malibu-beach"> </i>
                <?= _('Informações da Conta') ?>
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Preencha os campos abaixo para editar os dados de sua conta. Então, clique em "Salvar".') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </div>
        </div>

        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="name">
                        <?= _('Nome') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Digite o seu nome e sobrenome.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <input type="text" id="name" name="name" placeholder="<?= _('Informe um nome...') ?>"
                        class="form-control" value="<?= $user->name ?>" maxlength="50">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group col-md-6">
                    <label for="slug">
                        <?= _('Apelido') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Digite um apelido seu.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">@</span>
                        </div>
                        <input type="text" id="slug" name="slug" placeholder="<?= _('Informe um apelido...') ?>"
                            class="form-control" value="<?= $user->slug ?>" maxlength="50">
                        <div id="slug-feedback" class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="email">
                        <?= _('Email') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Digite um email válido seu.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <input type="email" id="email" name="email" placeholder="<?= _('Informe um email...') ?>"
                        class="form-control" value="<?= $user->email ?>"  maxlength="100">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12 align-middle">
                    <div class="d-flex">
                        <p class="mb-0 mr-2">
                            <strong><?= _('Deseja alterar a senha?') ?></strong>
                        </p>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="update_password" id="update_password1" value="1">
                            <label class="form-check-label" for="update_password1">
                                <?= _('Sim') ?>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="update_password" id="update_password2" value="0" checked>
                            <label class="form-check-label" for="update_password2">
                                <?= _('Não') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-row" id="password" style="display: none;">
                <div class="form-group col-md-6">
                    <label for="password">
                        <?= _('Senha') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Digite a nova senha de acesso à sua conta.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <input type="password" id="password" name="password" 
                        placeholder="<?= _('Digite uma senha...') ?>" class="form-control">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group col-md-6">
                    <label for="password_confirm">
                        <?= _('Confirmar Senha') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Digite novamente a nova senha de acesso à sua conta.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <input type="password" id="password_confirm" name="password_confirm" 
                        placeholder="<?= _('Digite novamente a senha...') ?>" class="form-control">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class="card-footer d-block text-center brb-15">
            <input type="submit" class="btn btn-lg btn-success" value="<?= _('Atualizar') ?>">
            <a href="<?= $router->route('user.index') ?>" class="btn btn-danger btn-lg">
                <?= _('Voltar') ?>
            </a>
        </div>
    </div>
</form>

<?php $this->start('scripts'); ?>
<script>
    $(function () {
        const app = new App();

        app.form($("#save-user"), function (response) {
            if(response.link) window.location.href = response.link;
        });

        $("input[name$='update_password']").change(function(){
            if($('#update_password1').is(':checked')) {
                $("#password").show('fast');
            }

            if($('#update_password2').is(':checked')) {
                $("#password").hide('fast');
            }
        });
    });
</script>
<?php $this->end(); ?>