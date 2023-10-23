<?php

namespace Src\App\Controllers\User;

use Src\App\Controllers\User\TemplateController;
use Src\Models\Config;
use Src\Models\Pallet;
use Src\Models\Product;
use Src\Models\Separation;
use Src\Models\SeparationItem;
use Src\Models\SeparationItemPallet;
use Src\Models\User;
use Src\Utils\ErrorMessages;

class SeparationItemsController extends TemplateController 
{
    private ?SeparationItem $separationItem = null;
    private ?Pallet $pallet = null;

    private function isOnPicking(string $ean): ?Product 
    {
        if(!$dbProduct = Product::getByBarcode($ean)) {
            $this->setMessage('error', ErrorMessages::form())->setErrors([
                'ean' => _('Nenhum produto foi encontrado por este EAN!')
            ])->APIResponse([], 404);
            return null;
        } elseif(!$this->pallet = (new Pallet())->get([
            'pro_id' => $dbProduct->id, 
            'height' => 1, 
            'p_status' => Pallet::PS_STORED
            ])->fetch(false)) {
            $this->setMessage('error', ErrorMessages::form())->setErrors([
                'ean' => _('Este produto não foi encontrado em posição de picking!')
            ])->APIResponse([], 404);
            return null;
        }

        return $dbProduct;
    }

    private function separationItem(int $separationId): ?SeparationItem 
    {
        if(!$this->separationItem = (new SeparationItem())->findById($separationId)) {
            $this->setMessage('error', _('Nenhum item de separação foi encontrado!'))->APIResponse([], 404);
            return null;
        }

        return $this->separationItem;
    }

    public function show(array $data): void 
    {
        if(!$dbSeparationItem = (new SeparationItem())->findById(intval($data['se_id']))) {
            $this->setMessage('error', _('Nenhum EAN foi encontrado!'))->APIResponse([], 404);
            return;
        }

        $dbSeparationItem->product();

        $this->APIResponse([
            'content' => $dbSeparationItem->getData() + [
                'ean' => $dbSeparationItem->product->ean
            ],
            'save' => [
                'action' => $this->getRoute('user.separationItems.update', ['se_id' => $dbSeparationItem->id]),
                'method' => 'put'
            ]
        ], 200);
    }

    public function store(array $data): void 
    {
        if(!$dbProduct = $this->isOnPicking($data['ean'] ?? '')) return;
        $dbSeparationItem = (new SeparationItem())->loadData([
            'adm_usu_id' => $this->session->getAuth()->id,
            'pro_id' => $dbProduct->id,
            'a_type' => $data['a_type'],
            'amount' => $data['amount']
        ]);

        if($dbSeparationItem->amount && !$dbSeparationItem->hasAmountInStock()) {
            $this->setMessage(
                'error', _('A quantidade selecionada ultrapassa a quantidade que está no estoque!')
            )->setErrors($dbSeparationItem->getFirstErrors())->APIResponse([], 422);
            return;
        }

        if(!$dbSeparationItem->setAsWaiting()->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbSeparationItem->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', _('O EAN foi separado com sucesso!'))->APIResponse([
            'needs_from_to' => $dbSeparationItem->needsFromTo()
        ], 200);
    }

    public function update(array $data): void 
    {
        if(!$dbProduct = $this->isOnPicking($data['ean'] ?? '')) return;
        if(!$dbSeparationItem = (new SeparationItem())->findById(intval($data['se_id']))) {
            $this->setMessage('error', _('Nenhum EAN foi encontrado!'))->APIResponse([], 404);
            return;
        } elseif(
            $dbSeparationItem->isListed() 
            || $dbSeparationItem->isSeparated() 
            || $dbSeparationItem->isChecked() 
            || $dbSeparationItem->isFinished()
            ) {
            $this->setMessage('error', _('O EAN já foi enviado para separação!'))->APIResponse([], 404);
            return;
        }

        $dbSeparationItem->loadData([
            'pro_id' => $dbProduct->id,
            'a_type' => $data['a_type'],
            'amount' => $data['amount']
        ]);

        if($dbSeparationItem->amount && !$dbSeparationItem->hasAmountInStock()) {
            $this->setMessage(
                'error', _('A quantidade selecionada ultrapassa a quantidade que está no estoque!')
            )->setErrors($dbSeparationItem->getFirstErrors())->APIResponse([], 422);
            return;
        }

        if(!$dbSeparationItem->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbSeparationItem->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', _('Os dados da separação do EAN foram atualizados com sucesso!'))->APIResponse([
            'needs_from_to' => $dbSeparationItem->needsFromTo()
        ], 200);
    }

    public function delete(array $data): void 
    {
        if(!$dbSeparationItem = (new SeparationItem())->findById(intval($data['se_id']))) {
            $this->setMessage('error', _('Nenhum EAN foi encontrado!'))->APIResponse([], 404);
            return;
        } elseif(!$dbSeparationItem->destroy()) {
            $this->setMessage('error', _('Não foi possível excluir a EAN!'))->APIResponse([], 422);
            return;
        }

        $this->setMessage(
            'success', sprintf(_('A EAN "%s" foi removida da separação com sucesso.'), $dbSeparationItem->product()->ean)
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

        $separationItems = (new SeparationItem())->get($filters)->paginate($limit, $page)->sort([$order => $orderType]);
        $count = $separationItems->count();
        $pages = ceil($count / $limit);
        
        if($objects = $separationItems->fetch(true)) {
            $productIds = SeparationItem::getPropertyValues($objects, 'pro_id');
            $pallets = (new Pallet())->get([
                'in' => ['pro_id' => $productIds],
                'height' => 1,
                'p_status' => Pallet::PS_STORED
            ])->fetch(true);
            $pallets = Pallet::getGroupedBy($pallets, 'pro_id');
            
            $objects = SeparationItem::withProduct($objects);
            foreach($objects as $separationItem) {
                $params = ['se_id' => $separationItem->id];
                $content[] = [
                    'ean' => $separationItem->product->ean,
                    'a_type' => $separationItem->getAmountType(),
                    'amount' => $separationItem->amount,
                    'code' => $pallets[$separationItem->pro_id]->code,
                    'street_number' => $pallets[$separationItem->pro_id]->street_number,
                    'position' => $pallets[$separationItem->pro_id]->position,
                    'height' => $pallets[$separationItem->pro_id]->height,
                    'actions' => "
                        <div class=\"dropup d-inline-block\">
                            <button type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\" 
                                data-toggle=\"dropdown\" class=\"dropdown-toggle btn btn-sm btn-primary\">
                                " . _('Ações') . "
                            </button>
                            <div tabindex=\"-1\" role=\"menu\" aria-hidden=\"true\" class=\"dropdown-menu\">
                                <h6 tabindex=\"-1\" class=\"dropdown-header\">" . _('Ações') . "</h6>
                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"pallets\" data-method=\"get\" data-separation-ean=\"{$separationItem->product->ean}\" 
                                    data-action=\"{$this->getRoute('user.separationItems.palletsList', $params)}\">
                                    " . _('Ver os De Para') . "
                                </button>
                                
                                <div class=\"dropdown-divider\"></div>
                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"edit\" data-method=\"get\" data-separation-ean=\"{$separationItem->product->ean}\" 
                                    data-action=\"{$this->getRoute('user.separationItems.show', $params)}\">
                                    " . _('Editar Item') . "
                                </button>

                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"delete\" data-method=\"delete\" 
                                    data-action=\"{$this->getRoute('user.separationItems.delete', $params)}\">
                                    " . _('Excluir Item') . "
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
                        'ean' => ['text' => _('EAN'), 'sort' => true],
                        'a_type' => ['text' => _('Tipo de Quantidade'), 'sort' => true],
                        'amount' => ['text' => _('Quantidade'), 'sort' => true],
                        'code' => ['text' => _('Código'), 'sort' => false],
                        'street_number' => ['text' => _('Rua'), 'sort' => false],
                        'position' => ['text' => _('Posição'), 'sort' => false],
                        'height' => ['text' => _('Altura'), 'sort' => false]
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

    public function palletsList(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));
        if(!$this->separationItem(intval($data['se_id']))) return;

        $content = [];
        $filters = [];

        $tnPallet = Pallet::tableName();
        $tnSeparationItemPallet = SeparationItemPallet::tableName();

        $limit = $data['limit'] ? intval($data['limit']) : 10;
        $page = $data['page'] ? intval($data['page']) : 1;
        $order = $data['order'] ? $data['order'] : 'id';
        $orderType = $data['orderType'] ? $data['orderType'] : 'ASC';

        if($data['search']) {
            $filters['search'] = [
                'term' => $data['search'],
                'columns' => ["{$tnPallet}.code"]
            ];
        }

        if($data['has_bond']) {
            if($data['has_bond'] == 1) {
                $filters['raw'] = "{$tnSeparationItemPallet}.id IS NOT NULL";
            } elseif($data['has_bond'] == 2) {
                $filters['raw'] = "{$tnSeparationItemPallet}.id IS NULL";
            }
        }

        $filters['pro_id'] = $this->separationItem->pro_id;
        $filters['!='] = ['height' => 1];

        $pallets = (new Pallet())->leftJoin($tnSeparationItemPallet, [
            'raw' => "{$tnSeparationItemPallet}.pal_id = {$tnPallet}.id 
                AND {$tnSeparationItemPallet}.site_id = {$this->separationItem->id}"
        ])->get($filters, "
            {$tnPallet}.*, 
            {$tnSeparationItemPallet}.id AS sip_id
        ")->paginate($limit, $page)->sort([$order => $orderType]);
        $count = $pallets->count();
        $pages = ceil($count / $limit);
        
        if($objects = $pallets->fetch(true)) {
            foreach($objects as $pallet) {
                $params = [
                    'se_id' => $this->separationItem->id, 
                    'pallet_id' => $pallet->id
                ];
                $content[] = [
                    'code' => $pallet->code,
                    'boxes_amount' => $pallet->boxes_amount,
                    'units_amount' => $pallet->units_amount,
                    'street_number' => $pallet->street_number,
                    'position' => $pallet->position,
                    'height' => $pallet->height,
                    'actions' => ($pallet->sip_id 
                        ? "
                            <button type=\"button\" class=\"btn btn-danger btn-sm\" data-act=\"pallet-action\" 
                                data-action=\"{$this->getRoute('user.separationItems.removePallet', $params)}\" data-method=\"delete\">" 
                                . _('Remover De Para') . "
                            </button>
                        " 
                        : "
                            <button type=\"button\" class=\"btn btn-success btn-sm\" data-act=\"pallet-action\" 
                                data-action=\"{$this->getRoute('user.separationItems.addPallet', $params)}\" data-method=\"post\">" 
                                . _('Fazer De Para') . "
                            </button>
                        "
                    )
                ];
            }
        }

        $this->APIResponse([
            'content' => [
                'table' => $this->getView('_components/data-table', [
                    'headers' => [
                        'code' => ['text' => _('Número do Pallet'), 'sort' => true],
                        'boxes_amount' => ['text' => _('Caixas'), 'sort' => true],
                        'units_amount' => ['text' => _('Unidades'), 'sort' => true],
                        'street_number' => ['text' => _('Rua'), 'sort' => true],
                        'position' => ['text' => _('Posição'), 'sort' => true],
                        'height' => ['text' => _('Altura'), 'sort' => true],
                        'actions' => ['text' => _('Ações')]
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

    public function addPallet(array $data): void 
    {
        if(!$this->separationItem(intval($data['se_id']))) return;
        if(!$this->separationItem->needsFromTo()) {
            $this->setMessage(
                'error', _('Não foi possível fazer o de para pois a quantidade separada já foi alcançada!')
            )->APIResponse([], 422);
            return;
        } elseif(!$dbPallet = (new Pallet())->findById(intval($data['pallet_id']))) {
            $this->setMessage('error', _('Nenhum pallet foi encontrado!'))->APIResponse([], 404);
            return;
        }

        $dbSeparationItemPallet = (new SeparationItemPallet())->get([
            'site_id' => $this->separationItem->id,
            'pal_id' => $dbPallet->id
        ])->fetch(false);
        if(!$dbSeparationItemPallet) {
            $dbSeparationItemPallet = new SeparationItemPallet();
            if(!$dbSeparationItemPallet->loadData([
                'site_id' => $this->separationItem->id,
                'pal_id' => $dbPallet->id
                ])->save()) {
                $this->setMessage('error', ErrorMessages::form())->setErrors($dbSeparationItemPallet->getFirstErrors())->APIResponse([], 422);
                return;
            }
        }

        $this->setMessage(
            'success', 
            sprintf(_('O de para do pallet de código "%s" foi adicionado com sucesso.'), $dbPallet->code)
        )->APIResponse([], 200);
    }

    public function removePallet(array $data): void 
    {
        if(!$this->separationItem(intval($data['se_id']))) return;
        if(!$dbPallet = (new Pallet())->findById(intval($data['pallet_id']))) {
            $this->setMessage('error', _('Nenhum pallet foi encontrado!'))->APIResponse([], 404);
            return;
        }
        
        $dbSeparationItemPallet = (new SeparationItemPallet())->get([
            'site_id' => $this->separationItem->id,
            'pal_id' => $dbPallet->id
        ])->fetch(false);
        if($dbSeparationItemPallet && !$dbSeparationItemPallet->destroy()) {
            $this->setMessage('error', _('Não foi possível remover este de para!'))->APIResponse([], 422);
            return;
        }

        $this->setMessage(
            'success', 
            sprintf(_('O de para do pallet de código "%s" foi cancelado com sucesso.'), $dbPallet->code)
        )->APIResponse([], 200);
    }
}