<form id="save-separation-item">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>
                <?= _('EAN') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite o número do EAN referente ao produto.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="text" name="ean" class="form-control" 
                placeholder="<?= _('Digite a ordem de serviço...') ?>">
            <div class="invalid-feedback"></div>
        </div>
        
        <div class="form-group col-md-6">
            <label>
                <?= _('Número do Pedido') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Digite o número do pedido referente ao cliente.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="text" name="order_number" class="form-control" 
                placeholder="<?= _('Digite o número do pedido...') ?>">
            <div class="invalid-feedback"></div>
        </div>
        
        <div class="form-group col-md-6">
            <label>
                <?= _('Tipo de Quantidade') ?> 
                <span data-toggle="tooltip" data-placement="top" 
                    title='<?= _('Escolha o tipo de quantidade à ser conferida.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <select class="form-control" name="a_type">
                <option value=""><?= _('Selecionar...') ?></option>
                <?php 
                    if($amountTypes) {
                        foreach($amountTypes as $atId => $amountType) {
                            echo "<option value=\"{$atId}\">{$amountType}</option>";
                        }
                    }
                ?>
            </select>
            <div class="invalid-feedback"></div>
        </div>
        
        <div class="form-group col-md-6">
            <label>
                <?= _('Quantidade') ?> 
                <span data-toggle="tooltip" data-placement="top" title='<?= _('Digite a quantidade.') ?>'>
                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                </span>
            </label>
            <input type="text" name="amount" class="form-control" 
                placeholder="<?= _('Digite a ordem de serviço...') ?>">
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>