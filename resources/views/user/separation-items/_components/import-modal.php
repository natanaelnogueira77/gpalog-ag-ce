<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="import-separation-items-modal" 
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= _('Importar Lista') ?></h5>
            </div>
            
            <div class="modal-body">
                <form id="import-separation-items" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>
                            <?= _('Importar Arquivo CSV') ?>
                            <span data-toggle="tooltip" data-placement="top" 
                                title="<?= _('Importe um arquivo CSV com as colunas na seguinte ordem: número do pedido, EAN do produto, 
                                    quantidade de caixas e quantidade de unidades.') ?>">
                                <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                            </span>
                        </label>
                        <input type="file" class="form-control-file" name="csv">
                        <div class="invalid-feedback"></div>
                    </div>

                    <li class="list-group-item">
                        <div class="widget-content p-0">
                            <div class="widget-content-wrapper">
                                <div class="widget-content-left mr-3">
                                    <input type="checkbox" id="auto_from_to" name="auto_from_to">
                                </div>

                                <div class="widget-content-left">
                                    <div class="widget-heading"><?= _('De Para Automático?') ?></div>
                                    <div class="widget-subheading">
                                        <?= _('Se deseja que o De Para seja feito automaticamente, marque a caixa ao lado.') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </form>
            </div>
            
            <div class="modal-footer d-block text-center">
                <input form="import-separation-items" type="submit" class="btn btn-success btn-lg" value="<?= _('Importar') ?>">
                <button type="button" class="btn btn-danger btn-lg" data-bs-dismiss="modal">
                    <?= _('Voltar') ?>
                </button>
            </div>
        </div>
    </div>
</div>