<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\Pallet;
use Src\Models\SeparationItem;
use Src\Models\User;

class Separation extends DBModel 
{
    const S_WAITING = 1;
    const S_IN_SEPARATION = 2;
    const S_IN_EXPEDITION_CONFERENCE = 3;
    const S_IN_LOADING = 4;

    public ?User $ADMUser = null;
    public ?User $loadingUser = null;
    public ?array $separationItems = null;

    public static function tableName(): string 
    {
        return 'separacao';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'adm_usu_id',
            'loading_usu_id',
            'loading_date',
            'plate',
            'dock',
            's_status'
        ];
    }

    public function rules(): array 
    {
        return array_merge([
            'adm_usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O usuário ADM é obrigatório!')]
            ]
        ], $this->isInLoading() ? [
            'loading_usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O usuário que carregou é obrigatório!')]
            ],
            'plate' => [
                [self::RULE_REQUIRED, 'message' => _('A placa é obrigatória!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('A placa deve conter no máximo %s caractéres!'), 20)]
            ],
            'dock' => [
                [self::RULE_REQUIRED, 'message' => _('A doca é obrigatória!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('A doca deve conter no máximo %s caractéres!'), 20)]
            ],
            'loading_date' => [
                [self::RULE_REQUIRED, 'message' => _('A data de carregamento é obrigatória!')],
                [self::RULE_DATETIME, 'pattern' => 'Y-m-d H:i:s', 'message' => _('A data de carregamento deve seguir o padrão dd/mm/yyyy hh:mm:ss!')]
            ]
        ] : []);
    }

    public function save(): bool 
    {
        $this->loading_usu_id = $this->isInLoading() ? $this->loading_usu_id : null;
        $this->loading_date = $this->isInLoading() ? $this->loading_date : null;
        $this->plate = $this->isInLoading() ? $this->plate : null;
        $this->dock = $this->isInLoading() ? $this->dock : null;
        return parent::save();
    }

    public function destroy(): bool 
    {
        if((new SeparationItem())->get(['sep_id' => $this->id])->count()) {
            $this->addError('destroy', _('Você não pode excluir uma ordem de separação vinculada à um EAN!'));
            return false;
        }
        return parent::destroy();
    }

    public function ADMUser(string $columns = '*'): ?User 
    {
        $this->ADMUser = $this->belongsTo(User::class, 'adm_usu_id', 'id', $columns)->fetch(false);
        return $this->ADMUser;
    }

    public function loadingUser(string $columns = '*'): ?User 
    {
        $this->loadingUser = $this->belongsTo(User::class, 'loading_usu_id', 'id', $columns)->fetch(false);
        return $this->loadingUser;
    }

    public function separationItems(array $filters = [], string $columns = '*'): ?array
    {
        $this->separationItems = $this->hasMany(SeparationItem::class, 'sep_id', 'id', $filters, $columns)->fetch(true);
        return $this->separationItems;
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

    public static function withLoadingUser(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            User::class, 
            'loading_usu_id', 
            'loadingUser', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withSeparationItem(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withHasMany(
            $objects, 
            SeparationItem::class, 
            'sep_id', 
            'separationItem', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function getByADMUserId(int $ADMUserId, string $columns = '*'): ?array 
    {
        return (new self())->get(['adm_usu_id' => $ADMUserId], $columns)->fetch(true);
    }

    public static function getByLoadingUserId(int $loadingUserId, string $columns = '*'): ?array 
    {
        return (new self())->get(['loading_usu_id' => $loadingUserId], $columns)->fetch(true);
    }

    public function getCreatedAtDateTime(): DateTime 
    {
        return new DateTime($this->created_at);
    }

    public function getUpdatedAtDateTime(): DateTime 
    {
        return new DateTime($this->updated_at);
    }

    public function getLoadingDateTime(): ?DateTime 
    {
        return $this->loading_date ? new DateTime($this->loading_date) : null;
    }

    public static function getStates(): array 
    {
        return [
            self::S_WAITING => _('Aguardando'),
            self::S_IN_SEPARATION => _('Em Separação'),
            self::S_IN_EXPEDITION_CONFERENCE => _('Em Conferência de Expedição'),
            self::S_IN_LOADING => _('Em Carregamento')
        ];
    }

    public function getStatus(): ?string 
    {
        return isset(self::getStates()[$this->s_status]) ? self::getStates()[$this->s_status] : null;
    }

    public static function getStatesColors(): array 
    {
        return [
            self::S_WAITING => 'warning',
            self::S_IN_SEPARATION => 'info',
            self::S_IN_EXPEDITION_CONFERENCE => 'primary',
            self::S_IN_LOADING => 'success'
        ];
    }

    public function getStatusColor(): ?string 
    {
        return isset(self::getStatesColors()[$this->s_status]) ? self::getStatesColors()[$this->s_status] : null;
    }

    public function hasNotSeparatedEANs(): bool 
    {
        if($this->separationItems()) {
            foreach($this->separationItems as $separationItem) {
                if(!$separationItem->isSeparated() && !$separationItem->isChecked() && !$separationItem->isFinished()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasNotCheckedEANs(): bool 
    {
        if($this->separationItems()) {
            foreach($this->separationItems as $separationItem) {
                if(!$separationItem->isChecked() && !$separationItem->isFinished()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function setAsWaiting(): self 
    {
        $this->s_status = self::S_WAITING;
        return $this;
    }

    public function setAsInSeparation(): self 
    {
        $this->s_status = self::S_IN_SEPARATION;
        return $this;
    }
    
    public function setAsInExpeditionConference(): self 
    {
        $this->s_status = self::S_IN_EXPEDITION_CONFERENCE;
        return $this;
    }

    public function setAsInLoading(): self 
    {
        $this->s_status = self::S_IN_LOADING;
        return $this;
    }

    public function isWaiting(): bool 
    {
        return $this->s_status == self::S_WAITING;
    }

    public function isInSeparation(): bool 
    {
        return $this->s_status == self::S_IN_SEPARATION;
    }

    public function isInExpeditionConference(): bool 
    {
        return $this->s_status == self::S_IN_EXPEDITION_CONFERENCE;
    }

    public function isInLoading(): bool 
    {
        return $this->s_status == self::S_IN_LOADING;
    }
}