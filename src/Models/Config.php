<?php

namespace Src\Models;

use GTG\MVC\DB\DBModel;

class Config extends DBModel 
{
    const KEY_LOGIN_IMG = 'login_img';
    const KEY_LOGO = 'logo';
    const KEY_LOGO_ICON = 'logo_icon';
    const KEY_STYLE = 'style';

    public static function tableName(): string 
    {
        return 'config';
    }

    public static function primaryKey(): string 
    {
        return 'id';
    }

    public static function attributes(): array 
    {
        return [
            'meta', 
            'value'
        ];
    }

    public static function metaTableData(): ?array 
    {
        return [
            'class' => self::class,
            'meta' => 'meta',
            'value' => 'value'
        ];
    }

    public function rules(): array 
    {
        return [
            'meta' => [
                [self::RULE_REQUIRED, 'message' => _('O metadado é obrigatório!')],
                [self::RULE_MAX, 'max' => 50, 'message' => sprintf(_('O metadado deve conter no máximo %s caractéres!'), 50)]
            ],
            self::RULE_RAW => [
                function ($model) {
                    if(!$model->hasError('meta')) {
                        if($model->meta == self::KEY_LOGIN_IMG) {
                            if(!$model->value) {
                                $model->addError(self::KEY_LOGIN_IMG, _('A imagem de fundo do login é obrigatória!'));
                            } elseif(!in_array(pathinfo($model->value, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])) {
                                $model->addError(self::KEY_LOGIN_IMG, _('A imagem de fundo não é uma imagem válida!'));
                            }
                        } elseif($model->meta == self::KEY_LOGO) {
                            if(!$model->value) {
                                $model->addError(self::KEY_LOGO, _('O logo é obrigatório!'));
                            } elseif(!in_array(pathinfo($model->value, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])) {
                                $model->addError(self::KEY_LOGO, _('O logo não é uma imagem válida!'));
                            }
                        } elseif($model->meta == self::KEY_LOGO_ICON) {
                            if(!$model->value) {
                                $model->addError(self::KEY_LOGO_ICON, _('O ícone é obrigatório!'));
                            } elseif(!in_array(pathinfo($model->value, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])) {
                                $model->addError(self::KEY_LOGO_ICON, _('O ícone não é uma imagem válida!'));
                            }
                        } elseif($model->meta == self::KEY_STYLE) {
                            if(!$model->value) {
                                $model->addError(self::KEY_STYLE, _('O tema é obrigatório!'));
                            } elseif(!in_array($model->value, ['light', 'dark'])) {
                                $model->addError(self::KEY_STYLE, _('O tema é inválido!'));
                            }
                        }
                    }
                }
            ]
        ];
    }
}