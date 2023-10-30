<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\ConferenceInput;
use Src\Models\Operation;
use Src\Models\Pallet;
use Src\Models\User;

class Conference extends DBModel 
{
    const CS_WAITING = 1;
    const CS_STARTED = 2;
    const CS_FINISHED = 3;

    public ?User $ADMUser = null;
    public ?array $conferenceInputs = null;
    public ?User $endUser = null;
    public ?Operation $operation = null;
    public ?array $pallets = null;
    public ?User $startUser = null;

    public static function tableName(): string 
    {
        return 'conferencia';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'ope_id', 
            'adm_usu_id', 
            'start_usu_id', 
            'date_start', 
            'end_usu_id', 
            'date_end', 
            'c_status'
        ];
    }

    public function rules(): array 
    {
        return [
            'ope_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ID de operação é obrigatório!')]
            ],
            'adm_usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O ADM é obrigatório!')]
            ],
            'c_status' => [
                [self::RULE_REQUIRED, 'message' => _('O status é obrigatório!')],
                [self::RULE_IN, 'values' => array_keys(self::getStates()), 'message' => _('O status é inválido!')],
            ]
        ] + (
            $this->isStarted() || $this->isFinished() 
            ? [
                'start_usu_id' => [
                    [self::RULE_REQUIRED, 'message' => _('O usuário que iniciou a conferência é obrigatório!')]
                ],
                'date_start' => [
                    [self::RULE_REQUIRED, 'message' => _('A data de início é obrigatória!')],
                    [self::RULE_DATETIME, 'pattern' => 'Y-m-d H:i:s', 'message' => _('A data de início deve seguir o padrão dd/mm/yyyy hh/mm/ss!')]
                ]
            ] : []
        ) + (
            $this->isFinished() 
            ? [
                'start_usu_id' => [
                    [self::RULE_REQUIRED, 'message' => _('O usuário que finalizou a conferência é obrigatório!')]
                ],
                'date_end' => [
                    [self::RULE_REQUIRED, 'message' => _('A data de término é obrigatória!')],
                    [self::RULE_DATETIME, 'pattern' => 'Y-m-d H:i:s', 'message' => _('A data de término deve seguir o padrão dd/mm/yyyy hh/mm/ss!')]
                ]
            ] : []
        );
    }

    public function save(): bool 
    {
        $this->c_status = $this->c_status ? $this->c_status : self::CS_WAITING;
        $this->start_usu_id = $this->isStarted() || $this->isFinished() ? $this->start_usu_id : null;
        $this->date_start = $this->isStarted() || $this->isFinished() ? $this->date_start : null;
        $this->end_usu_id = $this->isFinished() ? $this->end_usu_id : null;
        $this->date_end = $this->isFinished() ? $this->date_end : null;
        return parent::save();
    }

    public function destroy(): bool 
    {
        if($objects = (new ConferenceInput())->get(['con_id' => $this->id], 'id')->fetch(true)) {
            ConferenceInput::deleteByIds(ConferenceInput::getPropertyValues($objects));
        }

        if($objects = (new Pallet())->get(['con_id' => $this->id], 'id')->fetch(true)) {
            Pallet::deleteByIds(Pallet::getPropertyValues($objects));
        }

        return parent::destroy();
    }

    public function ADMUser(string $columns = '*'): ?User 
    {
        $this->ADMUser = $this->belongsTo(User::class, 'adm_usu_id', 'id', $columns)->fetch(false);
        return $this->ADMUser;
    }

    public function conferenceInputs(array $filters = [], string $columns = '*'): ?array
    {
        $this->conferenceInputs = $this->hasMany(ConferenceInput::class, 'con_id', 'id', $filters, $columns)->fetch(true);
        return $this->conferenceInputs;
    }

    public function endUser(string $columns = '*'): ?User 
    {
        $this->endUser = $this->belongsTo(User::class, 'end_usu_id', 'id', $columns)->fetch(false);
        return $this->endUser;
    }

    public function operation(string $columns = '*'): ?Operation 
    {
        $this->operation = $this->belongsTo(Operation::class, 'ope_id', 'id', $columns)->fetch(false);
        return $this->operation;
    }

    public function pallets(array $filters = [], string $columns = '*'): ?array
    {
        $this->pallets = $this->hasMany(Pallet::class, 'con_id', 'id', $filters, $columns)->fetch(true);
        return $this->pallets;
    }

    public function startUser(string $columns = '*'): ?User 
    {
        $this->startUser = $this->belongsTo(User::class, 'start_usu_id', 'id', $columns)->fetch(false);
        return $this->startUser;
    }

    public static function withADMUser(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            User::class, 
            'adm_usu_id', 
            'ADMUser', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withEndUser(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            User::class, 
            'end_usu_id', 
            'endUser', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withOperation(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            Operation::class, 
            'ope_id', 
            'operation', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withPallets(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withHasMany(
            $objects, 
            Pallet::class, 
            'con_id', 
            'pallets', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withStartUser(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            User::class, 
            'start_usu_id', 
            'startUser', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function getByADMUserId(int $userId, string $columns = '*'): ?array 
    {
        return (new self())->get(['adm_usu_id' => $userId], $columns)->fetch(true);
    }

    public static function getByEndUserId(int $userId, string $columns = '*'): ?array 
    {
        return (new self())->get(['end_usu_id' => $userId], $columns)->fetch(true);
    }

    public static function getByOperationId(int $operationId, string $columns = '*'): ?array 
    {
        return (new self())->get(['ope_id' => $operationId], $columns)->fetch(true);
    }

    public static function getByStartUserId(int $userId, string $columns = '*'): ?array 
    {
        return (new self())->get(['start_usu_id' => $userId], $columns)->fetch(true);
    }

    public function getServiceType(): ?string 
    {
        return isset(self::getServiceTypes()[$this->service_type]) ? self::getServiceTypes()[$this->service_type] : null;
    }

    public static function getStates(): array 
    {
        return [
            self::CS_WAITING => _('Aguardando'),
            self::CS_STARTED => _('Iniciada'),
            self::CS_FINISHED => _('Finalizada')
        ];
    }

    public function getStatus(): ?string 
    {
        return isset(self::getStates()[$this->c_status]) ? self::getStates()[$this->c_status] : null;
    }

    public static function getStatesColors(): array 
    {
        return [
            self::CS_WAITING => 'warning',
            self::CS_STARTED => 'primary',
            self::CS_FINISHED => 'success'
        ];
    }

    public function getStatusColor(): ?string 
    {
        return isset(self::getStatesColors()[$this->c_status]) ? self::getStatesColors()[$this->c_status] : null;
    }

    public function generatePallets(): ?array 
    {
        if(!$conferenceInputs = $this->conferenceInputs()) {
            return null;
        }

        $dbPallets = [];
        foreach($conferenceInputs as $conferenceInput) {
            for($i = 1; $i <= $conferenceInput->pallets_amount; $i++) {
                $dbPallets[] = (new Pallet())->loadData([
                    'con_id' => $this->id, 
                    'pro_id' => $conferenceInput->pro_id, 
                    'store_usu_id' => $this->start_usu_id, 
                    'package' => $conferenceInput->package, 
                    'start_boxes_amount' => $conferenceInput->boxes_amount, 
                    'start_units_amount' => $conferenceInput->units_amount, 
                    'boxes_amount' => $conferenceInput->boxes_amount, 
                    'units_amount' => $conferenceInput->units_amount, 
                    'service_type' => $conferenceInput->service_type, 
                    'pallet_height' => $conferenceInput->pallet_height,
                    'expiration_date' => $conferenceInput->expiration_date
                ]);
            }
        }

        return $dbPallets;
    }

    public function getStartDateTime(): ?DateTime 
    {
        return $this->date_start ? new DateTime($this->date_start) : null;
    }

    public function getEndDateTime(): ?DateTime 
    {
        return $this->date_end ? new DateTime($this->date_end) : null;
    }

    public function getCreatedAtDateTime(): DateTime 
    {
        return new DateTime($this->created_at);
    }

    public function getUpdatedAtDateTime(): DateTime 
    {
        return new DateTime($this->updated_at);
    }

    public function setAsWaiting(): self 
    {
        $this->c_status = self::CS_WAITING;
        return $this;
    }

    public function setAsStarted(): self 
    {
        $this->c_status = self::CS_STARTED;
        return $this;
    }

    public function setAsFinished(): self 
    {
        $this->c_status = self::CS_FINISHED;
        return $this;
    }

    public function isWaiting(): bool 
    {
        return $this->c_status == self::CS_WAITING;
    }

    public function isStarted(): bool 
    {
        return $this->c_status == self::CS_STARTED;
    }

    public function isFinished(): bool 
    {
        return $this->c_status == self::CS_FINISHED;
    }
}