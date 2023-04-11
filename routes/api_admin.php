<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::get('permissions', 'PermissionController@getPermissions');

    Route::group(['middleware' => 'role:admin'], function () {

        Route::post('give-permission', 'ModeratorController@givePermission');
        Route::post('give-role', 'PermissionController@giveRole');

        /* Locale Crud */
        Route::prefix('locales')->group(function () {
            Route::controller('LanguageController')->group(function () {
                Route::post('/', 'addLocale');
                Route::put('/', 'updateLocale');
                Route::delete('{language}', 'destroy');
            });
        });

        /**
         * Categories Route Namespace
         */
        Route::group(['namespace' => 'Category'], function () {

            Route::prefix('categories')->group(function () {
                Route::controller('CategoryController')->group(function () {

                    Route::get('/', 'list');
                    Route::post('/', 'store');
                    Route::put('order', 'orderUpdate');
                    Route::delete('/', 'destroy');

                    Route::prefix('{category}')->group(function () {
                        Route::get('/', 'show');
                        Route::post('/', 'duplicateCategory');
                        Route::put('/', 'update');
                    });
                });
            });

            Route::prefix('filters')->group(function () {

                Route::controller('FilterController')->group(function () {

                    Route::get('/', 'list');
                    Route::post('/', 'store');
                    Route::put('/', 'update');

                    Route::prefix('{filter}')->group(function () {
                        Route::get('/', 'show');
                        Route::delete('/', 'destroy');
                        Route::delete('{filterAnswer}', 'answerDelete');
                    });
                });
            });
        });

        Route::group(['namespace' => 'Pages', 'prefix' => 'pages'], function () {
            Route::controller('PagesController')->group(function () {
                Route::delete('{media}', 'deletePageMedia');
                Route::put('/', 'update');
            });
        });
    });

    Route::controller('ModeratorController')->group(function () {
        Route::prefix('moderators')->group(function () {
            Route::group([
                'middleware' => 'role_or_permission:admin|view_and_manage_managers',
            ], function () {
                Route::get('/', 'list');
                Route::post('/', 'store');
                Route::put('/', 'update');
                Route::put('banned', 'banModerator');

                Route::prefix('{user}')->group(function () {
                    Route::get('/', 'getModerator');
                    Route::get('statistics', 'getStatistics');
                    Route::delete('/', 'destroy');
                });
            });
        });
    });

    /**
     * Commercial
     */
    Route::group(['namespace' => 'Commercial', 'prefix' => 'commercial'], function () {

        /**
         * Commercial Users Logic
         */
        Route::controller('UsersController')->group(function () {
            Route::group([
                'middleware' => 'role_or_permission:admin|access_commercial',
                'prefix' => 'users'
            ], function () {

                Route::get('/', 'list');
                Route::put('/', 'updateOrCreate');

                Route::prefix('{commercial_user}')->group(function () {
                    Route::get('/', 'details');
                    Route::put('/', 'changeToDraft');
                    Route::delete('/', 'destroy');
                });
            });
        });

        /**
         * Commercial Users Logic
         */
        Route::controller('PeriodOfStayController')->group(function () {
            Route::group([
                'middleware' => 'role_or_permission:admin|access_commercial',
                'prefix' => 'period-of-stay'
            ], function () {
                Route::get('/', 'list');
                Route::put('/', 'updateOrCreate');
                Route::prefix('{period_of_stay}')->group(function () {
                    Route::get('/', 'details');
                    Route::delete('/', 'destroy');
                });
//                Route::delete('{period_of_stay}', 'destroy');
            });
        });

        /**
         * Commercial Client Logic
         */
        Route::controller('ClientController')->group(function () {
            Route::group([
                'middleware' => 'role_or_permission:admin|access_commercial',
                'prefix' => 'clients'
            ], function () {
                Route::get('/', 'list');
                Route::put('/', 'updateOrCreate');

                Route::prefix('{client}')->group(function () {
                    Route::get('/', 'client');
                    Route::delete('/', 'destroy');
                });
            });
        });

        /**
         * Commercial Business Logic
         */
        Route::controller('BusinessController')->group(function () {
            Route::group([
                'middleware' => 'role_or_permission:admin|access_commercial',
                'prefix' => 'businesses'
            ], function () {
                Route::get('/', 'list');
                Route::put('/', 'updateOrCreate');

                Route::prefix('{commercial_business}')->group(function () {
                    Route::get('/', 'details');
                    Route::put('/', 'changeToDraft');
                    Route::delete('/', 'destroy');
                });
            });
        });

        /**
         * Commercial Notifications Logic
         */
        Route::controller('NotificationsController')->group(function () {
            Route::group([
                'middleware' => 'role_or_permission:admin|access_commercial',
                'prefix' => 'notifications'
            ], function () {
                Route::get('/', 'list');
                Route::put('/', 'updateOrCreate');

                Route::prefix('{commercial_notification}')->group(function () {
                    Route::get('/', 'details');
                    Route::put('/', 'changeToDraft');
                    Route::delete('/', 'destroy');
                });
            });
        });
    });

    /**
     * Advertises
     */
    Route::group(['middleware' => 'role:admin|moderator'], function () {

        Route::prefix('products')->group(function () {
            Route::controller('AdvertiseController')->group(function () {
                Route::get('/', 'list');
                Route::get('{user}', 'otherAdvertises');
                Route::put('status', 'changeStatus');

                Route::prefix('{advertise}')->group(function () {
                    Route::get('details', 'show');
                    Route::put('change-category', 'changeCategory');
                });
            });
        });

        Route::controller('RefusalController')->group(function () {
            Route::prefix('refusals')->group(function () {
                Route::put('/', 'updateOrCreate');
                Route::delete('{refusal}', 'destroy');
            });
        });

        Route::prefix('users')->group(function () {
            Route::controller('UserController')->group(function () {
                Route::get('/', 'list');
                Route::put('banned', 'banUser');
                Route::get('{user}', 'getAdvertises');
            });
        });

        Route::prefix('gallery')->group(function () {
            Route::controller('GalleryController')->group(function () {
                Route::post('/', 'store');
                Route::get('/', 'list');
                Route::delete('/', 'deleteMultiple');
                Route::put('/', 'duplicate');

                Route::group([
                    'middleware' => 'role_or_permission:admin|access_website_content_and_picture_editing',
                    'prefix' => '{media}'
                ], function () {
                    Route::put('/', 'changeFileProperties');
                    Route::delete('/', 'destroy');
                    Route::get('/', 'show');
                });
            });
        });

        Route::group(['namespace' => 'Support', 'prefix' => 'supports'], function () {
            Route::controller('SupportController')->group(function () {
                Route::get('themes', 'listThemes');
                Route::get('tickets', 'listTickets');
                Route::prefix('{ticket}')->group(function () {
                    Route::put('accept', 'acceptTicket');
                    Route::put('{action}', 'actionTicket')->where('action', 'close|expect|viewed');
                });
            });
        });
    });
});
