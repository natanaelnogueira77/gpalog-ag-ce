<?php

namespace Src\App\Controllers\Web;

use GTG\MVC\Controller;
use Src\Models\Config;
use Src\Data\MenuData;

class TemplateController extends Controller 
{
    public function addData(): void 
    {
        $configMetas = (new Config())->getGroupedMetas([
            Config::KEY_LOGO, 
            Config::KEY_LOGO_ICON, 
            Config::KEY_STYLE
        ]);

        $logo = url($configMetas[Config::KEY_LOGO] ?? '');
        $logoIcon = url($configMetas[Config::KEY_LOGO_ICON] ?? '');

        $user = $this->session->getAuth();
        $headerMenu = MenuData::getHeaderMenuItems($this->router, $user);

        $this->addViewData([
            'user' => $user,
            'logo' => $logo,
            'shortcutIcon' => $logoIcon,
            'loadingText' => _('Aguarde, carregando...'),
            'preloader' => ['logo' => $logo],
            'header' => [
                'menu' => $headerMenu,
                'right' => [
                    'items' => MenuData::getRightMenuItems($this->router, $user),
                    'languages' => [
                        'heading' => _('Linguagens'),
                        'curr_img' => url("public/imgs/flags/{$this->session->getLanguage()[1]}.png"),
                        'items' => [
                            ['url' => $this->getRoute('language.index', ['lang' => 'pt']), 'desc' => _('Português')]
                        ]
                    ]
                ]
            ],
            'footer' => [
                'logo' => $logo,
                'socials' => [],
                'items' => $headerMenu
            ]
        ]);
    }
}