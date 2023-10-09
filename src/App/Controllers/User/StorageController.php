<?php

namespace Src\App\Controllers\User;

use GTG\MVC\Components\ExcelGenerator;
use Src\App\Controllers\User\TemplateController;
use Src\Models\Pallet;
use Src\Models\Street;
use Src\Utils\ErrorMessages;

class StorageController extends TemplateController 
{
    public function index(array $data): void 
    {
        $this->addData();

        if($dbStreets = (new Street())->get()->fetch(true)) {
            $dbStreets = Street::getGroupedBy($dbStreets, 'street_number');
            $dbPalletCounts = (new Pallet())->get([
                'in' => ['p_status' => [Pallet::PS_STORED, Pallet::PS_SEPARATED]]
            ], 'street_number, COUNT(*) as pallets_count')->group('street_number')->fetch('count');
            if($dbPalletCounts) {
                foreach($dbPalletCounts as $dbPalletCount) {
                    $dbStreets[$dbPalletCount->street_number]->allocateds = $dbPalletCount->pallets_count;
                }
            }
        }

        $this->render('user/storage/index', [
            'dbStreets' => $dbStreets,
            'storageCapacity' => $dbStreets ? array_sum(array_map(fn($s) => $s->max_plts, $dbStreets)) : 0,
            'freeAmount' => $dbStreets ? array_sum(array_map(fn($s) => $s->max_plts - $s->allocateds, $dbStreets)) : 0,
            'allocatedAmount' => $dbStreets ? array_sum(array_map(fn($s) => $s->allocateds, $dbStreets)) : 0
        ]);
    }

    public function export(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));

        $excelData = [];

        if($dbStreets = (new Street())->get()->fetch(true)) {
            $dbStreets = Street::getGroupedBy($dbStreets, 'street_number');
            $dbPalletCounts = (new Pallet())->get([
                'in' => ['p_status' => [Pallet::PS_STORED, Pallet::PS_SEPARATED]]
            ], 'street_number, COUNT(*) as pallets_count')->group('street_number')->fetch('count');
            if($dbPalletCounts) {
                foreach($dbPalletCounts as $dbPalletCount) {
                    $dbStreets[$dbPalletCount->street_number]->allocateds = $dbPalletCount->pallets_count;
                }
            }

            $free = 0;
            $busy = 0;
            $total = 0;

            foreach($dbStreets as $dbStreet) {
                $free += $dbStreet->max_plts - $dbStreet->allocateds;
                $busy += $dbStreet->allocateds;
                $total += $dbStreet->max_plts;

                $excelData[] = [
                    _('Número da Rua') => $dbStreet->street_number ?? '---',
                    _('Posição Inicial') => $dbStreet->start_position ?? '---',
                    _('Posição Final') => $dbStreet->end_position ?? '---',
                    _('Altura Máxima') => $dbStreet->max_height ?? '---',
                    _('Perfil') => $dbStreet->profile ?? '---',
                    _('Observações') => $dbStreet->obs ?? '---',
                    _('Rua de Bloqueio?') => $dbStreet->isLimitless() ? _('Sim') : _('Não'),
                    _('Livre') => $dbStreet->max_plts - $dbStreet->allocateds ?? 0,
                    _('Ocupado') => $dbStreet->allocateds ?? 0,
                    _('Total') => $dbStreet->max_plts ?? 0
                ];
            }

            $excelData[] = ['', '', '', '', '', '', '', $free, $busy, $total];
        }

        $excel = (new ExcelGenerator($excelData, _('Armazenagem')));
        if(!$excel->render()) {
            $this->session->setFlash('error', ErrorMessages::excel());
            $this->redirect('user.storage.index');
        }

        $excel->stream();
    }
}