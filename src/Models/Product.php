<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Components\Barcode;

class Product extends DBModel 
{
    public static function tableName(): string 
    {
        return 'produto';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'name', 
            'prov_id', 
            'prov_name', 
            'prod_id', 
            'emb_fb', 
            'ean', 
            'dun14', 
            'p_length', 
            'p_width', 
            'p_height', 
            'p_base', 
            'p_weight', 
            'plu'
        ];
    }

    public function rules(): array 
    {
        return [
            'name' => [
                [self::RULE_REQUIRED, 'message' => _('O nome é obrigatório!')],
                [self::RULE_MAX, 'max' => 100, 'message' => sprintf(_('O nome deve conter no máximo %s caractéres!'), 100)]
            ], 
            'prov_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID do fornecedor é obrigatório!')]
            ], 
            'prov_name' => [
                [self::RULE_REQUIRED, 'message' => _('O nome do fornecedor é obrigatório!')],
                [self::RULE_MAX, 'max' => 100, 'message' => sprintf(_('O nome do fornecedor deve conter no máximo %s caractéres!'), 100)]
            ], 
            'prod_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID do produto é obrigatório!')]
            ], 
            'ean' => [
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('O código EAN deve conter no máximo %s caractéres!'), 20)]
            ],
            'dun14' => [
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('O código Dun14 deve conter no máximo %s caractéres!'), 20)]
            ],
            'plu' => [
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('O código PLU deve conter no máximo %s caractéres!'), 20)]
            ]
        ];
    }

    public function save(): bool 
    {
        $this->emb_fb = $this->emb_fb ? $this->emb_fb : null;
        $this->ean = $this->ean ? $this->ean : null;
        $this->dun14 = $this->dun14 ? $this->dun14 : null;
        $this->p_length = $this->p_length ? $this->p_length : null;
        $this->p_width = $this->p_width ? $this->p_width : null;
        $this->p_height = $this->p_height ? $this->p_height : null;
        $this->p_base = $this->p_base ? $this->p_base : null;
        $this->p_weight = $this->p_weight ? $this->p_weight : null;
        $this->plu = $this->plu ? $this->plu : null;
        return parent::save();
    }

    public static function getByBarcode(string $code, string $columns = '*'): ?Product 
    {
        return (new self())->get(['ean' => $code], $columns)->fetch(false);
    }

    public static function insertMany(array $objects): array|false 
    {
        $allValidated = true;
        if(count($objects) > 0) {
            foreach($objects as $object) {
                if(is_array($object)) $object = (new self())->loadData($object);

                $object->emb_fb = $object->emb_fb ? $object->emb_fb : null;
                $object->ean = $object->ean ? $object->ean : null;
                $object->dun14 = $object->dun14 ? $object->dun14 : null;
                $object->p_length = $object->p_length ? $object->p_length : null;
                $object->p_width = $object->p_width ? $object->p_width : null;
                $object->p_height = $object->p_height ? $object->p_height : null;
                $object->p_base = $object->p_base ? $object->p_base : null;
                $object->p_weight = $object->p_weight ? $object->p_weight : null;
                $object->plu = $object->plu ? $object->plu : null;

                if(!$object->validate()) {
                    $allValidated = false;
                }
            }
        }

        if(!$allValidated) {
            return $objects;
        }

        for($i = 0; $i <= count($objects) - 1; $i += 1000) {
            if($objects) {
                parent::insertMany(array_slice($objects, $i, 1000));
            }
        }

        return $objects;
    }

    public function getCreatedAtDateTime(): DateTime 
    {
        return new DateTime($this->created_at);
    }

    public function getUpdatedAtDateTime(): DateTime 
    {
        return new DateTime($this->updated_at);
    }

    public function getEANBarcodePNG() 
    {
        return (new Barcode())->getBarcodePNG($this->ean);
    }
}