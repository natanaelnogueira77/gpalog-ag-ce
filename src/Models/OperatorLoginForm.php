<?php

namespace Src\Models;

use GTG\MVC\Model;
use Src\Models\User;

class OperatorLoginForm extends Model 
{
    public ?int $registration_number = null;
    public ?string $password = null;

    public function rules(): array 
    {
        return [
            'registration_number' => [
                [self::RULE_REQUIRED, 'message' => _('O número da matrícula é obrigatório!')]
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

        $user = User::getByRegistrationNumber($this->registration_number);
        if(!$user || !$user->verifyPassword($this->password)) {
            $this->addError('registration_number', _('O número da matrícula ou a senha estão incorretos!'));
            $this->addError('password', _('O número da matrícula ou a senha estão incorretos!'));
            return null;
        } elseif(!$user->isOperator()) {
            $this->addError('registration_number', _('Essa conta não possui permissão para entrar nessa área!'));
            return null;
        }

        return $user;
    }
}