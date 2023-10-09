<?php

namespace Src\App\Controllers\User;

use Src\App\Controllers\User\TemplateController;

class UserController extends TemplateController 
{
    public function index(array $data): void 
    {
        $user = $this->session->getAuth();
        if($user->isAdmin()) {
            $this->redirect('admin.index');
        } elseif($user->isADMUser()) {
            $this->redirect('user.operations.index');
        } elseif($user->isOperator()) {
            $this->redirect('user.conference.index');
        }
    }
}