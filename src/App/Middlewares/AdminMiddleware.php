<?php 

namespace Src\App\Middlewares;

use GTG\MVC\Middleware;

class AdminMiddleware extends Middleware 
{
    public function handle($router): bool
    {
        $user = $this->session->getAuth();
        if(!$user || !$user->isAdmin()) {
            $this->session->setFlash('error', _('Você precisa estar autenticado como administrador para acessar essa área!'));
            $this->redirect('auth.index');
            return false;
        }

        return true;
    }
}