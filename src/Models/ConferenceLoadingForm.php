<?php

namespace Src\Models;

use GTG\MVC\Model;
use Src\Models\Separation;

class ConferenceLoadingForm extends Model 
{
    const STEP_SEPARATION_ID = 1;
    const STEP_COMPLETION = 2;

    public ?string $sep_id = null;
    public ?string $plate = null;
    public ?string $dock = null;
    public int $step = 0;
    private bool $has_separation_id = false;
    private bool $has_completion = false;

    public function rules(): array 
    {
        return [
            'sep_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID de separação é obrigatório!')]
            ],
            'plate' => [
                [self::RULE_REQUIRED, 'message' => _('A placa de carregamento é obrigatória!')]
            ],
            'dock' => [
                [self::RULE_REQUIRED, 'message' => _('A doca é obrigatória!')]
            ],
            self::RULE_RAW => [
                function ($model) {
                    if(!$model->hasError('sep_id') && !$model->separation = (new Separation())->findById($model->sep_id)) {
                        $model->addError('sep_id', _('Este ID de separação não foi gerado!'));
                    } elseif(!$model->hasError('sep_id') && $model->separation->hasNotCheckedEANs()) {
                        $model->addError('sep_id', _('Este ID de separação ainda tem EANs para serem conferidos!'));
                    }
                }
            ]
        ];
    }

    public function getBySeparationId(): bool 
    {
        if(!$this->validate()) {
            return false;
        }

        $this->has_separation_id = true;
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

    public function isOnSeparationId(): bool 
    {
        return $this->step == self::STEP_SEPARATION_ID;
    }

    public function isOnCompletion(): bool 
    {
        return $this->step == self::STEP_COMPLETION;
    }

    public function hasSeparationId(): bool 
    {
        return $this->has_separation_id;
    }

    public function hasCompletion(): bool 
    {
        return $this->has_completion;
    }
}