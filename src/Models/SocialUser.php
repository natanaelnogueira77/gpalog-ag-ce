<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\User;

class SocialUser extends DBModel 
{
    public ?User $user = null;

    public static function tableName(): string 
    {
        return 'social_usuario';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'usu_id', 
            'social_id', 
            'email', 
            'social'
        ];
    }

    public function rules(): array 
    {
        return [
            'usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O usuário é obrigatório!')]
            ],
            'social_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID da rede social é obrigatório!')]
            ],
            'social' => [
                [self::RULE_REQUIRED, 'message' => _('O nome da rede social é obrigatório!')]
            ],
            'email' => [
                [self::RULE_REQUIRED, 'message' => _('O email é obrigatório!')],
                [self::RULE_EMAIL, 'message' => _('O email é inválido!')],
                [self::RULE_MAX, 'max' => 100, 'message' => sprintf(_('O email precisa ter no máximo %s caractéres!'), 100)]
            ],
            self::RULE_RAW => [
                function ($model) {
                    if(!$model->hasError('social') && !in_array($model->social, self::getSocialNames())) {
                        $model->addError('social', _('O nome da rede social é inválido!'));
                    }
            
                    if(!$model->hasError('email')) {
                        if((new self())->get(['email' => $model->email] + (isset($model->id) ? ['!=' => ['id' => $model->id]] : []))->count()) {
                            $model->addError('email', _('O email informado já está em uso! Tente outro.'));
                        }
                    }
                }
            ]
        ];
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

    public static function getBySocialId(string $socialId, string $social): ?self 
    {
        return (new self())->get([
            'social_id' => $socialId,
            'social' => $social
        ])->fetch(false);
    }

    public static function getBySocialEmail(string $email, string $social): ?self 
    {
        return (new self())->get([
            'email' => $email,
            'social' => $social
        ])->fetch(false);
    }

    public static function getSocialNames(): array 
    {
        return ['facebook', 'google'];
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