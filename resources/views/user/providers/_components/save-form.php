<form id="save-provider">
    <div class="form-group">
        <label>
            <?= _('Nome') ?>
            <span data-toggle="tooltip" data-placement="top" title='<?= _('Digite o nome do fornecedor.') ?>'>
                <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
            </span>
        </label>
        <input type="text" name="name" class="form-control" maxlength="100" 
            placeholder="<?= _('Digite o nome do fornecedor...') ?>">
        <div class="invalid-feedback"></div>
    </div>
</form>