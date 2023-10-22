<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="send-separation-item-list-modal" 
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= _('Enviar para Separação') ?></h5>
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Confira os dados abaixo e clique em "Enviar".') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.7rem;"></i>
                </span>
            </div>

            <form id="send-separation-item-list-form"></form>
            <div class="modal-body">

            </div>
            
            <div class="modal-footer d-block text-center">
                <input form="send-separation-item-list-form" type="submit" class="btn btn-success btn-lg" value="<?= _('Enviar') ?>">
                <button type="button" class="btn btn-danger btn-lg" data-bs-dismiss="modal"><?= _('Voltar') ?></button>
            </div>
        </div>
    </div>
</div>
