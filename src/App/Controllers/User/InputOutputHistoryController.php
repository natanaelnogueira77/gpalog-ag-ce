<?php

namespace Src\App\Controllers\User;

use GTG\MVC\Components\ExcelGenerator;
use GTG\MVC\Components\PDFRender;
use Src\App\Controllers\User\TemplateController;
use Src\Models\Conference;
use Src\Models\Config;
use Src\Models\Operation;
use Src\Models\Pallet;
use Src\Models\Product;
use Src\Models\Provider;
use Src\Models\User;
use Src\Utils\ErrorMessages;

class InputOutputHistoryController extends TemplateController 
{
    public function index(array $data): void 
    {
        $this->addData();
        $this->render('user/input-output-history/index');
    }

    public function list(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));

        $content = [];
        $filters = [];

        $tnConference = Conference::tableName();
        $tnOperation = Operation::tableName();
        $tnProvider = Provider::tableName();

        $limit = $data['limit'] ? intval($data['limit']) : 10;
        $page = $data['page'] ? intval($data['page']) : 1;
        $order = $data['order'] ? $data['order'] : 'id';
        $orderType = $data['orderType'] ? $data['orderType'] : 'ASC';

        if($data['search']) {
            $filters['search'] = [
                'term' => $data['search'],
                'columns' => [
                    "{$tnOperation}.loading_password", 
                    "{$tnOperation}.invoice_number", 
                    "{$tnOperation}.plate"
                ]
            ];
        }

        if($data['order_number']) {
            $filters["{$tnOperation}.order_number"] = $data['order_number'];
        }

        $conferences = (new Conference())->join($tnOperation, [
            'raw' => "{$tnOperation}.id = {$tnConference}.ope_id"
        ])->join($tnProvider, [
            'raw' => "{$tnProvider}.id = {$tnOperation}.for_id"
        ])->get($filters, "
            {$tnConference}.*,
            {$tnOperation}.plate,
            {$tnOperation}.loading_password,
            {$tnOperation}.order_number,
            {$tnProvider}.name AS provider_name
        ")->paginate($limit, $page)->sort([$order => $orderType]);
        $count = $conferences->count();
        $pages = ceil($count / $limit);

        if($objects = $conferences->fetch(true)) {
            $stColors = [
                Conference::CS_WAITING => 'warning',
                Conference::CS_STARTED => 'primary',
                Conference::CS_FINISHED => 'success'
            ];
            foreach($objects as $conference) {
                $params = ['conference_id' => $conference->id];
                $content[] = [
                    'plate' => $conference->plate,
                    'provider_name' => $conference->provider_name,
                    'loading_password' => $conference->loading_password,
                    'order_number' => $conference->order_number,
                    'c_status' => "<div class=\"badge badge-{$stColors[$conference->c_status]}\">" . $conference->getStatus() . "</div>",
                    'actions' => "
                        <div class=\"dropup d-inline-block\">
                            <button type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\" 
                                data-toggle=\"dropdown\" class=\"dropdown-toggle btn btn-sm btn-primary\">
                                " . _('Ações') . "
                            </button>
                            <div tabindex=\"-1\" role=\"menu\" aria-hidden=\"true\" class=\"dropdown-menu\">
                                <h6 tabindex=\"-1\" class=\"dropdown-header\">" . _('Ações') . "</h6>
                                " . (
                                    $conference->isFinished() 
                                    ? "
                                        <a href=\"{$this->getRoute('user.inputOutputHistory.getInputPDF', $params)}\" 
                                            type=\"button\" tabindex=\"0\" class=\"dropdown-item\" target=\"_blank\">
                                            " . _('Gerar Etiquetas de Entrada') . "
                                        </a>

                                        <a href=\"{$this->getRoute('user.inputOutputHistory.getOutputPDF', $params)}\" 
                                            type=\"button\" tabindex=\"0\" class=\"dropdown-item\" target=\"_blank\">
                                            " . _('Gerar Etiquetas de Saída') . "
                                        </a>
                                    " : ''
                                ) . "
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
                        'provider_name' => ['text' => _('Fornecedor'), 'sort' => true],
                        'loading_password' => ['text' => _('Senha de Carregamento'), 'sort' => true],
                        'order_number' => ['text' => _('Nº do Pedido'), 'sort' => true],
                        'c_status' => ['text' => _('Status'), 'sort' => true]
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

    public function getInputPDF(array $data): void 
    {
        ini_set('memory_limit', '256M');
        set_time_limit(300);

        $this->addData();

        if(!$dbConference = (new Conference())->findById(intval($data['conference_id']))) {
            $this->session->setFlash('error', _('A conferência não foi encontrada!'));
            $this->redirect('user.conference.index');
        } elseif(!$dbConference->isFinished()) {
            $this->session->setFlash('error', _('Esta conferência ainda não foi finalizada!'));
            $this->redirect('user.conference.index');
        }

        if($dbPallets = $dbConference->pallets()) {
            $dbPallets = Pallet::withProduct($dbPallets);
        }

        $filename = sprintf(_('Etiqueta de Entrada - %s'), $dbConference->id) . '.pdf';

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment');
        header("filename: {$filename}");

        $html = $this->getView('user/input-output-history/_components/input-pdf', [
            'dbPallets' => $dbPallets,
            'dbOperation' => $dbConference->operation(),
            'logo' => url((new Config())->getMeta(Config::KEY_LOGO))
        ]);

        $PDFRender = new PDFRender();
        if(!$PDFRender->loadHtml($html)->setPaper('A4', 'portrait')->render()) {
            $this->session->setFlash('error', ErrorMessages::pdf());
            $this->redirect('user.conference.index');
        }

        $dompdf = $PDFRender->getDompdf();
        $dompdf->stream($filename, ['Attachment' => false]);
    }

    public function getOutputPDF(array $data): void 
    {
        ini_set('memory_limit', '256M');
        set_time_limit(300);
        
        $this->addData();

        if(!$dbConference = (new Conference())->findById(intval($data['conference_id']))) {
            $this->session->setFlash('error', _('A conferência não foi encontrada!'));
            $this->redirect('user.conference.index');
        } elseif(!$dbConference->isFinished()) {
            $this->session->setFlash('error', _('Esta conferência ainda não foi finalizada!'));
            $this->redirect('user.conference.index');
        }

        if($dbPallets = $dbConference->pallets(['p_status' => Pallet::PS_RELEASED])) {
            $dbPallets = Pallet::withProduct($dbPallets);
        }

        $filename = sprintf(_('Etiqueta de Saída - %s'), $dbConference->code) . '.pdf';

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment');
        header("filename: {$filename}");

        $html = $this->getView('user/input-output-history/_components/output-pdf', [
            'dbPallets' => $dbPallets,
            'dbOperation' => $dbConference->operation(),
            'logo' => url((new Config())->getMeta(Config::KEY_LOGO))
        ]);

        $PDFRender = new PDFRender();
        if(!$PDFRender->loadHtml($html)->setPaper('A4', 'portrait')->render()) {
            $this->session->setFlash('error', ErrorMessages::pdf());
            $this->redirect('user.conference.index');
        }

        $dompdf = $PDFRender->getDompdf();
        $dompdf->stream($filename, ['Attachment' => false]);
    }

    public function export(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));

        $excelData = [];
        $filters = [];

        $tnConference = Conference::tableName();
        $tnOperation = Operation::tableName();
        $tnPallet = Pallet::tableName();
        $tnProduct = Product::tableName();
        $tnProvider = Provider::tableName();
        $tnUser = User::tableName();

        if($data['order_number']) {
            $filters["t2.order_number"] = $data['order_number'];
        }

        $dbConferences = (new Conference())->join("{$tnOperation} t2", [
            'raw' => "t2.id = {$tnConference}.ope_id"
        ])->join("{$tnUser} t3", [
            'raw' => "t3.id = {$tnConference}.adm_usu_id"
        ])->leftJoin("{$tnUser} t4", [
            'raw' => "t4.id = {$tnConference}.start_usu_id"
        ])->leftJoin("{$tnUser} t5", [
            'raw' => "t5.id = {$tnConference}.end_usu_id"
        ])->leftJoin("{$tnPallet} t6", [
            'raw' => "t6.con_id = {$tnConference}.id"
        ])->leftJoin("{$tnUser} t7", [
            'raw' => "t7.id = t6.release_usu_id"
        ])->leftJoin("{$tnProduct} t8", [
            'raw' => "t8.id = t6.pro_id"
        ])->get($filters, "
            {$tnConference}.*,
            t2.plate AS operation_plate,
            t3.name AS adm_name,
            t4.name AS start_user_name,
            t5.name AS end_user_name,
            t6.created_at AS p_created_at,
            t6.code AS p_code,
            t6.package AS p_package,
            t6.start_boxes_amount AS p_start_boxes_amount,
            t6.start_units_amount AS p_start_units_amount,
            t6.boxes_amount AS p_boxes_amount,
            t6.units_amount AS p_units_amount,
            t6.service_type AS p_service_type,
            t6.pallet_height AS p_pallet_height,
            t6.street_number AS p_street_number,
            t6.position AS p_position,
            t6.height AS p_height,
            t6.release_date AS p_release_date,
            t6.p_status AS p_p_status,
            t7.name AS release_user_name,
            t8.name AS product_name,
            t8.prov_name AS product_prov_name,
            t8.ean AS product_ean
        ")->fetch(true);
        if($dbConferences) {
            foreach($dbConferences as $dbConference) {
                $excelData[] = [
                    _('Placa') => $dbConference->operation_plate,
                    _('ADM') => $dbConference->adm_name,
                    _('Operador que Iniciou') => $dbConference->start_user_name ?? '---',
                    _('Data de Início') => $dbConference->date_start 
                        ? $dbConference->getStartDateTime()->format('d/m/Y') 
                        : '--/--/----',
                    _('Hora de Início') => $dbConference->date_start 
                        ? $dbConference->getStartDateTime()->format('H:i:s') 
                        : '--:--:--',
                    _('Operador que Finalizou') => $dbConference->end_user_name ?? '---',
                    _('Data de Término') => $dbConference->date_end 
                        ? $dbConference->getEndDateTime()->format('d/m/Y') 
                        : '--/--/----',
                    _('Hora de Término') => $dbConference->date_end 
                        ? $dbConference->getEndDateTime()->format('H:i:s') 
                        : '--:--:--',
                    _('Status da Conferência') => $dbConference->getStatus(),
                    _('Número do Pallet') => $dbConference->p_code ?? '---',
                    _('Data de Entrada') => $dbConference->p_created_at 
                        ? $this->getDateTime($dbConference->p_created_at)->format('d/m/Y') 
                        : '--/--/----',
                    _('Hora de Entrada') => $dbConference->p_created_at 
                        ? $this->getDateTime($dbConference->p_created_at)->format('H:i:s') 
                        : '--:--:--',
                    _('Embalagem') => $dbConference->p_package ?? '---',
                    _('Produto') => $dbConference->product_name ?? '---',
                    _('Código EAN') => $dbConference->product_ean ?? '---',
                    _('Fornecedor') => $dbConference->product_prov_name ?? '---',
                    _('Quantidade de Caixas Físicas Inicial') => $dbConference->p_start_boxes_amount ?? '---',
                    _('Quantidade de Caixas Físicas') => $dbConference->p_boxes_amount ?? '---',
                    _('Quantidade de Unidades Inicial') => $dbConference->p_start_units_amount ?? '---',
                    _('Quantidade de Unidades') => $dbConference->p_units_amount ?? '---',
                    _('Tipo de Serviço') => $dbConference->p_service_type ? Pallet::getServiceTypes()[$dbConference->p_service_type] : '---',
                    _('Altura do Pallet') => $dbConference->p_pallet_height ?? '---',
                    _('Número da Rua') => $dbConference->p_street_number ?? '---',
                    _('Posição') => $dbConference->p_position ?? '---',
                    _('Altura') => $dbConference->p_height ?? '---',
                    _('Operador que fez Saída') => $dbConference->release_user_name ?? '---',
                    _('Data de Saída') => $dbConference->p_release_date 
                        ? $this->getDateTime($dbConference->p_release_date)->format('d/m/Y') 
                        : '--/--/----',
                    _('Hora de Saída') => $dbConference->p_release_date 
                        ? $this->getDateTime($dbConference->p_release_date)->format('H:i:s') 
                        : '--:--:--',
                    _('Status') => $dbConference->p_p_status ? Pallet::getStates()[$dbConference->p_p_status] : '---'
                ];
            }
        }

        $excel = (new ExcelGenerator($excelData, _('Histórico de Conferências')));
        if(!$excel->render()) {
            $this->session->setFlash('error', ErrorMessages::excel());
            $this->redirect('user.inputOutputHistory.index');
        }

        $excel->stream();
    }
}