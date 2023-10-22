<form id="save-product">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="prod_id">
                <?= _('ID do Produto') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite o ID do produto.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="number" name="prod_id" placeholder="<?= _('Informe o ID do produto...') ?>" class="form-control">
            <div class="invalid-feedback"></div>
        </div>

        <div class="form-group col-md-6">
            <label for="name">
                <?= _('Nome do Produto') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite o nome do produto.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="text" name="name" placeholder="<?= _('Informe o nome do produto...') ?>" 
                class="form-control" maxlength="100">
            <div class="invalid-feedback"></div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="prov_id">
                <?= _('ID do Fornecedor') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite o ID do fornecedor referente ao produto.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="number" name="prov_id" placeholder="<?= _('Informe o ID do fornecedor...') ?>" class="form-control">
            <div class="invalid-feedback"></div>
        </div>

        <div class="form-group col-md-6">
            <label for="prov_name">
                <?= _('Nome do Fornecedor') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite o nome do fornecedor referente ao produto.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="text" name="prov_name" placeholder="<?= _('Informe o nome do fornecedor...') ?>"
                class="form-control" maxlength="100">
            <div class="invalid-feedback"></div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="ean">
                <?= _('EAN') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite o código EAN do produto.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="text" name="ean" placeholder="<?= _('Digite o código EAN...') ?>" class="form-control">
            <div class="invalid-feedback"></div>
        </div>

        <div class="form-group col-md-4">
            <label for="dun14">
                <?= _('Dun14') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite o código Dun14 do produto.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="text" name="dun14" placeholder="<?= _('Digite o código Dun14...') ?>" class="form-control">
            <div class="invalid-feedback"></div>
        </div>

        <div class="form-group col-md-4">
            <label for="plu">
                <?= _('PLU') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite o código PLU do produto.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="text" name="plu" placeholder="<?= _('Digite o código PLU...') ?>" class="form-control">
            <div class="invalid-feedback"></div>
        </div>
    </div>

    <h5 class="card-title"><?= _('Medidas') ?></h5>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="emb_fb">
                <?= _('Emb Fb') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite o número de embalagem do produto.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="number" name="emb_fb" placeholder="<?= _('Digite o Emb Fb...') ?>" class="form-control" min="0">
            <div class="invalid-feedback"></div>
        </div>

        <div class="form-group col-md-4">
            <label for="p_length">
                <?= _('Comprimento') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite o comprimento do produto, em centímetros.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="number" name="p_length" placeholder="<?= _('Digite o comprimento...') ?>" class="form-control" min="0">
            <div class="invalid-feedback"></div>
        </div>

        <div class="form-group col-md-4">
            <label for="p_width">
                <?= _('Largura') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite a largura do produto, em centímetros.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="number" name="p_width" placeholder="<?= _('Digite a largura...') ?>" class="form-control" min="0">
            <div class="invalid-feedback"></div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="p_height">
                <?= _('Altura') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite a altura do produto, em centímetros.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="number" name="p_height" placeholder="<?= _('Digite a altura...') ?>" class="form-control" min="0">
            <div class="invalid-feedback"></div>
        </div>

        <div class="form-group col-md-4">
            <label for="p_base">
                <?= _('Base') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite a base do produto.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="number" name="p_base" placeholder="<?= _('Digite a base...') ?>" class="form-control" min="0">
            <div class="invalid-feedback"></div>
        </div>

        <div class="form-group col-md-4">
            <label for="p_weight">
                <?= _('Peso') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite o peso do produto, em quilos.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <div class="input-group">
                <input type="number" name="p_weight" placeholder="<?= _('Digite o peso...') ?>" class="form-control" 
                    step="0.01" min="0">
                <div class="input-group-append">
                    <span class="input-group-text">Kg</span>
                </div>
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</form>