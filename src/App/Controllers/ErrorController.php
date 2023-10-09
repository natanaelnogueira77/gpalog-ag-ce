<?php

namespace Src\App\Controllers;

use GTG\MVC\Controller;

class ErrorController extends Controller 
{
    public function index(array $data): void 
    {
        $this->render('error/index', [
            'code' => $data['code'],
            'message' => _('Lamentamos, mas ocorreu um erro inesperado. Clique abaixo para voltar.'),
            'exception' => $exception
        ]);
    }
}