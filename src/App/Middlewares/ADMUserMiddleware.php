<?php 

namespace Src\App\Middlewares;

use GTG\MVC\Middleware;

class ADMUserMiddleware extends Middleware 
{
    public function handle($router): bool
    {
        $user = $this->session->getAuth();
        if(!$user || (!$user->isAdmin() && !$user->isADMUser())) {
            $this->session->setFlash('error', _('Você precisa estar autenticado como ADM para acessar essa área!'));
            $this->redirect('auth.index');
            return false;
        }

        return true;
    }
}