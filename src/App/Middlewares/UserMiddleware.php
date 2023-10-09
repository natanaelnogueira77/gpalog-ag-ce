<?php 

namespace Src\App\Middlewares;

use GTG\MVC\Middleware;

class UserMiddleware extends Middleware 
{
    public function handle($router): bool
    {
        if(!$this->session->getAuth()) {
            $this->session->setFlash('error', _('Você precisa estar autenticado para acessar essa área!'));
            $this->redirect('auth.index');
            return false;
        }

        return true;
    }
}