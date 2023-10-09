<?php

namespace Src\App\Controllers\User;

use Src\App\Controllers\User\TemplateController;
use Src\Models\User;
use Src\Models\UserForm;
use Src\Utils\ErrorMessages;

class EditController extends TemplateController 
{
    public function index(array $data): void 
    {
        $this->addData();
        $this->render('user/edit', ['user' => $this->session->getAuth()]);
    }

    public function update(array $data): void 
    {
        $user = $this->session->getAuth();
        $userForm = (new UserForm())->loadData([
            'id' => $user->id,
            'utip_id' => intval($user->utip_id),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'password_confirm' => $data['password_confirm'],
            'update_password' => $data['update_password'] ? true : false
        ]);
        if(!$userForm->validate()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($userForm->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $dbUser = $user;
        $dbUser->loadData([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['update_password'] ? $data['password'] : $user->password,
            'slug' => $data['slug']
        ]);

        if(!$dbUser->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbUser->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $this->session->setAuth($dbUser);
        $this->setMessage('success', _('Seus dados foram atualizados com sucesso!'))->APIResponse([], 200);
        return;
    }
}