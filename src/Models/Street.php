<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\Pallet;
use Src\Models\User;

class Street extends DBModel 
{
    public ?int $allocateds = null;
    public ?User $user = null;

    public static function tableName(): string 
    {
        return 'rua';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'usu_id', 
            'street_number', 
            'start_position', 
            'end_position', 
            'max_height', 
            'profile', 
            'max_plts', 
            'obs', 
            'is_limitless'
        ];
    }

    public function rules(): array 
    {
        return [
            'usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O usuário é obrigatório!')]
            ],
            'street_number' => [
                [self::RULE_REQUIRED, 'message' => _('O número da rua é obrigatório!')]
            ],
            'obs' => [
                [self::RULE_MAX, 'max' => 500, 'message' => sprintf(_('A observação deve conter no máximo %s caractéres!'), 500)]
            ]
        ] + (
            !$this->isLimitless() 
            ? [
                'start_position' => [
                    [self::RULE_REQUIRED, 'message' => _('A posição inicial é obrigatória!')]
                ],
                'end_position' => [
                    [self::RULE_REQUIRED, 'message' => _('A posição final é obrigatória!')]
                ],
                'max_height' => [
                    [self::RULE_REQUIRED, 'message' => _('A altura máxima é obrigatória!')]
                ],
                'profile' => [
                    [self::RULE_REQUIRED, 'message' => _('O perfil é obrigatório!')]
                ],
                'max_plts' => [
                    [self::RULE_REQUIRED, 'message' => _('A capacidade máxima é obrigatória!')]
                ]
            ] : []
        );
    }

    public function save(): bool 
    {
        $this->start_position = !$this->isLimitless() ? $this->start_position : null;
        $this->end_position = !$this->isLimitless() ? $this->end_position : null;
        $this->max_height = !$this->isLimitless() ? $this->max_height : null;
        $this->profile = !$this->isLimitless() ? $this->profile : null;
        $this->max_plts = !$this->isLimitless() ? $this->max_plts : null;
        $this->obs = $this->obs ? $this->obs : null;
        $this->is_limitless = $this->is_limitless ? 1 : 0;
        return parent::save();
    }

    public static function insertMany(array $objects): array|false 
    {
        if(count($objects) > 0) {
            foreach($objects as $object) {
                if(is_array($object)) $object = (new self())->loadData($object);
                
                $object->start_position = !$object->isLimitless() ? $object->start_position : null;
                $object->end_position = !$object->isLimitless() ? $object->end_position : null;
                $object->max_height = !$object->isLimitless() ? $object->max_height : null;
                $object->profile = !$object->isLimitless() ? $object->profile : null;
                $object->max_plts = !$object->isLimitless() ? $object->max_plts : null;
                $object->obs = $object->obs ? $object->obs : null;
                $object->is_limitless = $object->is_limitless ? 1 : 0;
            }
        }

        return parent::insertMany($objects);
    }

    public function user(string $columns = '*'): ?User 
    {
        $this->user = $this->belongsTo(User::class, 'usu_id', 'id', $columns)->fetch(false);
        return $this->user;
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

    public static function getAvailablePlacesByHeight(float $height, ?int $limit = null): ?array 
    {
        if(!$streets = (new Street())->get(['raw' => "(profile = {$height} OR is_limitless = 1)"])
            ->order('is_limitless, street_number')->fetch(true)) {
            return null;
        }

        $pallets = (new Pallet())->get([
            'in' => ['p_status' => [Pallet::PS_STORED, Pallet::PS_SEPARATED]], 
            'raw' => "pallet_height = {$height}"
        ], 'street_number, position, height')->fetch(true);

        if($pallets) {
            $gPallets = [];
            $palletsCount = [];
            foreach($pallets as $pallet) {
                $palletsCount[$pallet->street_number]++;
                $gPallets[$pallet->street_number][$pallet->position][$pallet->height] = true;
            }
            $pallets = $gPallets;
        } else {
            $pallets = [];
        }

        $availablePlaces = [];
        foreach($streets as $street) {
            if(!isset($pallets[$street->street_number])) {
                $pallets[$street->street_number] = [];
            }

            if(!$street->isLimitless()) {
                for($i = $street->start_position + ($street->start_position % 2 == 0 ? 1 : 0); $i <= $street->end_position; $i += 2) {
                    for($j = 1; $j <= $street->max_height; $j++) {
                        if($street->max_plts <= $palletsCount[$street->street_number] 
                            + count(array_filter($availablePlaces, fn($p) => $p['street_number'] == $street->street_number))) {
                            continue 3;
                        }
    
                        if(!is_null($limit) && $limit == 0) {
                            return $availablePlaces;
                        }
    
                        if(!isset($pallets[$street->street_number][$i][$j])) {
                            $availablePlaces[] = [
                                'street_number' => $street->street_number,
                                'position' => $i,
                                'height' => $j
                            ];
                            if(!is_null($limit)) $limit--;
                        }
                    }
                }

                for($i = $street->start_position + ($street->start_position % 2 == 1 ? 1 : 0); $i <= $street->end_position; $i += 2) {
                    for($j = 1; $j <= $street->max_height; $j++) {
                        if($street->max_plts <= $palletsCount[$street->street_number] 
                            + count(array_filter($availablePlaces, fn($p) => $p['street_number'] == $street->street_number))) {
                            continue 3;
                        }
    
                        if(!is_null($limit) && $limit == 0) {
                            return $availablePlaces;
                        }
    
                        if(!isset($pallets[$street->street_number][$i][$j])) {
                            $availablePlaces[] = [
                                'street_number' => $street->street_number,
                                'position' => $i,
                                'height' => $j
                            ];
                            if(!is_null($limit)) $limit--;
                        }
                    }
                }
            } else {
                if(!is_null($limit)) {
                    $i = 1;
                    while($limit > 0) {
                        if(!isset($pallets[$street->street_number][$i][1])) {
                            $availablePlaces[] = [
                                'street_number' => $street->street_number,
                                'position' => $i,
                                'height' => 1
                            ];
                            $limit--;
                        }
                        $i++;
                    }

                    return $availablePlaces;
                }
            }
        }

        return $availablePlaces;
    }

    public function isLimitless(): bool 
    {
        return $this->is_limitless ? true : false;
    }
}