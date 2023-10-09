<?php

namespace Src\App\Controllers\Web;

use Src\App\Controllers\Web\TemplateController;
use Src\Models\ContactForm;
use Src\Utils\ErrorMessages;

class ContactController extends TemplateController 
{
    public function index(array $data): void 
    {
        $this->addData();

        $contactForm = new ContactForm();
        if($this->request->isPost()) {
            $contactForm->loadData([
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'],
                'body' => $data['body']
            ]);
            if($contactForm->send()) {
                $this->session->setFlash('success', _('Sua mensagem foi enviada com sucesso!'));
                $this->redirect('contact.index');
            } else {
                $this->session->setFlash('error', ErrorMessages::form());
            }
        }
        
        $this->render('web/contact', [
            'contactForm' => $contactForm
        ]);
    }
}