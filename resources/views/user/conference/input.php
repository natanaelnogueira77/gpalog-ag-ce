<?php 
    $this->layout("themes/black-screen/_theme", [
        'title' => sprintf(_('Entrada | %s'), $appData['app_name']),
        'message' => $message
    ]);
?>

<p><?= _('Entrada') ?></p>

<div>
    <input type="button" value="<?= _('Voltar') ?>" onclick="window.location.href='<?= $router->route('user.conference.index') ?>'">
    <input type="button" value="<?= _('Sair') ?>" onclick="window.location.href='<?= $router->route('auth.logout') ?>'">
</div>

<table>
    <thead>
        <th><?= _('ID') ?></th>
        <th><?= _('OC') ?></th>
        <th><?= _('Placa') ?></th>
        <th><?= _('Fornecedor') ?></th>
        <th><?= _('Data') ?></th>
        <th><?= _('Ação') ?></th>
    </thead>
    <tbody>
        <?php
        if($dbConferences):
            foreach($dbConferences as $dbConference): 
            ?>
            <tr>
                <td><?= $dbConference->id ?></td>
                <td><?= $dbConference->operation->order_number ?></td>
                <td><?= $dbConference->plate ?></td>
                <td><?= $dbConference->provider_name ?></td>
                <td><?= $dbConference->created_at ?></td>
                <td>
                    <input type="submit" value="<?= _('Ir Conf.') ?>" 
                        onclick="window.location.href='<?= $router->route('user.conference.singleInput', ['conference_id' => $dbConference->id]) ?>'">
                </td>
            </tr>
            <?php 
            endforeach;
        endif;
        ?>
    </tbody>
</table>