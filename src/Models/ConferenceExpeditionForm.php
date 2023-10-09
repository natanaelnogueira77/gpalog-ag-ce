<?php

namespace Src\Models;

use GTG\MVC\Model;
use Src\Models\Product;
use Src\Models\Separation;
use Src\Models\SeparationEAN;

class ConferenceExpeditionForm extends Model 
{
    const STEP_EAN = 1;
    const STEP_COMPLETION = 2;

    public ?int $sep_id = null;
    public ?string $ean = null;
    public ?string $amount = null;
    public int $step = 0;
    public ?Product $product = null;
    public ?Separation $separation = null;
    public ?SeparationEAN $separationEAN = null;
    private bool $has_ean = false;
    private bool $has_completion = false;

    public function rules(): array 
    {
        return [
            'sep_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID de separação é obrigatório!')]
            ],
            'ean' => [
                [self::RULE_REQUIRED, 'message' => _('O EAN é obrigatório!')]
            ],
            'amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade é obrigatória!')]
            ],
            self::RULE_RAW => [
                function ($model) {
                    if(!$model->hasError('sep_id') && !$model->separation = (new Separation())->findById($model->sep_id)) {
                        $model->addError('sep_id', _('Este ID de separação não foi gerado!'));
                    }
            
                    if(!$model->hasError('ean') && !$model->product = Product::getByBarcode($model->ean)) {
                        $model->addError('ean', _('Nenhum produto foi encontrado a partir deste EAN!'));
                    }
                    
                    if(!$model->hasError('sep_id') && !$model->hasError('ean')) {
                        if(!$model->separationEAN = (new SeparationEAN())->get([
                            'sep_id' => $model->sep_id, 
                            'pro_id' => $model->product->id
                            ])->fetch(false)) {
                            $model->addError('sep_id', _('Nenhum registro de separação foi encontrado por este EAN e ID de separação!'));
                            $model->addError('ean', _('Nenhum registro de separação foi encontrado por este EAN e ID de separação!'));
                        } elseif(!$model->separationEAN->isSeparated()) {
                            $model->addError('sep_id', _('Este produto ainda não foi separado pelo operador!'));
                            $model->addError('ean', _('Este produto ainda não foi separado pelo operador!'));
                        }
                    }
                }
            ]
        ];
    }

    public function getByEAN(): bool 
    {
        if(!$this->validate()) {
            return false;
        }

        $this->has_ean = true;
        return true;
    }

    public function validateCompletion(): bool 
    {
        if(!$this->validate()) {
            return false;
        }

        $this->has_completion = true;
        return true;
    }

    public function isOnEAN(): bool 
    {
        return $this->step == self::STEP_EAN;
    }

    public function isOnCompletion(): bool 
    {
        return $this->step == self::STEP_COMPLETION;
    }

    public function hasEAN(): bool 
    {
        return $this->has_ean;
    }

    public function hasCompletion(): bool 
    {
        return $this->has_completion;
    }
}