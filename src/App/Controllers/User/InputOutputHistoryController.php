<?php

namespace Src\App\Controllers\User;

use GTG\MVC\Components\ExcelGenerator;
use GTG\MVC\Components\PDFRender;
use Src\App\Controllers\User\TemplateController;
use Src\Models\Conference;
use Src\Models\Config;
use Src\Models\Operation;
use Src\Models\Pallet;
use Src\Models\PalletFromTo;
use Src\Models\Product;
use Src\Models\Provider;
use Src\Models\Separation;
use Src\Models\SeparationItem;
use Src\Models\SeparationItemPallet;
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
                    'c_status' => "<div class=\"badge badge-{$conference->getStatusColor()}\">{$conference->getStatus()}</div>",
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
        ini_set('memory_limit', '256M');
        set_time_limit(300);
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));

        $excelData = [];
        $filters = [];

        $tnConference = Conference::tableName();
        $tnOperation = Operation::tableName();
        $tnPallet = Pallet::tableName();
        $tnProduct = Product::tableName();
        $tnProvider = Provider::tableName();
        $tnSeparation = Separation::tableName();
        $tnSeparationItem = SeparationItem::tableName();
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
        ])->leftJoin("{$tnSeparation} t9", [
            'raw' => "t9.id = t6.sep_id"
        ])->leftJoin("{$tnUser} t10", [
            'raw' => "t10.id = t9.adm_usu_id"
        ])->leftJoin("{$tnUser} t11", [
            'raw' => "t11.id = t9.loading_usu_id"
        ])->leftJoin("{$tnSeparationItem} t12", [
            'raw' => "t12.sep_id = t9.id AND t12.pal_id = t6.id"
        ])->leftJoin("{$tnUser} t13", [
            'raw' => "t13.id = t12.separation_usu_id"
        ])->leftJoin("{$tnUser} t14", [
            'raw' => "t14.id = t12.conf_usu_id"
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
            t6.sep_id AS p_sep_id,
            t7.name AS release_user_name,
            t8.name AS product_name,
            t8.prov_name AS product_prov_name,
            t8.ean AS product_ean,
            t9.created_at AS s_created_at,
            t9.loading_date AS s_loading_date,
            t10.name AS adm_user_name,
            t11.name AS loading_user_name,
            t12.order_number AS si_order_number,
            t12.separation_date AS si_separation_date,
            t12.conf_date AS si_conf_date,
            t13.name AS separation_user_name,
            t14.name AS conf_user_name
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
                    _('ID Separação') => $dbConference->p_sep_id ?? '---', 
                    _('ADM que Separou') => $dbConference->adm_user_name ?? '---',
                    _('Data de Separação do ADM') => $dbConference->s_created_at 
                        ? $this->getDateTime($dbConference->s_created_at)->format('d/m/Y') 
                        : '--/--/----',
                    _('Hora de Separação do ADM') => $dbConference->s_created_at 
                        ? $this->getDateTime($dbConference->s_created_at)->format('H:i:s') 
                        : '--:--:--',
                    _('Operador que Separou') => $dbConference->separation_user_name ?? '---',
                    _('Data de Separação do Operator') => $dbConference->si_separation_date 
                        ? $this->getDateTime($dbConference->si_separation_date)->format('d/m/Y') 
                        : '--/--/----',
                    _('Hora de Separação do Operator') => $dbConference->si_separation_date 
                        ? $this->getDateTime($dbConference->si_separation_date)->format('H:i:s') 
                        : '--:--:--',
                    _('Operador que Conferiu') => $dbConference->conf_user_name ?? '---',
                    _('Data de Conferência do Operator') => $dbConference->si_conf_date 
                        ? $this->getDateTime($dbConference->si_conf_date)->format('d/m/Y') 
                        : '--/--/----',
                    _('Hora de Conferência do Operator') => $dbConference->si_conf_date 
                        ? $this->getDateTime($dbConference->si_conf_date)->format('H:i:s') 
                        : '--:--:--',
                    _('Operador que Carregou') => $dbConference->loading_user_name ?? '---',
                    _('Data de Carregamento') => $dbConference->s_loading_date 
                        ? $this->getDateTime($dbConference->s_loading_date)->format('d/m/Y') 
                        : '--/--/----',
                    _('Hora de Carregamento') => $dbConference->s_loading_date 
                        ? $this->getDateTime($dbConference->s_loading_date)->format('H:i:s') 
                        : '--:--:--',
                    _('Número do Pedido') => $dbConference->si_order_number ?? '---',
                    _('Status do Pallet') => $dbConference->p_p_status ? Pallet::getStates()[$dbConference->p_p_status] : '---'
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

    public function expeditionReportExport(array $data): void 
    {
        ini_set('memory_limit', '256M');
        set_time_limit(300);
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));

        $excelData = [];
        $filters = [];

        $tnConference = Conference::tableName();
        $tnOperation = Operation::tableName();
        $tnPallet = Pallet::tableName();
        $tnPalletFromTo = PalletFromTo::tableName();
        $tnProduct = Product::tableName();
        $tnProvider = Provider::tableName();
        $tnSeparation = Separation::tableName();
        $tnSeparationItem = SeparationItem::tableName();
        $tnSeparationItemPallet = SeparationItemPallet::tableName();
        $tnUser = User::tableName();

        $results = (new Pallet())->join("{$tnConference} t2", [
            'raw' => "t2.id = {$tnPallet}.con_id"
        ])->join("{$tnProduct} t3", [
            'raw' => "t3.id = {$tnPallet}.pro_id"
        ])->join("{$tnUser} t4", [
            'raw' => "t4.id = {$tnPallet}.store_usu_id"
        ])->leftJoin("{$tnUser} t5", [
            'raw' => "t5.id = {$tnPallet}.release_usu_id"
        ])->leftJoin("{$tnSeparation} t6", [
            'raw' => "t6.id = {$tnPallet}.sep_id"
        ])->join("{$tnOperation} t7", [
            'raw' => "t7.id = t2.ope_id"
        ])->join("{$tnUser} t8", [
            'raw' => "t8.id = t2.adm_usu_id"
        ])->leftJoin("{$tnUser} t9", [
            'raw' => "t9.id = t2.start_usu_id"
        ])->leftJoin("{$tnUser} t10", [
            'raw' => "t10.id = t2.end_usu_id"
        ])->join("{$tnUser} t11", [
            'raw' => "t11.id = t7.usu_id"
        ])->join("{$tnProvider} t12", [
            'raw' => "t12.id = t7.for_id"
        ])->leftJoin("{$tnUser} t13", [
            'raw' => "t13.id = t6.adm_usu_id"
        ])->leftJoin("{$tnUser} t14", [
            'raw' => "t14.id = t6.loading_usu_id"
        ])->leftJoin("{$tnPallet} t15", [
            'raw' => "{$tnPallet}.height != 1 
                AND t15.height = 1 
                AND t15.p_status = 1 
                AND t15.pro_id = {$tnPallet}.pro_id"
        ])->leftJoin("{$tnSeparationItem} t16", [
            'raw' => "t16.sep_id = t6.id AND (
                ({$tnPallet}.height != 1 AND t16.pal_id = t15.id) 
                OR ({$tnPallet}.height = 1 AND t16.pal_id = t6.id)
            )"
        ])->leftJoin("{$tnUser} t18", [
            'raw' => "t18.id = t16.adm_usu_id"
        ])->leftJoin("{$tnUser} t19", [
            'raw' => "t19.id = t16.separation_usu_id"
        ])->leftJoin("{$tnUser} t20", [
            'raw' => "t20.id = t16.conf_usu_id"
        ])->get($filters, "
            {$tnPallet}.*, 
            t2.id AS t2_id,
            t2.ope_id AS t2_ope_id,
            t2.adm_usu_id AS t2_adm_usu_id,
            t2.start_usu_id AS t2_start_usu_id,
            t2.date_start AS t2_date_start,
            t2.end_usu_id AS t2_end_usu_id,
            t2.date_end AS t2_date_end,
            t2.c_status AS t2_c_status,
            t2.created_at AS t2_created_at,
            t3.id AS t3_id,
            t3.name AS t3_name,
            t4.id AS t4_id,
            t4.name AS t4_name,
            t5.id AS t5_id,
            t5.name AS t5_name,
            t6.id AS t6_id,
            t6.adm_usu_id AS t6_adm_usu_id,
            t6.loading_usu_id AS t6_loading_usu_id,
            t6.loading_date AS t6_loading_date,
            t6.plate AS t6_plate,
            t6.dock AS t6_dock,
            t6.s_status AS t6_s_status,
            t6.created_at AS t6_created_at,
            t7.usu_id AS t7_usu_id,
            t7.for_id AS t7_for_id,
            t7.loading_password AS t7_loading_password,
            t7.ga_password AS t7_ga_password,
            t7.order_number AS t7_order_number,
            t7.invoice_number AS t7_invoice_number,
            t7.plate AS t7_plate,
            t7.has_palletization AS t7_has_palletization,
            t7.has_rework AS t7_has_rework,
            t7.has_storage AS t7_has_storage,
            t7.has_import AS t7_has_import,
            t7.has_tr AS t7_has_tr,
            t7.created_at AS t7_created_at,
            t8.id AS t8_id,
            t8.name AS t8_name,
            t9.id AS t9_id,
            t9.name AS t9_name,
            t10.id AS t10_id,
            t10.name AS t10_name,
            t11.id AS t11_id,
            t11.name AS t11_name,
            t12.id AS t12_id,
            t12.name AS t12_name,
            t13.id AS t13_id,
            t13.name AS t13_name,
            t14.id AS t14_id,
            t14.name AS t14_name,
            t16.id AS t16_id,
            t16.a_type AS t16_a_type,
            t16.amount AS t16_amount,
            t16.separation_amount AS t16_separation_amount,
            t16.separation_date AS t16_separation_date,
            t16.dispatch_dock AS t16_dispatch_dock,
            t16.conf_amount AS t16_conf_amount,
            t16.conf_date AS t16_conf_date,
            t16.s_status AS t16_s_status,
            t16.created_at AS t16_created_at,
            t16.order_number AS t16_order_number,
            t16.created_at AS t16_created_at,
            t18.id AS t18_id,
            t18.name AS t18_name,
            t19.id AS t19_id,
            t19.name AS t19_name,
            t20.id AS t20_id,
            t20.name AS t20_name
        ")->fetch(true);

        if($results) {
            foreach($results as $result) {
                $dbPallet = (new Pallet())->loadData([
                    'con_id' => $result->con_id, 
                    'pro_id' => $result->pro_id, 
                    'store_usu_id' => $result->store_usu_id, 
                    'package' => $result->package, 
                    'start_boxes_amount' => $result->start_boxes_amount, 
                    'boxes_amount' => $result->boxes_amount, 
                    'start_units_amount' => $result->start_units_amount, 
                    'units_amount' => $result->units_amount, 
                    'service_type' => $result->service_type, 
                    'expiration_date' => $result->expiration_date,
                    'pallet_height' => $result->pallet_height, 
                    'street_number' => $result->street_number, 
                    'position' => $result->position, 
                    'height' => $result->height, 
                    'code' => $result->code, 
                    'sep_id' => $result->sep_id,
                    'release_usu_id' => $result->release_usu_id, 
                    'release_date' => $result->release_date, 
                    'p_status' => $result->p_status
                ]);
                $dbPallet->id = $result->id;
                $dbPallet->created_at = $result->created_at;
                $dbPallet->updated_at = $result->updated_at;
                $dbPallet->product = (new Product())->loadData(['name' => $result->t3_name]);
                $dbPallet->storeUser = (new User())->loadData(['name' => $result->t4_name]);
                if($result->t5_id) {
                    $dbPallet->releaseUser = (new User())->loadData(['name' => $result->t5_name]);
                }

                if($result->t6_id) {
                    $dbPallet->separation = (new Separation())->loadData([
                        'adm_usu_id' => $result->t6_adm_usu_id,
                        'loading_usu_id' => $result->t6_loading_usu_id,
                        'loading_date' => $result->t6_loading_date,
                        'plate' => $result->t6_plate,
                        'dock' => $result->t6_dock,
                        's_status' => $result->t6_s_status
                    ]);
                    $dbPallet->separation->created_at = $result->t6_created_at;
                    if($result->t13_id) {
                        $dbPallet->separation->ADMUser = (new User())->loadData(['name' => $result->t13_name]);
                    }

                    if($result->t14_id) {
                        $dbPallet->separation->loadingUser = (new User())->loadData(['name' => $result->t14_name]);
                    }
                }

                $dbPallet->conference = (new Conference())->loadData([
                    'ope_id' => $result->t2_ope_id, 
                    'adm_usu_id' => $result->t2_adm_usu_id, 
                    'start_usu_id' => $result->t2_start_usu_id, 
                    'date_start' => $result->t2_date_start, 
                    'end_usu_id' => $result->t2_end_usu_id, 
                    'date_end' => $result->t2_date_end, 
                    'c_status' => $result->t2_c_status
                ]);
                $dbPallet->conference->created_at = $result->t2_created_at;

                $dbPallet->conference->ADMUser = (new User())->loadData(['name' => $result->t8_name]);
                if($result->t9_id) {
                    $dbPallet->conference->startUser = (new User())->loadData(['name' => $result->t9_name]);
                }

                if($result->t10_id) {
                    $dbPallet->conference->endUser = (new User())->loadData(['name' => $result->t10_name]);
                }

                $dbPallet->conference->operation = (new Operation())->loadData([
                    'usu_id' => $result->t7_usu_id, 
                    'for_id' => $result->t7_for_id, 
                    'loading_password' => $result->t7_loading_password, 
                    'ga_password' => $result->t7_ga_password, 
                    'order_number' => $result->t7_order_number, 
                    'invoice_number' => $result->t7_invoice_number, 
                    'plate' => $result->t7_plate, 
                    'has_palletization' => $result->t7_has_palletization, 
                    'has_rework' => $result->t7_has_rework, 
                    'has_storage' => $result->t7_has_storage, 
                    'has_import' => $result->t7_has_import, 
                    'has_tr' => $result->t7_has_tr,
                    'created_at' => $result->t7_created_at
                ]);
                $dbPallet->conference->operation->created_at = $result->t7_created_at;
                $dbPallet->conference->operation->user = (new User())->loadData(['name' => $result->t11_name]);
                $dbPallet->conference->operation->provider = (new Provider())->loadData(['name' => $result->t12_name]);

                if($result->t16_id) {
                    $dbSeparationItem = (new SeparationItem())->loadData([
                        'id' => $result->t16_id,
                        'a_type' => $result->t16_a_type,
                        'amount' => $result->t16_amount,
                        'separation_amount' => $result->t16_separation_amount,
                        'separation_date' => $result->t16_separation_date,
                        'dispatch_dock' => $result->t16_dispatch_dock,
                        'conf_amount' => $result->t16_conf_amount,
                        'conf_date' => $result->t16_conf_date,
                        's_status' => $result->t16_s_status,
                        'order_number' => $result->t16_order_number,
                        'created_at' => $result->t16_created_at
                    ]);
                    $dbSeparationItem->created_at = $result->t16_created_at;
                    $dbSeparationItem->ADMUser = (new User())->loadData(['name' => $result->t18_name]);
                    $dbSeparationItem->separationUser = (new User())->loadData(['name' => $result->t19_name]);
                    $dbSeparationItem->conferenceUser = (new User())->loadData(['name' => $result->t20_name]);
                }

                $excelData[] = [
                    _('ID do Pallet') => $dbPallet->id,
                    _('Número do Pallet') => $dbPallet->code,
                    _('Rua') => $dbPallet->street_number,
                    _('Posição') => $dbPallet->position,
                    _('Altura') => $dbPallet->height,
                    _('Altura do Pallet') => $dbPallet->pallet_height,
                    _('Embalagem') => $dbPallet->package,
                    _('Serviço') => $dbPallet->getServiceType(),
                    _('Quantidade Inicial de Caixas') => $dbPallet->start_boxes_amount,
                    _('Quantidade Atual de Caixas') => $dbPallet->boxes_amount,
                    _('Quantidade Inicial de Unidades') => $dbPallet->start_units_amount,
                    _('Quantidade Atual de Unidades') => $dbPallet->units_amount,
                    _('Operador que Fez a Entrada do Pallet') => $dbPallet->storeUser->name,
                    _('Data de Entrada do Pallet') => $dbPallet->getCreatedAtDateTime()->format('d/m/Y'),
                    _('Hora de Entrada do Pallet') => $dbPallet->getCreatedAtDateTime()->format('H:i'),
                    _('Status do Pallet') => $dbPallet->getStatus(),
                    _('ID de Operação de Entrada') => $dbPallet->conference->ope_id,
                    _('Data de Início da Operação') => $dbPallet->conference->operation->getCreatedAtDateTime()->format('d/m/Y'),
                    _('Hora de Início da Operação') => $dbPallet->conference->operation->getCreatedAtDateTime()->format('H:i'),
                    _('ADM de Entrada') => $dbPallet->conference->operation->user->name,
                    _('Fornecedor da Operação') => $dbPallet->conference->operation->provider->name,
                    _('Senha de Carregamento') => $dbPallet->conference->operation->loading_password,
                    _('Senha G.A') => $dbPallet->conference->operation->ga_password,
                    _('Número do Pedido/TR') => $dbPallet->conference->operation->order_number,
                    _('Nota Fiscal') => $dbPallet->conference->operation->invoice_number,
                    _('Placa') => $dbPallet->conference->operation->plate,
                    _('Paletização?') => $dbPallet->conference->operation->hasPalletization() ? _('Sim') : _('Não'),
                    _('Retrabalho?') => $dbPallet->conference->operation->hasRework() ? _('Sim') : _('Não'),
                    _('Armazenagem?') => $dbPallet->conference->operation->hasStorage() ? _('Sim') : _('Não'),
                    _('Importação?') => $dbPallet->conference->operation->hasImport() ? _('Sim') : _('Não'),
                    _('TR?') => $dbPallet->conference->operation->hasTR() ? _('Sim') : _('Não'),
                    _('ID de Conferência de Entrada') => $dbPallet->con_id,
                    _('Data de Liberação da Conferência de Entrada') => $dbPallet->conference->getCreatedAtDateTime()->format('d/m/Y'),
                    _('Hora de Liberação da Conferência de Entrada') => $dbPallet->conference->getCreatedAtDateTime()->format('H:i'),
                    _('ADM que Liberou a Conferência de Entrada') => $dbPallet->conference->ADMUser->name,
                    _('Operador que Iniciou a Conferência de Entrada') => $dbPallet->conference->startUser?->name ?? '---',
                    _('Data de Início da Conferência de Entrada') => $dbPallet->conference->getStartDateTime()?->format('d/m/Y') ?? '--/--/----',
                    _('Hora de Início da Conferência de Entrada') => $dbPallet->conference->getStartDateTime()?->format('H:i') ?? '--:--',
                    _('Operador que Finalizou a Conferência de Entrada') => $dbPallet->conference->endUser?->name ?? '---',
                    _('Data de Término da Conferência de Entrada') => $dbPallet->conference->getEndDateTime()?->format('d/m/Y') ?? '--/--/----',
                    _('Hora de Término da Conferência de Entrada') => $dbPallet->conference->getEndDateTime()?->format('H:i') ?? '--:--',
                    _('Status da Conferência de Entrada') => $dbPallet->conference->getStatus(),
                    _('ID da Lista de Separação') => $dbPallet->sep_id ?? '---',
                    _('ADM que Gerou a Lista de Separação') => $dbPallet->separation?->ADMUser?->name ?? '---',
                    _('Data de Geração da Lista de Separação') => $dbPallet->separation?->getCreatedAtDateTime()?->format('d/m/Y') ?? '--/--/----',
                    _('Hora de Geração da Lista de Separação') => $dbPallet->separation?->getCreatedAtDateTime()?->format('H:i') ?? '--:--',
                    _('Status da Lista de Separação') => $dbPallet->separation?->getStatus() ?? '---',
                    _('Tipo de Quantidade Separada') => $dbSeparationItem?->getAmountType() ?? '---',
                    _('Número do Pedido de Separação') => $dbSeparationItem?->order_number ?? '---',
                    _('Quantidade Solicitada pela ADM') => $dbSeparationItem?->amount ?? '---',
                    _('Operador que Fez a Separação') => $dbSeparationItem?->separationUser?->name ?? '---',
                    _('Data de Separação do Operador') => $dbSeparationItem?->getSeparationDateTime()?->format('d/m/Y') ?? '--/--/----',
                    _('Hora de Separação do Operador') => $dbSeparationItem?->getSeparationDateTime()?->format('H:i') ?? '--:--',
                    _('Quantidade Separada pelo Operador') => $dbSeparationItem?->separation_amount ?? '---',
                    _('Doca de Despacho') => $dbSeparationItem?->dispatch_dock ?? '---',
                    _('Operador que Fez a Conferência') => $dbSeparationItem?->conferenceUser?->name ?? '---',
                    _('Data de Conferência do Operador') => $dbSeparationItem?->getConferenceDateTime()?->format('d/m/Y') ?? '--/--/----',
                    _('Hora de Conferência do Operador') => $dbSeparationItem?->getConferenceDateTime()?->format('H:i') ?? '--:--',
                    _('Operador que Fez o Carregamento') => $dbPallet->separation?->loadingUser?->name ?? '---',
                    _('Data de Carregamento') => $dbPallet->separation?->getLoadingDateTime()?->format('d/m/Y') ?? '--/--/----',
                    _('Hora de Carregamento') => $dbPallet->separation?->getLoadingDateTime()?->format('H:i') ?? '--:--',
                    _('Placa de Carregamento') => $dbPallet->separation?->plate ?? '---',
                    _('Doca de Carregamento') => $dbPallet->separation?->dock ?? '---',
                    _('Operador que Fez a Expedição') => $dbPallet->releaseUser?->name ?? '---',
                    _('Data de Expedição') => $dbPallet->getReleaseDateTime()?->format('d/m/Y') ?? '--/--/----',
                    _('Hora de Expedição') => $dbPallet->getReleaseDateTime()?->format('H:i') ?? '--:--'
                ];
            }
        }

        $excel = (new ExcelGenerator($excelData, _('Relatório de Exportação')));
        if(!$excel->render()) {
            $this->session->setFlash('error', ErrorMessages::excel());
            $this->redirect('user.inputOutputHistory.index');
        }

        $excel->stream();
    }
}