<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\User;

class UserType extends DBModel 
{
    public ?array $users = null;

    public static function tableName(): string 
    {
        return 'usuario_tipo';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'name_sing', 
            'name_plur'
        ];
    }

    public function rules(): array 
    {
        return [
            'name_sing' => [
                [self::RULE_REQUIRED, 'message' => _('O nome no singular é obrigatório!')],
                [self::RULE_MAX, 'max' => 50, 'message' => sprintf(_('O nome no singular deve conter no máximo %s caractéres!'), 50)]
            ],
            'name_plur' => [
                [self::RULE_REQUIRED, 'message' => _('O nome no plural é obrigatório!')],
                [self::RULE_MAX, 'max' => 50, 'message' => sprintf(_('O nome no plural deve conter no máximo %s caractéres!'), 50)]
            ]
        ];
    }

    public function users(array $filters = [], string $columns = '*'): ?array 
    {
        $this->users = $this->hasMany(User::class, 'utip_id', 'id', $filters, $columns)->fetch(true);
        return $this->users;
    }

    public static function withUsers(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withHasMany(
            $objects, 
            User::class, 
            'utip_id', 
            'users', 
            'id', 
            $filters, 
            $columns
        );
    }

    public function destroy(): bool 
    {
        if((new User())->get(['utip_id' => $this->id])->count()) {
            $this->addError('destroy', _('Você não pode excluir um tipo de usuário vinculado à um usuário!'));
            return false;
        }
        return parent::destroy();
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