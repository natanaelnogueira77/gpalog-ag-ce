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
                <form id="save-operation">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="for_id">
                                <?= _('Fornecedor') ?>
                                <span data-toggle="tooltip" data-placement="top" 
                                    title='<?= _('Selecione o fornecedor, ou cadastre um novo, clicando em "Cadastrar".') ?>'>
                                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                                </span>
                            </label>
                            <div class="input-group">
                                <select name="for_id" class="form-control">
                                    <option value=""><?= _('Selecionar...') ?></option>
                                    <?php 
                                        if($dbProviders) {
                                            foreach($dbProviders as $dbProvider) {
                                                echo "<option value=\"{$dbProvider->id}\">{$dbProvider->name}</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <div class="input-group-append">
                                    <button id="create-provider" type="button" class="btn btn-primary" 
                                        data-action="<?= $router->route('user.providers.store') ?>" 
                                        data-method="post"><?= _('Cadastrar') ?></button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-4 col-sm-6">
                            <label for="service_type">
                                <?= _('Tipo de Serviço') ?>
                                <span data-toggle="tooltip" data-placement="top" 
                                    title='<?= _('Selecione o(s) tipo(s) de serviço para essa operação.') ?>'>
                                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                                </span>
                            </label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="has_palletization">
                                <label class="form-check-label" for="has_palletization"><?= _('Paletização') ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="has_rework">
                                <label class="form-check-label" for="has_rework"><?= _('Retrabalho') ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="has_storage">
                                <label class="form-check-label" for="has_storage"><?= _('Armazenagem') ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="has_import">
                                <label class="form-check-label" for="has_import"><?= _('Importado') ?></label>
                            </div>
                            <small class="text-danger" data-error="service_types"></small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="occurrence_number">
                                <?= _('Senha de Carregamento') ?>
                                <span data-toggle="tooltip" data-placement="top" 
                                    title='<?= _('Digite a senha de carregamento referente à operação.') ?>'>
                                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                                </span>
                            </label>
                            <input type="text" name="occurrence_number" placeholder="<?= _('Informe a senha de carregamento...') ?>" 
                            class="form-control" maxlength="20">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="password_number">
                                <?= _('Senha de G.A') ?>
                                <span data-toggle="tooltip" data-placement="top" 
                                    title='<?= _('Digite a senha de G.A referente à operação.') ?>'>
                                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                                </span>
                            </label>
                            <input type="text" name="password_number" placeholder="<?= _('Informe a senha de G.A...') ?>" 
                            class="form-control" maxlength="20">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="col-md-6">
                            <li class="list-group-item">
                                <div class="widget-content p-0">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left mr-3">
                                            <input type="checkbox" id="has_tr" name="has_tr">
                                        </div>

                                        <div class="widget-content-left">
                                            <div class="widget-heading"><?= _('Tem TR?') ?></div>
                                            <div class="widget-subheading">
                                                <?= _('Se houver TR, marque a caixa ao lado.') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="order_number">
                                <?= _('Número do Pedido / TR / OC') ?>
                                <span data-toggle="tooltip" data-placement="top" 
                                    title='<?= _('Digite o número do pedido referente à operação. Esta será a ordem de serviço.') ?>'>
                                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                                </span>
                            </label>
                            <input type="text" name="order_number" placeholder="<?= _('Informe o número do pedido...') ?>" 
                                class="form-control" maxlength="20">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="invoice_number">
                                <?= _('Nota Fiscal') ?>
                                <span data-toggle="tooltip" data-placement="top" 
                                    title='<?= _('Digite a nota fiscal referente à operação.') ?>'>
                                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                                </span>
                            </label>
                            <input type="text" name="invoice_number" placeholder="<?= _('Informe o número da nota fiscal...') ?>" 
                                class="form-control" maxlength="20">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="plate">
                                <?= _('Placa') ?>
                                <span data-toggle="tooltip" data-placement="top" 
                                    title='<?= _('Digite a placa do veículo referente à operação.') ?>'>
                                    <i class="icofont-question-circle" style="font-size: 1.1rem;"></i>
                                </span>
                            </label>
                            <input type="text" name="plate" placeholder="<?= _('Informe a placa do veículo...') ?>" 
                                class="form-control" maxlength="20">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </form>
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
            </div>
            
            <div class="modal-footer d-block text-center">
                <input form="save-provider" type="submit" class="btn btn-success btn-lg" value="<?= _('Salvar') ?>">
                <button type="button" id="save-provider-return" class="btn btn-danger btn-lg"><?= _('Voltar') ?></button>
                <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal"><?= _('Fechar') ?></button>
            </div>
        </div>
    </div>
</div>
