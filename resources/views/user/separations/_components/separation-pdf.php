<html lang="pt-BR">
<head>
    <style>
        @page {
            margin: 15px;
        }

        div.page-break {
            page-break-after: always;
        }

        img.logo {
            position: absolute!important;
        }

        h1.title {
            text-align: center;
        }

        table.table {
            display: table;
            width: 100%;
            margin-bottom: 2rem;
            background-color: transparent;
            border-collapse: collapse;
            text-indent: initial;
            border-spacing: 2px;
            border-spacing: 2px;
            border-color: grey;
        }

        table.table thead th {
            color: white;
            padding: .25rem;
            vertical-align: middle!important;
            border-bottom: 2px solid #dee2e6;
        }

        table.table > thead {
            background-color: #0275d8;
        }

        table.table td {
            text-align: left;
            padding: .25rem;
            vertical-align: middle!important;
            border-top: 1px solid #dee2e6;
        }

        table.table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
    <link rel="shortcut icon" href="<?= $shortcutIcon ?>" type="image/png">
    <title><?= sprintf(_('Lista de Separação - ID %s'), $dbSeparation->id) ?></title>
</head>
<body>
    <div style="padding: 1.25rem;">
        <div>
            <img src="<?= $logo ?>" height="60px" class="logo">
        </div>

        <h1 class="title"><?= sprintf(_('Lista de Separação - ID %s'), $dbSeparation->id) ?></h1>
        <table class="table">
            <thead>
                <th colspan="8"><?= _('Informações da Separação') ?></th>
            </thead>
            
            <tbody>
                <tr>
                    <td colspan="4"><?= _('ID de Separação') ?></td>
                    <td colspan="4"><?= $dbSeparation->id ?></td>
                </tr>
                <tr>
                    <td colspan="4"><?= _('Data de Separação') ?></td>
                    <td colspan="4"><?= $dbSeparation->getCreatedAtDateTime()->format('d/m/Y') ?></td>
                </tr>
                <tr>
                    <td colspan="4"><?= _('Horário de Separação') ?></td>
                    <td colspan="4"><?= $dbSeparation->getCreatedAtDateTime()->format('H:i:s') ?></td>
                </tr>
            </tbody>

            <thead class="word-break: break-all;">
                <th style="text-align: center;"><?= _('Nº do Pallet') ?></th>
                <th style="text-align: center;"><?= _('Rua') ?></th>
                <th style="text-align: center;"><?= _('Posição') ?></th>
                <th style="text-align: center;"><?= _('Altura') ?></th>
                <th style="text-align: center;"><?= _('Nº do Pedido') ?></th>
                <th style="text-align: center;"><?= _('Nome do Produto') ?></th>
                <th style="text-align: center;"><?= _('EAN') ?></th>
                <th style="text-align: center;"><?= _('Quantidade') ?></th>
            </thead>
            
            <tbody>
                <?php 
                if($dbSeparationItems): 
                    foreach($dbSeparationItems as $dbSeparationItem):
                    ?>
                    <tr>
                        <td style="text-align: center;">
                            <?= $dbSeparationItem->pallet?->getBarcodePNG() ?>
                            <br>
                            <small><?= $dbSeparationItem->pallet->code ?></small>
                        </td>
                        <td style="text-align: center;">
                            <?= $dbSeparationItem->pallet->street_number ?>
                            <?php 
                                if($dbSeparationItem->pallets) {
                                    foreach($dbSeparationItem->pallets as $pallet) {
                                        echo "<br>{$pallet->street_number}";
                                    }
                                }
                            ?>
                        </td>
                        <td style="text-align: center;">
                            <?= $dbSeparationItem->pallet->position ?>
                            <?php 
                                if($dbSeparationItem->pallets) {
                                    foreach($dbSeparationItem->pallets as $pallet) {
                                        echo "<br>{$pallet->position}";
                                    }
                                }
                            ?>
                        </td>
                        <td style="text-align: center;">
                            <?= $dbSeparationItem->pallet->height ?>
                            <?php 
                                if($dbSeparationItem->pallets) {
                                    foreach($dbSeparationItem->pallets as $pallet) {
                                        echo "<br>{$pallet->height}";
                                    }
                                }
                            ?>
                        </td>
                        <td style="text-align: center;"><?= $dbSeparationItem->order_number ?></td>
                        <td style="text-align: center;"><?= $dbSeparationItem->product->name ?></td>
                        <td style="text-align: center;">
                            <?= $dbSeparationItem->product->getEANBarcodePNG() ?>
                            <br>
                            <small><?= $dbSeparationItem->product->ean ?></small>
                        </td>
                        <td style="text-align: center;">
                            <?= sprintf(_('%s %s'), $dbSeparationItem->amount, $dbSeparationItem->getAmountType()) ?>
                        </td>
                    </tr>
                    <?php 
                    endforeach;
                endif;    
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>