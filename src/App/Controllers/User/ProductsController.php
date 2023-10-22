<?php

namespace Src\App\Controllers\User;

use GTG\MVC\Components\ExcelGenerator;
use Src\App\Controllers\User\TemplateController;
use Src\Models\Product;
use Src\Utils\ErrorMessages;

class ProductsController extends TemplateController 
{
    public function index(array $data): void 
    {
        $this->addData();
        $this->render('user/products/index');
    }

    public function show(array $data): void 
    {
        if(!$dbProduct = (new Product())->findById(intval($data['product_id']))) {
            $this->setMessage('error', _('Nenhum produto foi encontrado!'))->APIResponse([], 404);
            return;
        }

        $this->APIResponse([
            'content' => $dbProduct->getData(),
            'save' => [
                'action' => $this->getRoute('user.products.update', ['product_id' => $dbProduct->id]),
                'method' => 'put'
            ]
        ], 200);
    }

    public function store(array $data): void 
    {
        $dbProduct = new Product();
        if(!$dbProduct->loadData($data)->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbProduct->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', sprintf(_('O produto "%s" foi cadastrado com sucesso!'), $dbProduct->name));
        $this->APIResponse(['content' => $dbProduct->getData()], 200);
    }

    public function update(array $data): void 
    {
        if(!$dbProduct = (new Product())->findById(intval($data['product_id']))) {
            $this->setMessage('error', _('Nenhum produto foi encontrado!'))->APIResponse([], 422);
            return;
        }

        if(!$dbProduct->loadData($data)->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbProduct->getFirstErrors())->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', sprintf(_('Os dados do produto "%s" foram alterados com sucesso!'), $dbProduct->name))
            ->APIResponse([], 200);
    }

    public function delete(array $data): void 
    {
        if(!$dbProduct = (new Product())->findById(intval($data['product_id']))) {
            $this->setMessage('error', _('Nenhum produto foi encontrado!'))->APIResponse([], 404);
            return;
        } elseif(!$dbProduct->destroy()) {
            $this->setMessage('error', _('Não foi possível excluir o produto!'))->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', sprintf(_('O produto "%s" foi excluído com sucesso.'), $dbProduct->name))
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
                'columns' => ['name', 'user_name', 'plate']
            ];
        }

        $products = (new Product())->get($filters)->paginate($limit, $page)->sort([$order => $orderType]);
        $count = $products->count();
        $pages = ceil($count / $limit);
        
        if($objects = $products->fetch(true)) {
            foreach($objects as $product) {
                $params = ['product_id' => $product->id];
                $content[] = [
                    'name' => $product->name,
                    'prov_name' => $product->prov_name,
                    'prov_id' => $product->prov_id,
                    'prod_id' => $product->prod_id,
                    'emb_fb' => $product->emb_fb,
                    'ean' => $product->ean,
                    'dun14' => $product->dun14,
                    'p_length' => $product->p_length,
                    'p_width' => $product->p_width,
                    'p_height' => $product->p_height,
                    'p_base' => $product->p_base,
                    'p_weight' => $product->p_weight,
                    'plu' => $product->plu,
                    'actions' => "
                        <div class=\"dropup d-inline-block\">
                            <button type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\" 
                                data-toggle=\"dropdown\" class=\"dropdown-toggle btn btn-sm btn-primary\">
                                " . _('Ações') . "
                            </button>
                            <div tabindex=\"-1\" role=\"menu\" aria-hidden=\"true\" class=\"dropdown-menu\">
                                <h6 tabindex=\"-1\" class=\"dropdown-header\">" . _('Ações') . "</h6>
                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"edit\" data-method=\"get\" data-product-name=\"{$product->name}\" 
                                    data-action=\"{$this->getRoute('user.products.show', $params)}\">
                                    " . _('Editar Produto') . "
                                </button>

                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"delete\" data-method=\"delete\" 
                                    data-action=\"{$this->getRoute('user.products.delete', $params)}\">
                                    " . _('Excluir Produto') . "
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
                        'prov_name' => ['text' => _('Fornecedor'), 'sort' => true],
                        'name' => ['text' => _('Nome'), 'sort' => true],
                        'p_length' => ['text' => _('Comprimento'), 'sort' => true],
                        'p_width' => ['text' => _('Largura'), 'sort' => true],
                        'p_height' => ['text' => _('Altura'), 'sort' => true],
                        'p_base' => ['text' => _('Base'), 'sort' => true],
                        'p_weight' => ['text' => _('Peso'), 'sort' => true]
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
        ini_set('memory_limit', '256M');
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
            $this->redirect('user.products.index');
        } elseif(!in_array($_FILES['csv']['type'], $excelTypes)) {
            $this->session->setFlash('error', _('O arquivo precisa ser um excel CSV!'));
            $this->redirect('user.products.index');
        }
        
        if($file = $_FILES['csv']['tmp_name']) {
            $handle = fopen($file, 'r');
            while(($csv = fgetcsv($handle, 1000, ';')) !== false) {
                $rows[] = $csv;
            }
            fclose($handle);
            
            if($rows) {
                $dbProducts = [];
                foreach($rows as $index => $row) {
                    $dbProducts[] = (new Product())->loadData([
                        'usu_id' => $user->id,
                        'prov_name' => $row[0],
                        'prov_id' => $row[1],
                        'prod_id' => $row[2],
                        'name' => $row[3],
                        'emb_fb' => $row[4],
                        'dun14' => $row[5],
                        'ean' => $row[6], 
                        'p_length' => $row[7],
                        'p_width' => $row[8],
                        'p_height' => $row[9],
                        'p_base' => $row[10],
                        'p_weight' => floatval($row[11]),
                        'plu' => $row[12]
                    ]);
                }

                if(!$objects = Product::insertMany($dbProducts)) {
                    $this->session->setFlash('error', ErrorMessages::requisition());
                    $this->redirect('user.products.index');
                } elseif($errors = Product::getErrorsFromMany($objects)) {
                    $message = '';
                    foreach($errors as $rowNumber => $error) {
                        $message .= sprintf(_('Linha %s: '), $rowNumber);
                    }
                    $this->session->setFlash('error', sprintf(_('Houveram erros no excel! %s'), $message));
                    $this->redirect('user.products.index');
                }

                $this->session->setFlash(
                    'success', sprintf(_('Todos os %s registros foram importados com sucesso!'), $rows ? count($rows) : 0)
                );
                $this->redirect('user.products.index');
            }
        }
    }

    public function export(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));

        $excelData = [];

        if($dbProducts = (new Product())->get()->fetch(true)) {
            foreach($dbProducts as $dbProduct) {
                $excelData[] = [
                    _('Nome') => $dbProduct->name,
                    _('ID do Fornecedor') => $dbProduct->prov_id,
                    _('Nome do Fornecedor') => $dbProduct->prov_name,
                    _('ID do Produto') => $dbProduct->prod_id,
                    _('Embalagem') => $dbProduct->emb_fb ?? '---',
                    _('Código EAN') => $dbProduct->ean ?? '---',
                    _('Código Dun14') => $dbProduct->dun14 ?? '---',
                    _('Comprimento') => $dbProduct->p_length ?? '---',
                    _('Largura') => $dbProduct->p_width ?? '---',
                    _('Altura') => $dbProduct->p_height ?? '---',
                    _('Base') => $dbProduct->p_base ?? '---',
                    _('Peso') => $dbProduct->p_weight ?? '---',
                    _('PLU') => $dbProduct->plu ?? '---'
                ];
            }
        }

        $excel = (new ExcelGenerator($excelData, _('Produtos')));
        if(!$excel->render()) {
            $this->session->setFlash('error', ErrorMessages::excel());
            $this->redirect('user.products.index');
        }

        $excel->stream();
    }
}