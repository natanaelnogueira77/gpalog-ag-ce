<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\User;

class Provider extends DBModel 
{
    public ?User $user = null;
    
    public static function tableName(): string 
    {
        return 'fornecedor';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'usu_id', 
            'name'
        ];
    }

    public function rules(): array 
    {
        return [
            'usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O usuário é obrigatório!')]
            ],
            'name' => [
                [self::RULE_REQUIRED, 'message' => _('O nome é obrigatório!')],
                [self::RULE_MAX, 'max' => 100, 'message' => sprintf(_('O nome deve conter no máximo %s caractéres!'), 100)]
            ]
        ];
    }

    public static function getByUserId(int $userId, string $columns = '*'): ?array
    {
        return (new self())->get(['usu_id' => $userId], $columns)->fetch(true);
    }

    public static function insertMany(array $objects): array|false 
    {
        $allValidated = true;
        if(count($objects) > 0) {
            foreach($objects as $object) {
                if(is_array($object)) $object = (new self())->loadData($object);
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

    public function getCreatedAtDateTime(): DateTime 
    {
        return new DateTime($this->created_at);
    }

    public function getUpdatedAtDateTime(): DateTime 
    {
        return new DateTime($this->updated_at);
    }
}