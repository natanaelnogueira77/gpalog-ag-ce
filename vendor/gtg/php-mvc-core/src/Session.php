<?php 

namespace GTG\MVC;

class Session 
{
    protected string $flashKey;
    protected string $authKey;
    protected string $langKey;

    public function __construct(array $config) 
    {
        session_start();
        
        $this->authKey = $config['auth_key'];
        $this->langKey = $config['lang_key'];
        $this->flashKey = $config['flash_key'];
        $flashMessages = $_SESSION[$this->flashKey] ?? [];
        foreach($flashMessages as $key => &$flashMessage) {
            $flashMessage['remove'] = true;
        }

        $_SESSION[$this->flashKey] = $flashMessages;
    }

    public function setFlash(string $key, string $message): void 
    {
        $_SESSION[$this->flashKey][$key] = [
            'remove' => false,
            'value' => $message
        ];
    }

    public function getFlash(string $key): string|false
    {
        return $_SESSION[$this->flashKey][$key]['value'] ?? false;
    }

    public function setAuth($user): void 
    {
        $_SESSION[$this->authKey] = $user;
    }

    public function getAuth(): object|false
    {
        return $_SESSION[$this->authKey] ?? false;
    }

    public function removeAuth(): void
    {
        unset($_SESSION[$this->authKey]);
    }

    public function setLanguage(array $languageInfo): void 
    {
        $_SESSION[$this->langKey] = $languageInfo;
    }

    public function getLanguage(): ?array
    {
        if(!$_SESSION[$this->langKey]) {
            $this->setLanguage(['pt_BR.utf-8', 'pt_BR', 'Portuguese_Brazil']);
        }
        return $_SESSION[$this->langKey] ?? null;
    }

    public function set(string $key, mixed $value): void 
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key): mixed 
    {
        return $_SESSION[$key] ?? false;
    }

    public function remove(string $key): void 
    {
        unset($_SESSION[$key]);
    }

    public function __destruct() 
    {
        $flashMessages = $_SESSION[$this->flashKey] ?? [];
        foreach($flashMessages as $key => &$flashMessage) {
            if($flashMessage['remove']) {
                unset($flashMessages[$key]);
            }
        }

        $_SESSION[$this->flashKey] = $flashMessages;
    }
}