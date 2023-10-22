<?php

namespace Src\App\Controllers\User;

use GTG\MVC\Components\ExcelGenerator;
use GTG\MVC\Components\PDFRender;
use Src\App\Controllers\User\TemplateController;
use Src\Models\Config;
use Src\Models\Operation;
use Src\Models\Pallet;
use Src\Models\Product;
use Src\Models\Provider;
use Src\Models\Separation;
use Src\Models\SeparationItem;
use Src\Models\User;
use Src\Utils\ErrorMessages;

class SeparationsController extends TemplateController 
{
    public function index(array $data): void 
    {
        $this->addData();
        $this->render('user/separations/index', [
            'amountTypes' => SeparationItem::getAmountTypes(),
            'states' => Separation::getStates(),
            'dbConference' => $dbConference,
            'dbPallets' => $dbPallets
        ]);
    }

    public function store(array $data): void 
    {
        if(!$dbSeparationItems = (new SeparationItem())->get(['raw' => 'sep_id IS NULL'])->fetch(true)) {
            $this->setMessage('error', _('Não há nenhum produto para separação!'))->APIResponse([], 404);
            return;
        }

        foreach($dbSeparationItems as $dbSeparationItem) {
            if($dbSeparationItem->needsFromTo()) {
                $this->setMessage(
                    'error', _('Esta separação ainda possui ao menos um item que precisa de um de para!')
                )->APIResponse([], 422);
                return;
            }
        }

        $dbSeparation = (new Separation())->loadData(['adm_usu_id' => $this->session->getAuth()->id]);
        if(!$dbSeparation->setAsWaiting()->save()) {
            $this->setMessage('error', ErrorMessages::form())->setErrors($dbSeparation->getFirstErrors())->APIResponse([], 422);
            return;
        }

        foreach($dbSeparationItems as $dbSeparationItem) {
            $dbSeparationItem->sep_id = $dbSeparation->id;
            $dbSeparationItem->setAsListed();
        }

        if(!SeparationItem::saveMany($dbSeparationItems)) {
            $this->setMessage('error', ErrorMessages::requisition())->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', _('A separação foi gerada com sucesso!'))->APIResponse([], 200);
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
                'columns' => ['plate', 'dock']
            ];
        }

        if($data['separation_status']) {
            $filters['s_status'] = $data['separation_status'];
        }

        $separations = (new Separation())->get($filters)->paginate($limit, $page)->sort([$order => $orderType]);
        $count = $separations->count();
        $pages = ceil($count / $limit);
        
        if($objects = $separations->fetch(true)) {
            $objects = Separation::withADMUser($objects);
            $objects = Separation::withLoadingUser($objects);
            foreach($objects as $separation) {
                $params = ['separation_id' => $separation->id];
                $content[] = [
                    'id' => $separation->id,
                    'adm_usu_id' => $separation->ADMUser ? $separation->ADMUser->name : '---',
                    'loading_usu_id' => $separation->loadingUser ? $separation->loadingUser->name : '---',
                    'plate' => $separation->plate ?? '---',
                    'dock' => $separation->dock ?? '---',
                    's_status' => "<div class=\"badge badge-{$separation->getStatusColor()}\">{$separation->getStatus()}</div>",
                    'actions' => "
                        <div class=\"dropup d-inline-block\">
                            <button type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\" 
                                data-toggle=\"dropdown\" class=\"dropdown-toggle btn btn-sm btn-primary\">
                                " . _('Ações') . "
                            </button>
                            <div tabindex=\"-1\" role=\"menu\" aria-hidden=\"true\" class=\"dropdown-menu\">
                                <h6 tabindex=\"-1\" class=\"dropdown-header\">" . _('Ações') . "</h6>
                                <a href=\"{$this->getRoute('user.separations.getOperatorPDF', $params)}\" 
                                    type=\"button\" tabindex=\"0\" class=\"dropdown-item\" target=\"_blank\">
                                    " . _('Gerar PDF de Separação') . "
                                </a>
                                " . (
                                    !$separation->hasNotSeparatedEANs() 
                                    ? "<a href=\"{$this->getRoute('user.separations.getUpdatedPDF', $params)}\" 
                                        type=\"button\" tabindex=\"0\" class=\"dropdown-item\" target=\"_blank\">
                                        " . _('Gerar PDF Atualizado') . "
                                    </a>" 
                                    : '' 
                                ) . "
                                <button type=\"button\" tabindex=\"0\" class=\"dropdown-item\" 
                                    data-act=\"show\" data-method=\"get\" data-separation-id=\"{$separation->id}\" 
                                    data-action=\"{$this->getRoute('user.separations.getSeparationTable', $params)}\">
                                    " . _('Ver Lista de Separação') . "
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
                        'adm_usu_id' => ['text' => _('ADM'), 'sort' => false],
                        'loading_usu_id' => ['text' => _('Carregador'), 'sort' => false],
                        'plate' => ['text' => _('Placa'), 'sort' => true],
                        'dock' => ['text' => _('Doca'), 'sort' => true],
                        's_status' => ['text' => _('Status'), 'sort' => true]
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

    public function getOperatorPDF(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));
        $this->addData();

        if(!$dbSeparation = (new Separation())->findById(intval($data['separation_id']))) {
            $this->session->setFlash('error', _('A separação não foi encontrada!'));
            $this->redirect('user.separations.index');
        }

        if($dbSeparationItems = $dbSeparation->separationItems()) {
            $dbSeparationItems = SeparationItem::withProduct($dbSeparationItems);
            $dbSeparationItems = SeparationItem::withPallets($dbSeparationItems);
            $dbSeparationItems = SeparationItem::withPallet($dbSeparationItems);
        }

        $filename = sprintf(_('Lista de Separação - ID %s'), $dbSeparation->id) . '.pdf';

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment');
        header("filename: {$filename}");

        $html = $this->getView('user/separations/_components/separation-pdf', [
            'dbSeparation' => $dbSeparation,
            'dbSeparationItems' => $dbSeparationItems,
            'logo' => url((new Config())->getMeta(Config::KEY_LOGO))
        ]);

        $PDFRender = new PDFRender();
        if(!$PDFRender->loadHtml($html)->setPaper('A4', 'portrait')->render()) {
            $this->session->setFlash('error', ErrorMessages::pdf());
            $this->redirect('user.separations.index');
        }

        $dompdf = $PDFRender->getDompdf();
        $dompdf->stream($filename, ['Attachment' => false]);
    }

    public function getUpdatedPDF(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));
        $this->addData();

        if(!$dbSeparation = (new Separation())->findById(intval($data['separation_id']))) {
            $this->session->setFlash('error', _('A separação não foi encontrada!'));
            $this->redirect('user.separations.index');
        } elseif($dbSeparation->hasNotSeparatedEANs()) {
            $this->session->setFlash('error', _('Este ID de separação ainda tem EANs para serem separados!'));
            $this->redirect('user.separations.index');
        }

        if($dbSeparationItems = $dbSeparation->separationItems()) {
            $dbSeparationItems = SeparationItem::withPallet($dbSeparationItems);
            $dbSeparationItems = SeparationItem::withPallets($dbSeparationItems);
            $dbSeparationItems = SeparationItem::withProduct($dbSeparationItems);
        }

        $filename = sprintf(_('Lista de Separação - ID %s'), $dbSeparation->id) . '.pdf';

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment');
        header("filename: {$filename}");

        $html = $this->getView('user/separations/_components/updated-pdf', [
            'dbSeparation' => $dbSeparation,
            'dbSeparationItems' => $dbSeparationItems,
            'logo' => url((new Config())->getMeta(Config::KEY_LOGO))
        ]);

        $PDFRender = new PDFRender();
        if(!$PDFRender->loadHtml($html)->setPaper('A4', 'portrait')->render()) {
            $this->session->setFlash('error', ErrorMessages::pdf());
            $this->redirect('user.separations.index');
        }

        $dompdf = $PDFRender->getDompdf();
        $dompdf->stream($filename, ['Attachment' => false]);
    }

    public function export(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));

        $excelData = [];

        $tnOperation = Operation::tableName();
        $tnPallet = Pallet::tableName();
        $tnProduct = Product::tableName();
        $tnProvider = Provider::tableName();
        $tnSeparation = Separation::tableName();
        $tnUser = User::tableName();

        $dbSeparations = (new Separation())->join("{$tnOperation} t2", [
            'raw' => "t2.id = {$tnSeparation}.ope_id"
        ])->join("{$tnUser} t3", [
            'raw' => "t3.id = {$tnSeparation}.usu_id"
        ])->leftJoin("{$tnPallet} t4", [
            'raw' => "t4.sai_id = {$tnSeparation}.id"
        ])->leftJoin("{$tnUser} t5", [
            'raw' => "t5.id = t4.release_usu_id"
        ])->leftJoin("{$tnProduct} t6", [
            'raw' => "t6.id = t4.pro_id"
        ])->get([], "
            {$tnSeparation}.*,
            t2.id AS operation_id,
            t2.plate AS operation_plate,
            t3.name AS adm_name,
            t4.created_at AS p_created_at,
            t4.code AS p_code,
            t4.package AS p_package,
            t4.boxes_amount AS p_boxes_amount,
            t4.units_amount AS p_units_amount,
            t4.service_type AS p_service_type,
            t4.pallet_height AS p_pallet_height,
            t4.street_number AS p_street_number,
            t4.position AS p_position,
            t4.height AS p_height,
            t4.sai_id AS p_sai_id,
            t4.release_date AS p_release_date,
            t4.load_plate AS p_load_plate,
            t4.dock AS p_dock,
            t4.p_status AS p_p_status,
            t5.name AS release_user_name,
            t6.name AS product_name,
            t6.prov_name AS product_prov_name,
            t6.ean AS product_ean
        ")->fetch(true);

        if($dbSeparations) {
            foreach($dbSeparations as $dbOutput) {
                $excelData[] = [
                    _('ID de Separação') => $dbOutput->operation_plate,
                    _('ADM') => $dbOutput->adm_name,
                    _('ID de Operação') => $dbOutput->operation_id,
                    _('Placa') => $dbOutput->operation_plate,
                    _('Número do Pallet') => $dbOutput->p_code ?? '---',
                    _('Data de Entrada') => $dbOutput->p_created_at 
                        ? $this->getDateTime($dbOutput->p_created_at)->format('d/m/Y') 
                        : '--/--/----',
                    _('Hora de Entrada') => $dbOutput->p_created_at 
                        ? $this->getDateTime($dbOutput->p_created_at)->format('H:i:s') 
                        : '--:--:--',
                    _('Embalagem') => $dbOutput->p_package ?? '---',
                    _('Produto') => $dbOutput->product_name ?? '---',
                    _('Código EAN') => $dbOutput->product_ean ?? '---',
                    _('Fornecedor') => $dbOutput->product_prov_name ?? '---',
                    _('Quantidade de Caixas Físicas') => $dbOutput->p_boxes_amount ?? '---',
                    _('Quantidade de Unidades') => $dbOutput->p_units_amount ?? '---',
                    _('Tipo de Serviço') => Pallet::getServiceTypes()[$dbOutput->p_service_type],
                    _('Altura do Pallet') => $dbOutput->p_pallet_height ?? '---',
                    _('Número da Rua') => $dbOutput->p_street_number ?? '---',
                    _('Posição') => $dbOutput->p_position ?? '---',
                    _('Altura') => $dbOutput->p_height ?? '---',
                    _('ID de Separação') => $dbOutput->p_sai_id ?? '---',
                    _('Operador que fez Saída') => $dbOutput->release_user_name ?? '---',
                    _('Data de Saída') => $dbOutput->p_release_date 
                        ? $this->getDateTime($dbOutput->p_release_date)->format('d/m/Y') 
                        : '--/--/----',
                    _('Hora de Saída') => $dbOutput->p_release_date 
                        ? $this->getDateTime($dbOutput->p_release_date)->format('H:i:s') 
                        : '--:--:--',
                    _('Placa de Carregamento') => $dbOutput->p_load_plate ?? '---',
                    _('Doca') => $dbOutput->p_dock ?? '---',
                    _('Status') => Pallet::getStates()[$dbOutput->p_p_status]
                ];
            }
        }

        $excel = (new ExcelGenerator($excelData, _('Separação')));
        if(!$excel->render()) {
            $this->session->setFlash('error', ErrorMessages::excel());
            $this->redirect('user.separations.index');
        }

        $excel->stream();
    }

    public function getSeparationTable(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));
        $dbSeparationItems = (new SeparationItem())->get(
            isset($data['separation_id']) ? ['sep_id' => $data['separation_id']] : ['raw' => 'sep_id IS NULL']
        )->fetch(true);
        if(!$dbSeparationItems) {
            $this->setMessage('error', _('Não há nenhum produto para separação!'))->APIResponse([], 404);
            return;
        }

        $dbSeparationItems = SeparationItem::withPallet($dbSeparationItems);
        $dbSeparationItems = SeparationItem::withProduct($dbSeparationItems);
        $dbSeparationItems = SeparationItem::withPallets($dbSeparationItems);

        $this->APIResponse([
            'content' => $this->getView('user/separations/_components/list', [
                'dbSeparationItems' => $dbSeparationItems
            ]),
            'save' => [
                'action' => $this->getRoute('user.separations.store'),
                'method' => 'post'
            ]
        ], 200);
    }
}