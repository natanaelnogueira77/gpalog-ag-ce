<?php

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'on' && $_SERVER['HTTP_HOST'] != 'localhost') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

if(file_exists($maintenance = __DIR__ . '/../maintenance.php')) {
    require $maintenance;
}

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../config/app.php';

$app->router->namespace('Src\App\Controllers\Auth');

$app->router->group(null);
$app->router->get('/', 'AuthController:index', 'home.index', \Src\App\Middlewares\GuestMiddleware::class);
$app->router->post('/', 'AuthController:index', 'home.index', \Src\App\Middlewares\GuestMiddleware::class);

$app->router->group('login');
$app->router->post('/expired', 'AuthController:expired', 'auth.expired');
$app->router->post('/check', 'AuthController:check', 'auth.check');

$app->router->group('entrar', \Src\App\Middlewares\GuestMiddleware::class);
$app->router->get('/', 'AuthController:index', 'auth.index');
$app->router->post('/', 'AuthController:index', 'auth.index');

$app->router->group('operacao/entrar', \Src\App\Middlewares\GuestMiddleware::class);
$app->router->get('/', 'OperatorLoginController:index', 'operatorLogin.index');
$app->router->post('/', 'OperatorLoginController:index', 'operatorLogin.index');
$app->router->post('/check', 'OperatorLoginController:check', 'operatorLogin.check');

$app->router->group('redefinir-senha', \Src\App\Middlewares\GuestMiddleware::class);
$app->router->get('/', 'ResetPasswordController:index', 'resetPassword.index');
$app->router->post('/', 'ResetPasswordController:index', 'resetPassword.index');
$app->router->get('/{code}', 'ResetPasswordController:verify', 'resetPassword.verify');
$app->router->post('/{code}', 'ResetPasswordController:verify', 'resetPassword.verify');

$app->router->group('logout', \Src\App\Middlewares\UserMiddleware::class);
$app->router->get('/', 'AuthController:logout', 'auth.logout');

$app->router->namespace('Src\App\Controllers');

$app->router->group('erro');
$app->router->get('/{code}', 'ErrorController:index', 'error.index');

$app->router->group('ml');
$app->router->post('/add', 'MediaLibraryController:add', 'mediaLibrary.add');
$app->router->get('/load', 'MediaLibraryController:load', 'mediaLibrary.load');
$app->router->delete('/delete', 'MediaLibraryController:delete', 'mediaLibrary.delete');

$app->router->group('language');
$app->router->get('/{lang}', 'LanguageController:index', 'language.index');

$app->router->namespace('Src\App\Controllers\Admin');

$app->router->group('admin', \Src\App\Middlewares\AdminMiddleware::class);
$app->router->get('/', 'AdminController:index', 'admin.index');
$app->router->put('/system', 'AdminController:system', 'admin.system');

$app->router->group('admin/usuarios', \Src\App\Middlewares\AdminMiddleware::class);
$app->router->get('/', 'UsersController:index', 'admin.users.index');
$app->router->post('/', 'UsersController:store', 'admin.users.store');
$app->router->get('/{user_id}', 'UsersController:edit', 'admin.users.edit');
$app->router->put('/{user_id}', 'UsersController:update', 'admin.users.update');
$app->router->delete('/{user_id}', 'UsersController:delete', 'admin.users.delete');
$app->router->get('/criar', 'UsersController:create', 'admin.users.create');
$app->router->get('/list', 'UsersController:list', 'admin.users.list');

$app->router->namespace('Src\App\Controllers\Web');

$app->router->group('contato');
$app->router->get('/', 'ContactController:index', 'contact.index');
$app->router->post('/', 'ContactController:index', 'contact.index');

$app->router->namespace('Src\App\Controllers\User');

$app->router->group('u', \Src\App\Middlewares\ADMUserMiddleware::class);
$app->router->get('/', 'UserController:index', 'user.index');

$app->router->group('u/editar', \Src\App\Middlewares\ADMUserMiddleware::class);
$app->router->get('/', 'EditController:index', 'user.edit.index');
$app->router->put('/', 'EditController:update', 'user.edit.update');

$app->router->group('u/operacoes', \Src\App\Middlewares\ADMUserMiddleware::class);
$app->router->get('/', 'OperationsController:index', 'user.operations.index');
$app->router->post('/', 'OperationsController:store', 'user.operations.store');
$app->router->get('/{operation_id}', 'OperationsController:show', 'user.operations.show');
$app->router->put('/{operation_id}', 'OperationsController:update', 'user.operations.update');
$app->router->delete('/{operation_id}', 'OperationsController:delete', 'user.operations.delete');
$app->router->post('/{operation_id}/create-conference', 'OperationsController:createConference', 'user.operations.createConference');
$app->router->get('/list', 'OperationsController:list', 'user.operations.list');
$app->router->get('/export', 'OperationsController:export', 'user.operations.export');

$app->router->group('u/ruas', \Src\App\Middlewares\ADMUserMiddleware::class);
$app->router->get('/', 'StreetsController:index', 'user.streets.index');
$app->router->post('/', 'StreetsController:store', 'user.streets.store');
$app->router->get('/{street_id}', 'StreetsController:show', 'user.streets.show');
$app->router->put('/{street_id}', 'StreetsController:update', 'user.streets.update');
$app->router->delete('/{street_id}', 'StreetsController:delete', 'user.streets.delete');
$app->router->get('/list', 'StreetsController:list', 'user.streets.list');
$app->router->get('/export', 'StreetsController:export', 'user.streets.export');

$app->router->group('u/produtos', \Src\App\Middlewares\ADMUserMiddleware::class);
$app->router->get('/', 'ProductsController:index', 'user.products.index');
$app->router->post('/', 'ProductsController:store', 'user.products.store');
$app->router->get('/{product_id}', 'ProductsController:show', 'user.products.show');
$app->router->put('/{product_id}', 'ProductsController:update', 'user.products.update');
$app->router->delete('/{product_id}', 'ProductsController:delete', 'user.products.delete');
$app->router->get('/list', 'ProductsController:list', 'user.products.list');
$app->router->post('/import', 'ProductsController:import', 'user.products.import');
$app->router->get('/export', 'ProductsController:export', 'user.products.export');

$app->router->group('u/fornecedores', \Src\App\Middlewares\ADMUserMiddleware::class);
$app->router->get('/', 'ProvidersController:index', 'user.providers.index');
$app->router->post('/', 'ProvidersController:store', 'user.providers.store');
$app->router->get('/{provider_id}', 'ProvidersController:show', 'user.providers.show');
$app->router->put('/{provider_id}', 'ProvidersController:update', 'user.providers.update');
$app->router->delete('/{provider_id}', 'ProvidersController:delete', 'user.providers.delete');
$app->router->get('/list', 'ProvidersController:list', 'user.providers.list');
$app->router->post('/import', 'ProvidersController:import', 'user.providers.import');
$app->router->get('/export', 'ProvidersController:export', 'user.providers.export');

$app->router->group('u/armazenagem', \Src\App\Middlewares\ADMUserMiddleware::class);
$app->router->get('/', 'StorageController:index', 'user.storage.index');
$app->router->get('/export', 'StorageController:export', 'user.storage.export');

$app->router->group('u/separacao', \Src\App\Middlewares\ADMUserMiddleware::class);
$app->router->get('/', 'SeparationsController:index', 'user.separations.index');
$app->router->post('/', 'SeparationsController:store', 'user.separations.store');
$app->router->post('/ean', 'SeparationEANsController:store', 'user.separationEANs.store');
$app->router->get('/ean/{se_id}', 'SeparationEANsController:show', 'user.separationEANs.show');
$app->router->put('/ean/{se_id}', 'SeparationEANsController:update', 'user.separationEANs.update');
$app->router->delete('/ean/{se_id}', 'SeparationEANsController:delete', 'user.separationEANs.delete');
$app->router->get('/ean/list', 'SeparationEANsController:list', 'user.separationEANs.list');
$app->router->get('/separacao/{separation_id}', 'SeparationsController:getPDF', 'user.separations.getPDF');
$app->router->get('/list', 'SeparationsController:list', 'user.separations.list');
$app->router->get('/tabela', 'SeparationsController:getSeparationTable', 'user.separations.getSeparationTable');
$app->router->get('/export', 'SeparationsController:export', 'user.separations.export');

$app->router->group('u/historico-entrada-saida', \Src\App\Middlewares\ADMUserMiddleware::class);
$app->router->get('/', 'InputOutputHistoryController:index', 'user.inputOutputHistory.index');
$app->router->get('/{conference_id}/input-pdf', 'InputOutputHistoryController:getInputPDF', 'user.inputOutputHistory.getInputPDF');
$app->router->get('/{conference_id}/output-pdf', 'InputOutputHistoryController:getOutputPDF', 'user.inputOutputHistory.getOutputPDF');
$app->router->get('/list', 'InputOutputHistoryController:list', 'user.inputOutputHistory.list');
$app->router->get('/export', 'InputOutputHistoryController:export', 'user.inputOutputHistory.export');

$app->router->group('u/conferencia', \Src\App\Middlewares\OperatorMiddleware::class);
$app->router->get('/', 'ConferenceController:index', 'user.conference.index');
$app->router->get('/entrada', 'ConferenceController:input', 'user.conference.input');
$app->router->get('/entrada/{conference_id}', 'ConferenceController:singleInput', 'user.conference.singleInput');
$app->router->post('/entrada/{conference_id}', 'ConferenceController:singleInput', 'user.conference.singleInput');
$app->router->get('/entrada/{conference_id}/produtos', 'ConferenceController:inputProducts', 'user.conference.inputProducts');
$app->router->get('/separacao', 'ConferenceController:separation', 'user.conference.separation');
$app->router->post('/separacao', 'ConferenceController:separation', 'user.conference.separation');
$app->router->get('/conferencia-de-expedicao', 'ConferenceController:expedition', 'user.conference.expedition');
$app->router->post('/conferencia-de-expedicao', 'ConferenceController:expedition', 'user.conference.expedition');
$app->router->get('/carregamento', 'ConferenceController:loading', 'user.conference.loading');
$app->router->post('/carregamento', 'ConferenceController:loading', 'user.conference.loading');

$app->run();