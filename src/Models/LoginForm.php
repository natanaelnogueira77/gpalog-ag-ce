<?php

namespace Src\Models;

use GTG\MVC\Model;
use Src\Models\User;

class LoginForm extends Model 
{
    public ?string $email = null;
    public ?string $password = null;

    public function rules(): array 
    {
        return [
            'email' => [
                [self::RULE_REQUIRED, 'message' => _('O email é obrigatório!')], 
                [self::RULE_EMAIL, 'message' => _('O email é inválido!')]
            ],
            'password' => [
                [self::RULE_REQUIRED, 'message' => _('A senha é obrigatória!')]
            ]
        ];
    }

    public function login(): ?User 
    {
        if(!$this->validate()) {
            return null;
        }

        if(!$user = User::getByEmail($this->email)) {
            $this->addError('email', _('Este email não foi encontrado!'));
            return null;
        }
        
        if(!$user->verifyPassword($this->password)) {
            $this->addError('password', _('A senha está incorreta!'));
            return null;
        }

        return $user;
    }
}