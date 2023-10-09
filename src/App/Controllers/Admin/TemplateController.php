<?php

namespace Src\App\Controllers\Admin;

use GTG\MVC\Controller;
use Src\Data\MenuData;
use Src\Models\Config;

class TemplateController extends Controller 
{
    public function addData(): void 
    {
        $configMetas = (new Config())->getGroupedMetas([
            Config::KEY_LOGO, 
            Config::KEY_LOGO_ICON, 
            Config::KEY_STYLE
        ]);

        $user = $this->session->getAuth();
        $user->userType();

        $bgColors = [
            'left' => [
                'light' => 'bg-heavy-rain sidebar-text-dark',
                'dark' => 'bg-slick-carbon sidebar-text-light'
            ],
            'header' => [
                'light' => 'bg-heavy-rain header-text-dark',
                'dark' => 'bg-slick-carbon header-text-light'
            ]
        ];

        $this->view->addData([
            'user' => $user,
            'storeAt' => 'public/storage/users/user' . $user->id,
            'logo' => $configMetas && $configMetas[Config::KEY_LOGO] ? url($configMetas[Config::KEY_LOGO]) : '',
            'shortcutIcon' => $configMetas && $configMetas[Config::KEY_LOGO_ICON] ? url($configMetas[Config::KEY_LOGO_ICON]) : '',
            'loadingText' => _('Aguarde, carregando...'),
            'fullBackground' => url('public/imgs/user-page/banner.jpeg'),
            'noLeft' => false,
            'noFooter' => true,
            'left' => [
                'color' => $configMetas[Config::KEY_STYLE] ? $bgColors['left'][$configMetas[Config::KEY_STYLE]] : null,
                'menu' => MenuData::getLeftMenuItems($this->router, $user),
                'active' => url() . filter_input(INPUT_GET, 'route', FILTER_DEFAULT)
            ],
            'header' => [
                'left' => true,
                'color' => $configMetas[Config::KEY_STYLE] ? $bgColors['header'][$configMetas[Config::KEY_STYLE]] : null,
                'menu' => MenuData::getHeaderMenuItems($this->router, $user),
                'right' => [
                    'show' => true,
                    'languages' => [
                        'heading' => _('Linguagens'),
                        'curr_img' => url("public/imgs/flags/{$this->session->getLanguage()[1]}.png"),
                        'items' => [
                            ['url' => $this->getRoute('language.index', ['lang' => 'pt']), 'desc' => _('Português')]
                        ]
                    ],
                    'items' => MenuData::getRightMenuItems($this->router, $user),
                    'avatar' => 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))),
                    'avatar_title' => $user->name,
                    'avatar_subtitle' => $user->userType->name_sing
                ]
            ]
        ]);
    }
}