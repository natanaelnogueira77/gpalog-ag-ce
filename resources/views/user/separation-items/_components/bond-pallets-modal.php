<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="bond-pallets-modal" 
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" modal-info="title"><?= _('Fazer De Para') ?></h5>
            </div>
            
            <div class="modal-body">
                <form id="bond-pallets-filters">
                    <?php $v->insert('_components/data-table-filters', ['formId' => 'bond-pallets-filters']); ?>
                    <div class="form-row">
                        <div class="form-group col-md-3 col-sm-6">
                            <label><?= _('Filtrar Pallets') ?></label>
                            <select name="has_bond" class="form-control">
                                <option value="0"><?= _('Mostrar tudo') ?></option>
                                <option value="1"><?= _('Apenas vinculadas') ?></option>
                                <option value="2"><?= _('Apenas não vinculadas') ?></option>
                            </select>
                        </div>
                    </div>
                </form>

                <div id="bond-pallets" data-action="">
                    <div class="d-flex justify-content-around p-5">
                        <div class="spinner-grow text-secondary" role="status">
                            <span class="visually-hidden"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer d-block text-center">
                <button type="button" class="btn btn-danger btn-lg" data-bs-dismiss="modal"><?= _('Voltar') ?></button>
            </div>
        </div>
    </div>
</div>