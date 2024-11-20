<?php

declare(strict_types=1);
/*
* Web Routes
*
* Here is where you can register web routes for your application. These
* routes are loaded by the RouteServiceProvider within a group which
* contains the "web" middleware group. Now create something great!
*
*/

use App\Http\Controllers;
use Illuminate\Support\Facades\{Route};

// language switch
Route::get(
    'admin/set_lang/{lang}',
    static function (Illuminate\Http\Request $request, string $lang) {
        $user = null;
        session()->put('translatable_lang', $lang);

        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
        }
        if (Auth::check()) {
            $user = Auth::user();
        }

        if (is_null($user)) {
            return back();
        }

        $user->lang = $lang;
        $user->save();

        return back()->withCookie(Cookie::make('translatable_lang', $lang, config('session.lifetime')));
    }
)
    ->whereAlpha('lang')
    ->name('lang.switch');

// Email confirmation.
Route::group(
    [
        'middleware' => ['throttle:5'],
        'prefix'     => 'email'
    ],
    static function () {
        Route::get('verify/{id}/{hash}', [Controllers\Auth\VerificationController::class, 'verify'])->name('verification.verify');
        Route::get('{id}/resend', [Controllers\Auth\VerificationController::class, 'resend'])->name('verification.resend');
    }
);

// Prevent accessing diary images from Unauthenticated users from and users that image do not belong
// deprecated route, typo issue, TODO:: @NickMost remove this later
Route::get('dairy/{postId}/{style?}/{filename?}', Controllers\Posts\PostFilesController::class)->middleware('auth:sanctum')->name('post.image.dairy.full');
Route::get('dairy/{postId}/{style?}', Controllers\Posts\PostFilesController::class)->middleware('auth:sanctum')->name('post.image.dairy.short');


Route::get('diary/{postId}/{style?}/{filename?}', Controllers\Posts\PostFilesController::class)->middleware('auth:sanctum')->name('post.image.full');
Route::get('diary/{postId}/{style?}', Controllers\Posts\PostFilesController::class)->middleware('auth:sanctum')->name('post.image');


Route::middleware('lang.manager')
    ->group(
        function () {
            Route::view('about', 'pages.about')->name('pages.about');
            // TODO: Remove this route group as it is deprecated
            /**@deprecated */
            //			 Route::prefix('formular')
            //				  ->middleware('guest')
            //				  ->group(
            //					  function () {
            //						  Route::redirect('welcome', config('questionnaire.wp_form_redirect_link'))->name('formular.welcome');
            //						  Route::get('foodpunk-experience-buchen', [Controllers\PageController::class, 'showPricingTable'])
            //							   ->name('formular.pricingTable');
            //
            //						  Route::controller(Controllers\FormularController::class)
            //							   ->group(
            //								   function () {
            //									   Route::get('step-by-step', 'tryForFree')->name('formular.tryForFree');
            //									   Route::post('step-by-step', 'store')->name('formular.tryForFree.store');
            //									   Route::post('check-email', 'checkEmail')->name('user.check.email.formular');
            //								   }
            //							   );
            //					  }
            //				  );
            //			 Route::prefix('form')
            //				  ->middleware('guest')
            //				  ->group(
            //					  function () {
            //						  Route::view('welcome', 'form.welcome')->name('form.welcome');
            //						  Route::get('form-buchen', [Controllers\PageController::class, 'showTable'])->name('form.table');
            //						  Route::get('step-by-step', [Controllers\FormularController::class, 'tryForMarketing'])
            //							   ->name('form.tryForMarketing');
            //						  Route::post('step-by-step', [Controllers\FormularController::class, 'storeAgain'])
            //							   ->name('form.tryForMarketing.storeAgain');
            //						  Route::post('check-email', [Controllers\FormularController::class, 'checkEmail'])
            //							   ->name('user.check.email.form');
            //					  }
            //				  );
            Route::get(
                '/',
                static fn() => Auth::check() ? redirect()->route('user.dashboard') : redirect()->route('login')
            );

            // Login Routes
            require_once 'partials/web.login.php';

            // Password Request Reset Routes...
            require_once 'partials/web.password.php';

            Route::middleware(['auth', 'checkRole.user'])
                ->group(
                    function () {
                        Route::prefix('user')
                            ->group(
                                function () {
                                    Route::view('wallet', 'pages.wallet')->name('pages.wallet');
                                    Route::post('cookies', [Controllers\CookieController::class, 'set'])->name('cookies.set');

                                    // Formular
                                    require_once 'partials/web.formular.php';

                                    Route::get('layouts/choose_device', Controllers\ChooseDeviceController::class)
                                        ->name('layouts.choose_device');

                                    Route::middleware('check.questionnaire')
                                        ->group(
                                            function () {
                                                Route::get('/', static fn() => redirect()->route('user.dashboard'));

                                                Route::get(
                                                    'dashboard',
                                                    [Controllers\User\DashboardController::class, 'index']
                                                )
                                                    ->name('user.dashboard');

                                                // Diary create
                                                Route::controller(Controllers\DiaryController::class)
                                                    ->group(
                                                        function () {
                                                            Route::get('diary', 'create')->name('diary.create');
                                                            Route::post('diary', 'store')->name('diary.store');
                                                        }
                                                    );

                                                // Diary statistics and views
                                                Route::controller(Controllers\DiaryController::class)
                                                    ->middleware('check.challenge')
                                                    ->group(
                                                        function () {
                                                            Route::get('statistics', 'statistics')
                                                                ->name('diary.statistics');
                                                            Route::get('statistics/chartdata', 'getChartData')
                                                                ->name('diary.statistics.chartdata');
                                                            Route::get('diary/edit/{date?}', 'edit')->name('diary.edit');
                                                            Route::put('diary/{date?}', 'update')->name('diary.update');
                                                            Route::delete('diary/{id}', 'destroy')->name('diary.destroy');
                                                        }
                                                    );

                                                // User posts
                                                Route::controller(Controllers\Posts\PostController::class)
                                                    ->middleware('check.challenge')
                                                    ->group(
                                                        function () {
                                                            Route::get('posts', 'index')->name('posts.list');
                                                            Route::post('post', 'store')->name('post.store');

                                                            Route::get('post/{id}/edit', 'edit')->name('post.edit');
                                                            Route::get('post/form', 'getPostForm')->name('post.form');

                                                            Route::put('post/{id}', 'update')->name('post.update');
                                                            Route::delete('post/{id}', 'destroy')->name('post.destroy');
                                                        }
                                                    );

                                                // user settings
                                                Route::controller(Controllers\User\SettingsController::class)
                                                    ->prefix('settings')
                                                    ->group(
                                                        function () {
                                                            Route::get('/', 'index')->name('user.settings');
                                                            Route::post('sendingemail', 'sendEmail')
                                                                ->name('user.settings.sendemail');
                                                            Route::post('save', 'save')->name('user.settings.save');
                                                            Route::post('delete', 'deleteSelf')->name(
                                                                'user.settings.delete'
                                                            );
                                                            Route::post('profile-image', 'uploadProfileImage')
                                                                ->name('user.settings.profile_image');
                                                            Route::delete('profile-image', 'deleteProfileImage')
                                                                ->name('user.settings.profile_image.delete');
                                                        }
                                                    );

                                                // Recipes
                                                require_once 'partials/web.user_recipes.php';
                                            }
                                        );
                                }
                            );

                        Route::post('generate_pdf', [Controllers\PDFController::class, 'generatePdf'])->name('generate_pdf');
                    }
                );
        }
    );
