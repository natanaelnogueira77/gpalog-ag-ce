<?php

namespace Src\Models;

use GTG\MVC\Model;
use Src\Models\Operation;

class SeparationForm extends Model 
{
    public ?string $order_number = null;

    public function rules(): array 
    {
        return [
            'order_number' => [
                [self::RULE_REQUIRED, 'message' => _('A ordem de serviço é obrigatória!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('A ordem de serviço precisa ter no máximo %s caractéres!'), 20)]
            ]
        ];
    }

    public function getOperation(): ?Operation
    {
        if(!$this->validate()) {
            return null;
        }

        if(!$dbOperation = Operation::getByServiceOrder($this->order_number)) {
            $this->addError('order_number', _('Nenhuma operação foi encontrada a partir dessa ordem de serviço!'));
            return null;
        }

        return $dbOperation;
    }
}