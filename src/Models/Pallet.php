<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Components\Barcode;
use Src\Models\Conference;
use Src\Models\Output;
use Src\Models\Pallet;
use Src\Models\Product;
use Src\Models\Street;
use Src\Models\User;

class Pallet extends DBModel 
{
    const ST_PALLETIZATION = 1;
    const ST_REWORK = 2;
    const ST_STORAGE = 3;
    const ST_IMPORTED = 4;

    const PS_STORED = 1;
    const PS_SEPARATED = 2;
    const PS_RELEASED = 3;

    public ?Conference $conference = null;
    public ?Output $output = null;
    public ?Product $product = null;
    public ?User $releaseUser = null;
    public ?User $storeUser = null;
    private bool $isProductOnPicking = false;

    public static function tableName(): string 
    {
        return 'pallet';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'con_id', 
            'pro_id', 
            'store_usu_id', 
            'package', 
            'physic_boxes_amount', 
            'units_amount', 
            'service_type', 
            'pallet_height', 
            'street_number', 
            'position', 
            'height', 
            'code', 
            'sai_id', 
            'release_usu_id', 
            'release_date', 
            'load_plate', 
            'dock', 
            'p_status'
        ];
    }

    public function rules(): array 
    {
        return [
            'con_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID de conferência é obrigatório!')]
            ],
            'pro_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID do produto é obrigatório!')]
            ],
            'store_usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O usuário que alocou é obrigatório!')]
            ],
            'package' => [
                [self::RULE_REQUIRED, 'message' => _('A embalagem é obrigatória!')]
            ],
            'physic_boxes_amount' => [
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
            'street_number' => [
                [self::RULE_REQUIRED, 'message' => _('O número da rua é obrigatório!')]
            ],
            'position' => [
                [self::RULE_REQUIRED, 'message' => _('A posição da rua é obrigatório!')]
            ],
            'height' => [
                [self::RULE_REQUIRED, 'message' => _('A altura da rua é obrigatória!')]
            ],
            'code' => [
                [self::RULE_REQUIRED, 'message' => _('O código é obrigatório!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('O código deve conter no máximo %s caractéres!'), 20)]
            ],
            'p_status' => [
                [self::RULE_REQUIRED, 'message' => _('O status é obrigatório!')],
                [self::RULE_IN, 'values' => array_keys(self::getStates()), 'message' => _('O status é inválido!')],
            ]
        ] + (
            $this->isSeparated() 
            ? [
                'sai_id' => [
                    [self::RULE_REQUIRED, 'message' => _('O ID de separação é obrigatório!')]
                ]
            ]
            : []
        ) + (
            $this->isReleased() 
            ? [
                'release_usu_id' => [
                    [self::RULE_REQUIRED, 'message' => _('O usuário que liberou é obrigatório!')]
                ],
                'release_date' => [
                    [self::RULE_REQUIRED, 'message' => _('A data de saída é obrigatória!')],
                    [self::RULE_DATETIME, 'pattern' => 'Y-m-d H:i:s', 'message' => _('A data de saída deve seguir o padrão dd/mm/yyyy hh/mm/ss!')]
                ],
                'load_plate' => [
                    [self::RULE_REQUIRED, 'message' => _('A placa de carregamento é obrigatória!')],
                    [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('A placa de carregamento deve conter no máximo %s caractéres!'), 20)]
                ],
                'dock' => [
                    [self::RULE_REQUIRED, 'message' => _('A doca é obrigatória!')],
                    [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('A doca deve conter no máximo %s caractéres!'), 20)]
                ]
            ]
            : []
        );
    }

    public function save(): bool 
    {
        $this->p_status = $this->p_status ? $this->p_status : self::PS_STORED;
        $this->sai_id = $this->isSeparated() || $this->isReleased() ? $this->sai_id : null;
        $this->release_usu_id = $this->isReleased() ? $this->release_usu_id : null;
        $this->release_date = $this->isReleased() ? $this->release_date : null;
        $this->load_plate = $this->isReleased() ? $this->load_plate : null;
        $this->dock = $this->isReleased() ? $this->dock : null;
        return parent::save();
    }

    public function conference(string $columns = '*'): ?Conference 
    {
        $this->conference = $this->belongsTo(Conference::class, 'con_id', 'id', $columns)->fetch(false);
        return $this->conference;
    }

    public function output(string $columns = '*'): ?Output 
    {
        $this->output = $this->sai_id ? $this->belongsTo(Output::class, 'sai_id', 'id', $columns)->fetch(false) : null;
        return $this->output;
    }

    public function product(string $columns = '*'): ?Product 
    {
        $this->product = $this->pro_id ? $this->belongsTo(Product::class, 'pro_id', 'id', $columns)->fetch(false) : null;
        return $this->product;
    }

    public function releaseUser(string $columns = '*'): ?User 
    {
        $this->releaseUser = $this->release_usu_id ? $this->belongsTo(User::class, 'release_usu_id', 'id', $columns)->fetch(false) : null;
        return $this->releaseUser;
    }

    public function storeUser(string $columns = '*'): ?User 
    {
        $this->storeUser = $this->belongsTo(User::class, 'store_usu_id', 'id', $columns)->fetch(false);
        return $this->storeUser;
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

    public static function withOutput(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            Output::class, 
            'sai_id', 
            'output', 
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

    public static function withReleaseUser(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            User::class, 
            'release_usu_id', 
            'releaseUser', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withStoreUser(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            User::class, 
            'store_usu_id', 
            'storeUser', 
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

    public static function getByReleaseUserId(int $userId, string $columns = '*'): ?array 
    {
        return (new self())->get(['release_usu_id' => $userId], $columns)->fetch(true);
    }

    public static function getByStoreUserId(int $userId, string $columns = '*'): ?array 
    {
        return (new self())->get(['store_usu_id' => $userId], $columns)->fetch(true);
    }

    public static function getByCode(string $code, string $columns = '*'): ?self 
    {
        return (new self())->get([
            'code' => $code, 
            'in' => ['p_status' => [self::PS_STORED, self::PS_SEPARATED]]
        ], $columns)->fetch(false);
    }

    public function getStorageDateTime(): DateTime 
    {
        return new DateTime($this->created_at);
    }

    public function getReleaseDateTime(): ?DateTime 
    {
        return $this->release_date ? new DateTime($this->release_date) : null;
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

    public static function getStates(): array 
    {
        return [
            self::PS_STORED => _('Alocado'),
            self::PS_SEPARATED => _('Separado'),
            self::PS_RELEASED => _('Liberado')
        ];
    }

    public function getStatus(): ?string 
    {
        return isset(self::getStates()[$this->p_status]) ? self::getStates()[$this->p_status] : null;
    }

    public function getCreatedAtDateTime(): DateTime 
    {
        return new DateTime($this->created_at);
    }

    public function getUpdatedAtDateTime(): DateTime 
    {
        return new DateTime($this->updated_at);
    }

    public function getBarcodePNG(): string 
    {
        return (new Barcode())->getBarcodePNG($this->code);
    }

    public static function hasAllocationForAll(array $pallets): bool 
    {
        $palletsPerHeight = [
            '1.40' => 0, 
            '2.20' => 0
        ];
        foreach($pallets as $pallet) {
            if($pallet->pallet_height == 1.4) {
                $palletsPerHeight['1.40']++;
            } elseif($pallet->pallet_height == 2.2) {
                $palletsPerHeight['2.20']++;
            }
        }

        $availablePlaces = [
            '1.40' => Street::getAvailablePlacesByHeight(1.40, $palletsPerHeight['1.40']),
            '2.20' => Street::getAvailablePlacesByHeight(2.20, $palletsPerHeight['2.20'])
        ];

        if(count($availablePlaces['1.40']) < $palletsPerHeight['1.40'] 
            || count($availablePlaces['2.20']) < $palletsPerHeight['2.20']) {
            return false;
        }

        return true;
    }

    public static function allocateMany(array $pallets): bool 
    {
        $palletsPerHeight = [
            '1.40' => 0, 
            '2.20' => 0
        ];

        foreach($pallets as $pallet) {
            if($pallet->pallet_height == 1.4) {
                $palletsPerHeight['1.40']++;
            } elseif($pallet->pallet_height == 2.2) {
                $palletsPerHeight['2.20']++;
            }
        }

        $availablePlaces = [
            '1.40' => Street::getAvailablePlacesByHeight(1.40),
            '2.20' => Street::getAvailablePlacesByHeight(2.20)
        ];

        $isProductOnPicking = [];
        $skippedPlaces = [];
        $skippedPickingPlaces = [];

        foreach($pallets as $pallet) {
            if(!isset($isProductOnPicking[$pallet->pro_id])) {
                $isProductOnPicking[$pallet->pro_id] = false;
            }

            if(!$pallet->isStored() && !$pallet->isSeparated() && !$pallet->isReleased()) {
                if($pallet->pallet_height == 1.4) {
                    $profile = '1.40';
                } elseif($pallet->pallet_height == 2.2) {
                    $profile = '2.20';
                }

                $i = 0;
                $hasSkippedPlaces = false;
                if(!$isProductOnPicking[$pallet->pro_id] || !$pallet->isProductOnPicking()) {
                    while($availablePlaces[$profile][$i]['height'] != 1) {
                        $hasSkippedPlaces = true;
                        $i++;
                    }
                }

                if($availablePlaces[$profile][$i]['height'] == 1) {
                    if($isProductOnPicking[$pallet->pro_id] || $pallet->isProductOnPicking()) {
                        while($availablePlaces[$profile][$i]['height'] == 1) {
                            $hasSkippedPlaces = true;
                            $i++;
                        }
                    } else {
                        $isProductOnPicking[$pallet->pro_id] = true;
                    }
                }

                $pallet->street_number = $availablePlaces[$profile][$i]['street_number'];
                $pallet->position = $availablePlaces[$profile][$i]['position'];
                $pallet->height = $availablePlaces[$profile][$i]['height'];
                $pallet->code = $pallet->street_number . (
                    $pallet->position < 10 ? "00{$pallet->position}" : (
                        $pallet->position < 100 ? "0{$pallet->position}" : $pallet->position
                    )
                ) . $pallet->height;
                $pallet->setAsStored();

                if($hasSkippedPlaces) {
                    unset($availablePlaces[$profile][$i]);
                    $availablePlaces[$profile] = array_values($availablePlaces[$profile]);
                } else {
                    array_shift($availablePlaces[$profile]);
                }
            }
        }

        return Pallet::saveMany($pallets) ? true : false;
    }

    public function setAsStored(): self 
    {
        $this->p_status = self::PS_STORED;
        return $this;
    }

    public function setAsSeparated(): self 
    {
        $this->p_status = self::PS_SEPARATED;
        return $this;
    }

    public function setAsReleased(): self 
    {
        $this->p_status = self::PS_RELEASED;
        return $this;
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

    public function isStored(): bool 
    {
        return $this->p_status == self::PS_STORED;
    }

    public function isSeparated(): bool 
    {
        return $this->p_status == self::PS_SEPARATED;
    }

    public function isReleased(): bool 
    {
        return $this->p_status == self::PS_RELEASED;
    }

    public function isProductOnPicking(): bool 
    {
        if($this->isProductOnPicking) {
            return $this->isProductOnPicking;
        }

        $this->isProductOnPicking = (new self())->get([
            'pro_id' => $this->pro_id, 
            'height' => 1
        ])->count() ? true : false;

        return $this->isProductOnPicking;
    }
}