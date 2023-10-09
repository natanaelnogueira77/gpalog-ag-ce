<?php

namespace Src\App\Controllers\User;

use Src\App\Controllers\User\TemplateController;
use Src\Models\Config;
use Src\Models\Pallet;
use Src\Models\Product;
use Src\Models\Separation;
use Src\Models\SeparationEAN;
use Src\Models\User;
use Src\Utils\ErrorMessages;

class SeparationEANsController extends TemplateController 
{
    private function isOnPicking(string $ean): ?Product 
    {
        if(!$dbProduct = Product::getByBarcode($ean)) {
            $this->setMessage('error', _('O produto não foi encontrado pelo EAN!'))->APIResponse([], 404);
            return null;
        } elseif(!(new Pallet())->get(['pro_id' => $dbProduct->id, 'height' => 1])->count()) {
            $this->setMessage('error', _('Este produto não foi encontrado em posição de picking!'))->APIResponse([], 404);
            return null;
        }

        return $dbProduct;
    }

    public function show(array $data): void 
    {
        if(!$dbSeparationEAN = (new SeparationEAN())->findById(intval($data['se_id']))) {
            $this->setMessage('error', _('Nenhum EAN foi encontrado!'))->APIResponse([], 404);
            return;
        }

        $dbSeparationEAN->product();

        $this->APIResponse([
            'content' => $dbSeparationEAN->getData() + [
                'ean' => $dbSeparationEAN->product->ean
            ],
            'save' => [
                'action' => $this->getRoute('user.separationEANs.update', ['se_id' => $dbSeparationEAN->id]),
                'method' => 'put'
            ]
        ], 200);
    }

    public function store(array $data): void 
    {
        if(!$dbProduct = $this->isOnPicking($data['ean'] ?? '')) return;
        $dbSeparationEAN = (new SeparationEAN())->loadData([
            'adm_usu_id' => $this->session->getAuth()->id,
            'pro_id' => $dbProduct->id,
            'a_type' => $data['a_type'],
            'amount' => $data['amount']
        ]);
        if(!$dbSeparationEAN->setAsWaiting()->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbSeparationEAN->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', _('O EAN foi separado com sucesso!'))->APIResponse([], 200);
    }

    public function update(array $data): void 
    {
        if(!$dbProduct = $this->isOnPicking($data['ean'] ?? '')) return;
        if(!$dbSeparationEAN = (new SeparationEAN())->findById(intval($data['se_id']))) {
            $this->setMessage('error', _('Nenhum EAN foi encontrado!'))->APIResponse([], 404);
            return;
        } elseif($dbSeparationEAN->isListed()) {
            $this->setMessage('error', _('O EAN já foi enviado para separação!'))->APIResponse([], 404);
            return;
        }

        $dbSeparationEAN->loadData([
            'pro_id' => $dbProduct->id,
            'a_type' => $data['a_type'],
            'amount' => $data['amount']
        ]);
        if(!$dbSeparationEAN->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbSeparationEAN->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', _('Os dados da separação do EAN foram atualizados com sucesso!'))->APIResponse([], 200);
    }

    public function delete(array $data): void 
    {
        if(!$dbSeparationEAN = (new SeparationEAN())->findById(intval($data['se_id']))) {
            $this->setMessage('error', _('Nenhum EAN foi encontrado!'))->APIResponse([], 404);
            return;
        } elseif(!$dbSeparationEAN->destroy()) {
            $this->setMessage('error', _('Não foi possível excluir a EAN!'))->APIResponse([], 422);
            return;
        }

        $this->setMessage(
            'success', sprintf(_('A EAN "%s" foi removida da separação com sucesso.'), $dbSeparationEAN->product()->ean)
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
                'columns' => ['ean']
            ];
        }

        $filters['raw'] = "sep_id IS NULL";

        $separationEANs = (new SeparationEAN())->get($filters)->paginate($limit, $page)->sort([$order => $orderType]);
        $count = $separationEANs->count();
        $pages = ceil($count / $limit);
        
        if($objects = $separationEANs->fetch(true)) {
            $objects = SeparationEAN::withProduct($objects);
            foreach($objects as $separationEAN) {
                $params = ['se_id' => $separationEAN->id];
                $content[] = [
                    'ean' => $separationEAN->product->ean,
                    'a_type' => $separationEAN->getAmountType(),
                    'amount' => $separationEAN->amount,
                    'actions' => "
                        <div class=\"dropup d-inline-block\">
                            <button type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\" 
                                data-toggle=\"dropdown\" class=\"dropdown-toggle btn btn-sm btn-primary\">
                                " . _('Ações') . "
                            </button>
                            <div tabindex=\"-1\" role=\"menu\" aria-hidden=\"true\" class=\"dropdown-menu\">
                                <h6 tabindex=\"-1\" class=\"dropdown-header\">" . _('Ações') . "</h6>
                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"edit\" data-method=\"get\" data-separation-ean=\"{$separationEAN->product->ean}\" 
                                    data-action=\"{$this->getRoute('user.separationEANs.show', $params)}\">
                                    " . _('Editar EAN') . "
                                </button>

                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"delete\" data-method=\"delete\" 
                                    data-action=\"{$this->getRoute('user.separationEANs.delete', $params)}\">
                                    " . _('Excluir EAN') . "
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
                        'ean' => ['text' => _('EAN'), 'sort' => true],
                        'a_type' => ['text' => _('Tipo de Quantidade'), 'sort' => true],
                        'amount' => ['text' => _('Quantidade'), 'sort' => true]
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
}