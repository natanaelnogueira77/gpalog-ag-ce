<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\Pallet;
use Src\Models\SeparationEAN;
use Src\Models\User;

class Separation extends DBModel 
{
    const S_WAITING = 1;
    const S_IN_LOADING = 2;

    public ?User $ADMUser = null;
    public ?User $loadingUser = null;
    public ?array $separationEANs = null;

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
            'plate',
            'dock',
            's_status'
        ];
    }

    public function rules(): array 
    {
        return [
            'adm_usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O usuário ADM é obrigatório!')]
            ]
        ];
    }

    public function save(): bool 
    {
        $this->loading_usu_id = $this->isInLoading() ? $this->loading_usu_id : null;
        $this->plate = $this->isInLoading() ? $this->plate : null;
        $this->dock = $this->isInLoading() ? $this->dock : null;
        return parent::save();
    }

    public function destroy(): bool 
    {
        if((new SeparationEAN())->get(['sep_id' => $this->id])->count()) {
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

    public function separationEANs(array $filters = [], string $columns = '*'): ?array
    {
        $this->separationEANs = $this->hasMany(SeparationEAN::class, 'sep_id', 'id', $filters, $columns)->fetch(true);
        return $this->separationEANs;
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

    public static function withSeparationEAN(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withHasMany(
            $objects, 
            SeparationEAN::class, 
            'sep_id', 
            'separationEAN', 
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

    public static function getStates(): array 
    {
        return [
            self::S_WAITING => _('Aguardando'),
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
            self::S_IN_LOADING => 'primary'
        ];
    }

    public function getStatusColor(): ?string 
    {
        return isset(self::getStatesColors()[$this->s_status]) ? self::getStatesColors()[$this->s_status] : null;
    }

    public function hasNotSeparatedEANs(): bool 
    {
        if($this->separationEANs()) {
            foreach($this->separationEANs as $separationEAN) {
                if(!$separationEAN->isSeparated() && !$separationEAN->isChecked()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasNotCheckedEANs(): bool 
    {
        if($this->separationEANs()) {
            foreach($this->separationEANs as $separationEAN) {
                if(!$separationEAN->isChecked()) {
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

    public function setAsInLoading(): self 
    {
        $this->s_status = self::S_IN_LOADING;
        return $this;
    }

    public function isWaiting(): bool 
    {
        return $this->s_status == self::S_WAITING;
    }

    public function isInLoading(): bool 
    {
        return $this->s_status == self::S_IN_LOADING;
    }
}