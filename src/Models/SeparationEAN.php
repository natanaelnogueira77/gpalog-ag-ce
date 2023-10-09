<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\Pallet;
use Src\Models\Product;
use Src\Models\Separation;
use Src\Models\User;

class SeparationEAN extends DBModel 
{
    const AT_BOXES = 1;
    const AT_UNITS = 2;

    const S_WAITING = 1;
    const S_LISTED = 2;
    const S_SEPARATED = 3;
    const S_CHECKED = 4;

    public ?User $ADMUser = null;
    public ?User $conferenceUser = null;
    public ?Pallet $pallet = null;
    public ?Product $product = null;
    public ?Separation $separation = null;
    public ?User $separationUser = null;

    public static function tableName(): string 
    {
        return 'separacao_ean';
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
            'a_type', 
            'amount', 
            'sep_id',
            'separation_usu_id',
            'address',
            'sep_amount',
            'dispatch_dock',
            'conf_usu_id',
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
            'a_type' => [
                [self::RULE_REQUIRED, 'message' => _('O tipo de quantidade é obrigatório!')],
                [self::RULE_IN, 'values' => array_keys(self::getAmountTypes()), 'message' => _('O tipo de quantidade é inválido!')],
            ],
            'amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade é obrigatória!')],
                [self::RULE_LARGER_THAN, 'value' => 0, 'message' => sprintf(_('A quantidade precisa ser maior ou igual a %s!'), 0)]
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
            'address' => [
                [self::RULE_REQUIRED, 'message' => _('O endereçamento é obrigatório!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('O endereçamento deve conter no máximo %s caractéres!'), 20)]
            ],
            'sep_amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade é obrigatória!')]
            ],
            'dispatch_dock' => [
                [self::RULE_REQUIRED, 'message' => _('A doca de dispacho é obrigatória!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('A doca de dispacho deve conter no máximo %s caractéres!'), 20)]
            ]
        ] : [],
        $this->isChecked() ? [
            'conf_usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O conferente é obrigatório!')]
            ],
            'conf_amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade conferida é obrigatória!')]
            ]
        ] : []);
    }

    public function save(): bool 
    {
        $this->sep_id = $this->isListed() || $this->isSeparated() || $this->isChecked() ? $this->sep_id : null;
        $this->separation_usu_id = $this->isSeparated() || $this->isChecked() ? $this->separation_usu_id : null;
        $this->address = $this->isSeparated() || $this->isChecked() ? $this->address : null;
        $this->sep_amount = $this->isSeparated() || $this->isChecked() ? $this->sep_amount : null;
        $this->dispatch_dock = $this->isSeparated() || $this->isChecked() ? $this->dispatch_dock : null;
        $this->conf_usu_id = $this->isChecked() ? $this->conf_usu_id : null;
        $this->conf_amount = $this->isChecked() ? $this->conf_amount : null;
        return parent::save();
    }

    public function ADMUser(string $columns = '*'): ?User 
    {
        $this->ADMUser = $this->belongsTo(User::class, 'adm_usu_id', 'id', $columns)->fetch(false);
        return $this->ADMUser;
    }
    
    public function conferenceUser(string $columns = '*'): ?User 
    {
        $this->conferenceUser = $this->conf_usu_id 
            ? $this->belongsTo(User::class, 'adm_usu_id', 'id', $columns)->fetch(false)
            : null;
        return $this->conferenceUser;
    }

    public function pallet(string $columns = '*'): ?Pallet 
    {
        $this->pallet = Pallet::getByCode($this->address, $columns);
        return $this->pallet;
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
        $this->separationUser = $this->conf_usu_id 
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
        $addresses = self::getPropertyValues($objects, 'address');
        if($registries = (new Pallet())->get(['in' => ['code' => $addresses]] + $filters, $columns)->fetch(true)) {
            $registries = Pallet::getGroupedBy($registries, 'code');
            foreach($objects as $index => $object) {
                $objects[$index]->pallet = $registries[$object->address];
            }
        }

        return $objects;
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
            self::S_CHECKED => _('Conferido')
        ];
    }

    public function getStatus(): ?string 
    {
        return isset(self::getStatuss()[$this->s_status]) ? self::getStatuss()[$this->s_status] : null;
    }

    public function getCreatedAtDateTime(): DateTime 
    {
        return new DateTime($this->created_at);
    }

    public function getUpdatedAtDateTime(): DateTime 
    {
        return new DateTime($this->updated_at);
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
}