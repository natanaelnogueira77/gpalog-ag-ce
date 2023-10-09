<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\UserModel;
use Src\Models\SocialUser;
use Src\Models\UserMeta;
use Src\Models\UserType;

class User extends UserModel 
{
    const UT_ADMIN = 1;
    const UT_ADM = 2;
    const UT_OPERATOR = 3;

    public ?SocialUser $socialUser = null;
    public ?array $userMetas = [];
    public ?UserType $userType = null;
    
    public static function tableName(): string 
    {
        return 'usuario';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'utip_id', 
            'name', 
            'email', 
            'password', 
            'token', 
            'slug'
        ];
    }

    public static function metaTableData(): ?array 
    {
        return [
            'class' => UserMeta::class,
            'entity' => 'usu_id',
            'meta' => 'meta',
            'value' => 'value'
        ];
    }

    public function rules(): array 
    {
        return [
            'utip_id' => [
                [self::RULE_REQUIRED, 'message' => _('O tipo de usuário é obrigatório!')]
            ],
            'name' => [
                [self::RULE_REQUIRED, 'message' => _('O nome é obrigatório!')],
                [self::RULE_MAX, 'max' => 100, 'message' => sprintf(_('O nome deve conter no máximo %s caractéres!'), 100)]
            ],
            'email' => [
                [self::RULE_REQUIRED, 'message' => _('O email é obrigatório!')], 
                [self::RULE_EMAIL, 'message' => _('O email é inválido!')], 
                [self::RULE_MAX, 'max' => 100, 'message' => sprintf(_('O email deve conter no máximo %s caractéres!'), 100)]
            ],
            'password' => [
                [self::RULE_REQUIRED, 'message' => _('A senha é obrigatória!')], 
                [self::RULE_MIN, 'min' => 5, 'message' => sprintf(_('A senha deve conter no mínimo %s caractéres!'), 5)]
            ],
            'slug' => [
                [self::RULE_REQUIRED, 'message' => _('O apelido é obrigatório!')],
                [self::RULE_MAX, 'max' => 100, 'message' => sprintf(_('O apelido deve conter no máximo %s caractéres!'), 100)]
            ],
            self::RULE_RAW => [
                function ($model) {
                    if(!$model->hasError('email')) {
                        if((new self())->get(['email' => $model->email] + (isset($model->id) ? ['!=' => ['id' => $model->id]] : []))->count()) {
                            $model->addError('email', _('O email informado já está em uso! Tente outro.'));
                        }
                    }
                    
                    if(!$model->hasError('slug')) {
                        if((new self())->get(['slug' => $model->slug] + (isset($model->id) ? ['!=' => ['id' => $model->id]] : []))->count()) {
                            $model->addError('slug', _('O apelido informado já está em uso! Tente outro.'));
                        }
                    }
                }
            ]
        ];
    }

    public function save(): bool 
    {
        $this->slug = is_string($this->slug) ? slugify($this->slug) : $this->getSlugByName();
        $this->email = strtolower($this->email);
        $this->token = is_string($this->email) ? md5($this->email) : null;

        return parent::save();
    }

    public function encode(): static 
    {
        if(!password_get_info($this->password)['algo']) {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        }

        return $this;
    }

    public function destroy(): bool 
    {
        if($this->isAdmin()) {
            $this->addError('destroy', _('Vai por mim, isso vai dar ruim! Você não pode excluir o administrador do sistema.'));
            return false;
        } elseif((new SocialUser())->get(['usu_id' => $this->id])->count()) {
            $this->addError('destroy', _('Você não pode excluir um usuário vinculado à uma rede social!'));
            return false;
        } elseif((new UserMeta())->get(['usu_id' => $this->id])->count()) {
            $this->addError('destroy', _('Você não pode excluir um usuário com dados armazenados!'));
            return false;
        }
        return parent::destroy();
    }

    public function socialUser(string $columns = '*'): ?SocialUser 
    {
        $this->socialUser = $this->hasOne(SocialUser::class, 'usu_id', 'id', $columns)->fetch(false);
        return $this->socialUser;
    }

    public function userMetas(array $filters = [], string $columns = '*'): ?array 
    {
        $this->userMetas = $this->hasMany(UserMeta::class, 'usu_id', 'id', $filters, $columns)->fetch(true);
        return $this->userMetas;
    }

    public function userType(string $columns = '*'): ?UserType 
    {
        $this->userType = $this->belongsTo(UserType::class, 'utip_id', 'id', $columns)->fetch(false);
        return $this->userType;
    }

    public static function withSocialUser(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withHasOne(
            $objects, 
            SocialUser::class, 
            'usu_id', 
            'socialUser', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withUserType(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            UserType::class, 
            'utip_id', 
            'userType', 
            'id', 
            $filters, 
            $columns
        );
    }

    public function isAdmin(): bool
    {
        return $this->utip_id == self::UT_ADMIN;
    }

    public function isADMUser(): bool
    {
        return $this->utip_id == self::UT_ADM;
    }

    public function isOperator(): bool
    {
        return $this->utip_id == self::UT_OPERATOR;
    }

    public static function getBySlug(string $slug, string $columns = '*'): ?self 
    {
        return (new self())->get(['slug' => $slug], $columns)->fetch(false);
    }

    public static function getByEmail(string $email, string $columns = '*'): ?self 
    {
        return (new self())->get(['email' => $email], $columns)->fetch(false);
    }

    public static function getByToken(string $token, string $columns = '*'): ?self 
    {
        return (new self())->get(['token' => $token], $columns)->fetch(false);
    }

    public function verifyPassword(string $password): bool 
    {
        return $this->password ? password_verify($password, $this->password) : false;
    }

    public static function getByRegistrationNumber(string $registrationNumber, string $columns = '*'): ?self 
    {
        $userMeta = (new UserMeta())->get([
            'meta' => UserMeta::KEY_REGISTRATION_NUMBER,
            'value' => $registrationNumber
        ])->fetch(false);
        return $userMeta ? $userMeta->user() : null;
    }
    
    public function getSlugByName(): ?string 
    {
        return is_string($this->name) ? slugify($this->name . (new DateTime())->getTimestamp()) : null;
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