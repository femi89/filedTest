<?php

use App\Events\SendWelcomeEmail;
use App\Mail\FeedBackMail;
use App\Mail\RequestPasswordResetLinkMail;
use App\Mail\WelcomeMail;
use App\User;
use Dingo\Api\Routing\Router;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    $api->get('test/send-mail', function () {
        $user = User::query()->where('username', 'super_admin')->first();
        $activation_link = Hash::make('welcome');
//        event(new SendWelcomeEmail($user, $activation_link));

        $activation_link = url('/activate/' . $activation_link);
//        Mail::to('gemdajs@gmail.com')->sendNow(new RequestPasswordResetLinkMail($user, $activation_link));
        Mail::to('kenny@ksolutionsng.com')->sendNow(new FeedBackMail($user, "This is the message"));
//        Mail::to('nasirudeen.lasisi@ilortech.com')->sendNow(new RequestPasswordResetLinkMail($user, $activation_link));
        return 'Done';
    });
    $api->get('registration/bpp-api/{rcNumber}', function ($rcNumber) {
        $curl = curl_init();
        $url = "http://federalcontractors.bpp.gov.ng/api/v1/contractors/?query=$rcNumber&page=1&rows=1&sidx=companyName&sord=asc";
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set Here Your Requesred Headers
                'Content-Type: application/json',
                'Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjM2ODU2NDg5MTYsImlhdCI6MTUzODE2NTI2OSwidXNlcm5hbWUiOiJlMTQ1MDk3YS1iYzk0LTRhN2MtODM4ZS02YTlkOTIxOTJmYmUifQ.3_yYP9ZsJUTXb5gaA8N7K3E-6LGTuzlguK-XspCOH2I'
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return response($err . "", 500);
        } else {
            return response()->json(json_decode($response));
        }
//            return $response;
    });
    $api->group(['prefix' => 'auth'], function (Router $api) {
        //authentication routes
        $api->get('vendor/signup', 'App\Api\Controllers\AuthController@showVendorSignUp');
        $api->post('vendor/signup', 'App\Api\Controllers\AuthController@vendorSignUp');

        $api->get('user-exists/email/{email}', 'App\Api\Controllers\AuthController@getUserByEmail');
        $api->get('user-exists/username/{username}', 'App\Api\Controllers\AuthController@getUserByUsername');

        $api->get('internal/signup', 'App\Api\Controllers\AuthController@showInternalUserSignUp');
        $api->post('internal/signup', 'App\Api\Controllers\AuthController@signUp');

        $api->post('login', 'App\Api\Controllers\AuthController@login');

        $api->get('activate/{activation_token}', 'App\Api\Controllers\AuthController@getAccountActivation');

        $api->group(['prefix' => 'password'], function (Router $api) {
            //password reset routes
            $api->post('recovery', 'App\Api\Controllers\AuthController@sendResetEmail');
            $api->get('reset/{reset_token}', 'App\Api\Controllers\ResetPasswordController@getResetPassword');
            $api->post('new', 'App\Api\Controllers\ResetPasswordController@doResetPassword');
        });

        $api->get('is-taken/{field}/{value}', 'App\Api\Controllers\AuthController@checkFormFields');
    });
    $api->get('states', 'App\Api\Controllers\StateController@index');

    $api->get('public/faqs', 'App\Api\Controllers\FaqController@publicIndex');
    $api->post('public/feedback', 'App\Api\Controllers\FeedbackController@store');


    //requires sign-in, all routes here need token to get access
    $api->group(['middleware' => 'jwt.auth'], function (Router $api) {

        $api->get('users/{username}/profile', 'App\Api\Controllers\UserController@getUserProfileData');

        $api->group(['prefix' => 'chats'], function (Router $api) {
            $api->post('group', 'App\Api\Controllers\ChatController@store');
            $api->get('group', 'App\Api\Controllers\ChatController@fetchParticipants');
            $api->get('groups', 'App\Api\Controllers\ChatController@getGroups');
            $api->post('messages', 'App\Api\Controllers\ChatController@sendMessage');
            $api->post('messages/batch', 'App\Api\Controllers\ChatController@sendBatchMessage');
            $api->get('messages/{group_id}', 'App\Api\Controllers\ChatController@fetchMessages');

            $api->get('authenticate', 'App\Api\Controllers\ChatController@authenticateUser');
        });

        $api->get('dashboard/data', 'App\Api\Controllers\DashboardController@getData');
        $api->get('dashboard/mobile/data', 'App\Api\Controllers\DashboardController@getMobileData');

        $api->group(['prefix' => 'all-users'], function (Router $api) {
//            $api->get( 'users', 'App\Api\Controllers\UserController@index' );
            $api->get('', 'App\Api\Controllers\UserController@getAuthUserDetail');
            $api->get('{id}', 'App\Api\Controllers\UserController@getAllUserDetail')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
            $api->put('{id}', 'App\Api\Controllers\UserController@updateAllUserData')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
            $api->put('', 'App\Api\Controllers\UserController@updateAllUserData');
        });

        $api->get('users/me', 'App\Api\Controllers\UserController@showAuthenticatedUser');
        $api->put('change-password', 'App\Api\Controllers\UserController@changePassword');

        //$api->group(['middleware' => 'permission:view_goods_category'], function (Router $api) {
        $api->get('goods-category', 'App\Api\Controllers\CategoryController@index');
        $api->get('paged/goods-category', 'App\Api\Controllers\CategoryController@pagedIndex');
        //});

        $api->get('/all_goods/paged', 'App\Api\Controllers\AllGoodsController@pagedIndex');
        $api->group(['prefix' => 'all_goods'], function (Router $api) {
            //password reset routes
            //page_all_goods
            $api->post('/', 'App\Api\Controllers\AllGoodsController@store');
            $api->get('/', 'App\Api\Controllers\AllGoodsController@index');
            $api->get('/{id}', 'App\Api\Controllers\AllGoodsController@show')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
            $api->put('/{id}', 'App\Api\Controllers\AllGoodsController@update')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
            $api->delete('/{id}', 'App\Api\Controllers\AllGoodsController@destroy')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
            $api->put('/clear', 'App\Api\Controllers\AllGoodsController@deleteMany');
            $api->put('/approve/{id}', 'App\Api\Controllers\AllGoodsController@approval')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });

        $api->get('refresh', [
            'middleware' => 'tokenrefresh', 'as' => 'refresh',
            function () {
                return response()->json([
                    'message' => 'Token refreshed. Check response headers for new token!'
                ]);
            }
        ]);

        /* $api->group(['middleware' => 'permission:vendor_update_own_account'], function (Router $api) {
            $api->put('vendor/account', 'App\Api\Controllers\VendorController@update');
        }); */
        $api->put('vendor/account', 'App\Api\Controllers\VendorController@update');

        $api->resource('goods', 'App\Api\Controllers\GoodController', [
            'only' => ['index', 'store']
        ]);
        $api->group(['middleware' => 'owner:goods'], function (Router $api) {
            $api->resource('goods', 'App\Api\Controllers\GoodController', [
                'except' => ['index', 'store']
            ]);
        });

        $api->group(['middleware' => ['role:internal_user|admin|super_admin']], function (Router $api) {
            $api->get('/search/goods', 'App\Api\Controllers\GoodController@search');
            $api->get('/goods/{id}/trend', 'App\Api\Controllers\GoodController@trend');
        });

        $api->post('goods/batch-upload', 'App\Api\Controllers\GoodController@massImport');
        $api->get('goods/batch-upload/prices', 'App\Api\Controllers\GoodController@getMyGoods');
        $api->post('goods/batch-upload/sample', 'App\Api\Controllers\GoodController@sampleImportGoods');
        $api->get('goods/latest/{amount}', 'App\Api\Controllers\GoodController@latest');

        $api->group(['middleware' => ['permission:view_goods_by_other_users']], function (Router $api) {
            $api->get('user/{id}/goods', 'App\Api\Controllers\GoodController@getGoodByUserId')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });

        $api->resource('warehouses', 'App\Api\Controllers\WarehouseController', [
            'only' => ['index', 'store']
        ]);
        $api->group(['middleware' => 'owner:warehouses'], function (Router $api) {
            $api->resource('warehouses', 'App\Api\Controllers\WarehouseController', [
                'except' => ['index', 'store']
            ]);
        });
        $api->get('warehouses/latest/{amount}', 'App\Api\Controllers\WarehouseController@latest');
        $api->group(['middleware' => ['permission:view_warehouses_by_other_users|add_warehouses|view_warehouses']], function (Router $api) {
            $api->get('user/{id}/warehouses', 'App\Api\Controllers\WarehouseController@getWarehouseByUserId')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
            $api->get('vendor/{id}/warehouses', 'App\Api\Controllers\WarehouseController@getWarehouseByVendorId')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });

        $api->post('goods/add-picture', 'App\Api\Controllers\GoodController@addPicture');
        $api->delete('goods/pictures/{gId}/{pId}', 'App\Api\Controllers\GoodController@deletePicture')->where(['gId' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}', 'pId' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);

        $api->resource('pictures', 'App\Api\Controllers\PictureController', [
            'only' => ['store']
        ]);
        $api->post('uploads', 'App\Api\Controllers\PictureController@storeUpload');
        $api->delete('pictures/{picturePath}', 'App\Api\Controllers\PictureController@destroy')->where('picturePath', '(.*)');

        $api->group(['middleware' => ['role:internal_user|admin|super_admin']], function (Router $api) {
            $api->get('internal/account/edit', 'App\Api\Controllers\InternalUserController@show');
            $api->put('internal/account/update', 'App\Api\Controllers\InternalUserController@update');

            $api->get('survey-reports', 'App\Api\Controllers\SurveyReportController@indexNoPagination');
            $api->get('survey-reports/paged', 'App\Api\Controllers\SurveyReportController@index');
            $api->get('survey-reports/{id}', 'App\Api\Controllers\SurveyReportController@show')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
            $api->get('{userId}/survey-reports', 'App\Api\Controllers\SurveyReportController@showByUser')->where(['userId' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);

            $api->resource('comments', 'App\Api\Controllers\CommentController', [
                'only' => ['store', 'destroy']
            ]);
        });

        $api->group(['middleware' => ['permission:create_survey_report']], function (Router $api) {
            $api->post('survey-reports', 'App\Api\Controllers\SurveyReportController@store');
        });

        $api->group(['middleware' => ['permission:delete_survey_report']], function (Router $api) {
            $api->delete('survey-reports/{id}', 'App\Api\Controllers\SurveyReportController@destroy')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:update_survey_report']], function (Router $api) {
            $api->put('survey-reports/{id}', 'App\Api\Controllers\SurveyReportController@update')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:review_survey_report']], function (Router $api) {
            $api->put('survey-reports/review/{id}', 'App\Api\Controllers\SurveyReportController@review')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:approve_survey_report']], function (Router $api) {
            $api->put('survey-reports/approve/{id}', 'App\Api\Controllers\SurveyReportController@approve')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:view_internal_users|change_others_password|update_internal_users']], function (Router $api) {
//            $api->get( 'users', 'App\Api\Controllers\UserController@index' );
            $api->get('users/{id}', 'App\Api\Controllers\UserController@getUserDetailForAdmin')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
            $api->get('users/internal-users', 'App\Api\Controllers\UserController@getInternalUsers');
            $api->get('users/internal-users/single', 'App\Api\Controllers\UserController@getInternalUsersNoPagination');
            $api->get('users/all', 'App\Api\Controllers\UserController@getAllNoPaginate');
        });

        $api->group(['middleware' => ['permission:view_vendors|change_others_password|update_vendors']], function (Router $api) {
            $api->get('users/vendors', 'App\Api\Controllers\UserController@getVendors');
            $api->get('users/vendors/single', 'App\Api\Controllers\UserController@getVendorsAll');
            $api->get('users/vendors/bpp', 'App\Api\Controllers\UserController@getBPPVendors');
        });

        $api->group(['middleware' => ['permission:lock_users|change_others_password|update_vendors|update_internal_users']], function (Router $api) {
            $api->put('users/lock/{id}', 'App\Api\Controllers\UserController@lockUserById')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:view_locked_users|change_others_password|update_vendors|update_internal_users']], function (Router $api) {
            $api->get('users/locked', 'App\Api\Controllers\UserController@getLockedUsers');
            $api->get('users/status/{user_id}', 'App\Api\Controllers\UserController@isLocked')->where(['user_id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:delete_users']], function (Router $api) {
            $api->delete('users/{id}', 'App\Api\Controllers\UserController@destroy')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:change_others_password']], function (Router $api) {
            $api->put('users/change-password/{id}', 'App\Api\Controllers\UserController@adminChangePassword')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:create_internal_users']], function (Router $api) {
            $api->get('users/internal/create', 'App\Api\Controllers\InternalUserController@showAdminCreate');
            $api->post('users/internal', 'App\Api\Controllers\InternalUserController@adminStore');
        });

        $api->group(['middleware' => ['permission:create_vendors']], function (Router $api) {
            $api->get('users/vendors/create', 'App\Api\Controllers\VendorController@showAdminCreate');
            $api->post('users/vendors', 'App\Api\Controllers\VendorController@adminStore');
        });

        $api->group(['middleware' => ['update_user_record:update_vendors']], function (Router $api) {
            $api->put('users/vendors/{id}', 'App\Api\Controllers\VendorController@adminUpdate')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
            $api->get('users/vendors/{id}', 'App\Api\Controllers\UserController@getVendorDetailForAdmin')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['update_user_record:update_internal_users']], function (Router $api) {
            $api->put('users/internal/{id}', 'App\Api\Controllers\InternalUserController@adminUpdate')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
            $api->get('users/internal/{id}', 'App\Api\Controllers\UserController@getInternalUserDetailForAdmin')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:view_goods_category|update_goods_category']], function (Router $api) {
            $api->get('goods-category/{id}', 'App\Api\Controllers\CategoryController@show')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:create_goods_category']], function (Router $api) {
            $api->post('goods-category', 'App\Api\Controllers\CategoryController@store');
        });

        $api->group(['middleware' => ['permission:update_goods_category']], function (Router $api) {
            $api->put('goods-category/{id}', 'App\Api\Controllers\CategoryController@update');
        });

        $api->group(['middleware' => ['permission:delete_goods_category']], function (Router $api) {
            $api->delete('goods-category/{id}', 'App\Api\Controllers\CategoryController@destroy')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
            $api->post('goods-category/delete', 'App\Api\Controllers\CategoryController@deleteMulti');
        });

        $api->group(['middleware' => ['permission:update_job_titles']], function (Router $api) {
            $api->put('job-titles/{id}', 'App\Api\Controllers\InternalUserStatusController@update')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:create_job_titles']], function (Router $api) {
            $api->post('job-titles', 'App\Api\Controllers\InternalUserStatusController@update');
        });

        $api->group(['middleware' => ['permission:delete_job_titles']], function (Router $api) {
            $api->delete('job-titles/{id}', 'App\Api\Controllers\InternalUserStatusController@destroy')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:view_feedbacks|respond_to_feedbacks|delete_feedbacks|send_emails']], function (Router $api) {
            $api->get('feedbacks', 'App\Api\Controllers\FeedbackController@index');
            $api->get('feedbacks/{id}', 'App\Api\Controllers\FeedbackController@show')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:respond_to_feedbacks']], function (Router $api) {
            $api->post('response', 'App\Api\Controllers\ResponseController@store');
        });
        $api->group(['middleware' => ['permission:view_responses']], function (Router $api) {
            $api->get('response', 'App\Api\Controllers\ResponseController@index');
            $api->get('response/{id}', 'App\Api\Controllers\ResponseController@show')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });
        $api->group(['middleware' => ['permission:update_responses']], function (Router $api) {
            $api->put('response/{id}', 'App\Api\Controllers\ResponseController@update')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });
        $api->group(['middleware' => ['permission:delete_responses']], function (Router $api) {
            $api->delete('response/{id}', 'App\Api\Controllers\ResponseController@destroy')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:delete_feedbacks']], function (Router $api) {
            $api->delete('feedbacks/{id}', 'App\Api\Controllers\FeedbackController@destroy')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:view_vendors_categories']], function (Router $api) {
            $api->get('vendor-category/all', 'App\Api\Controllers\VendorCategoryController@getAllForAdmin');
        });

        $api->group(['middleware' => ['permission:send_emails']], function (Router $api) {
            $api->post('email/send', 'App\Api\Controllers\EmailController@send');
        });

        $api->get('logout', 'App\Api\Controllers\LogoutController@logoutUser');

        $api->group(['middleware' => ['permission:manage_states']], function (Router $api) {
            $api->post('states', 'App\Api\Controllers\StateController@create');
            $api->put('states/{id}', 'App\Api\Controllers\StateController@update')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
            $api->delete('states/{id}', 'App\Api\Controllers\StateController@destroy')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);

            $api->post('cities', 'App\Api\Controllers\CityController@create');
            $api->put('cities/{id}', 'App\Api\Controllers\CityController@update')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
            $api->delete('cities/{id}', 'App\Api\Controllers\CityController@destroy')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        //sam
        $api->group(['middleware' => ['role:super_admin']], function (Router $api) {
            $api->resource('permissions', 'App\Api\Controllers\PermissionController', [
                'except' => ['update', 'destroy']
            ]);
        });

        $api->group(['middleware' => ['permission:add_institutions|add_institution']], function (Router $api) {
            $api->post('institution', 'App\Api\Controllers\InstitutionController@store');
//            $api->resource('institution', 'App\Api\Controllers\InstitutionController', [
//                'only' => ['store']
//            ]);
        });
        $api->group(['middleware' => ['permission:edit_institutions|edit_institution']], function (Router $api) {
            $api->put('institution/{id}', 'App\Api\Controllers\InstitutionController@update');
//            $api->resource('institution', , [
//                'only' => ['update']
//            ]);
        });
        $api->group(['middleware' => ['permission:delete_institutions|delete_institution']], function (Router $api) {
            $api->delete('institution/{id}', 'App\Api\Controllers\InstitutionController@destory');
//            $api->resource('institution', 'App\Api\Controllers\InstitutionController', [
//                'only' => ['destroy']
//            ]);
        });
        $api->group(['middleware' => ['permission:add_parastatals|add_parastatal']], function (Router $api) {
            $api->post('parastatal', 'App\Api\Controllers\ParastatalController@store');
//            $api->resource('parastatal', 'App\Api\Controllers\ParastatalController', [
//                'only' => ['store']
//            ]);
        });
        $api->group(['middleware' => ['permission:edit_parastatals|edit_parastatal']], function (Router $api) {
            $api->put('parastatal', 'App\Api\Controllers\ParastatalController@update');
//            $api->resource('parastatal', 'App\Api\Controllers\ParastatalController', [
//                'only' => ['update']
//            ]);
        });
        $api->group(['middleware' => ['permission:delete_parastatals|delete_parastatal']], function (Router $api) {
            $api->delete('parastatal/{id}', 'App\Api\Controllers\ParastatalController@destroy');
//            $api->resource('parastatal', 'App\Api\Controllers\ParastatalController', [
//                'only' => ['destroy']
//            ]);
        });

        $api->group(['middleware' => ['role:admin|super_admin']], function (Router $api) {
            //sam
            $api->resource('roles', 'App\Api\Controllers\RoleController');

            $api->get('settings', 'App\Api\Controllers\SettingController@index');
            $api->put('settings/{id}', 'App\Api\Controllers\SettingController@update')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');

            $api->resource('message-templates', 'App\Api\Controllers\MessageTemplateController', [
                'except' => ['store', 'destroy']
            ]);

            $api->get('events', 'App\Api\Controllers\EventController@index');
            $api->delete('events/{id}', 'App\Api\Controllers\EventController@destroy')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
            $api->delete('events/range', 'App\Api\Controllers\EventController@destroyByDates');

            $api->resource('faqs', 'App\Api\Controllers\FaqController', [
                'only' => ['store', 'update', 'destroy', 'show', 'index']
            ]);

            //sam
            $api->resource('goods-review', 'App\Api\Controllers\GoodReviewController', [
                'only' => ['update', 'destroy', 'show']
            ]);
        });

        //sam
        $api->get('institution', 'App\Api\Controllers\InstitutionController@index');
        $api->get('institution/{id}', 'App\Api\Controllers\InstitutionController@show');
        $api->get('parastatal', 'App\Api\Controllers\ParastatalController@index');
        $api->get('parastatal/{id}', 'App\Api\Controllers\ParastatalController@show');
//        $api->resource('institution', 'App\Api\Controllers\InstitutionController', [
//            'only' => ['index', 'show']
//        ]);
//        $api->resource('parastatal', 'App\Api\Controllers\ParastatalController', [
//            'only' => ['index', 'show']
//        ]);

        //sam
        $api->resource('goods-review', 'App\Api\Controllers\GoodReviewController', [
            'except' => ['update', 'destroy', 'show']
        ]);

        //sam
        $api->group(['middleware' => ['permission:create_notifications']], function (Router $api) {
            $api->resource('notifications', 'App\Api\Controllers\NotificationController', [
                'only' => ['store']
            ]);
        });
        $api->group(['middleware' => ['permission:delete_notifications']], function (Router $api) {
            $api->resource('notifications', 'App\Api\Controllers\NotificationController', [
                'only' => ['destroy']
            ]);
        });
        $api->resource('notifications', 'App\Api\Controllers\SysNotificationController', [
            'only' => ['index', 'show']
        ]);
        $api->get('notifications/unread', 'App\Api\Controllers\SysNotificationController@getUnread');

        //user request account cancellation. mainly used by vendors to cancel their registration
        $api->post('user/cancel-account', 'App\Api\Controllers\UserController@cancelAccount')->name('user.cancel_account');

        //documents
        $api->post('documents', 'App\Api\Controllers\DocumentController@store');
        $api->get('documents', 'App\Api\Controllers\DocumentController@index');
        $api->get('documents/{documentUrl}', 'App\Api\Controllers\DocumentController@show')->where('documentUrl', '(.*)');
        $api->delete('documents/delete/{documentPath}', 'App\Api\Controllers\DocumentController@destroy')->where('documentPath', '(.*)');
        $api->delete('documents/batch-delete', 'App\Api\Controllers\DocumentController@batchDestroy');

        //doc-formats
        $api->group(['middleware' => ['permission:create_document_formats']], function (Router $api) {
            $api->get('doc-formats', 'App\Api\Controllers\DocumentFormatsController@index');
            $api->get('doc-formats/allowed-formats', 'App\Api\Controllers\DocumentFormatsController@getAllowed');
            $api->post('doc-formats', 'App\Api\Controllers\DocumentFormatsController@store');
        });
        $api->group(['middleware' => ['permission:edit_document_formats']], function (Router $api) {
            $api->put('doc-formats/{id}', 'App\Api\Controllers\DocumentFormatsController@update')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
            $api->post('doc-formats/allow-marked', 'App\Api\Controllers\DocumentFormatsController@batchAllowDocumentFormats');
            $api->get('doc-formats/{id}', 'App\Api\Controllers\DocumentFormatsController@show')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        $api->group(['middleware' => ['permission:delete_document_formats']], function (Router $api) {
            $api->delete('doc-formats/{id}', 'App\Api\Controllers\DocumentFormatsController@destroy')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        //doc-types
        $api->group(['middleware' => ['permission:create_document_types']], function (Router $api) {
            $api->post('doc-types', 'App\Api\Controllers\DocumentTypeController@store');
            $api->get('doc-types', 'App\Api\Controllers\DocumentTypeController@index');
        });
        $api->group(['middleware' => ['permission:edit_document_types']], function (Router $api) {
            $api->put('doc-types/{id}', 'App\Api\Controllers\DocumentTypeController@update')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
            $api->get('doc-types/{id}', 'App\Api\Controllers\DocumentTypeController@show')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });
        $api->group(['middleware' => ['permission:delete_document_types']], function (Router $api) {
            $api->delete('doc-types/{id}', 'App\Api\Controllers\DocumentTypeController@destroy')->where(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}']);
        });

        //workflow management
        //task types
        $api->group(['middleware' => ['permission:create_task_types']], function (Router $api) {
            $api->post('task-types', 'App\Api\Controllers\TaskTypeController@store');
        });
        $api->group(['middleware' => ['permission:delete_task_types']], function (Router $api) {
            $api->delete('task-types/{id}', 'App\Api\Controllers\TaskTypeController@destroy')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });
        $api->group(['middleware' => ['permission:view_task_types']], function (Router $api) {
            $api->get('task-types', 'App\Api\Controllers\TaskTypeController@index');
            $api->get('task-types/{id}', 'App\Api\Controllers\TaskTypeController@show')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });
        $api->group(['middleware' => ['permission:edit_task_types']], function (Router $api) {
            $api->put('task-types/{id}', 'App\Api\Controllers\TaskTypeController@update')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });

        //approvals
        $api->group(['middleware' => ['permission:create_approvals']], function (Router $api) {
            $api->post('approvals', 'App\Api\Controllers\ApprovalController@store');
        });
        $api->group(['middleware' => ['permission:delete_approvals']], function (Router $api) {
            $api->delete('approvals/{id}', 'App\Api\Controllers\ApprovalController@destroy')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });
        $api->group(['middleware' => ['permission:view_approvals']], function (Router $api) {
            $api->get('approvals', 'App\Api\Controllers\ApprovalController@index');
            $api->get('approvals/{id}', 'App\Api\Controllers\ApprovalController@show')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });
        $api->group(['middleware' => ['permission:edit_approvals']], function (Router $api) {
            $api->put('approvals/{id}', 'App\Api\Controllers\ApprovalController@update')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });

        //approval details
        $api->group(['middleware' => ['permission:create_approval_details']], function (Router $api) {
            $api->post('approval-details/create', 'App\Api\Controllers\ApprovalDetailController@store');
        });
        $api->group(['middleware' => ['permission:delete_approval_details']], function (Router $api) {
            $api->delete('approval-details/delete/{id}', 'App\Api\Controllers\ApprovalDetailController@destroy')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });
        $api->group(['middleware' => ['permission:view_approval_details']], function (Router $api) {
            $api->get('approval-details', 'App\Api\Controllers\ApprovalDetailController@index');
            $api->get('approval-details/show/{id}', 'App\Api\Controllers\ApprovalDetailController@show')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });
        $api->group(['middleware' => ['permission:edit_approval_details']], function (Router $api) {
            $api->put('approval-details/update/{id}', 'App\Api\Controllers\ApprovalDetailController@update')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });

        $api->put('approval-details/approve/{approvalDetailID}', 'App\Api\Controllers\ApprovalDetailController@approveAnApprovalDetail')->middleware('is_user_approver')->where('approvalDetailId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        $api->put('approval-details/disapprove/{approvalDetailID}', 'App\Api\Controllers\ApprovalDetailController@disapproveAnApprovalDetail')->middleware('is_user_approver')->where('approvalDetailId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');

        //tasks (approval requests)
        $api->group(['middleware' => ['permission:create_approval_requests']], function (Router $api) {
            $api->post('approval-requests', 'App\Api\Controllers\TaskController@store');
        });
        $api->group(['middleware' => ['permission:delete_approval_requests']], function (Router $api) {
            $api->delete('approval-requests/{id}', 'App\Api\Controllers\TaskController@destroy')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });
        $api->group(['middleware' => ['permission:view_approval_requests']], function (Router $api) {
            $api->get('approval-requests', 'App\Api\Controllers\TaskController@index');
            $api->get('approval-requests/{id}', 'App\Api\Controllers\TaskController@show')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });

        //grades
        $api->group(['middleware' => ['permission:create_grades|update_grades|delete_grades']], function (Router $api) {
            $api->get('grades', 'App\Api\Controllers\GradeController@index');
        });

        $api->group(['middleware' => ['permission:update_grades']], function (Router $api) {
            $api->get('grades/{id}', 'App\Api\Controllers\GradeController@show');
        });

        $api->group(['middleware' => ['permission:create_grades']], function (Router $api) {
            $api->post('grades', 'App\Api\Controllers\GradeController@store');
        });
        $api->group(['middleware' => ['permission:update_grades']], function (Router $api) {
            $api->put('grades/{id}', 'App\Api\Controllers\GradeController@update');
        });
        $api->group(['middleware' => ['permission:delete_grades']], function (Router $api) {
            $api->delete('grades/{id}', 'App\Api\Controllers\GradeController@destroy')->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        });

        $api->get('feedback-status', 'App\Api\Controllers\FeedbackController@getFeedbackStatus');
    });
});
