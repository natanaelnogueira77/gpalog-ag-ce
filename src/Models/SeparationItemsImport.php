<?php

namespace Src\Models;

use GTG\MVC\Model;
use Src\Models\Pallet;
use Src\Models\Product;
use Src\Models\SeparationItem;
use Src\Models\SeparationItemPallet;

class SeparationItemsImport extends Model 
{
    public ?array $rows = null;
    private ?array $separationItems = null;
    private ?array $separationItemPallets = null;

    public function rules(): array 
    {
        return [
            self::RULE_RAW => [
                function ($model) {
                    foreach($model->rows as $index => $row) {
                        $rowNumber = $index + 1;
                        if(!$row[0]) {
                            $model->addError("order_number_{$rowNumber}", _('O número do pedido é obrigatório!'));
                        }
                        
                        if(!$row[1]) {
                            $model->addError("ean_{$rowNumber}", _('O EAN do produto é obrigatório!'));
                        }

                        if(!$row[2] && !$row[3]) {
                            $model->addError("amount_{$rowNumber}", _('Ao menos uma das quantidades é obrigatória!'));
                        }
                    }
                }
            ]
        ];
    }

    public function generateSeparationItems(int $userId): bool 
    {
        $this->separationItems = [];
        $productEANs = [];
        foreach($this->rows as $index => $row) {
            $productEANs[] = $row[1];
        }

        if($dbProducts = (new Product())->get(['in' => ['ean' => $productEANs]])->fetch(true)) {
            $dbProducts = Product::getGroupedBy($dbProducts, 'ean');
            if($dbPallets = (new Pallet())->get([
                'height' => 1, 
                'in' => ['pro_id' => Product::getPropertyValues($dbProducts)]
                ])->fetch(true)) {
                $dbPallets = Pallet::getGroupedBy($dbPallets, 'pro_id');
            }
        }

        $listData = [];
        foreach($this->rows as $index => $row) {
            $rowNumber = $index + 1;

            $productId = $dbProducts[$row[1]]->id;
            $order = $row[0];
            $amountType = $row[2] > 0 ? SeparationItem::AT_BOXES : SeparationItem::AT_UNITS;

            if(!$productId) {
                $this->addError("ean_{$rowNumber}", _('Este EAN não encontrou nenhum produto!'));
            } elseif(!$dbPallets[$productId]->id) {
                $this->addError("ean_{$rowNumber}", _('Este EAN não está em posição de picking!'));
            }

            if($productId && $dbPallets[$productId]->id) {
                if(isset($listData[$productId][$order][$amountType])) {
                    $listData[$productId][$order][$amountType]['amount'] += $row[2] > 0 ? $row[2] : $row[3];
                } else {
                    $listData[$productId][$order][$amountType] = [
                        'adm_usu_id' => $userId,
                        'pro_id' => $productId,
                        'pal_id' => $dbPallets[$productId]->id,
                        'a_type' => $amountType,
                        'amount' => $row[2] > 0 ? $row[2] : $row[3],
                        'order_number' => $row[0]
                    ];
                }
            }
        }

        if($this->hasErrors()) {
            return false;
        }

        foreach($listData as $productId => $list2) {
            foreach($list2 as $order => $list3) {
                foreach($list3 as $amountType => $attributes) {
                    $this->separationItems[] = (new SeparationItem())->loadData($attributes)->setAsWaiting();
                }
            }
        }

        return true;
    }

    public function generateSeparationItemPallets(bool $autoFromTo = false): bool 
    {
        $tnPallet = Pallet::tableName();
        $tnSeparationItemPallet = SeparationItemPallet::tableName();
        if($this->separationItems) {
            $pallets = (new Pallet())->leftJoin($tnSeparationItemPallet, [
                'raw' => "{$tnSeparationItemPallet}.pal_id = {$tnPallet}.id"
            ])->get([
                'in' => ['pro_id' => Pallet::getPropertyValues($this->separationItems, 'pro_id')],
                'p_status' => Pallet::PS_STORED,
            ], "
                {$tnPallet}.*, 
                {$tnSeparationItemPallet}.id AS sip_id
            ")->order("
                {$tnPallet}.street_number, 
                {$tnPallet}.position, 
                {$tnPallet}.height
            ")->fetch(true);
            $groupedPallets = Pallet::getGroupedBy($pallets, 'pro_id', true);

            $amountInStock = [];
            $amountToBeSeparated = [];
            foreach($this->separationItems as $separationItem) {
                if($separationItem->isBoxesType()) {
                    $amountInStock[$separationItem->pro_id][$separationItem->a_type] 
                        = Pallet::getPalletsTotalBoxesAmount($groupedPallets[$separationItem->pro_id]);
                } elseif($separationItem->isUnitsType()) {
                    $amountInStock[$separationItem->pro_id][$separationItem->a_type] 
                        = Pallet::getPalletsTotalUnitsAmount($groupedPallets[$separationItem->pro_id]);
                }
            }
    
            $awaitingSeparationItems = (new SeparationItem())->get([
                'in' => [
                    'pro_id' => Pallet::getPropertyValues($this->separationItems, 'pro_id'),
                    's_status' => [SeparationItem::S_WAITING, SeparationItem::S_LISTED]
                ]
            ])->fetch(true);

            if($awaitingSeparationItems) {
                $awaitingSeparationItems = SeparationItem::withPallet($awaitingSeparationItems);
                foreach($awaitingSeparationItems as $separationItem) {
                    $amountToBeSeparated[$separationItem->pro_id][SeparationItem::AT_BOXES] += $separationItem->isBoxesType() 
                        ? $separationItem->amount 
                        : floor($separationItem->amount / $separationItem->pallet->package);
                    $amountToBeSeparated[$separationItem->pro_id][SeparationItem::AT_UNITS] += $separationItem->isUnitsType() 
                        ? $separationItem->amount 
                        : $separationItem->amount * $separationItem->pallet->package;
                }
            }

            foreach($this->separationItems as $separationItem) {
                if($separationItem->isBoxesType()) {
                    if($amountToBeSeparated[$separationItem->pro_id][SeparationItem::AT_BOXES] + $separationItem->amount 
                        > $amountInStock[$separationItem->pro_id][SeparationItem::AT_BOXES]) {
                        return false;
                    }
                } elseif($separationItem->isUnitsType()) {
                    if($amountToBeSeparated[$separationItem->pro_id][SeparationItem::AT_UNITS] + $separationItem->amount 
                        > $amountInStock[$separationItem->pro_id][SeparationItem::AT_UNITS]) {
                        return false;
                    }
                }
            }

            if($autoFromTo) {
                $pickingPallets = array_filter($pallets, fn($o) => $o->height == 1);
                $pickingPallets = Pallet::getGroupedBy($pickingPallets, 'pro_id');

                $fromToPallets = array_filter($pallets, fn($o) => $o->height != 1 && !$o->sip_id);
                $fromToPallets = Pallet::getGroupedBy($fromToPallets, 'pro_id', true);

                foreach($this->separationItems as $separationItem) {
                    $this->separationItems = SeparationItem::withPallet($this->separationItems);
                    if($separationItem->isBoxesType()) {
                        $pickingPallets[$separationItem->pro_id]->boxes_amount -= $separationItem->amount;
                        $pickingPallets[$separationItem->pro_id]->units_amount -= $separationItem->amount * $separationItem->pallet->package;
                        while($pickingPallets[$separationItem->pro_id]->boxes_amount < 0) {
                            if($fromToPallets[$separationItem->pro_id]) {
                                $pallet = array_shift($fromToPallets[$separationItem->pro_id]);
                                $dbSeparationItemPallet = (new SeparationItemPallet())->loadData([
                                    'site_id' => $separationItem->id,
                                    'pal_id' => $pallet->id
                                ]);
                                $pickingPallets[$separationItem->pro_id]->boxes_amount += $pallet->boxes_amount;
                                $pickingPallets[$separationItem->pro_id]->units_amount += $pallet->units_amount;
                                $separationItem->separationItemPallets[] = $dbSeparationItemPallet;
                            }
                        }
                    } elseif($separationItem->isUnitsType()) {
                        $pickingPallets[$separationItem->pro_id]->units_amount -= $separationItem->amount;
                        $pickingPallets[$separationItem->pro_id]->boxes_amount -= floor($separationItem->amount / $separationItem->pallet->package);
                        while($pickingPallets[$separationItem->pro_id]->units_amount < 0) {
                            if($fromToPallets[$separationItem->pro_id]) {
                                $pallet = array_shift($fromToPallets[$separationItem->pro_id]);
                                $dbSeparationItemPallet = (new SeparationItemPallet())->loadData([
                                    'site_id' => $separationItem->id,
                                    'pal_id' => $pallet->id
                                ]);
                                $pickingPallets[$separationItem->pro_id]->boxes_amount += $pallet->boxes_amount;
                                $pickingPallets[$separationItem->pro_id]->units_amount += $pallet->units_amount;
                                $separationItem->separationItemPallets[] = $dbSeparationItemPallet;
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    public function insertData(): bool 
    {

        if($this->separationItems) {
            if(!$this->separationItems = SeparationItem::insertMany($this->separationItems)) {
                return false;
            }

            foreach($this->separationItems as $separationItem) {
                if($separationItem->separationItemPallets) {
                    foreach($separationItem->separationItemPallets as $separationItemPallet) {
                        $separationItemPallet->site_id = $separationItem->id;
                        $this->separationItemPallets[] = $separationItemPallet;
                    }
                }
            }

            if($this->separationItemPallets) {
                if(!$this->separationItemPallets = SeparationItemPallet::insertMany($this->separationItemPallets)) {
                    return false;
                }
            }
        }

        return true;
    }
}