<?php $v->insert('_scripts/media-library.js'); ?>
<div class="modal fade" id="media-library-modal" tabindex="-1" role="dialog" aria-hidden="true" 
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= _('Biblioteca de Mídia') ?></h5>
            </div>

            <div class="card">
                <ul class="nav nav-tabs mb-0">
                    <li class="nav-item">
                        <a data-toggle="tab" href="#ml-tab-1" class="nav-link show active">
                            <?= _('Upload') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#ml-tab-2" class="nav-link">
                            <?= _('Tirar Foto') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#ml-tab-3" class="nav-link">
                            <?= _('Biblioteca de Mídia') ?>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane tabs-animation fade show active" id="ml-tab-1" role="tabpanel">
                        <div class="card-body">
                            <label id="ml-upload-area" class="w-100" style="cursor: pointer;" for="ml-upload">
                                <div id="ml-area" class="card card-border border-info w-100 p-5" style="cursor: pointer;">
                                    <div class="d-flex justify-content-around align-items-center" style="height: 90%;">
                                        <div>
                                            <h3 class="text-center"><?= _('Clique para selecionar ou') ?></h3>
                                            <h3 class="text-center"><?= _('arraste o(s) arquivo(s)') ?></h3>
                                            <h5 class="text-center" id="ml-maxsize"></h5>
                                            <div class="progress-bar-sm progress-bar-animated-alt progress">
                                                <div class="progress-bar progress-bar-animated progress-bar-striped bg-primary" 
                                                    id="ml-progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" 
                                                    aria-valuemax="100" style="width: 0%;"></div>
                                            </div>
                                            <h5 class="text-center" id="ml-progress"></h5>
                                        </div>
                                    </div>
                                    <input type="file" id="ml-upload" style="position: absolute; left: 0; top: 0; right: 0; 
                                        bottom: 0; width: 100%; opacity: 0; -webkit-appearance: none; cursor: pointer;">
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="tab-pane tabs-animation fade show" id="ml-tab-2" role="tabpanel">
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label for="ml-camera-select"><?= _('Selecione a Câmera') ?></label>
                                <select id="ml-camera-select" class="form-control"></select>
                            </div>
                        </div>
                        <hr class="my-0">

                        <div class="card-body">
                            <h5 class="card-title"><?= _('Câmera Atual') ?></h5>
                            <div class="d-flex justify-content-around">
                                <video id="ml-video" class="border border-primary" style="max-width: 100%" autoplay></video>
                            </div>
                        </div>
                        <hr class="my-0">

                        <div class="d-block text-center m-2">
                            <button type="button" id="ml-snap" class="btn btn-lg btn-primary mx-1">
                                <?= _('Capturar') ?>
                            </button>

                            <button type="button" id="ml-save-snap" class="btn btn-lg btn-success mx-1" disabled>
                                <?= _('Salvar Captura') ?>
                            </button>
                        </div>
                        <hr class="my-0">

                        <div class="card-body">
                            <h5 class="card-title"><?= _('Foto Capturada') ?></h5>
                            <div class="d-flex justify-content-around">
                                <canvas id="ml-canvas" class="border border-primary" width="640" height="480" 
                                    style="max-width: 100%"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane tabs-animation fade show" id="ml-tab-3" role="tabpanel">
                        <div class="card-body">
                            <form id="ml-images-list">
                                <div class="input-group">
                                    <input type="search" name="search" id="ml-search" class="form-control rounded" 
                                        placeholder="<?= _('Pesquisar...') ?>">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="icofont-search"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" id="ml-clean-search">
                                            <i class="icofont-close"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <hr class="my-0">
    
                        <div class="card-body">
                            <div class="d-flex justify-content-around" id="ml-pagination">
                                <nav>
                                    <ul class="pagination"></ul>
                                </nav>
                            </div>
    
                            <div class="w-100 d-flex flex-wrap" id="ml-list-group" style="height: 100%;"></div>
    
                            <div class="d-block text-center mt-4">
                                <input type="hidden" id="ml-choosen-file">
                                <button type="button" id="ml-choose" class="btn btn-primary">
                                    <?= _('Escolher') ?>
                                </button>

                                <button type="button" id="ml-cancel" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <?= _('Cancelar') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-block text-center">
                    <button type="button" class="btn btn-danger btn-lg" data-bs-dismiss="modal"><?= _('Voltar') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>