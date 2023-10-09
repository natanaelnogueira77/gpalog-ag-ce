<?php

namespace Src\Models;

use GTG\MVC\Model;
use Src\Models\Pallet;
use Src\Models\Product;
use Src\Models\SeparationEAN;

class ConferenceSeparationForm extends Model 
{
    const STEP_EAN = 1;
    const STEP_AMOUNT = 2;
    const STEP_DOCK = 3;

    public ?string $address = null;
    public ?string $ean = null;
    public ?string $amount = null;
    public ?string $dispatch_dock = null;
    public int $step = 0;
    public ?Pallet $pallet = null;
    public ?Product $product = null;
    public ?SeparationEAN $separationEAN = null;
    private bool $has_ean = false;
    private bool $has_amount = false;
    private bool $has_dock = false;

    public function rules(): array 
    {
        return array_merge($this->isOnEAN() || $this->isOnAmount() || $this->isOnDock() ? [
            'address' => [
                [self::RULE_REQUIRED, 'message' => _('O endereçamento é obrigatório!')]
            ],
            'ean' => [
                [self::RULE_REQUIRED, 'message' => _('O EAN é obrigatório!')]
            ]
        ] : [], $this->hasEAN() && ($this->isOnAmount() || $this->isOnDock()) ? [
            'amount' => [
                [self::RULE_REQUIRED, 'message' => _('A quantidade é obrigatória!')]
            ]
        ] : [], $this->hasAmount() && $this->isOnDock() ? [
            'dispatch_dock' => [
                [self::RULE_REQUIRED, 'message' => _('A doca de despacho é obrigatória!')]
            ]
        ] : []);
    }

    public function getByEAN(): bool 
    {
        if(!$this->validate()) {
            return false;
        }
        
        if(!$this->pallet = Pallet::getByCode($this->address)) {
            $this->addError('address', _('Nenhum pallet foi encontrado por este endereçamento!'));
            return false;
        }

        if(!$this->product = Product::getByBarcode($this->ean)) {
            $this->addError('ean', _('Nenhum produto foi encontrado a partir deste EAN!'));
            return false;
        }

        if($this->pallet->pro_id != $this->product->id) {
            $this->addError('ean', _('O EAN de separação não é o mesmo que o do produto que está no pallet!'));
            return false;
        }

        if(!$this->separationEAN = (new SeparationEAN())->get([
            'pro_id' => $this->product->id, 
            's_status' => SeparationEAN::S_LISTED
            ])->fetch(false)) {
            $this->addError('sep_id', _('Nenhum registro de separação foi encontrado por este EAN e ID de separação!'));
            $this->addError('ean', _('Nenhum registro de separação foi encontrado por este EAN e ID de separação!'));
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

    public function validateDock(): bool 
    {
        if(!$this->validate()) {
            return false;
        }

        $this->has_dock = true;
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

    public function isOnDock(): bool 
    {
        return $this->step == self::STEP_DOCK;
    }

    public function hasEAN(): bool 
    {
        return $this->has_ean;
    }

    public function hasAmount(): bool 
    {
        return $this->has_amount;
    }

    public function hasDock(): bool 
    {
        return $this->has_dock;
    }
}