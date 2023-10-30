<?php

namespace Src\App\Controllers\Auth;

use GTG\MVC\Controller;
use Src\Components\Theme;
use Src\Models\Config;
use Src\Models\OperatorLoginForm;
use Src\Models\User;

class OperatorLoginController extends Controller 
{
    public function index(array $data): void 
    {
        $configMetas = (new Config())->getGroupedMetas([
            Config::KEY_LOGO, 
            Config::KEY_LOGO_ICON, 
            Config::KEY_LOGIN_IMG
        ]);

        $operatorLoginForm = new OperatorLoginForm();
        if($this->request->isPost()) {
            $operatorLoginForm->loadData([
                'registration_number' => $data['registration_number'],
                'password' => $data['password']
            ]);
            if($user = $operatorLoginForm->login()) {
                $this->session->setAuth($user);
                $this->session->setFlash('success', sprintf(_("Seja bem-vindo(a), %s!"), $user->name));
                if(isset($data['redirect'])) {
                    $this->response->redirect(url($data['redirect']));
                } else {
                    $this->redirect('user.conference.index');
                }
            } else {
                $this->session->setFlash('error', _('Usuário e/ou senha inválidos!'));
            }
        }

        $this->render('auth/operator-login', [
            'theme' => (new Theme())->loadData([
                'title' => sprintf(_('Entrar - Operação | %s'), $this->appData['app_name']),
                'logo' => $configMetas && $configMetas[Config::KEY_LOGO] ? url($configMetas[Config::KEY_LOGO]) : null,
                'logo_icon' => $configMetas && $configMetas[Config::KEY_LOGO_ICON] ? url($configMetas[Config::KEY_LOGO_ICON]) : null,
            ]),
            'redirect' => $_GET['redirect'],
            'operatorLoginForm' => $operatorLoginForm
        ]);
    }

    public function logout(array $data): void 
    {
        $this->session->removeAuth();
        $this->redirect('operatorLogin.index');
    }
}