<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="save-operation-modal" 
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div id="save-operation-area" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" modal-info="title"></h5>
                <span data-toggle="tooltip" data-placement="top" 
                    title="<?= _('Complete os campos abaixo para dar entrada / editar uma operação.') ?>">
                    <i class="icofont-question-circle" style="font-size: 1.7rem;"></i>
                </span>
            </div>

            <div class="modal-body">
                <?php $v->insert('user/operations/_components/save-form', ['dbProviders' => $dbProviders]) ?>
            </div>
            
            <div class="modal-footer d-block text-center">
                <input form="save-operation" type="submit" class="btn btn-success btn-lg" value="<?= _('Salvar') ?>">
                <button type="button" class="btn btn-danger btn-lg" data-bs-dismiss="modal"><?= _('Voltar') ?></button>
            </div>
        </div>

        <div id="save-provider-area" class="modal-content" style="display: none;">
            <div class="modal-header">
                <h5 class="modal-title"><?= _('Cadastrar Fornecedor') ?></h5>
            </div>
            
            <div class="modal-body">
                <?php $v->insert('user/providers/_components/save-form') ?>
            </div>
            
            <div class="modal-footer d-block text-center">
                <input form="save-provider" type="submit" class="btn btn-success btn-lg" value="<?= _('Salvar') ?>">
                <button type="button" id="save-provider-return" class="btn btn-danger btn-lg"><?= _('Voltar') ?></button>
                <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal"><?= _('Fechar') ?></button>
            </div>
        </div>
    </div>
</div>
