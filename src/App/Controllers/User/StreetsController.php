<?php

namespace Src\App\Controllers\User;

use GTG\MVC\Components\ExcelGenerator;
use Src\App\Controllers\User\TemplateController;
use Src\Models\Street;
use Src\Utils\ErrorMessages;

class StreetsController extends TemplateController 
{
    public function index(array $data): void 
    {
        $this->addData();
        $this->render('user/streets/index');
    }

    public function show(array $data): void 
    {
        if(!$dbStreet = (new Street())->findById(intval($data['street_id']))) {
            $this->setMessage('error', _('Nenhuma rua foi encontrada!'))->APIResponse([], 404);
            return;
        }

        $this->APIResponse([
            'content' => $dbStreet->getData(),
            'save' => [
                'action' => $this->getRoute('user.streets.update', ['street_id' => $dbStreet->id]),
                'method' => 'put'
            ]
        ], 200);
    }

    public function store(array $data): void 
    {
        $dbStreet = new Street();
        if(!$dbStreet->loadData(['usu_id' => $this->session->getAuth()->id] + $data)->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbStreet->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', sprintf(_('A rua "%s" foi criada com sucesso!'), $dbStreet->street_number));
        $this->APIResponse(['content' => $dbStreet->getData()], 200);
    }

    public function update(array $data): void 
    {
        if(!$dbStreet = (new Street())->findById(intval($data['street_id']))) {
            $this->setMessage('error', _('Nenhuma rua foi encontrada!'))->APIResponse([], 422);
            return;
        }

        if(!$dbStreet->loadData($data)->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbStreet->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', sprintf(_('Os dados da rua "%s" foram alterados com sucesso!'), $dbStreet->street_number))
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
                'columns' => ['street_number']
            ];
        }

        $streets = (new Street())->get($filters)->paginate($limit, $page)->sort([$order => $orderType]);
        $count = $streets->count();
        $pages = ceil($count / $limit);
        
        if($objects = $streets->fetch(true)) {
            foreach($objects as $street) {
                $params = ['street_id' => $street->id];
                $content[] = [
                    'street_number' => $street->street_number,
                    'start_position' => $street->start_position ?? '---',
                    'end_position' => $street->end_position ?? '---',
                    'max_height' => $street->max_height ?? '---',
                    'profile' => $street->profile ?? '---',
                    'max_plts' => $street->max_plts ?? '---',
                    'obs' => $street->obs ?? '---',
                    'is_limitless' => $street->isLimitless() 
                        ? "<div class=\"badge badge-success\">" . _('Sim') . "</div>"
                        : "<div class=\"badge badge-secondary\">" . _('Não') . "</div>",
                    'actions' => "
                        <div class=\"dropup d-inline-block\">
                            <button type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\" 
                                data-toggle=\"dropdown\" class=\"dropdown-toggle btn btn-sm btn-primary\">
                                " . _('Ações') . "
                            </button>
                            <div tabindex=\"-1\" role=\"menu\" aria-hidden=\"true\" class=\"dropdown-menu\">
                                <h6 tabindex=\"-1\" class=\"dropdown-header\">" . _('Ações') . "</h6>
                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"edit\" data-method=\"get\" data-street-number=\"{$street->street_number}\" 
                                    data-action=\"{$this->getRoute('user.streets.show', $params)}\">
                                    " . _('Editar Rua') . "
                                </button>

                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"delete\" data-method=\"delete\" 
                                    data-action=\"{$this->getRoute('user.streets.delete', $params)}\">
                                    " . _('Excluir Rua') . "
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
                        'street_number' => ['text' => _('Rua'), 'sort' => true],
                        'start_position' => ['text' => _('Pos. Inicial'), 'sort' => true],
                        'end_position' => ['text' => _('Pos. Final'), 'sort' => true],
                        'max_height' => ['text' => _('Altura Máxima'), 'sort' => true],
                        'profile' => ['text' => _('Perfil'), 'sort' => true],
                        'max_plts' => ['text' => _('Capacidade Máxima'), 'sort' => true],
                        'obs' => ['text' => _('Observações'), 'sort' => true],
                        'is_limitless' => ['text' => _('Bloqueio?'), 'sort' => true]
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
        if(!$dbStreet = (new Street())->findById(intval($data['street_id']))) {
            $this->setMessage('error', _('Nenhuma rua foi encontrada!'))->APIResponse([], 404);
            return;
        } elseif(!$dbStreet->destroy()) {
            $this->setMessage('error', _('Não foi possível excluir a rua!'))->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', sprintf(_('A rua "%s" foi excluída com sucesso.'), $dbStreet->street_number))
            ->APIResponse([], 200);
    }

    public function export(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));

        $excelData = [];

        if($dbStreets = (new Street())->get()->fetch(true)) {
            foreach($dbStreets as $dbStreet) {
                $excelData[] = [
                    _('Número da Rua') => $dbStreet->street_number ?? '---',
                    _('Posição Inicial') => $dbStreet->start_position ?? '---',
                    _('Posição Final') => $dbStreet->end_position ?? '---',
                    _('Altura Máxima') => $dbStreet->max_height ?? '---',
                    _('Perfil') => $dbStreet->profile ?? '---',
                    _('Capacidade Máxima de Pallets') => $dbStreet->max_plts ?? '---',
                    _('Observações') => $dbStreet->obs ?? '---',
                    _('Rua de Bloqueio?') => $dbStreet->isLimitless() ? _('Sim') : _('Não')
                ];
            }
        }

        $excel = (new ExcelGenerator($excelData, _('Ruas')));
        if(!$excel->render()) {
            $this->session->setFlash('error', ErrorMessages::excel());
            $this->redirect('user.streets.index');
        }

        $excel->stream();
    }
}