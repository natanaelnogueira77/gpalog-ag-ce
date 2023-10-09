<div class="modal <?= $modal["effect"] ? $modal["effect"] : "fade" ?>" 
    tabindex="-1" role="dialog" aria-hidden="true" id="<?= $modal["id"] ?>" 
    <?= $modal["noBackdrop"] ? "" : "data-bs-backdrop=\"static\"" ?> <?= $modal["keyboard"] ? "" : "data-bs-keyboard=\"false\"" ?>>
    <div class="modal-dialog modal-<?= $modal["size"] ? $modal["size"] : "md" ?>">
        <div class="modal-content">
            <?php if($modal["header"]): ?>
            <div class="modal-header">
                <h5 class="modal-title">
                    <?= $modal["header"]["title"] ? $modal["header"]["title"] : "" ?>
                </h5>
            </div>
            <?php endif; ?>
            
            <div class="modal-body">
                <?php 
                    if($modal["body"] && $modal["body"]["html"]) {
                        echo $modal["body"]["html"];
                    }
                ?>
            </div>
            
            <?php if($modal["footer"]): ?>
            <div class="modal-footer d-block text-center">
                <?php 
                    if($modal["footer"]["html"]) {
                        echo $modal["footer"]["html"];
                    }
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>