<?php

namespace Src\App\Controllers\User;

use GTG\MVC\Components\ExcelGenerator;
use Src\App\Controllers\User\TemplateController;
use Src\Models\Provider;
use Src\Utils\ErrorMessages;

class ProvidersController extends TemplateController 
{
    public function index(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));
        $this->addData();
        $this->render('user/providers/index', [
            'importErrors' => $data['import_errors']
        ]);
    }

    public function show(array $data): void 
    {
        if(!$dbProvider = (new Provider())->findById(intval($data['provider_id']))) {
            $this->setMessage('error', _('Nenhum fornecedor foi encontrado!'))->APIResponse([], 404);
            return;
        }

        $this->APIResponse([
            'content' => $dbProvider->getData(),
            'save' => [
                'action' => $this->getRoute('user.providers.update', ['provider_id' => $dbProvider->id]),
                'method' => 'put'
            ]
        ], 200);
    }

    public function store(array $data): void 
    {
        $dbProvider = new Provider();
        if(!$dbProvider->loadData(['usu_id' => $this->session->getAuth()->id] + $data)->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbProvider->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', sprintf(_('O fornecedor "%s" foi cadastrado com sucesso!'), $dbProvider->name));
        $this->APIResponse(['content' => $dbProvider->getData()], 200);
    }

    public function update(array $data): void 
    {
        if(!$dbProvider = (new Provider())->findById(intval($data['provider_id']))) {
            $this->setMessage('error', _('Nenhum fornecedor foi encontrado!'))->APIResponse([], 422);
            return;
        }

        if(!$dbProvider->loadData($data)->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbProvider->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', sprintf(_('Os dados do fornecedor "%s" foram alterados com sucesso!'), $dbProvider->name))
            ->APIResponse([], 200);
    }

    public function delete(array $data): void 
    {
        if(!$dbProvider = (new Provider())->findById(intval($data['provider_id']))) {
            $this->setMessage('error', _('Nenhum fornecedor foi encontrado!'))->APIResponse([], 404);
            return;
        } elseif(!$dbProvider->destroy()) {
            $this->setMessage('error', _('Não foi possível excluir o fornecedor!'))->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', sprintf(_('O fornecedor "%s" foi excluído com sucesso.'), $dbProvider->name))
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

        $providers = (new Provider())->get($filters)->paginate($limit, $page)->sort([$order => $orderType]);
        $count = $providers->count();
        $pages = ceil($count / $limit);
        
        if($objects = $providers->fetch(true)) {
            foreach($objects as $provider) {
                $params = ['provider_id' => $provider->id];
                $content[] = [
                    'name' => $provider->name,
                    'actions' => "
                        <div class=\"dropup d-inline-block\">
                            <button type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\" 
                                data-toggle=\"dropdown\" class=\"dropdown-toggle btn btn-sm btn-primary\">
                                " . _('Ações') . "
                            </button>
                            <div tabindex=\"-1\" role=\"menu\" aria-hidden=\"true\" class=\"dropdown-menu\">
                                <h6 tabindex=\"-1\" class=\"dropdown-header\">" . _('Ações') . "</h6>
                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"edit\" data-method=\"get\" data-provider-name=\"{$provider->name}\" 
                                    data-action=\"{$this->getRoute('user.providers.show', $params)}\">
                                    " . _('Editar Fornecedor') . "
                                </button>

                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"delete\" data-method=\"delete\" 
                                    data-action=\"{$this->getRoute('user.providers.delete', $params)}\">
                                    " . _('Excluir Fornecedor') . "
                                </button>
                            </div>
                        </div>
                    "
                ];
            }
        }

        $this->APIResponse([
            'content' => [
                'table' => $this->getView('_components/data-table', [
                    'headers' => [
                        'actions' => ['text' => _('Ações')],
                        'name' => ['text' => _('Nome'), 'sort' => true]
                    ],
                    'order' => [
                        'selected' => $order,
                        'type' => $orderType
                    ],
                    'data' => $content
                ]),
                'pagination' => $this->getView('_components/pagination', [
                    'pages' => $pages,
                    'currPage' => $page,
                    'results' => $count,
                    'limit' => $limit
                ])
            ]
        ], 200);
    }

    public function import(array $data): void 
    {
        ini_set('memory_limit', '32M');
        set_time_limit(300);

        $user = $this->session->getAuth();
        $excelTypes = [
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/octet-stream',
            'application/vnd.ms-excel',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
            'application/excel',
            'application/vnd.msexcel',
            'text/plain'
        ];

        if(empty($_FILES['csv']['name'])) {
            $this->session->setFlash('error', _('Nenhum arquivo foi selecionado!'));
            $this->redirect('user.providers.index');
        } elseif(!in_array($_FILES['csv']['type'], $excelTypes)) {
            $this->session->setFlash('error', _('O arquivo precisa ser um excel CSV!'));
            $this->redirect('user.providers.index');
        }
        
        if($file = $_FILES['csv']['tmp_name']) {
            $handle = fopen($file, 'r');
            while(($csv = fgetcsv($handle, 1000, ';')) !== false) {
                $rows[] = $csv;
            }
            fclose($handle);

            if($rows) {
                $dbProviders = [];
                foreach($rows as $index => $row) {
                    $dbProviders[] = (new Provider())->loadData([
                        'usu_id' => $user->id,
                        'name' => $row[0]
                    ]);
                }

                if(!$objects = Provider::insertMany($dbProviders)) {
                    $this->session->setFlash('error', ErrorMessages::requisition());
                    $this->redirect('user.providers.index');
                } elseif($errors = Provider::getErrorsFromMany($objects)) {
                    $this->session->setFlash('error', ErrorMessages::csvImport());
                    $this->redirect('user.providers.index', ['import_errors' => $errors]);
                }

                $this->session->setFlash(
                    'success', sprintf(_('Todos os %s registros foram importados com sucesso!'), $rows ? count($rows) : 0)
                );
                $this->redirect('user.providers.index');
            }
        }
    }

    public function export(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));

        $excelData = [];

        if($dbProviders = (new Provider())->get()->fetch(true)) {
            foreach($dbProviders as $dbProvider) {
                $excelData[] = [
                    _('Nome') => $dbProvider->name
                ];
            }
        }

        $excel = (new ExcelGenerator($excelData, _('Fornecedores')));
        if(!$excel->render()) {
            $this->session->setFlash('error', ErrorMessages::excel());
            $this->redirect('user.providers.index');
        }

        $excel->stream();
    }
}