<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\Pallet;
use Src\Models\User;

class PalletFromTo extends DBModel 
{
    const AT_BOXES = 1;
    const AT_UNITS = 2;
    
    public ?Pallet $fromPallet = null;
    public ?Pallet $toPallet = null;
    public ?User $user = null;

    public static function tableName(): string 
    {
        return 'pallet_depara';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'usu_id',
            'amount',
            'a_type',
            'to_pal_id',
            'from_pal_id'
        ];
    }

    public function rules(): array 
    {
        return [
            'usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID de conferência é obrigatório!')]
            ],
            'amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade é obrigatória!')]
            ],
            'a_type' => [
                [self::RULE_REQUIRED, 'message' => _('O tipo de quantidade é obrigatório!')],
                [self::RULE_IN, 'values' => array_keys(self::getAmountTypes()), 'message' => _('O tipo de quantidade é inválido!')],
            ]
        ];
    }

    public function save(): bool 
    {
        $this->to_pal_id = $this->to_pal_id ? $this->to_pal_id : null;
        $this->from_pal_id = $this->from_pal_id ? $this->from_pal_id : null;
        return parent::save();
    }

    public function fromPallet(string $columns = '*'): ?Pallet 
    {
        $this->fromPallet = $this->from_pal_id 
            ? $this->belongsTo(Pallet::class, 'from_pal_id', 'id', $columns)->fetch(false) 
            : null;
        return $this->fromPallet;
    }

    public function toPallet(string $columns = '*'): ?Pallet 
    {
        $this->toPallet = $this->to_pal_id 
            ? $this->belongsTo(Pallet::class, 'to_pal_id', 'id', $columns)->fetch(false) 
            : null;
        return $this->toPallet;
    }

    public function user(string $columns = '*'): ?User 
    {
        $this->user = $this->belongsTo(User::class, 'usu_id', 'id', $columns)->fetch(false);
        return $this->user;
    }

    public static function withFromPallet(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            Pallet::class, 
            'from_pal_id', 
            'fromPallet', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withToPallet(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            Pallet::class, 
            'to_pal_id', 
            'toPallet', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withUser(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            User::class, 
            'usu_id', 
            'user', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function getByFromPalletId(int $fromPalletId, string $columns = '*'): ?array 
    {
        return (new self())->get(['from_pal_id' => $fromPalletId], $columns)->fetch(true);
    }

    public static function getByToPalletId(int $toPalletId, string $columns = '*'): ?array 
    {
        return (new self())->get(['to_pal_id' => $toPalletId], $columns)->fetch(true);
    }

    public static function getByUserId(int $userId, string $columns = '*'): ?array 
    {
        return (new self())->get(['usu_id' => $userId], $columns)->fetch(true);
    }

    public function getCreatedAtDateTime(): DateTime 
    {
        return new DateTime($this->created_at);
    }

    public function getUpdatedAtDateTime(): DateTime 
    {
        return new DateTime($this->updated_at);
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
}