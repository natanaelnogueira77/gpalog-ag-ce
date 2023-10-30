<?php

namespace Src\Models;

use DateTime;
use GTG\MVC\DB\DBModel;
use Src\Models\Conference;
use Src\Models\Provider;
use Src\Models\User;

class Operation extends DBModel 
{
    const ST_PALLETIZATION = 1;
    const ST_REWORK = 2;
    const ST_STORAGE = 3;
    const ST_IMPORTED = 4;

    public ?Conference $conference = null;
    public ?Provider $provider = null;
    public ?User $user = null;

    public static function tableName(): string 
    {
        return 'operacao';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'usu_id', 
            'for_id', 
            'loading_password', 
            'ga_password', 
            'order_number', 
            'invoice_number', 
            'plate', 
            'has_palletization', 
            'has_rework', 
            'has_storage', 
            'has_import', 
            'has_tr'
        ];
    }

    public function rules(): array 
    {
        return [
            'usu_id' => [
                [self::RULE_REQUIRED, 'message' => _('O usuário é obrigatório!')]
            ],
            'for_id' => [
                [self::RULE_REQUIRED, 'message' => _('O fornecedor é obrigatório!')]
            ],
            'loading_password' => [
                [self::RULE_REQUIRED, 'message' => _('A senha de carregamento é obrigatória!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('A senha de carregamento deve conter no máximo %s caractéres!'), 20)]
            ],
            'ga_password' => [
                [self::RULE_REQUIRED, 'message' => _('A senha de G.A é obrigatória!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('A senha de G.A deve conter no máximo %s caractéres!'), 20)]
            ],
            'order_number' => [
                [self::RULE_REQUIRED, 'message' => _('O número do pedido/TR é obrigatório!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('O número do pedido/TR deve conter no máximo %s caractéres!'), 20)]
            ],
            'invoice_number' => [
                [self::RULE_REQUIRED, 'message' => _('O número da nota fiscal é obrigatório!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('O número da nota fiscal deve conter no máximo %s caractéres!'), 20)]
            ],
            'plate' => [
                [self::RULE_REQUIRED, 'message' => _('A placa é obrigatória!')],
                [self::RULE_MAX, 'max' => 20, 'message' => sprintf(_('A placa deve conter no máximo %s caractéres!'), 20)]
            ],
            self::RULE_RAW => [
                function ($model) {
                    if(!$model->has_palletization && !$model->has_rework && !$model->has_storage && !$model->has_import) {
                        $model->addError('service_types', _('Pelo menos um serviço precisa ser escolhido!'));
                    }

                    if(!$model->hasError('loading_password')) {
                        if((new self())->get(['loading_password' => $model->loading_password] 
                            + (isset($model->id) ? ['!=' => ['id' => $model->id]] : []))->count()) {
                            $model->addError('loading_password', _('A senha de carregamento informada já está em uso! Tente outra.'));
                        }
                    }

                    if(!$model->hasError('order_number')) {
                        if((new self())->get(['order_number' => $model->order_number] 
                            + (isset($model->id) ? ['!=' => ['id' => $model->id]] : []))->count()) {
                            $model->addError('order_number', _('O número do pedido/TR informado já está em uso! Tente outro.'));
                        }
                    }
                }
            ]
        ];
    }

    public function save(): bool 
    {
        $this->has_palletization = $this->has_palletization ? 1 : 0;
        $this->has_rework = $this->has_rework ? 1 : 0;
        $this->has_storage = $this->has_storage ? 1 : 0;
        $this->has_import = $this->has_import ? 1 : 0;
        $this->has_tr = $this->has_tr ? 1 : 0;
        return parent::save();
    }

    public function destroy(): bool 
    {
        if((new Conference())->get(['ope_id' => $this->id])->count()) {
            $this->addError('destroy', _('Você não pode excluir uma operação vinculada à uma conferência!'));
            return false;
        }
        return parent::destroy();
    }

    public function conference(string $columns = '*'): ?Conference 
    {
        $this->conference = $this->hasOne(Conference::class, 'ope_id', 'id', $columns)->fetch(false);
        return $this->conference;
    }

    public function provider(string $columns = '*'): ?Provider 
    {
        $this->provider = $this->belongsTo(Provider::class, 'for_id', 'id', $columns)->fetch(false);
        return $this->provider;
    }

    public function user(string $columns = '*'): ?User 
    {
        $this->user = $this->belongsTo(User::class, 'usu_id', 'id', $columns)->fetch(false);
        return $this->user;
    }

    public static function withConference(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withHasOne(
            $objects, 
            Conference::class, 
            'ope_id', 
            'conference', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withProvider(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            Provider::class, 
            'for_id', 
            'provider', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function withUser(array $objects, array $filters = [], string $columns = '*'): array
    {
        return self::withBelongsTo(
            $objects, 
            User::class, 
            'usu_id', 
            'user', 
            'id', 
            $filters, 
            $columns
        );
    }

    public static function getByProviderId(int $providerId, string $columns = '*'): ?array 
    {
        return (new self())->get(['for_id' => $providerId], $columns)->fetch(true);
    }

    public static function getByUserId(int $userId, string $columns = '*'): ?array 
    {
        return (new self())->get(['usu_id' => $userId], $columns)->fetch(true);
    }

    public static function getByServiceOrder(string $serviceOrder, string $columns = '*'): ?self
    {
        return (new self())->get(['order_number' => $serviceOrder], $columns)->fetch(false);
    }

    public static function getServiceTypes(): array 
    {
        return [
            self::ST_PALLETIZATION => _('Paletização'),
            self::ST_REWORK => _('Retrabalho'),
            self::ST_STORAGE => _('Armazenagem'),
            self::ST_IMPORTED => _('Importado')
        ];
    }

    public function getCreatedAtDateTime(): DateTime 
    {
        return new DateTime($this->created_at);
    }

    public function getUpdatedAtDateTime(): DateTime 
    {
        return new DateTime($this->updated_at);
    }

    public function hasPalletization(): bool 
    {
        return $this->has_palletization ? true : false;
    }

    public function hasRework(): bool 
    {
        return $this->has_rework ? true : false;
    }

    public function hasStorage(): bool 
    {
        return $this->has_storage ? true : false;
    }

    public function hasImport(): bool 
    {
        return $this->has_import ? true : false;
    }

    public function hasTR(): bool 
    {
        return $this->has_tr ? true : false;
    }
}