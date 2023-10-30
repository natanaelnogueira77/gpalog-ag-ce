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
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));
        $this->addData();
        $this->render('user/separations/index', [
            'amountTypes' => SeparationItem::getAmountTypes(),
            'states' => Separation::getStates(),
            'importErrors' => $data['import_errors'],
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
            $dbSeparationItem->setAsHavingAmountInStock();
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
                    'loading_date' => $separation->getLoadingDateTime()?->format('d/m/Y H:i') ?? '--/--/---- --:--',
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
                        'loading_date' => ['text' => _('Data de Carregamento'), 'sort' => false],
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