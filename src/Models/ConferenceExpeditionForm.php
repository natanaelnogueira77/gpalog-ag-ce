<?php

namespace Src\Models;

use GTG\MVC\Model;
use Src\Models\Product;
use Src\Models\Separation;
use Src\Models\SeparationItem;

class ConferenceExpeditionForm extends Model 
{
    const STEP_EAN = 1;
    const STEP_AMOUNT = 2;
    const STEP_COMPLETION = 3;

    public ?int $sep_id = null;
    public ?string $ean = null;
    public ?string $amount = null;
    public int $step = 0;
    public ?Product $product = null;
    public ?Separation $separation = null;
    public ?SeparationItem $separationItem = null;
    private bool $has_ean = false;
    private bool $has_amount = false;
    private bool $has_completion = false;

    public function rules(): array 
    {
        return array_merge($this->isOnEAN() || $this->isOnAmount() ? [
            'sep_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID de separação é obrigatório!')]
            ],
            'ean' => [
                [self::RULE_REQUIRED, 'message' => _('O EAN é obrigatório!')]
            ]
        ] : [], $this->hasEAN() && $this->isOnAmount() ? [
            'amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade é obrigatória!')]
            ]
        ] : []);
    }

    public function getByEAN(): bool 
    {
        if(!$this->validate()) {
            return false;
        }

        if(!$this->separation = (new Separation())->findById($this->sep_id)) {
            $this->addError('sep_id', _('Este ID de separação não foi gerado!'));
        }

        if(!$this->product = Product::getByBarcode($this->ean)) {
            $this->addError('ean', _('Nenhum produto foi encontrado a partir deste EAN!'));
        }
        
        if(!$this->hasError('sep_id') && !$this->hasError('ean')) {
            if(!$this->separationItem = (new SeparationItem())->get([
                'sep_id' => $this->sep_id, 
                'pro_id' => $this->product->id,
                'in' => ['s_status' => [SeparationItem::S_LISTED, SeparationItem::S_SEPARATED]]
                ])->fetch(false)) {
                $this->addError('sep_id', _('Nenhum item de separação para ser conferido foi encontrado por este EAN e ID de separação!'));
                $this->addError('ean', _('Nenhum item de separação para ser conferido foi encontrado por este EAN e ID de separação!'));
            } elseif(!$this->separationItem->isSeparated()) {
                $this->addError('sep_id', _('Este produto ainda não foi separado pelo operador!'));
                $this->addError('ean', _('Este produto ainda não foi separado pelo operador!'));
            }
        }

        if($this->hasErrors()) {
            return false;
        }

        $this->has_ean = true;
        return true;
    }

    public function validateAmount(): bool 
    {
        if(!$this->validate()) {
            return false;
        }

        $this->has_amount = true;
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

    public function isOnAmount(): bool 
    {
        return $this->step == self::STEP_AMOUNT;
    }

    public function isOnCompletion(): bool 
    {
        return $this->step == self::STEP_COMPLETION;
    }

    public function hasEAN(): bool 
    {
        return $this->has_ean;
    }

    public function hasAmount(): bool 
    {
        return $this->has_amount;
    }

    public function hasCompletion(): bool 
    {
        return $this->has_completion;
    }
}