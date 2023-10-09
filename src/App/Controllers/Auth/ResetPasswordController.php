<?php

namespace Src\App\Controllers\Auth;

use GTG\MVC\Components\Email;
use GTG\MVC\Controller;
use Src\Models\Config;
use Src\Models\ForgotPasswordForm;
use Src\Models\ResetPasswordForm;
use Src\Models\User;
use Src\Models\UserMeta;
use Src\Utils\ErrorMessages;

class ResetPasswordController extends Controller 
{
    public function index(array $data): void 
    {
        $configMetas = (new Config())->getGroupedMetas([
            Config::KEY_LOGO, 
            Config::KEY_LOGO_ICON, 
            Config::KEY_LOGIN_IMG
        ]);
        $forgotPasswordForm = new ForgotPasswordForm();
        if($this->request->isPost()) {
            $forgotPasswordForm->loadData(['email' => $data['email']]);
            if($user = $forgotPasswordForm->user()) {
                $user->saveMeta(UserMeta::KEY_LAST_PASS_REQUEST, date('Y-m-d H:i:s'));

                $email = new Email();
                $email->add(_('Redefinir Senha'), $this->getView('emails/reset-password', [
                    'user' => $user,
                    'logo' => url((new Config())->getMeta(Config::KEY_LOGO))
                ]), $user->name, $user->email);
                
                if(!$email->send()) {
                    $this->session->setFlash('error', $email->error()->getMessage());
                }

                $this->session->setFlash('success', 
                    sprintf(_("Um email foi enviado para %s. Verifique para poder redefinir sua senha."), $user->email)
                );
                $this->redirect('auth.index');
            } else {
                $this->session->setFlash('error', ErrorMessages::form());
            }
        }

        $this->render('auth/reset-password', [
            'background' => $configMetas && $configMetas[Config::KEY_LOGIN_IMG] ? url($configMetas[Config::KEY_LOGIN_IMG]) : '',
            'logo' => $configMetas && $configMetas[Config::KEY_LOGO] ? url($configMetas[Config::KEY_LOGO]) : '',
            'shortcutIcon' => $configMetas && $configMetas[Config::KEY_LOGO_ICON] ? url($configMetas[Config::KEY_LOGO_ICON]) : '',
            'forgotPasswordForm' => $forgotPasswordForm
        ]);
    }

    public function verify(array $data): void 
    {
        $configMetas = (new Config())->getGroupedMetas([
            Config::KEY_LOGO, 
            Config::KEY_LOGO_ICON, 
            Config::KEY_LOGIN_IMG
        ]);
        
        if(!$user = User::getByToken($data['code'])) {
            $this->session->setFlash('error', _('A chave de verificação é inválida!'));
            $this->redirect('auth.index');
        }
        
        $resetPasswordForm = new ResetPasswordForm();
        if($this->request->isPost()) {
            $resetPasswordForm->loadData([
                'password' => $data['password'],
                'password_confirm' => $data['password_confirm']
            ]);
            if($resetPasswordForm->validate()) {
                $user->password = $this->password;
                $user->save();

                $this->session->setAuth($user);
                $this->session->setFlash('success', _('A senha foi redefinida com sucesso!'));
                $this->redirect('auth.index');
            } else {
                $this->session->setFlash('error', ErrorMessages::form());
            }
        }

        $this->render('auth/reset-password', [
            'code' => $data['code'],
            'background' => $configMetas && $configMetas[Config::KEY_LOGIN_IMG] ? url($configMetas[Config::KEY_LOGIN_IMG]) : '',
            'logo' => $configMetas && $configMetas[Config::KEY_LOGO] ? url($configMetas[Config::KEY_LOGO]) : '',
            'shortcutIcon' => $configMetas && $configMetas[Config::KEY_LOGO_ICON] ? url($configMetas[Config::KEY_LOGO_ICON]) : '',
            'resetPasswordForm' => $resetPasswordForm
        ]);
    }
}