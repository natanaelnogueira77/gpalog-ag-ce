<?php

namespace Src\App\Controllers\User;

use Src\App\Controllers\User\TemplateController;
use Src\Models\Conference;
use Src\Models\ConferenceExpeditionForm;
use Src\Models\ConferenceLoadingForm;
use Src\Models\ConferenceInput;
use Src\Models\ConferenceInputForm;
use Src\Models\ConferenceProduct;
use Src\Models\ConferenceProductForm;
use Src\Models\ConferenceSeparationForm;
use Src\Models\Config;
use Src\Models\Operation;
use Src\Models\Pallet;
use Src\Models\PalletFromTo;
use Src\Models\Provider;
use Src\Utils\ErrorMessages;

class ConferenceController extends TemplateController 
{
    public function index(array $data): void 
    {
        $this->addData();
        $this->render('user/conference/index', [
            'message' => $this->getFeedbackMessage()
        ]);
    }

    public function input(array $data): void 
    {
        $this->addData();

        $tnConference = Conference::tableName();
        $tnOperation = Operation::tableName();
        $tnProvider = Provider::tableName();

        $dbConferences = (new Conference())->join("{$tnOperation} t2", [
            'raw' => "t2.id = {$tnConference}.ope_id"
        ])->join("{$tnProvider} t3", [
            'raw' => "t3.id = t2.for_id"
        ])->get([
            'in' => ["{$tnConference}.c_status" => [Conference::CS_WAITING, Conference::CS_STARTED]]
        ], "{$tnConference}.*, t2.plate AS plate, t3.name AS provider_name")->fetch(true);

        if($dbConferences) {
            $dbConferences = Conference::withOperation($dbConferences);
            foreach($dbConferences as $dbConference) {
                $dbConference->created_at = $dbConference->getCreatedAtDateTime()->format('d/m/Y');
            }
        }

        $this->render('user/conference/input', [
            'dbConferences' => $dbConferences,
            'message' => $this->getFeedbackMessage()
        ]);
    }

    public function singleInput(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));
        $this->addData();

        if(!$dbConference = (new Conference())->findById(intval($data['conference_id']))) {
            $this->session->setFlash('error', _('A conferência não foi encontrada!'));
            $this->redirect('user.conference.index');
        } elseif($dbConference->isFinished()) {
            $this->session->setFlash('error', _('Esta conferência já foi finalizada!'));
            $this->redirect('user.conference.index');
        }

        if($dbConference->isWaiting()) {
            $dbConference->loadData([
                'start_usu_id' => $this->session->getAuth()->id,
                'date_start' => date('Y-m-d H:i:s')
            ])->setAsStarted()->save();
        }

        if($dbOperation = $dbConference->operation()) {
            $dbOperation->provider();
        }

        if($dbConferenceInputs = $dbConference->conferenceInputs()) {
            $dbConferenceInputs = ConferenceInput::withProduct($dbConferenceInputs);
        }

        $conferenceInputForm = new ConferenceInputForm();
        if(isset($data['include_product']) || isset($data['search_product']) || isset($data['is_completed']) || $this->request->isPost()) {
            $conferenceInputForm->has_started = true;
        }

        if(!isset($data['finish_conference'])) {
            if(isset($data['search_product']) || $this->request->isPost()) {
                if(!$dbProduct = $conferenceInputForm->loadData(['barcode' => $data['barcode']])->getProduct()) {
                    $this->session->setFlash('error', ErrorMessages::form());
                }
            }
        }

        if($this->request->isPost()) {
            if(!isset($data['finish_conference'])) {
                $conferenceInputForm->loadData([
                    'package' => $dbProduct->emb_fb,
                    'barcode' => $data['barcode'] ? $data['barcode'] : null,
                    'physic_boxes_amount' => $data['physic_boxes_amount'] ? $data['physic_boxes_amount'] : null,
                    'closed_plts_amount' => $data['closed_plts_amount'] ? $data['closed_plts_amount'] : null,
                    'service_type' => $data['service_type'] ? $data['service_type'] : null,
                    'pallet_height' => $data['pallet_height'] ? floatval($data['pallet_height']) : null
                ]);
                if(!$conferenceInputForm->complete()) {
                    $this->session->setFlash('error', ErrorMessages::form());
                }
    
                if(isset($data['is_completed'])) {
                    $dbConferenceInput = (new ConferenceInput())->loadData([
                        'con_id' => $dbConference->id,
                        'usu_id' => $this->session->getAuth()->id,
                        'barcode' => $conferenceInputForm->barcode,
                        'pro_id' => $dbProduct->id, 
                        'package' => $conferenceInputForm->package, 
                        'physic_boxes_amount' => $conferenceInputForm->physic_boxes_amount, 
                        'closed_plts_amount' => $conferenceInputForm->closed_plts_amount, 
                        'units_amount' => $conferenceInputForm->physic_boxes_amount * $conferenceInputForm->package, 
                        'service_type' => $conferenceInputForm->service_type, 
                        'pallet_height' => $conferenceInputForm->pallet_height
                    ]);
    
                    if(!$dbConferenceInput->save()) {
                        $this->session->setFlash('error', ErrorMessages::requisition());
                        $this->redirect('user.conference.singleInput', ['conference_id' => $dbConference->id]);
                    } else {
                        $this->session->setFlash(
                            'success', 
                            sprintf(
                                _('A entrada do produto "%s" na conferência de ID %s foi feita com sucesso!'), 
                                $dbProduct->name, $dbConference->id
                            )
                        );
                        $this->redirect('user.conference.singleInput', ['conference_id' => $dbConference->id]);
                    }
                }
            } else {
                $dbConference->loadData([
                    'end_usu_id' => $this->session->getAuth()->id, 
                    'date_end' => date('Y-m-d H:i:s')
                ]);

                if(!$dbPallets = $dbConference->generatePallets()) {
                    $this->session->setFlash('error', _('Não há pallets para serem armazenados!'));
                    $this->redirect('user.conference.singleInput', ['conference_id' => $dbConference->id]);
                }

                if(!Pallet::allocateMany($dbPallets) || !$dbConference->setAsFinished()->save()) {
                    $this->session->setFlash('error', ErrorMessages::requisition());
                    $this->redirect('user.conference.singleInput', ['conference_id' => $dbConference->id]);
                } else {
                    $this->session->setFlash('success', sprintf(_('A conferência de ID %s foi finalizada com sucesso!'), $dbConference->id));
                    $this->redirect('user.conference.input');
                }
            }
        }

        if(!$conferenceInputForm->hasStarted()) {
            $return = $this->getRoute('user.conference.input');
        } elseif(!$conferenceInputForm->hasProduct()) {
            $return = $this->getRoute('user.conference.singleInput', ['conference_id' => $dbConference->id]);
        } elseif(!$conferenceInputForm->isCompleted()) {
            $return = $this->getRoute('user.conference.singleInput', [
                'conference_id' => $dbConference->id,
                'include_product' => true
            ]);
        } else {
            $return = $this->getRoute('user.conference.singleInput', [
                'conference_id' => $dbConference->id,
                'search_product' => true,
                'barcode' => $conferenceInputForm->barcode
            ]);
        }

        $dbConference->created_at = $dbConference->getCreatedAtDateTime()->format('d/m/Y');

        $this->render('user/conference/single-input', [
            'dbConference' => $dbConference,
            'dbOperation' => $dbOperation,
            'dbProduct' => $dbProduct,
            'dbConferenceInputs' => $dbConferenceInputs,
            'conferenceInputForm' => $conferenceInputForm,
            'serviceTypes' => ConferenceInput::getServiceTypes(),
            'return' => $return,
            'message' => $this->getFeedbackMessage()
        ]);
    }

    public function inputProducts(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));
        $this->addData();

        if(!$dbConference = (new Conference())->findById(intval($data['conference_id']))) {
            $this->session->setFlash('error', _('A conferência não foi encontrada!'));
            $this->redirect('user.conference.index');
        } elseif($dbConference->isFinished()) {
            $this->session->setFlash('error', _('Esta conferência já foi finalizada!'));
            $this->redirect('user.conference.index');
        }

        if($dbOperation = $dbConference->operation()) {
            $dbOperation->provider();
        }

        if($dbConferenceInputs = $dbConference->conferenceInputs()) {
            $dbConferenceInputs = ConferenceInput::withProduct($dbConferenceInputs);
        }

        $dbConference->created_at = $dbConference->getCreatedAtDateTime()->format('d/m/Y');

        $this->render('user/conference/input-products', [
            'dbConference' => $dbConference,
            'dbOperation' => $dbOperation,
            'dbConferenceInputs' => $dbConferenceInputs,
            'message' => $this->getFeedbackMessage()
        ]);
    }

    public function separation(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));
        $this->addData();

        $nextStep = ConferenceSeparationForm::STEP_EAN;
        $previousStep = 0;

        $CSF = (new ConferenceSeparationForm())->loadData([
            'address' => $data['address'] ? $data['address'] : null,
            'ean' => $data['ean'] ? $data['ean'] : null,
            'amount' => $data['amount'] ? $data['amount'] : null,
            'dispatch_dock' => $data['dispatch_dock'] ? $data['dispatch_dock'] : null,
            'step' => intval($data['step']),
            'has_ean' => $data['has_ean'] ? true : false,
            'has_amount' => $data['has_amount'] ? true : false,
            'has_dock' => $data['has_dock'] ? true : false
        ]);

        if($CSF->isOnEAN() || $CSF->isOnAmount() || $CSF->isOnDock()) {
            if(!$CSF->getByEAN()) {
                $this->session->setFlash('error', ErrorMessages::form());
                $nextStep = ConferenceSeparationForm::STEP_EAN;
                $previousStep = 0;
            } else {
                $nextStep = ConferenceSeparationForm::STEP_AMOUNT;
                $previousStep = 0;
            }
        }

        if($CSF->isOnAmount() || $CSF->isOnDock()) {
            if(!$CSF->validateAmount()) {
                $this->session->setFlash('error', ErrorMessages::form());
                $nextStep = ConferenceSeparationForm::STEP_AMOUNT;
                $previousStep = ConferenceSeparationForm::STEP_EAN;
            } else {
                $nextStep = ConferenceSeparationForm::STEP_DOCK;
                $previousStep = ConferenceSeparationForm::STEP_EAN;
            }
        }

        if($this->request->isPost() && $CSF->isOnDock()) {
            if(!$CSF->validateDock()) {
                $this->session->setFlash('error', ErrorMessages::form());
                $nextStep = ConferenceSeparationForm::STEP_DOCK;
                $previousStep = ConferenceSeparationForm::STEP_AMOUNT;
            } else {
                $CSF->separationEAN->loadData([
                    'separation_usu_id' => $this->session->getAuth()->id,
                    'address' => $CSF->address,
                    'sep_amount' => $CSF->amount,
                    'dispatch_dock' => $CSF->dispatch_dock
                ]);

                if(!$CSF->separationEAN->setAsSeparated()->save()) {
                    $this->session->setFlash('error', ErrorMessages::requisition());
                } else {
                    $palletFromTo = (new PalletFromTo())->loadData([
                        'usu_id' => $this->session->getAuth()->id,
                        'amount' => $CSF->amount,
                        'a_type' => $CSF->separationEAN->a_type,
                        'from_pal_id' => $CSF->pallet->id
                    ]);
    
                    if(!$palletFromTo->save()) {
                        $this->session->setFlash('error', ErrorMessages::requisition());
                    }

                    $this->session->setFlash('success', _('A quantidade foi separada com sucesso!'));
                    $this->redirect('user.conference.separation');
                }
            }
        }

        $this->render('user/conference/separation', [
            'CSF' => $CSF,
            'nextStep' => $nextStep,
            'previousStep' => $previousStep,
            'message' => $this->getFeedbackMessage()
        ]);
    }

    public function expedition(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));
        $this->addData();

        $nextStep = ConferenceExpeditionForm::STEP_EAN;
        $previousStep = 0;

        $CEF = (new ConferenceExpeditionForm())->loadData([
            'sep_id' => $data['sep_id'] ? $data['sep_id'] : null,
            'ean' => $data['ean'] ? $data['ean'] : null,
            'amount' => $data['amount'] ? $data['amount'] : null,
            'step' => intval($data['step']),
            'has_ean' => $data['has_ean'] ? true : false,
            'has_completion' => $data['has_completion'] ? true : false
        ]);

        if($CEF->isOnEAN() || $CEF->isOnCompletion()) {
            if(!$CEF->getByEAN()) {
                $this->session->setFlash('error', ErrorMessages::form());
                $nextStep = ConferenceExpeditionForm::STEP_EAN;
                $previousStep = 0;
            } else {
                $nextStep = ConferenceExpeditionForm::STEP_COMPLETION;
                $previousStep = 0;
            }
        }

        if($this->request->isPost() && $CEF->isOnCompletion()) {
            if(!$CEF->validateCompletion()) {
                $this->session->setFlash('error', ErrorMessages::form());
                $nextStep = ConferenceExpeditionForm::STEP_COMPLETION;
                $previousStep = ConferenceExpeditionForm::STEP_EAN;
            } else {
                $CEF->separationEAN->loadData([
                    'conf_usu_id' => $this->session->getAuth()->id,
                    'conf_amount' => $CEF->amount
                ]);

                if(!$CEF->separationEAN->setAsChecked()->save()) {
                    $this->session->setFlash('error', ErrorMessages::requisition());
                } else {
                    $this->session->setFlash('success', _('A conferência de expedição foi realizada com sucesso!'));
                    $this->redirect('user.conference.expedition');
                }
            }
        }

        $this->render('user/conference/expedition', [
            'CEF' => $CEF,
            'nextStep' => $nextStep,
            'previousStep' => $previousStep,
            'message' => $this->getFeedbackMessage()
        ]);
    }

    public function loading(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_DEFAULT));
        $this->addData();

        $nextStep = ConferenceLoadingForm::STEP_SEPARATION_ID;
        $previousStep = 0;

        $CLF = (new ConferenceLoadingForm())->loadData([
            'sep_id' => $data['sep_id'] ? $data['sep_id'] : null,
            'plate' => $data['plate'] ? $data['plate'] : null,
            'dock' => $data['dock'] ? $data['dock'] : null,
            'step' => intval($data['step']),
            'has_sep_id' => $data['has_sep_id'] ? true : false,
            'has_completion' => $data['has_completion'] ? true : false
        ]);

        if($CLF->isOnSeparationId() || $CLF->isOnCompletion()) {
            if(!$CLF->getBySeparationId()) {
                $this->session->setFlash('error', ErrorMessages::form());
                $nextStep = ConferenceLoadingForm::STEP_SEPARATION_ID;
                $previousStep = 0;
            } else {
                $nextStep = ConferenceLoadingForm::STEP_COMPLETION;
                $previousStep = 0;
            }
        }

        if($this->request->isPost() && $CLF->isOnCompletion()) {
            if(!$CLF->validateCompletion()) {
                $this->session->setFlash('error', ErrorMessages::form());
                $nextStep = ConferenceLoadingForm::STEP_COMPLETION;
                $previousStep = ConferenceLoadingForm::STEP_EAN;
            } else {
                $CLF->separation->loadData([
                    'loading_usu_id' => $this->session->getAuth()->id,
                    'plate' => $CLF->plate,
                    'dock' => $CLF->dock
                ]);

                if(!$CLF->separation->setAsInLoading()->save()) {
                    $this->session->setFlash('error', ErrorMessages::requisition());
                } else {
                    $this->session->setFlash(
                        'success', 
                        sprintf(_('A lista de separação de ID %s foi carregada com sucesso!'), $CLF->separation->id)
                    );
                    $this->redirect('user.conference.loading');
                }
            }
        }

        $this->render('user/conference/loading', [
            'CLF' => $CLF,
            'nextStep' => $nextStep,
            'previousStep' => $previousStep,
            'message' => $this->getFeedbackMessage()
        ]);
    }

    private function getFeedbackMessage(): ?array 
    {
        if($message = $this->session->getFlash('error')) {
            return [
                'type' => 'error',
                'message' => $message
            ];
        } elseif($message = $this->session->getFlash('success')) {
            return [
                'type' => 'success',
                'message' => $message
            ];
        }

        return null;
    }
}