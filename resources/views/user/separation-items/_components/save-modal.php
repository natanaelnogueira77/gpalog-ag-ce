<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="save-separation-item-modal" 
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" modal-info="title"></h5>
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Complete os campos abaixo e clique em "Salvar" para fazer a separação do produto.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.7rem;"></i>
                </span>
            </div>

            <div class="modal-body">
                <?php $v->insert('user/separation-items/_components/save-form', ['amountTypes' => $amountTypes]) ?>
            </div>
            
            <div class="modal-footer d-block text-center">
                <input form="save-separation-item" type="submit" class="btn btn-success btn-lg" value="<?= _('Salvar') ?>">
                <button type="button" class="btn btn-danger btn-lg" data-bs-dismiss="modal"><?= _('Voltar') ?></button>
            </div>
        </div>
    </div>
</div>
