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
Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {

    Route::controller('AuthController')->group(function () {

        Route::middleware('check_confirm_type')->group(function () {
            Route::post('register', 'register');
            Route::post('login', 'login');
            Route::post('confirm-login', 'checkConfirmCodeAndLogin');
            Route::post('send-again', 'sendCodeAgain');
            Route::post('reset/code', 'sendResetCode');
        });

        Route::get('check-exists', 'checkConfirmTypeExists');

        /* Social Login And Register */
        Route::group(['prefix' => 'social'], function () {
            Route::prefix('{provider}')->group(function () {
                Route::get('redirect', 'socialRedirect');
                Route::get('callback', 'callback')->where('provider', 'google|facebook');
            });
        });

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('logout', 'logout');
            Route::put('password/reset', 'showResetForm')
                ->middleware(['check_confirm_type', 'abilities:reset-password']);
        });
    });
});

Route::stripeWebhooks('stripe-webhooks/{configKey}');

Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::get('user',
        fn() => new \App\Http\Resources\V1\User\UserResource(
            user()->loadCount('unread_notifications')->load([
                'avatar',
                'city.country:id,name,phone_code,currency_name,currency_symbol',
                'city.state:id,name',
            ])
        )
    );

    Route::group(['namespace' => 'Users', 'prefix' => 'users'], function () {

        Route::controller('UserBlockController')->group(function () {
            Route::post('blocked-user', 'blockUser');
            Route::post('unblocked-user', 'unBlockUser');
            Route::get('blocked-user-list', 'blockList');
        });

        Route::controller('UserController')->group(function () {
            Route::prefix('products')->group(function () {
                Route::get('/', 'getAdvertises');
                Route::get('statistics', 'getStatisticUser');
                Route::get('favorites', 'getFavoriteAdvertises');
            });

            Route::prefix('settings')->group(function () {
                Route::put('/', 'update');

                Route::group(['middleware' => 'check_confirm_type', 'prefix' => 'change'], function () {
                    Route::put('password', 'changePassword');
                    Route::put('send-email-phone', 'sendCodForEmailOrPhone');
                    Route::put('update-email-phone', 'changeEmailOrPhone');
                });
            });
        });

        Route::controller('NotificationController')->group(function () {

            Route::prefix('notifications')->group(function () {
                Route::get('/', 'list');
                Route::put('/', 'readNotifications');
                Route::delete('{notification}', 'destroy');

                Route::middleware('check_confirm_type')->group(function () {
                    Route::post('confirmation-send', 'sendConfirmationCode');
                    Route::put('confirmation-check', 'checkConfirmationCode');
                });
            });
        });
    });

    Route::prefix('products')->group(function () {

        Route::controller('AdvertiseController')->group(function () {

            Route::prefix('favorite')->group(function () {
                Route::post('/', 'addFavorite');
                Route::delete('remove', 'detachFavorite');
            });

            Route::post('/', 'store');
            Route::put('action/{action}', 'changeProductStatusOrDeleteProduct')
                ->where('action', 'delete|deactivate|draft|activate|moderation');

            Route::prefix('{advertise}')->group(function () {
                Route::put('/', 'update');
                Route::delete('{media}', 'deletePicture');
            });
        });
    });

    Route::group(['namespace' => 'Payments', 'prefix' => 'payments'], function () {
        Route::controller('SubscriptionController')->group(function () {
            Route::post('checkout', 'createSession');
        });
    });
});

/**
 * Advertises List and details Advertise
 */
Route::prefix('products')->group(function () {
    Route::controller('AdvertiseController')->group(function () {
        Route::get('/', 'list');
        Route::get('{id_or_slug}', 'show');
    });
});

Route::group(['namespace' => 'Admin'], function () {
    /**
     * Project Local Lists
     */
    Route::prefix('locales')->group(function () {
        Route::controller('LanguageController')->group(function () {
            Route::get('/', 'getLocales');
        });
    });

    /**
     * Commercial Users list
     */
    Route::group(['namespace' => 'Commercial',  'prefix' => 'commercial'], function () {
        Route::get('users', 'UsersController@list');
        Route::get('businesses', 'BusinessController@list');
        Route::get('period-of-stay', 'PeriodOfStayController@list');
    });

    Route::group(['namespace' => 'Pages'], function () {
        Route::controller('PagesController')->group(function () {
            Route::get('text-pages', 'getPages');
        })->namespace('Pages');
    });

    /**
     * Categories Route Namespace
     */
    Route::group(['namespace' => 'Category'], function () {

        Route::prefix('categories')->group(function () {
            Route::controller('CategoryController')->group(function () {
                Route::get('/', 'list');
                Route::get('list', 'parentCategories');
                Route::get('top-list', 'topCategories');
                Route::get('{category}', 'show');
            });
        });

        Route::get('filters', 'FilterController@list');
    });

    Route::get('refusals', 'RefusalController@list');
});

Route::group(['namespace' => 'Users'], function () {

    Route::get('users/{user}/information', 'UserController@getUserInformation');

    Route::prefix('supports')->group(function () {
        Route::controller('SupportController')->group(function () {
            Route::get('themes', 'getThemes');
            Route::post('add-ticket', 'addTicket');
        });
    });
});

Route::controller('AdvertiseController')->group(function () {
    Route::prefix('statistics')->group(function () {
        Route::prefix('{advertise}')->group(function () {
            Route::post('phone-view', 'addStatisticsPhoneView')->middleware('throttle:phone_view');
            Route::post('view-details', 'addStatisticsDetailsView')->middleware('throttle:show_details');
            Route::post('favorite', 'addStatisticsFavorite');
        });
    });

    Route::get('search_texts', 'getSearchTexts');
    Route::get('search-products', 'getCategoriesAndAdvertisesCountBySearch');
});

/**
 * State List And Search Cities
 */
Route::controller('StateController')->group(function () {
    Route::get('states', 'getStates');
    Route::get('search-cities', 'getCitiesBySearch');
    Route::get('check-city', 'city');
});
