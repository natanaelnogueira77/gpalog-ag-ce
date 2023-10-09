<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="export-history-modal" 
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= _('Exportar Excel') ?></h5>
            </div>
            
            <div class="modal-body">
                <form id="export-history">
                    <div class="form-group">
                        <label><?= _('Ordem de Serviço') ?></label>
                        <input type="text" name="order_number" class="form-control" 
                            placeholder="<?= _('Digite a ordem de serviço...') ?>">
                    </div>
                </form>
            </div>
            
            <div class="modal-footer d-block text-center">
                <input form="export-history" type="submit" class="btn btn-success btn-lg" value="<?= _('Exportar Excel') ?>">
                <button type="button" class="btn btn-danger btn-lg" data-bs-dismiss="modal"><?= _('Voltar') ?></button>
            </div>
        </div>
    </div>
</div>