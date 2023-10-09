<?php 
    $this->layout("themes/architect-ui/_theme", [
        'title' => sprintf($dbUser ? _('Editar Usuário | %s') : _('Cadastrar Usuário | %s'), $appData['app_name'])
    ]);
?>

<?php 
    $this->insert('themes/architect-ui/components/title', [
        'title' => ($dbUser ? sprintf(_("Editar Usuário \"%s\""), $dbUser->name) : _('Cadastrar Usuário')),
        'subtitle' => $dbUser 
            ? _('Preencha os dados abaixo para alterar o usuário, e então clique em "Atualizar Usuário"') 
            : _('Preencha os dados abaixo para cadastrar um usuário, e então clique em "Cadastrar Usuário"'),
        'icon' => 'pe-7s-user',
        'icon_color' => 'bg-malibu-beach'
    ]);
?>

<form action="<?= $dbUser ? $router->route('admin.users.update', ['user_id' => $dbUser->id]) : $router->route('admin.users.store') ?>" 
    method="<?= $dbUser ? 'put' : 'post' ?>" id="save-user">
    <div class="card shadow mb-4 br-15">
        <div class="card-header-tab card-header-tab-animation card-header brt-15">    
            <div class="card-header-title">
                <i class="header-icon icofont-user icon-gradient bg-malibu-beach"> </i>
                <?= _('Informações da Conta') ?>
            </div>
        </div>

        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="name">
                        <?= _('Nome') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Digite o nome e sobrenome.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <input type="text" id="name" name="name" placeholder="<?= _('Informe um nome...') ?>"
                        class="form-control" value="<?= $dbUser ? $dbUser->name : '' ?>" maxlength="50">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group col-md-6">
                    <label for="email">
                        <?= _('Email') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Digite um email válido.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <input type="email" id="email" name="email" placeholder="<?= _('Informe um email...') ?>"
                        class="form-control" value="<?= $dbUser ? $dbUser->email : '' ?>" maxlength="100">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="utip_id">
                        <?= _('Nível do Usuário') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Escolha o nível do usuário.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <select id="utip_id" name="utip_id" class="form-control">
                        <option value=""><?= _('Selecionar...') ?></option>
                        <?php 
                            foreach($userTypes as $userType) {
                                $selected = $dbUser ? ($dbUser->utip_id == $userType->id ? 'selected' : '') : '';
                                echo "<option value='{$userType->id}' {$selected}>{$userType->name_sing}</option>";
                            }
                        ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div id="registration-number-area" class="form-group col-md-6" 
                    style="<?= $dbUser && $dbUser->isOperator() ? '' : 'display: none;' ?>">
                    <label for="registration_number">
                        <?= _('Número de Matrícula') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('O número de matrícula será usado para o operador entrar.') ?>">
                            <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                        </span>
                    </label>
                    <input type="text" id="registration_number" name="registration_number" class="form-control" 
                        placeholder="<?= _('Informe o número de matrícula...') ?>" maxlength="20" 
                        value="<?= $userMetas && $userMetas['registration_number'] ? $userMetas['registration_number'] : '' ?>">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <?php if($dbUser): ?>
            <div class="form-row">
                <div class="form-group col-md-12 align-middle">
                    <div class="d-flex">
                        <p class="mb-0 mr-2"><strong><?= _('Deseja alterar a senha?') ?></strong></p>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="update_password" 
                                id="update_password1" value="1">
                            <label class="form-check-label" for="update_password1">
                                <?= _('Sim') ?>
                            </label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="update_password" 
                                id="update_password2" value="0" checked>
                            <label class="form-check-label" for="update_password2">
                                <?= _('Não') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="form-row" id="password" style="<?= $dbUser ? 'display: none' : '' ?>">
                <div class="form-group col-md-6">
                    <label for="password">
                        <?= _('Senha') ?>
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Digite a nova senha de acesso à conta.') ?>">
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
                        <span data-toggle="tooltip" data-placement="top" title="<?= _('Digite novamente a nova senha de acesso à conta.') ?>">
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
            <input type="submit" class="btn btn-lg btn-success" 
                value="<?= $dbUser ? _('Atualizar Usuário') : _('Cadastrar Usuário') ?>">
            <a href="<?= $router->route('admin.users.index') ?>" class="btn btn-danger btn-lg">
                <?= _('Voltar') ?>
            </a>
        </div>
    </div>
</form>

<?php $this->start('scripts'); ?>
<script>
    $(function () {
        const app = new App();
        const form = $("#save-user");
        const update_password = $("input[name$='update_password']");
        const password_area = $("#password");

        const user_type = $("select[name$='utip_id']");
        const registration_number_area = $("#registration-number-area");

        update_password.change(function () {
            if($('#update_password1').is(':checked')) {
                password_area.show('fast');
            }

            if($('#update_password2').is(':checked')) {
                password_area.hide('fast');
            }
        });

        user_type.change(function () {
            if($(this).val() == 3) {
                registration_number_area.show('fast');
            } else {
                registration_number_area.hide('fast');
            }
        });

        app.form(form, function (response) {
            if(response.link) window.location.href = response.link;
        });
    });
</script>
<?php $this->end(); ?>