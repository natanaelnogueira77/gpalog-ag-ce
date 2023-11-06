<?php

namespace Src\App\Controllers\User;

use GTG\MVC\Components\ExcelGenerator;
use Src\App\Controllers\User\TemplateController;
use Src\Models\Conference;
use Src\Models\Provider;
use Src\Models\Operation;
use Src\Utils\ErrorMessages;

class OperationsController extends TemplateController 
{
    public function index(array $data): void 
    {
        $this->addData();
        $this->render('user/operations/index', [
            'dbProviders' => (new Provider())->get()->fetch(true),
            'serviceTypes' => Operation::getServiceTypes()
        ]);
    }

    public function show(array $data): void 
    {
        if(!$dbOperation = (new Operation())->findById(intval($data['operation_id']))) {
            $this->setMessage('error', _('Nenhuma operação foi encontrada!'))->APIResponse([], 404);
            return;
        }

        $this->APIResponse([
            'content' => $dbOperation->getData(),
            'save' => [
                'action' => $this->getRoute('user.operations.update', ['operation_id' => $dbOperation->id]),
                'method' => 'put'
            ]
        ], 200);
    }

    public function store(array $data): void 
    {
        $dbOperation = (new Operation())->loadData([
            'usu_id' => $this->session->getAuth()->id
        ] + $data);

        if(!$dbOperation->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors(
                $dbOperation->getFirstErrors()
            )->APIResponse([], 422);
            return;
        }

        $this->setMessage(
            'success', 
            sprintf(_('A operação de ID %s foi criada com sucesso!'), $dbOperation->id)
        )->APIResponse([
            'content' => $dbOperation->getData()
        ], 200);
    }

    public function update(array $data): void 
    {
        if(!$dbOperation = (new Operation())->findById(intval($data['operation_id']))) {
            $this->setMessage('error', _('Nenhuma operação foi encontrada!'))->APIResponse([], 422);
            return;
        }

        if(!$dbOperation->loadData($data)->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors(
                $dbOperation->getFirstErrors()
            )->APIResponse([], 422);
            return;
        }

        $this->setMessage(
            'success', 
            sprintf(_('Os dados da operação de ID %s foram alterados com sucesso!'), $dbOperation->id)
        )->APIResponse([], 200);
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
                'columns' => ['loading_password', 'invoice_number', 'plate']
            ];
        }

        $operations = (new Operation())->get($filters)->paginate($limit, $page)->sort([$order => $orderType]);
        $count = $operations->count();
        $pages = ceil($count / $limit);

        if($objects = $operations->fetch(true)) {
            $objects = Operation::withConference($objects);
            $objects = Operation::withProvider($objects);
            foreach($objects as $operation) {
                $params = ['operation_id' => $operation->id];
                $content[] = [
                    'plate' => $operation->plate,
                    'for_id' => $operation->provider->name,
                    'loading_password' => $operation->loading_password,
                    'order_number' => $operation->order_number,
                    'has_conference' => $operation->conference 
                        ? "<div class=\"badge badge-success\">" . _('Sim') . "</div>"
                        : "<div class=\"badge badge-danger\">" . _('Não') . "</div>",
                    'actions' => "
                        <div class=\"dropup d-inline-block\">
                            <button type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\" 
                                data-toggle=\"dropdown\" class=\"dropdown-toggle btn btn-sm btn-primary\">
                                " . _('Ações') . "
                            </button>
                            <div tabindex=\"-1\" role=\"menu\" aria-hidden=\"true\" class=\"dropdown-menu\">
                                <h6 tabindex=\"-1\" class=\"dropdown-header\">" . _('Ações') . "</h6>
                                " . (
                                    $operation->conference 
                                    ? '' 
                                    : "
                                        <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                            data-act=\"create-conference\" data-method=\"post\" 
                                            data-action=\"{$this->getRoute('user.operations.createConference', $params)}\">
                                            " . _('Liberar Para Conferência') . "
                                        </button>
                                    "
                                ) . "
                                <div class=\"dropdown-divider\"></div>
                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"edit\" data-method=\"get\" data-operation-id=\"{$operation->id}\" 
                                    data-action=\"{$this->getRoute('user.operations.show', $params)}\">
                                    " . _('Editar Operação') . "
                                </button>

                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"delete\" data-method=\"delete\" 
                                    data-action=\"{$this->getRoute('user.operations.delete', $params)}\">
                                    " . _('Excluir Operação') . "
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
                        'plate' => ['text' => _('Placa'), 'sort' => true],
                        'for_id' => ['text' => _('Fornecedor'), 'sort' => true],
                        'loading_password' => ['text' => _('Senha de Carregamento'), 'sort' => true],
                        'order_number' => ['text' => _('Nº do Pedido'), 'sort' => true],
                        'has_conference' => ['text' => _('Liberado para Conferência?'), 'sort' => false]
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

    public function delete(array $data): void 
    {
        if(!$dbOperation = (new Operation())->findById(intval($data['operation_id']))) {
            $this->setMessage('error', _('Nenhuma operação foi encontrada!'))->APIResponse([], 404);
            return;
        } elseif(!$dbOperation->destroy()) {
            $this->setMessage('error', _('Não foi possível excluir a operação!'))->APIResponse([], 422);
            return;
        }

        $this->setMessage(
            'success', 
            sprintf(_('A operação de ID %s foi excluída com sucesso.'), $dbOperation->id)
        )->APIResponse([], 200);
    }

    public function createConference(array $data): void 
    {
        if(!$dbOperation = (new Operation())->findById(intval($data['operation_id']))) {
            $this->setMessage('error', _('Nenhuma operação foi encontrada!'))->APIResponse([], 404);
            return;
        } elseif($dbOperation->conference()) {
            $this->setMessage('error', _('A operação já foi liberada para conferência!'))->APIResponse([], 422);
            return;
        }

        $dbConference = (new Conference())->loadData([
            'ope_id' => $dbOperation->id,
            'adm_usu_id' => $this->session->getAuth()->id
        ]);

        if(!$dbConference->setAsWaiting()->save()) {
            $this->setMessage('error', ErrorMessages::requisition())->APIResponse([], 500);
            return;
        }

        $this->setMessage(
            'success', 
            sprintf(
                _('A operação de ID %s foi liberada para conferência com sucesso. O ID gerado da conferência é %s.'), 
                $dbOperation->id, $dbConference->id
            )
        )->APIResponse([], 200);
    }

    public function export(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));

        $excelData = [];

        if($dbOperations = (new Operation())->get()->fetch(true)) {
            $dbOperations = Operation::withConference($dbOperations);
            $dbOperations = Operation::withProvider($dbOperations);
            $dbOperations = Operation::withUser($dbOperations);
            foreach($dbOperations as $dbOperation) {
                $excelData[] = [
                    _('Placa') => $dbOperation->plate,
                    _('ADM') => $dbOperation->user->name,
                    _('Fornecedor') => $dbOperation->provider->name,
                    _('Senha de Carregamento') => $dbOperation->loading_password,
                    _('Senha de G.A') => $dbOperation->ga_password,
                    _('Número do Pedido / TR / OC') => $dbOperation->order_number,
                    _('Nota Fiscal') => $dbOperation->invoice_number,
                    _('Possui TR?') => $dbOperation->hasTR() ? _('Sim') : _('Não'),
                    _('Paletização?') => $dbOperation->hasPalletization() ? _('Sim') : _('Não'),
                    _('Retrabalho?') => $dbOperation->hasRework() ? _('Sim') : _('Não'),
                    _('Armazenagem?') => $dbOperation->hasStorage() ? _('Sim') : _('Não'),
                    _('Importado?') => $dbOperation->hasImport() ? _('Sim') : _('Não'),
                    _('Conferência Liberada?') => $dbOperation->conference ? _('Sim') : _('Não')
                ];
            }
        }

        $excel = (new ExcelGenerator($excelData, _('Operações')));
        if(!$excel->render()) {
            $this->session->setFlash('error', ErrorMessages::excel());
            $this->redirect('user.operations.index');
        }

        $excel->stream();
    }
}