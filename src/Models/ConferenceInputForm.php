<?php

namespace Src\Models;

use GTG\MVC\Model;
use Src\Models\Product;

class ConferenceInputForm extends Model 
{
    public ?string $package = null;
    public ?string $barcode = null;
    public bool $has_started = false;
    public bool $has_product = false;
    public bool $is_completed = false;
    public ?int $boxes_amount = null;
    public ?int $pallets_amount = null;
    public ?int $service_type = null;
    public ?float $pallet_height = null;

    public function rules(): array 
    {
        return [
            'barcode' => [
                [self::RULE_REQUIRED, 'message' => _('O código de barras é obrigatório!')]
            ]
        ] + (
            $this->hasProduct() 
            ? [
                'package' => [
                    [self::RULE_REQUIRED, 'message' => _('A embalagem é obrigatória!')]
                ],
                'barcode' => [
                    [self::RULE_REQUIRED, 'message' => _('O código de barras é obrigatório!')]
                ],
                'boxes_amount' => [
                    [self::RULE_REQUIRED, 'message' => _('A quantidade de caixas no físico é obrigatória!')]
                ],
                'pallets_amount' => [
                    [self::RULE_REQUIRED, 'message' => _('A quantidade de pallets fechados é obrigatória!')]
                ],
                'service_type' => [
                    [self::RULE_REQUIRED, 'message' => _('O tipo de serviço é obrigatório!')]
                ],
                'pallet_height' => [
                    [self::RULE_REQUIRED, 'message' => _('A altura é obrigatória!')]
                ]
            ] : []
        );
    }

    public function getProduct(): ?Product 
    {
        if(!$this->validate()) {
            return null;
        }

        if(!$dbProduct = Product::getByBarcode($this->barcode)) {
            $this->addError('barcode', _('Nenhum produto foi encontrado!'));
            return null;
        }

        $this->has_product = true;
        return $dbProduct;
    }

    public function complete(): bool 
    {
        if(!$this->validate()) {
            return false;
        }

        $this->is_completed = true;
        return true;
    }

    public function hasStarted(): bool 
    {
        return $this->has_started;
    }

    public function hasBarcode(): bool 
    {
        return $this->barcode ? true : false;
    }

    public function hasProduct(): bool 
    {
        return $this->has_product;
    }

    public function isCompleted(): bool 
    {
        return $this->is_completed;
    }
}