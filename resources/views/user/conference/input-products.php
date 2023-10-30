<?php 
    $theme->title = sprintf(_('Entrada | %s'), $appData['app_name']);
    $this->layout("themes/black-screen/_theme", [
        'theme' => $theme,
        'message' => $message
    ]);
?>

<p><?= _('Produtos') ?></p>

<table>
    <thead>
        <th><?= _('ID') ?></th>
        <th><?= _('OC') ?></th>
        <th><?= _('Placa') ?></th>
        <!-- <th><?php //_('Fornecedor') ?></th> -->
        <th><?= _('Data') ?></th>
    </thead>
    <tbody>
        <tr>
            <td><?= $dbConference->id ?></td>
            <td><?= $dbOperation->order_number ?></td>
            <td><?= $dbOperation->plate ?></td>
            <!-- <td><?php //$dbOperation->provider->name ?></td> -->
            <td><?= $dbConference->created_at ?></td>
        </tr>
    </tbody>
</table>
<br>

<input type="button" value="<?= _('Voltar') ?>" 
    onclick="window.location.href='<?= $router->route('user.conference.singleInput', ['conference_id' => $dbConference->id]) ?>'">

<?php foreach($dbConferenceInputs as $dbConferenceInput):?>
<table>
    <tbody>
        <tr>
            <td><?= _('Nome do Produto') ?></td>
            <td><?= $dbConferenceInput->product->name ?></td>
        </tr>
        <tr>
            <td><?= _('Quantidade de Caixas') ?></td>
            <td><?= $dbConferenceInput->boxes_amount ?></td>
        </tr>
        <tr>
            <td><?= _('Código EAN') ?></td>
            <td><?= $dbConferenceInput->product->ean ?></td>
        </tr>
    </tbody>
</table>
<br>
<?php endforeach; ?>