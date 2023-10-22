<?php 

namespace Src\Data;

use GTG\MVC\Router;
use Src\Components\MenuItem;
use Src\Models\User;

class MenuData 
{
    public static function getHeaderMenuItems(Router $router, ?User $user = null, ?array $data = null): array 
    {
        return [
            (new MenuItem())
                ->setType(MenuItem::T_ITEM)
                ->setIcon('nav-link-icon fa fa-home')
                ->setURL($user && $user->isAdmin() ? $router->route('admin.index') : $router->route('user.index'))
                ->setText(_('Início'))
        ];
    }

    public static function getLeftMenuItems(Router $router, ?User $user = null, ?array $data = null): array 
    {
        return array_merge($user->isAdmin() ? [
            (new MenuItem())
                ->setType(MenuItem::T_HEADING)
                ->setText(_('Painéis')),
            (new MenuItem())
                ->setType(MenuItem::T_ITEM)
                ->setIcon('metismenu-icon pe-7s-display2')
                ->setURL($user && $user->isAdmin() ? $router->route('admin.index') : $router->route('user.index'))
                ->setText(_('Painel Principal')),
            (new MenuItem())
                ->setType(MenuItem::T_HEADING)
                ->setText(_('Usuários')),
            (new MenuItem())
                ->setType(MenuItem::T_ITEM)
                ->setIcon('metismenu-icon pe-7s-users')
                ->setURL($router->route('admin.users.index'))
                ->setText(_('Usuários')),
            (new MenuItem())
                ->setType(MenuItem::T_ITEM)
                ->setIcon('metismenu-icon pe-7s-user')
                ->setURL($router->route('admin.users.create'))
                ->setText(_('Cadastrar Usuário'))
        ] : [], 
        [
            (new MenuItem())
                ->setType(MenuItem::T_HEADING)
                ->setText(_('GPA Log')),
            (new MenuItem())
                ->setType(MenuItem::T_ITEM)
                ->setIcon('metismenu-icon pe-7s-server')
                ->setURL($router->route('user.operations.index'))
                ->setText(_('Operações')),
            (new MenuItem())
                ->setType(MenuItem::T_ITEM)
                ->setIcon('metismenu-icon pe-7s-box2')
                ->setURL($router->route('user.streets.index'))
                ->setText(_('Ruas')),
            (new MenuItem())
                ->setType(MenuItem::T_ITEM)
                ->setIcon('metismenu-icon pe-7s-box2')
                ->setURL($router->route('user.products.index'))
                ->setText(_('Produtos')),
            (new MenuItem())
                ->setType(MenuItem::T_ITEM)
                ->setIcon('metismenu-icon pe-7s-users')
                ->setURL($router->route('user.providers.index'))
                ->setText(_('Fornecedores')),
            (new MenuItem())
                ->setType(MenuItem::T_ITEM)
                ->setIcon('metismenu-icon pe-7s-server')
                ->setURL($router->route('user.storage.index'))
                ->setText(_('Armazenagem')),
            (new MenuItem())
                ->setType(MenuItem::T_ITEM)
                ->setIcon('metismenu-icon pe-7s-next-2')
                ->setURL($router->route('user.separations.index'))
                ->setText(_('Separação')),
            (new MenuItem())
                ->setType(MenuItem::T_ITEM)
                ->setIcon('metismenu-icon pe-7s-date')
                ->setURL($router->route('user.inputOutputHistory.index'))
                ->setText(_('Histórico de Entrada e Saída'))
        ]);
    }

    public static function getRightMenuItems(Router $router, ?User $user = null, ?array $data = null): array 
    {
        return array_merge(
            $user ? [
                (new MenuItem())
                    ->setURL($user && $user->isAdmin() ? $router->route('admin.index') : $router->route('user.index'))
                    ->setText(_('Painel Principal')),
                (new MenuItem())
                    ->setURL($router->route('user.edit.index'))
                    ->setText(_('Editar meus Dados')),
                (new MenuItem())
                    ->setURL($router->route('auth.index'))
                    ->setText(_('Voltar ao Início')),
                (new MenuItem())
                    ->setURL($router->route('auth.logout'))
                    ->setText(_('Sair'))
            ] : [
            (new MenuItem())
                ->setURL($router->route('auth.index'))
                ->setText(_('Entrar'))
            ]
        );
    }
}