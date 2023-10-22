<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="import-products-modal" 
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= _('Importar Produtos') ?></h5>
            </div>
            
            <div class="modal-body">
                <form id="import-products" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>
                            <?= _('Importar Arquivo CSV') ?>
                            <span data-toggle="tooltip" data-placement="top" 
                                title="<?= _('Importe um arquivo CSV com as colunas na seguinte ordem: nome do fornecedor, ID do fornecedor, 
                                    ID do produto, nome do produto, código EMB FB, código DUN14, código EAN, comprimento do produto, 
                                    largura do produto, altura do produto, base do produto, peso do produto e PLU.') ?>">
                                <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                            </span>
                        </label>
                        <input type="file" class="form-control-file" name="csv">
                        <div class="invalid-feedback"></div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer d-block text-center">
                <input form="import-products" type="submit" class="btn btn-success btn-lg" value="<?= _('Importar') ?>">
                <button type="button" class="btn btn-danger btn-lg" data-bs-dismiss="modal">
                    <?= _('Voltar') ?>
                </button>
            </div>
        </div>
    </div>
</div>