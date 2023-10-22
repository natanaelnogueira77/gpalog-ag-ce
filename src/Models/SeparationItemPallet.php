<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\Pallet;
use Src\Models\Product;
use Src\Models\Separation;
use Src\Models\User;

class SeparationItemPallet extends DBModel 
{
    public ?SeparationItem $separationItem = null;
    public ?Pallet $pallet = null;

    public static function tableName(): string 
    {
        return 'separacao_item_pallet';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'site_id',
            'pal_id'
        ];
    }

    public function rules(): array 
    {
        return array_merge([
            'site_id' => [
                [self::RULE_REQUIRED, 'message' => _('O item de separação é obrigatório!')]
            ],
            'pal_id' => [
                [self::RULE_REQUIRED, 'message' => _('O pallet é obrigatório!')]
            ]
        ]);
    }

    public function separationItem(string $columns = '*'): ?SeparationItem 
    {
        $this->separationItem = $this->belongsTo(SeparationItem::class, 'site_id', 'id', $columns)->fetch(false);
        return $this->separationItem;
    }

    public function pallet(string $columns = '*'): ?Pallet 
    {
        $this->pallet = $this->belongsTo(Pallet::class, 'pal_id', 'id', $columns)->fetch(false);
        return $this->pallet;
    }

    public static function withSeparationItem(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            SeparationItem::class, 
            'site_id', 
            'separationItem', 
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

    public function getCreatedAtDateTime(): DateTime 
    {
        return new DateTime($this->created_at);
    }

    public function getUpdatedAtDateTime(): DateTime 
    {
        return new DateTime($this->updated_at);
    }
}