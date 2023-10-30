<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\Pallet;
use Src\Models\PalletFromTo;
use Src\Models\Product;
use Src\Models\Separation;
use Src\Models\SeparationItemPallet;
use Src\Models\User;

class SeparationItem extends DBModel 
{
    const AT_BOXES = 1;
    const AT_UNITS = 2;

    const S_WAITING = 1;
    const S_LISTED = 2;
    const S_SEPARATED = 3;
    const S_CHECKED = 4;
    const S_FINISHED = 5;

    public ?User $ADMUser = null;
    public ?User $conferenceUser = null;
    private ?bool $hasAmountInStock = null;
    public ?Pallet $pallet = null;
    public ?SeparationItemPallet $pivotPallet = null;
    public ?Product $product = null;
    public ?Separation $separation = null;
    public ?array $separationItemPallets = null;
    public ?User $separationUser = null;

    public static function tableName(): string 
    {
        return 'separacao_item';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'adm_usu_id',
            'pro_id', 
            'pal_id', 
            'a_type', 
            'amount',
            'order_number', 
            'sep_id',
            'separation_usu_id',
            'separation_date',
            'address',
            'separation_amount',
            'dispatch_dock',
            'conf_usu_id',
            'conf_date',
            'conf_amount',
            's_status'
        ];
    }

    public function rules(): array 
    {
        return array_merge([
            'adm_usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ADM é obrigatório!')]
            ],
            'pro_id' => [
                [self::RULE_REQUIRED, 'message' => _('O produto é obrigatório!')]
            ],
            'pal_id' => [
                [self::RULE_REQUIRED, 'message' => _('O pallet é obrigatório!')]
            ],
            'a_type' => [
                [self::RULE_REQUIRED, 'message' => _('O tipo de quantidade é obrigatório!')],
                [self::RULE_IN, 'values' => array_keys(self::getAmountTypes()), 'message' => _('O tipo de quantidade é inválido!')],
            ],
            'amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade é obrigatória!')],
                [self::RULE_LARGER_THAN, 'value' => 0, 'message' => sprintf(_('A quantidade precisa ser maior ou igual a %s!'), 0)]
            ],
            'order_number' => [
                [self::RULE_REQUIRED, 'message' => _('O número do pedido é obrigatório!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('O número do pedido deve conter no máximo %s caractéres!'), 20)]
            ],
            's_status' => [
                [self::RULE_REQUIRED, 'message' => _('O status é obrigatório!')],
                [self::RULE_IN, 'values' => array_keys(self::getStates()), 'message' => _('O status é inválido!')]
            ]
        ], $this->isListed() ? [
            'sep_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID de separação é obrigatório!')]
            ]
        ] : [], 
        $this->isSeparated() ? [
            'separation_usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O separador é obrigatório!')]
            ],
            'separation_date' => [
                [self::RULE_REQUIRED, 'message' => _('A data de separação é obrigatória!')],
                [self::RULE_DATETIME, 'pattern' => 'Y-m-d H:i:s', 'message' => _('A data de separação deve seguir o padrão dd/mm/yyyy hh:mm:ss!')]
            ],
            'address' => [
                [self::RULE_REQUIRED, 'message' => _('O endereçamento é obrigatório!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('O endereçamento deve conter no máximo %s caractéres!'), 20)]
            ],
            'separation_amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade é obrigatória!')]
            ],
            'dispatch_dock' => [
                [self::RULE_REQUIRED, 'message' => _('A doca de despacho é obrigatória!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('A doca de despacho deve conter no máximo %s caractéres!'), 20)]
            ]
        ] : [],
        $this->isChecked() ? [
            'conf_usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O conferente é obrigatório!')]
            ],
            'conf_date' => [
                [self::RULE_REQUIRED, 'message' => _('A data de conferência é obrigatória!')],
                [self::RULE_DATETIME, 'pattern' => 'Y-m-d H:i:s', 'message' => _('A data de conferência deve seguir o padrão dd/mm/yyyy hh:mm:ss!')]
            ],
            'conf_amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade conferida é obrigatória!')]
            ]
        ] : [], 
        [
            self::RULE_RAW => [
                function ($model) {
                    if(!$model->hasError('amount') && $model->isWaiting() && !$model->hasAmountInStock()) {
                        $model->addError('amount', _('A quantidade selecionada ultrapassa a quantidade que está no estoque!'));
                    }
                }
            ]
        ]);
    }

    public function save(): bool 
    {
        $this->sep_id = $this->isListed() || $this->isSeparated() || $this->isChecked() || $this->isFinished() ? $this->sep_id : null;
        $this->separation_usu_id = $this->isSeparated() || $this->isChecked() || $this->isFinished() ? $this->separation_usu_id : null;
        $this->separation_date = $this->isSeparated() || $this->isChecked() || $this->isFinished() ? $this->separation_date : null;
        $this->address = $this->isSeparated() || $this->isChecked() || $this->isFinished() ? $this->address : null;
        $this->separation_amount = $this->isSeparated() || $this->isChecked() || $this->isFinished() ? $this->separation_amount : null;
        $this->dispatch_dock = $this->isSeparated() || $this->isChecked() || $this->isFinished() ? $this->dispatch_dock : null;
        $this->conf_usu_id = $this->isChecked() || $this->isFinished() ? $this->conf_usu_id : null;
        $this->conf_date = $this->isChecked() || $this->isFinished() ? $this->conf_date : null;
        $this->conf_amount = $this->isChecked() || $this->isFinished() ? $this->conf_amount : null;
        return parent::save();
    }

    public static function insertMany(array $objects): array|false 
    {
        $allValidated = true;
        if(count($objects) > 0) {
            foreach($objects as $object) {
                if(is_array($object)) $object = (new self())->loadData($object);

                $object->sep_id = $object->isListed() || $object->isSeparated() || $object->isChecked() || $object->isFinished() ? $object->sep_id : null;
                $object->separation_usu_id = $object->isSeparated() || $object->isChecked() || $object->isFinished() ? $object->separation_usu_id : null;
                $object->separation_date = $object->isSeparated() || $object->isChecked() || $object->isFinished() ? $object->separation_date : null;
                $object->address = $object->isSeparated() || $object->isChecked() || $object->isFinished() ? $object->address : null;
                $object->separation_amount = $object->isSeparated() || $object->isChecked() || $object->isFinished() ? $object->separation_amount : null;
                $object->dispatch_dock = $object->isSeparated() || $object->isChecked() || $object->isFinished() ? $object->dispatch_dock : null;
                $object->conf_usu_id = $object->isChecked() || $object->isFinished() ? $object->conf_usu_id : null;
                $object->conf_date = $object->isChecked() || $object->isFinished() ? $object->conf_date : null;
                $object->conf_amount = $object->isChecked() || $object->isFinished() ? $object->conf_amount : null;

                if(!$object->validate()) {
                    $allValidated = false;
                }
            }
        }

        if(!$allValidated) {
            return $objects;
        }

        for($i = 0; $i <= count($objects) - 1; $i += 1000) {
            if($objects) {
                parent::insertMany(array_slice($objects, $i, 1000));
            }
        }

        return $objects;
    }

    public function destroy(): bool 
    {
        if($this->pallets()) {
            SeparationItemPallet::deleteByObjects($this->pallets);
        }
        return parent::destroy();
    }

    public function ADMUser(string $columns = '*'): ?User 
    {
        $this->ADMUser = $this->belongsTo(User::class, 'adm_usu_id', 'id', $columns)->fetch(false);
        return $this->ADMUser;
    }
    
    public function conferenceUser(string $columns = '*'): ?User 
    {
        $this->conferenceUser = $this->conf_usu_id 
            ? $this->belongsTo(User::class, 'conf_usu_id', 'id', $columns)->fetch(false)
            : null;
        return $this->conferenceUser;
    }

    public function pallet(string $columns = '*'): ?Pallet 
    {
        $this->pallet = $this->belongsTo(Pallet::class, 'pal_id', 'id', $columns)->fetch(false);
        return $this->pallet;
    }

    public function pallets(array $filters = [], string $columns = '*', string $pivotColumns = '*'): ?array
    {
        $this->separationItemPallets = $this->belongsToMany(
            Pallet::class, 
            SeparationItemPallet::class, 
            'site_id', 
            'pal_id', 
            'pivotSeparationItem', 
            'id', 
            'id', 
            $filters, 
            $columns, 
            $pivotColumns
        );
        return $this->separationItemPallets;
    }

    public function product(string $columns = '*'): ?Product 
    {
        $this->product = $this->belongsTo(Product::class, 'pro_id', 'id', $columns)->fetch(false);
        return $this->product;
    }

    public function separation(string $columns = '*'): ?Separation 
    {
        $this->separation = $this->belongsTo(Separation::class, 'sep_id', 'id', $columns)->fetch(false);
        return $this->separation;
    }

    public function separationUser(string $columns = '*'): ?User 
    {
        $this->separationUser = $this->separation_usu_id 
            ? $this->belongsTo(User::class, 'separation_usu_id', 'id', $columns)->fetch(false)
            : null;
        return $this->separationUser;
    }

    public static function withADMUser(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            User::class, 
            'adm_usu_id', 
            'ADMUser', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withConferenceUser(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            User::class, 
            'conf_usu_id', 
            'conferenceUser', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withPallet(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            Pallet::class, 
            'pal_id', 
            'pallet', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withPallets(
        array $objects, 
        array $filters = [], 
        string $columns = '*', 
        string $pivotColumns = '*'
    ): array
    {
        return self::withBelongsToMany(
            $objects, 
            Pallet::class, 
            SeparationItemPallet::class, 
            'site_id', 
            'pal_id', 
            'pallets', 
            'pivotSeparationItem', 
            'id', 
            'id', 
            $filters, 
            $columns, 
            $pivotColumns
        );
    }

    public static function withProduct(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            Product::class, 
            'pro_id', 
            'product', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withSeparation(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            Separation::class, 
            'sep_id', 
            'separation', 
            'id', 
            $filters, 
            $columns
        );
    }
    
    public static function withSeparationUser(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            User::class, 
            'separation_usu_id', 
            'separationUser', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function getAmountTypes(): array 
    {
        return [
            self::AT_BOXES => _('Caixas'),
            self::AT_UNITS => _('Unidades')
        ];
    }

    public function getAmountType(): ?string 
    {
        return isset(self::getAmountTypes()[$this->a_type]) ? self::getAmountTypes()[$this->a_type] : null;
    }
    
    public static function getStates(): array 
    {
        return [
            self::S_WAITING => _('Aguardando'),
            self::S_LISTED => _('Listado'),
            self::S_SEPARATED => _('Separado'),
            self::S_CHECKED => _('Conferido'),
            self::S_FINISHED => _('Finalizado')
        ];
    }

    public function getStatus(): ?string 
    {
        return isset(self::getStates()[$this->s_status]) ? self::getStates()[$this->s_status] : null;
    }

    public static function getStatesColors(): array 
    {
        return [
            self::S_WAITING => 'warning',
            self::S_LISTED => 'alternate',
            self::S_SEPARATED => 'info',
            self::S_CHECKED => 'primary',
            self::S_FINISHED => 'success'
        ];
    }

    public function getStatusColor(): ?string 
    {
        return isset(self::getStatesColors()[$this->s_status]) ? self::getStatesColors()[$this->s_status] : null;
    }

    public function getCreatedAtDateTime(): DateTime 
    {
        return new DateTime($this->created_at);
    }

    public function getUpdatedAtDateTime(): DateTime 
    {
        return new DateTime($this->updated_at);
    }
    
    public function getSeparationDateTime(): ?DateTime 
    {
        return $this->separation_date ? new DateTime($this->separation_date) : null;
    }

    public function getConferenceDateTime(): ?DateTime 
    {
        return $this->conf_date ? new DateTime($this->conf_date) : null;
    }

    public static function getToBeSeparatedBoxesByProductId(int $productId): int
    {
        $awaitingSeparationItems = (new self())->get([
            'pro_id' => $productId,
            'in' => ['s_status' => [self::S_WAITING, self::S_LISTED]]
        ])->fetch(true);

        $boxesAmount = 0;
        if($awaitingSeparationItems) {
            $awaitingSeparationItems = self::withPallet($awaitingSeparationItems);
            foreach($awaitingSeparationItems as $separationItem) {
                $boxesAmount += $separationItem->isBoxesType() 
                    ? $separationItem->amount 
                    : floor($separationItem->amount / $separationItem->pallet->package);
            }
        }

        return $boxesAmount;
    }

    public static function getToBeSeparatedUnitsByProductId(int $productId): int
    {
        $awaitingSeparationItems = (new self())->get([
            'pro_id' => $productId,
            'in' => ['s_status' => [self::S_WAITING, self::S_LISTED]]
        ])->fetch(true);

        $unitsAmount = 0;
        if($awaitingSeparationItems) {
            $awaitingSeparationItems = self::withPallet($awaitingSeparationItems);
            foreach($awaitingSeparationItems as $separationItem) {
                $unitsAmount += $separationItem->isUnitsType() 
                    ? $separationItem->amount 
                    : $separationItem->amount * $separationItem->pallet->package;
            }
        }

        return $unitsAmount;
    }

    public function getFromToTotal(): array 
    {
        if(!$this->separationItemPallets) {
            $this->pallets();
        }

        return [
            'boxes' => $this->separationItemPallets ? array_sum(array_map(fn($o) => $o->boxes_amount, $this->separationItemPallets)) : 0,
            'units' => $this->separationItemPallets ? array_sum(array_map(fn($o) => $o->units_amount, $this->separationItemPallets)) : 0
        ];
    }

    public function needsFromTo(): bool 
    {
        $tnPallet = Pallet::tableName();
        $tnSeparationItemPallet = SeparationItemPallet::tableName();
        $pickingPallet = (new Pallet())->get([
            'pro_id' => $this->pro_id,
            'height' => 1,
            'p_status' => Pallet::PS_STORED
        ])->fetch(false);

        $pallets = (new Pallet())->join($tnSeparationItemPallet, [
            'raw' => "{$tnSeparationItemPallet}.pal_id = {$tnPallet}.id"
        ])->get([
            "{$tnPallet}.pro_id" => $this->pro_id,
            "{$tnPallet}.p_status" => Pallet::PS_STORED,
            '!=' => ["{$tnSeparationItemPallet}.site_id" => $this->id]
        ], "{$tnPallet}.*")->fetch(true);

        if(($this->isBoxesType() && $pickingPallet->boxes_amount - self::getToBeSeparatedBoxesByProductId($this->pro_id) 
            + $this->getFromToTotal()['boxes'] + ($pallets ? array_sum(array_map(fn($o) => $o->boxes_amount, $pallets)) : 0) < 0) 
            || ($this->isUnitsType() && $pickingPallet->units_amount - self::getToBeSeparatedUnitsByProductId($this->pro_id) 
            + $this->getFromToTotal()['units'] + ($pallets ? array_sum(array_map(fn($o) => $o->units_amount, $pallets)) : 0) < 0)) {
            return true;
        }

        return false;
    }

    public function hasAmountInStock(): bool
    {
        if(!is_null($this->hasAmountInStock)) {
            return $this->hasAmountInStock;
        }

        $amountInStock = 0;
        $amountToBeSeparated = 0;

        $pallets = (new Pallet())->get([
            'pro_id' => $this->pro_id,
            'p_status' => Pallet::PS_STORED
        ])->fetch(true);

        $amountInStock = 0;
        $amountToBeSeparated = 0;
        if($this->isBoxesType()) {
            $amountInStock = Pallet::getPalletsTotalBoxesAmount($pallets);
        } elseif($this->isUnitsType()) {
            $amountInStock = Pallet::getPalletsTotalUnitsAmount($pallets);
        }

        $awaitingSeparationItems = (new self())->get([
            'pro_id' => $this->pro_id,
            'in' => ['s_status' => [self::S_WAITING, self::S_LISTED]]
        ])->fetch(true);
        if($awaitingSeparationItems) {
            $awaitingSeparationItems = self::withPallet($awaitingSeparationItems);
            foreach($awaitingSeparationItems as $separationItem) {
                if($this->isBoxesType()) {
                    $amountToBeSeparated += $separationItem->isBoxesType() 
                        ? $separationItem->amount 
                        : floor($separationItem->amount / $separationItem->pallet->package);
                } elseif($this->isUnitsType()) {
                    $amountToBeSeparated += $separationItem->isUnitsType() 
                        ? $separationItem->amount 
                        : $separationItem->amount * $separationItem->pallet->package;
                }
            }
        }
        
        return $amountToBeSeparated + $this->amount > $amountInStock ? false : true;
    }

    public function separate(int $amount, int $userId): bool 
    {
        $this->pallet();

        $palletsFromTo = [
            (new PalletFromTo())->loadData([
                'usu_id' => $userId,
                'amount' => $this->isBoxesType() ? $this->pallet->boxes_amount : $this->pallet->units_amount,
                'a_type' => $this->a_type,
                'from_pal_id' => $this->pallet->id,
                'to_pal_id' => null
            ])
        ];

        $separationAmount = 0;
        if($dbPallets = $this->pallets()) {
            foreach($dbPallets as $index => $pallet) {
                $separationAmount += $this->isBoxesType() ? $pallet->boxes_amount : $pallet->units_amount;
                $dbPallets[$index]->sep_id = $this->sep_id;
                $dbPallets[$index]->release_usu_id = $userId;
                $dbPallets[$index]->release_date = date('Y-m-d H:i:s');
                $dbPallets[$index]->setAsReleased();

                $palletsFromTo[] = (new PalletFromTo())->loadData([
                    'usu_id' => $userId,
                    'amount' => $this->isBoxesType() ? $pallet->boxes_amount : $pallet->units_amount,
                    'a_type' => $this->a_type,
                    'from_pal_id' => $pallet->id,
                    'to_pal_id' => $this->pallet->id
                ]);
            }

            $palletsFromTo[] = (new PalletFromTo())->loadData([
                'usu_id' => $userId,
                'amount' => $amount - ($this->isBoxesType() ? $this->pallet->boxes_amount : $this->pallet->units_amount),
                'a_type' => $this->a_type,
                'from_pal_id' => $this->pallet->id
            ]);

            if($amount >= $separationAmount + ($this->isBoxesType() ? $this->pallet->boxes_amount : $this->pallet->units_amount)) {
                $this->pallet->sep_id = $this->sep_id;
                $this->pallet->release_usu_id = $userId;
                $this->pallet->release_date = date('Y-m-d H:i:s');
                $this->pallet->setAsReleased();

                $dbPallets[] = $this->pallet;
            }
        }

        if($dbPallets) {
            if(!Pallet::saveMany($dbPallets)) {
                return false;
            }
        }

        if($palletsFromTo) {
            if(!PalletFromTo::insertMany($palletsFromTo)) {
                return false;
            }
        }

        return true;
    }

    public function setAsWaiting(): self 
    {
        $this->s_status = self::S_WAITING;
        return $this;
    }

    public function setAsListed(): self 
    {
        $this->s_status = self::S_LISTED;
        return $this;
    }

    public function setAsSeparated(): self 
    {
        $this->s_status = self::S_SEPARATED;
        return $this;
    }

    public function setAsChecked(): self 
    {
        $this->s_status = self::S_CHECKED;
        return $this;
    }

    public function setAsFinished(): self 
    {
        $this->s_status = self::S_FINISHED;
        return $this;
    }

    public function setAsHavingAmountInStock(): self 
    {
        $this->hasAmountInStock = true;
        return $this;
    }

    public function setAsNotHavingAmountInStock(): self 
    {
        $this->hasAmountInStock = false;
        return $this;
    }

    public function isBoxesType(): bool 
    {
        return $this->a_type == self::AT_BOXES;
    }

    public function isUnitsType(): bool 
    {
        return $this->a_type == self::AT_UNITS;
    }
    
    public function isWaiting(): bool 
    {
        return $this->s_status == self::S_WAITING;
    }

    public function isListed(): bool 
    {
        return $this->s_status == self::S_LISTED;
    }

    public function isSeparated(): bool 
    {
        return $this->s_status == self::S_SEPARATED;
    }

    public function isChecked(): bool 
    {
        return $this->s_status == self::S_CHECKED;
    }

    public function isFinished(): bool 
    {
        return $this->s_status == self::S_FINISHED;
    }
}