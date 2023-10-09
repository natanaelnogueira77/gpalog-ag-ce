<?php 

namespace Src\App\Middlewares;

use GTG\MVC\Middleware;

class OperatorMiddleware extends Middleware 
{
    public function handle($router): bool
    {
        $user = $this->session->getAuth();
        if(!$user || !$user->isOperator()) {
            $this->session->setFlash('error', _('Você precisa estar autenticado como operador para acessar essa área!'));
            $this->redirect('auth.index');
            return false;
        }

        return true;
    }
}