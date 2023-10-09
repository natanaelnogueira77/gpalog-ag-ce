<?php

namespace Src\App\Controllers;

use GTG\MVC\Controller;

class LanguageController extends Controller 
{
    public function index(array $data): void 
    {
        if($data['lang'] == 'pt') {
            $this->session->setLanguage(['pt_BR.utf-8', 'pt_BR', 'Portuguese_Brazil']);
        } elseif($data['lang'] == 'es') {
            $this->session->setLanguage(['es_ES.utf-8', 'es_ES', 'Spanish_Spain']);
        } elseif($data['lang'] == 'en') {
            $this->session->setLanguage(['en_US.utf-8', 'en_US', 'English_US']);
        }

        $previous = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        if($previous) {
            header("Location: {$previous}");
            exit;
        } else {
            $this->redirect('home.index');
        }
    }
}