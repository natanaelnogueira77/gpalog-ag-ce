<?php 
    $theme->title = sprintf(_('Painel do Usuário | %s'), $appData['app_name']);
    $this->layout("themes/architect-ui/_theme", ['theme' => $theme]);
    
    $this->insert('themes/architect-ui/_components/title', [
        'title' => _('Painel do Usuário'),
        'subtitle' => _('Informações sobre sua atividade no sistema'),
        'icon' => 'pe-7s-user',
        'icon_color' => 'bg-malibu-beach'
    ]);
?>

<?php if($blocks): ?>
<div class="row">
    <?php foreach($blocks as $block): ?>
    <div class="col-md-4 mb-4">
        <a href="<?= $block['url'] ?>" style="text-decoration: none;">
            <div class="card shadow br-15" card-link>
                <div class="card-body text-dark">
                    <div class="text-center">
                        <i class="<?= $block['icon'] ?> text-info" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="text-center"><?= $block['title'] ?></h3>
                    <p class="text-center"><?= $block['text'] ?></p>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php 
    $this->start('scripts'); 
    $this->insert('user/_scripts/index.js');
    $this->end(); 
?>