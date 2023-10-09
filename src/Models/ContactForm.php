<?php

namespace Src\Models;

use GTG\MVC\Components\Email;
use GTG\MVC\Model;

class ContactForm extends Model 
{
    public ?string $name = null;
    public ?string $email = null;
    public ?string $subject = null;
    public ?string $body = null;

    public function rules(): array 
    {
        return [
            'name' => [
                [self::RULE_REQUIRED, 'message' => _('O nome é obrigatório!')],
                [self::RULE_MAX, 'max' => 100, 'message' => sprintf(_('O nome precisa ter no máximo %s caractéres!'), 100)]
            ],
            'email' => [
                [self::RULE_REQUIRED, 'message' => _('O email é obrigatório!')], 
                [self::RULE_EMAIL, 'message' => _('O email é inválido!')],
                [self::RULE_MAX, 'max' => 100, 'message' => sprintf(_('O email precisa ter no máximo %s caractéres!'), 100)]
            ],
            'subject' => [
                [self::RULE_REQUIRED, 'message' => _('O assunto é obrigatório!')],
                [self::RULE_MAX, 'max' => 100, 'message' => sprintf(_('O assunto precisa ter no máximo %s caractéres!'), 100)]
            ],
            'body' => [
                [self::RULE_REQUIRED, 'message' => _('O corpo da mensagem é obrigatório!')],
                [self::RULE_MAX, 'max' => 1000, 'message' => sprintf(_('A mensagem precisa ter no máximo %s caractéres!'), 1000)]
            ]
        ];
    }

    public function send(): bool 
    {
        if(!$this->validate()) {
            return false;
        }

        $email = new Email();
        $email->add($this->subject, $this->body, 'Natanael Nogueira', 'piano.nogueirans@gmail.com');
        if(!$email->send($this->name, $this->email)) {
            return false;
        }

        return true;
    }
}