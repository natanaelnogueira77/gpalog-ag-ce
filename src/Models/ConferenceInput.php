<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\Conference;
use Src\Models\Product;
use Src\Models\User;

class ConferenceInput extends DBModel 
{
    const ST_PALLETIZATION = 1;
    const ST_REWORK = 2;
    const ST_STORAGE = 3;
    const ST_IMPORTED = 4;

    public ?Conference $conference = null;
    public ?Product $product = null;
    public ?User $releaseUser = null;
    public ?User $storeUser = null;

    public static function tableName(): string 
    {
        return 'entrada';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'con_id', 
            'usu_id', 
            'pro_id', 
            'package', 
            'physic_boxes_amount', 
            'closed_plts_amount', 
            'units_amount', 
            'service_type', 
            'pallet_height', 
            'barcode'
        ];
    }

    public function rules(): array 
    {
        return [
            'con_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID de conferência é obrigatório!')]
            ],
            'usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID do usuário é obrigatório!')]
            ],
            'pro_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID do produto é obrigatório!')]
            ],
            'package' => [
                [self::RULE_REQUIRED, 'message' => _('A embalagem é obrigatória!')]
            ],
            'physic_boxes_amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade de caixas físicas é obrigatório!')]
            ],
            'closed_plts_amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade de caixas físicas é obrigatório!')]
            ],
            'units_amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade de unidades é obrigatório!')]
            ],
            'service_type' => [
                [self::RULE_REQUIRED, 'message' => _('O tipo de serviço é obrigatório!')],
                [self::RULE_IN, 'values' => array_keys(self::getServiceTypes()), 'message' => _('O tipo de serviço é inválido!')]
            ],
            'pallet_height' => [
                [self::RULE_REQUIRED, 'message' => _('A altura do pallet é obrigatória!')]
            ],
            'barcode' => [
                [self::RULE_REQUIRED, 'message' => _('O código de barras é obrigatório!')],
                [self::RULE_MAX, 'max' => 50, 'message' => sprintf(_('O código de barras deve conter no máximo %s caractéres!'), 50)]
            ]
        ];
    }

    public function conference(string $columns = '*'): ?Conference 
    {
        $this->conference = $this->belongsTo(Conference::class, 'con_id', 'id', $columns)->fetch(false);
        return $this->conference;
    }

    public function product(string $columns = '*'): ?Product 
    {
        $this->product = $this->pro_id ? $this->belongsTo(Product::class, 'pro_id', 'id', $columns)->fetch(false) : null;
        return $this->product;
    }

    public function user(string $columns = '*'): ?User 
    {
        $this->user = $this->belongsTo(User::class, 'usu_id', 'id', $columns)->fetch(false);
        return $this->user;
    }

    public static function withConference(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            Conference::class, 
            'con_id', 
            'conference', 
            'id', 
            $filters, 
            $columns
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

    public static function getByConferenceId(int $conferenceId, string $columns = '*'): ?array 
    {
        return (new self())->get(['con_id' => $conferenceId], $columns)->fetch(true);
    }

    public static function getByProductId(int $productId, string $columns = '*'): ?array 
    {
        return (new self())->get(['pro_id' => $productId], $columns)->fetch(true);
    }

    public static function getByUserId(int $userId, string $columns = '*'): ?array 
    {
        return (new self())->get(['usu_id' => $userId], $columns)->fetch(true);
    }

    public static function getServiceTypes(): array 
    {
        return [
            self::ST_PALLETIZATION => _('Paletização'),
            self::ST_REWORK => _('Retrabalho'),
            self::ST_STORAGE => _('Armazenagem'),
            self::ST_IMPORTED => _('Importado')
        ];
    }

    public function getServiceType(): ?string 
    {
        return isset(self::getServiceTypes()[$this->service_type]) ? self::getServiceTypes()[$this->service_type] : null;
    }

    public function getCreatedAtDateTime(): DateTime 
    {
        return new DateTime($this->created_at);
    }

    public function getUpdatedAtDateTime(): DateTime 
    {
        return new DateTime($this->updated_at);
    }

    public function isPalletization(): bool 
    {
        return $this->service_type == self::ST_PALLETIZATION;
    }

    public function isRework(): bool 
    {
        return $this->service_type == self::ST_REWORK;
    }

    public function isStorage(): bool 
    {
        return $this->service_type == self::ST_STORAGE;
    }

    public function isImported(): bool 
    {
        return $this->service_type == self::ST_IMPORTED;
    }
}