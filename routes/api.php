<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessPlanController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PartnersController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\FinancesController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\AuthController;

Route::post('/auth/firebase/token', [AuthController::class, 'exchangeFirebaseToken']);

Route::middleware(['auth:api'])->get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes for mobile
Route::post('/register', [RegisteredUserController::class, 'store'])->name('api.register');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('api.login');
// Public routes
Route::post('/send-notif', [PushNotificationController::class, 'sendPushNotification']);
Route::post('/testimonial', [ClientController::class, 'testimonial'])->name('api.testimonial.store');
Route::get('/testimonials', [ClientController::class, 'getTestimonials'])->name('api.testimonials');
Route::get('/forgetpassword', [ClientController::class, 'forgetpassword'])->name('api.password.forget');
Route::post('/sendotp', [ClientController::class, 'sendotp'])->name('api.password.otp');
Route::post('/verifyotp', [ClientController::class, 'checkotp'])->name('api.password.verify.otp');
Route::post('/resetpassword', [ClientController::class, 'resetpassword'])->name('api.password.reset.otp');
Route::post('/fcm-token', [PushNotificationController::class, 'getToken'])->name('api.fcm-token');

// Routes d'authentification
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/user', [AuthController::class, 'user']);

// Client routes
Route::prefix('client')->name('api.client.')->middleware('auth:api')->group(function () {
    Route::get('/new', [ClientController::class, 'newdashboard'])->name('newdashboard');
    Route::get('/dashboard', [ClientController::class, 'clientdashboard'])->name('dashboard');
    Route::get('/business-plan', [ClientController::class, 'bplan'])->name('bplan');
    Route::post('/business-plan/create', [BusinessPlanController::class, 'addpblan'])->name('addpblan');
    Route::get('/business-plan/{id}/edit', [BusinessPlanController::class, 'editpblan'])->name('editpblan');
    Route::post('/business-plan', [BusinessPlanController::class, 'store'])->name('storepblan');
    Route::put('/business-plan/{id}', [BusinessPlanController::class, 'update'])->name('updatepblan');
    Route::post('/save-template-settings', [BusinessPlanController::class, 'saveTemplateSettings'])->name('save.template.settings');
    Route::delete('/business-plan/{id}', [BusinessPlanController::class, 'destroy'])->name('destroypblan');
    Route::post('/business-plan/preview', [BusinessPlanController::class, 'preview'])->name('previewpblan');
    Route::get('/business-plan/download/{id}', [BusinessPlanController::class, 'downloadBusinessPlan'])->name('downloadBusinessPlan');
    Route::get('/curriculum-vitae', [ClientController::class, 'cvitae'])->name('cvitae');
    Route::get('/lettre-motivation', [ClientController::class, 'lmotivation'])->name('lmotivation');
    Route::get('/profile', [ClientController::class, 'profile'])->name('profile');
    Route::get('/notifications', [ClientController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/mark-as-read', [ClientController::class, 'markNotificationAsRead'])->name('notifications.markAsRead');
    Route::put('/profile', [ClientController::class, 'updateProfile'])->name('update-profile');
    Route::post('/updateprofilepic', [ClientController::class, 'updateprofilepic'])->name('updateprofilepic');
    Route::get('/paiement', [ClientController::class, 'payment'])->name('payment');
    Route::get('/payment-success', [ClientController::class, 'paymentSucess'])->name('payment.success');

    // Evaluation Projet
    Route::get('/evaluation', [EvaluationController::class, 'index'])->name('evaluation');
    Route::post('/ia/evaluate', [EvaluationController::class, 'generateResponse'])->name('iaevaluate');
    Route::get('/ia/response/{id}', [EvaluationController::class, 'responsepage'])->name('iarresponse');
    Route::get('/evaluation/document/{id}', [EvaluationController::class, 'getDocumentEvaluation'])->name('evaluation.getDocument');

    // Etude Financière
    Route::get('/finance', [FinancesController::class, 'index'])->name('finance');
    Route::get('/finances/{id}', [FinancesController::class, 'getDocumentFinances']);
    Route::post('/finance/produit', [FinancesController::class, 'storeProduit'])->name('finance.storeProduit');
    Route::post('/finance/capitaldemarrage', [FinancesController::class, 'storePreExploitation'])->name('finance.storeCapitalDemarrage');
    Route::post('/finance/immobilisation', [FinancesController::class, 'storeImmobilisation'])->name('finance.storeImmobilisation');
    Route::post('/finance/fondsderoulement', [FinancesController::class, 'storeFondsDeRoulement'])->name('finance.storeFondsDeRoulement');
    Route::post('/finance/vente', [FinancesController::class, 'storeVente'])->name('finance.storeVente');

    // Page partenaire
    Route::get('/partners', [PartnersController::class, 'index'])->name('partners');

    // Mes projets
    Route::get('/project', [ProjectController::class, 'index'])->name('project');
});

// Routes protégées
Route::middleware('auth:api')->group(function () {
    Route::get('/client', [ClientController::class, 'index']);
    Route::get('/client/dashboard', [ClientController::class, 'clientdashboard']);
    Route::get('/client/newdashboard', [ClientController::class, 'newdashboard']);
    Route::get('/documents', [ClientController::class, 'getDocuments'])->name('api.documents');
    Route::get('/user/profile', [ClientController::class, 'profile'])->name('api.user.profile');
});

// Profile routes
Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('api.profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('api.profile.destroy');
});

// Google Auth routes
Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('api.auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callbackGoogle'])->name('api.auth.google.callback');
Route::post('/auth/google/signin', [GoogleAuthController::class, 'signInWithGoogle'])->name('api.auth.google.signin');