<?php

namespace Src\App\Controllers\Admin;

use GTG\MVC\Components\Email;
use Src\App\Controllers\Admin\TemplateController;
use Src\Models\Config;
use Src\Models\User;
use Src\Models\UserForm;
use Src\Models\UserMeta;
use Src\Models\UserType;
use Src\Utils\ErrorMessages;

class UsersController extends TemplateController 
{
    public function index(array $data): void 
    {
        $this->addData();
        $this->render('admin/users/index', [
            'userTypes' => (new UserType())->get()->fetch(true)
        ]);
    }

    public function create(array $data): void 
    {
        $this->addData();
        $this->render('admin/users/save', [
            'userTypes' => (new UserType())->get()->fetch(true)
        ]);
    }

    public function store(array $data): void 
    {
        $userForm = (new UserForm())->loadData([
            'utip_id' => intval($data['utip_id']),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'password_confirm' => $data['password_confirm'],
            'registration_number' => $data['registration_number']
        ]);
        if(!$userForm->validate()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($userForm->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $dbUser = new User();
        if(!$dbUser->loadData($data)->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbUser->getFirstErrors())->APIResponse([], 422);
            return;
        }

        if($dbUser->isOperator()) {
            $dbUser->saveMeta(UserMeta::KEY_REGISTRATION_NUMBER, $data[UserMeta::KEY_REGISTRATION_NUMBER]);
        }

        $email = new Email();
        $email->add(
            _('Você se Registrou com Sucesso!'), 
            $this->getView('emails/user-register', [
                'user' => $dbUser,
                'password' => $data['password'],
                'logo' => url((new Config())->getMeta(Config::KEY_LOGO))
            ]), 
            $dbUser->name, 
            $dbUser->email
        );

        if(!$email->send()) {
            $this->session->setFlash('success', 
                sprintf(
                    _('O usuário "%s" foi cadastrado com sucesso! Porém não foi possível enviar uma notificação no email dele.'), 
                    $dbUser->name
                )
            );
        } else {
            $this->session->setFlash('success', sprintf(_('O usuário "%s" foi criado com sucesso!'), $dbUser->name));
        }

        $this->APIResponse(['link' => $this->getRoute('admin.users.edit', ['user_id' => $dbUser->id])], 200);
    }

    public function edit(array $data): void 
    {
        $this->addData();

        if(!$dbUser = (new User())->findById(intval($data['user_id']))) {
            $this->session->setFlash('error', _('Nenhum usuário foi encontrado!'));
            $this->redirect('admin.users.index');
        }

        $this->render('admin/users/save', [
            'dbUser' => $dbUser,
            'userMetas' => $dbUser->getGroupedMetas([UserMeta::KEY_REGISTRATION_NUMBER]),
            'userTypes' => (new UserType())->get()->fetch(true)
        ]);
    }

    public function update(array $data): void 
    {
        if(!$dbUser = (new User())->findById(intval($data['user_id']))) {
            $this->setMessage('error', _('Nenhum usuário foi encontrado!'))->APIResponse([], 422);
            return;
        }

        $userForm = (new UserForm())->loadData([
            'id' => $dbUser->id,
            'utip_id' => intval($data['utip_id']),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'password_confirm' => $data['password_confirm'],
            'update_password' => $data['update_password'] ? true : false,
            'registration_number' => $data['registration_number']
        ]);
        if(!$userForm->validate()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($userForm->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $dbUser->loadData([
            'utip_id' => $data['utip_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['update_password'] ? $data['password'] : $dbUser->password,
            'slug' => $data['slug']
        ]);

        if(!$dbUser->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbUser->getFirstErrors())->APIResponse([], 422);
            return;
        }

        if($dbUser->isOperator()) {
            $dbUser->saveMeta(UserMeta::KEY_REGISTRATION_NUMBER, $data[UserMeta::KEY_REGISTRATION_NUMBER]);
        }

        $this->setMessage('success', sprintf(_('Os dados do usuário "%s" foram alterados com sucesso!'), $dbUser->name))
            ->APIResponse([], 200);
    }

    public function list(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));

        $content = [];
        $filters = [];

        $limit = $data['limit'] ? intval($data['limit']) : 10;
        $page = $data['page'] ? intval($data['page']) : 1;
        $order = $data['order'] ? $data['order'] : 'id';
        $orderType = $data['orderType'] ? $data['orderType'] : 'ASC';

        if($data['search']) {
            $filters['search'] = [
                'term' => $data['search'],
                'columns' => ['name']
            ];
        }

        if($data['user_type']) {
            $filters['utip_id'] = $data['user_type'];
        }

        $users = (new User())->get($filters)->paginate($limit, $page)->sort([$order => $orderType]);
        $count = $users->count();
        $pages = ceil($count / $limit);
        
        if($objects = $users->fetch(true)) {
            User::withUserType($objects);
            foreach($objects as $user) {
                $params = ['user_id' => $user->id];
                $content[] = [
                    'id' => '#' . $user->id,
                    'name' => "
                        <div class=\"widget-content p-0\">
                            <div class=\"widget-content-wrapper\">
                                <div class=\"widget-content-left mr-3\">
                                    <div class=\"widget-content-left\">
                                        <img width=\"40\" class=\"rounded-circle\" 
                                            src=\"https://www.gravatar.com/avatar/"
                                            . md5(strtolower(trim($user->email))) . "\">
                                    </div>
                                </div>
                                <div class=\"widget-content-left\">
                                    <div class=\"widget-heading\">{$user->name}</div>
                                    <div class=\"widget-subheading opacity-7\">
                                        {$user->userType->name_sing}
                                    </div>
                                </div>
                            </div>
                        </div>
                    ",
                    'email' => $user->email,
                    'created_at' => $user->getCreatedAtDateTime()->format('d/m/Y'),
                    'actions' => "
                        <div class=\"dropup d-inline-block\">
                            <button type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\" 
                                data-toggle=\"dropdown\" class=\"dropdown-toggle btn btn-sm btn-primary\">
                                " . _('Ações') . "
                            </button>
                            <div tabindex=\"-1\" role=\"menu\" aria-hidden=\"true\" class=\"dropdown-menu\">
                                <h6 tabindex=\"-1\" class=\"dropdown-header\">" . _('Ações') . "</h6>
                                <a href=\"{$this->getRoute('admin.users.edit', $params)}\" 
                                    type=\"button\" tabindex=\"0\" class=\"dropdown-item\">
                                    " . _('Editar Usuário') . "
                                </a>

                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"delete\" data-method=\"delete\" 
                                    data-action=\"{$this->getRoute('admin.users.delete', $params)}\">
                                    " . _('Excluir Usuário') . "
                                </button>
                            </div>
                        </div>
                    "
                ];
            }
        }

        $this->APIResponse([
            'content' => [
                'table' => $this->getView('components/data-table', [
                    'headers' => [
                        'actions' => ['text' => _('Ações')],
                        'id' => ['text' => _('ID'), 'sort' => true],
                        'name' => ['text' => _('Nome'), 'sort' => true],
                        'email' => ['text' => _('Email'), 'sort' => true],
                        'created_at' => ['text' => _('Criado em'), 'sort' => true]
                    ],
                    'order' => [
                        'selected' => $order,
                        'type' => $orderType
                    ],
                    'data' => $content
                ]),
                'pagination' => $this->getView('components/pagination', [
                    'pages' => $pages,
                    'currPage' => $page,
                    'results' => $count,
                    'limit' => $limit
                ])
            ]
        ], 200);
    }

    public function delete(array $data): void 
    {
        if(!$dbUser = (new User())->findById(intval($data['user_id']))) {
            $this->setMessage('error', _('Nenhum usuário foi encontrado!'))->APIResponse([], 404);
            return;
        } elseif(!$dbUser->destroy()) {
            $this->setMessage('error', _('Não foi possível excluir o usuário!'))->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', sprintf(_('O usuário "%s" foi excluído com sucesso.'), $dbUser->name))
            ->APIResponse([], 200);
    }
}