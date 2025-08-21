<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\Paiement;
use App\Models\Passwordotp;
use App\Models\Testimonial;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Mail\PasswordOtpMail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Cloudinary\Cloudinary;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use App\Models\Finance;
use App\Models\Evaluation;
// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ClientController extends Controller
{
    /**
     * Afficher la page d'accueil.
     *
     * @group Client
     *
     * Cette route retourne les données de la page d'accueil incluant les témoignages
     * et les statistiques associées.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "testimonial": [
     *       {
     *         "id": 1,
     *         "nom": "Jean Dupont",
     *         "note": 5,
     *         "commentaire": "Excellent service"
     *       }
     *     ],
     *     "testimonialcount": 10,
     *     "testimonialnote": 4.5
     *   }
     * }
     */
    public function index()
    {
        $testimonial = Testimonial::all();
        $testimonialcount = Testimonial::count();
        if ($testimonialcount > 0) {
            $testimonialnote = Testimonial::sum('note') / $testimonialcount;
        } else {
            $testimonialnote = 0;
        }
        return response()->json([
            'success' => true,
            'data' => [
                'testimonial' => $testimonial,
                'testimonialcount' => $testimonialcount,
                'testimonialnote' => $testimonialnote
            ]
        ]);
    }

    /**
     * Afficher la page de réinitialisation de mot de passe.
     *
     * @group Authentification
     *
     * Cette route retourne les informations pour la page de réinitialisation de mot de passe.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Page de réinitialisation de mot de passe"
     * }
     */
    public function forgetpassword()
    {
        return response()->json([
            'success' => true,
            'message' => 'Page de réinitialisation de mot de passe'
        ]);
    }

    /**
     * Afficher le tableau de bord client.
     *
     * @group Client
     *
     * Cette route retourne toutes les données nécessaires pour le tableau de bord du client :
     * documents, business plans, finances, évaluations, activités récentes et notifications.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Jean Dupont",
     *       "email": "jean@example.com"
     *     },
     *     "bplans": [],
     *     "finances": [],
     *     "evaluations": [],
     *     "lastactivity": [],
     *     "notificationcount": 3
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Utilisateur non authentifié"
     * }
     */
    public function clientdashboard()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        $user = Auth::user();
        $documents = Document::where('user_id', $user->id)->get();
        $bplans = $documents->where('type', 'business_plan');
        $cvs = $documents->where('type', 'cv');
        $lms = $documents->where('type', 'lettre_motivation');
        $documentIds = $documents->pluck('id');
        $finances = Finance::whereIn('document_id', $documentIds)->get();
        $evaluations = Evaluation::whereIn('document_id', $documentIds)->get();
        $lastactivity = Document::where('user_id', $user->id)->latest()->get();
        $notificationcount = Notification::where('user_id', $user->id)->where('read', 0)->count();

        // Récupérer les templates disponibles
        $bplanTemplates = \App\Models\Template::where('type', 'business_plan')->get();
        $cvTemplates = \App\Models\Template::where('type', 'cv')->get();
        $lmTemplates = \App\Models\Template::where('type', 'lettre_motivation')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'bplans' => $bplans,
                'cvs' => $cvs,
                'lms' => $lms,
                'finances' => $finances,
                'evaluations' => $evaluations,
                'lastactivity' => $lastactivity,
                'notificationcount' => $notificationcount,
                'bplan_templates' => $bplanTemplates,
                'cv_templates' => $cvTemplates,
                'lm_templates' => $lmTemplates,
            ]
        ]);
    }

    public function newdashboard()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        $user = Auth::user();
        $documents = Document::where('user_id', $user->id)->get();
        $bplans = Document::where('user_id', $user->id)->where('type', 'business_plan')->with('templateCustoms.template')->get();
        $cvs = Document::where('user_id', $user->id)->where('type', 'cv')->get();
        $lms = Document::where('user_id', $user->id)->where('type', 'lettre_motivation')->get();
        $documentIds = $documents->pluck('id');
        $finances = Finance::whereIn('document_id', $documentIds)->get();
        $evaluations = Evaluation::whereIn('document_id', $documentIds)->get();
        $lastactivity = Document::where('user_id', $user->id)->latest()->get();
        $notificationcount = Notification::where('user_id', $user->id)->where('read', 0)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'bplans' => $bplans,
                'cvs' => $cvs,
                'lms' => $lms,
                'finances' => $finances,
                'evaluations' => $evaluations,
                'lastactivity' => $lastactivity,
                'notificationcount' => $notificationcount
            ]
        ]);
    }

    /**
     * Afficher la page des business plans.
     *
     * @group Business Plan
     *
     * Cette route retourne tous les business plans de l'utilisateur connecté
     * avec leurs templates personnalisés et les activités récentes.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "bplans": [
     *       {
     *         "id": 1,
     *         "type": "business_plan",
     *         "status": "completed",
     *         "templateCustoms": {
     *           "template": {
     *             "id": 1,
     *             "name": "Template Moderne"
     *           }
     *         }
     *       }
     *     ],
     *     "lastactivity": [],
     *     "notificationcount": 2
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Utilisateur non authentifié"
     * }
     */
    public function bplan()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $bplans = Document::where('user_id', Auth::user()->id)->where('type', 'business_plan')->with('templateCustoms.template')->get();
        $lastactivity = Document::where('user_id', Auth::user()->id)->where('type', 'business_plan')->latest()->get();
        $notificationcount = Notification::where('user_id', Auth::user()->id)->where('read', 0)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'bplans' => $bplans,
                'lastactivity' => $lastactivity,
                'notificationcount' => $notificationcount
            ]
        ]);
    }

    /**
     * Afficher la page des CV.
     *
     * @group Documents
     *
     * Cette route retourne les données nécessaires pour la page des CV
     * incluant les activités récentes et le nombre de notifications.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "lastactivity": [
     *       {
     *         "id": 1,
     *         "type": "cv",
     *         "created_at": "2024-01-01T10:00:00Z"
     *       }
     *     ],
     *     "notificationcount": 1
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Utilisateur non authentifié"
     * }
     */
    public function cvitae()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $lastactivity = Document::where('user_id', Auth::user()->id)->where('type', 'cv')->latest()->get();
        $notificationcount = Notification::where('user_id', Auth::user()->id)->where('read', 0)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'lastactivity' => $lastactivity,
                'notificationcount' => $notificationcount
            ]
        ]);
    }

    /**
     * Afficher la page des lettres de motivation.
     *
     * @group Documents
     *
     * Cette route retourne les données nécessaires pour la page des lettres de motivation
     * incluant les activités récentes et le nombre de notifications.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "lastactivity": [
     *       {
     *         "id": 1,
     *         "type": "lettre_motivation",
     *         "created_at": "2024-01-01T10:00:00Z"
     *       }
     *     ],
     *     "notificationcount": 1
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Utilisateur non authentifié"
     * }
     */
    public function lmotivation()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $lastactivity = Document::where('user_id', Auth::user()->id)->where('type', 'lettre_motivation')->latest()->get();
        $notificationcount = Notification::where('user_id', Auth::user()->id)->where('read', 0)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'lastactivity' => $lastactivity,
                'notificationcount' => $notificationcount
            ]
        ]);
    }

    /**
     * Afficher la page de profil.
     *
     * @group Client
     *
     * Cette route retourne les données nécessaires pour la page de profil de l'utilisateur
     * incluant les activités récentes et le nombre de notifications.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "lastactivity": [
     *       {
     *         "id": 1,
     *         "type": "business_plan",
     *         "created_at": "2024-01-01T10:00:00Z"
     *       }
     *     ],
     *     "notificationcount": 2
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Utilisateur non authentifié"
     * }
     */
    public function profile()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $user = Auth::user();
        $notificationcount = Notification::where('user_id', $user->id)->where('read', 0)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'notificationcount' => $notificationcount
            ]
        ]);
    }

    /**
     * Afficher la page des notifications.
     *
     * @group Client
     *
     * Cette route retourne toutes les notifications de l'utilisateur connecté
     * avec le nombre total de notifications non lues.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "notifications": [
     *       {
     *         "id": 1,
     *         "title": "Nouveau business plan créé",
     *         "message": "Votre business plan a été créé avec succès",
     *         "read": 0,
     *         "created_at": "2024-01-01T10:00:00Z"
     *       }
     *     ],
     *     "notificationcount": 5
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Utilisateur non authentifié"
     * }
     */
    public function notifications()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $user_id = Auth::user()->id;
        $notification = Notification::where('user_id', $user_id)->orderBy('created_at', 'desc')->get();
        $read = Notification::where('user_id', $user_id)->where('read', 1)->get();
        $unread = Notification::where('user_id', $user_id)->where('read', 0)->get();
        $notificationcount = Notification::where('user_id', Auth::user()->id)->where('read', 0)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user_id,
                'notificationcount' => $notificationcount,
                'notification' => $notification,
                'read' => $read,
                'unread' => $unread
            ]
        ]);
    }

    /**
     * Marquer une notification comme lue.
     *
     * @group Client
     *
     * Cette route permet de marquer une notification spécifique comme lue.
     *
     * @bodyParam notification_id integer requis L'ID de la notification à marquer comme lue. Exemple: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Notification marquée comme lue"
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Utilisateur non authentifié"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Notification non trouvée"
     * }
     */
    public function markNotificationAsRead(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $notification_id = $request->notification_id;
        $notification = Notification::find($notification_id);

        if ($notification && $notification->user_id == Auth::user()->id) {
            $notification->read = 1;
            $notification->save();

            return response()->json([
                'success' => true,
                'message' => 'Notification marquée comme lue',
                'data' => $notification
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification non trouvée'
        ], 404);
    }

    /**
     * Créer un témoignage.
     *
     * @group Client
     *
     * Cette route permet à un utilisateur de créer un témoignage avec une note et un commentaire.
     *
     * @bodyParam nom string requis Le nom de la personne qui témoigne. Exemple: Jean Dupont
     * @bodyParam note integer requis La note donnée (1-5). Exemple: 5
     * @bodyParam commentaire string requis Le commentaire du témoignage. Exemple: Excellent service
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Témoignage créé avec succès",
     *   "data": {
     *     "id": 1,
     *     "nom": "Jean Dupont",
     *     "note": 5,
     *     "commentaire": "Excellent service"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Erreur de validation",
     *   "errors": {
     *     "nom": ["Le champ nom est requis"]
     *   }
     * }
     */
    public function testimonial(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'testimonial' => 'required|string|max:1000',
            'note' => 'required|integer|between:1,5',
        ]);

        try {
            $testimonial = new Testimonial();
            $testimonial->name = $request->name;
            $testimonial->testimonial = $request->testimonial;
            $testimonial->note = $request->note;
            $testimonial->save();

            return response()->json([
                'success' => true,
                'message' => 'Merci pour votre témoignage!',
                'data' => $testimonial
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'enregistrement du témoignage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer un code OTP par email.
     *
     * @group Authentification
     *
     * Cette route permet d'envoyer un code OTP (One-Time Password) à l'adresse email
     * spécifiée pour la réinitialisation du mot de passe.
     *
     * @bodyParam email string requis L'adresse email pour recevoir l'OTP. Exemple: jean@example.com
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Code OTP envoyé avec succès"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Utilisateur non trouvé"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Erreur lors de l'envoi de l'email",
     *   "error": "Message d'erreur détaillé"
     * }
     */
    public function sendotp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|max:255',
            ]);

            $email = $request->email;
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => "Aucun compte n'est associé à cette adresse email"
                ], 404);
            }

            if ($user->google_id) {
                return response()->json([
                    'success' => false,
                    'message' => "Un compte google est associé à cette adresse email"
                ], 409);
            }

            // Generate a 4-digit OTP
            $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $time = time();

            // Delete any existing OTPs for this email that are older than 3 minutes
            Passwordotp::where('email', $email)
                ->where('created_at', '<=', $time - 180)
                ->delete();

            // Create new OTP
            Passwordotp::updateOrCreate(
                ['email' => $email],
                [
                    'otp' => $otp,
                    'created_at' => $time
                ]
            );

            // Send email
            try {
                Mail::to($email)->send(new PasswordOtpMail($otp));
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible d'envoyer l'email. Veuillez réessayer plus tard.",
                    'error' => $e->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Un code de vérification a été envoyé à votre adresse email',
                'email' => $email
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier un code OTP.
     *
     * @group Authentification
     *
     * Cette route permet de vérifier la validité d'un code OTP envoyé par email
     * pour la réinitialisation du mot de passe.
     *
     * @bodyParam email string requis L'adresse email associée à l'OTP. Exemple: jean@example.com
     * @bodyParam otp string requis Le code OTP à vérifier. Exemple: 123456
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Code OTP valide"
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Code OTP invalide ou expiré"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Utilisateur non trouvé"
     * }
     */
    public function checkotp(Request $request)
    {
        try {
            $request->validate([
                'otp1' => 'required|numeric|digits:1',
                'otp2' => 'required|numeric|digits:1',
                'otp3' => 'required|numeric|digits:1',
                'otp4' => 'required|numeric|digits:1',
                'email' => 'required|email|max:255',
            ]);

            $otp = $request->otp1 . $request->otp2 . $request->otp3 . $request->otp4;
            $email = $request->email;
            $time = time();

            // Check if OTP exists and is not expired (3 minutes)
            $otpData = Passwordotp::where('email', $email)
                ->where('created_at', '>', $time - 180)
                ->first();

            if (!$otpData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le code de vérification a expiré. Veuillez en demander un nouveau.'
                ], 410);
            }

            if ($otpData->otp !== $otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code de vérification incorrect'
                ], 401);
            }

            // Delete used OTP
            $otpData->delete();

            return response()->json([
                'success' => true,
                'message' => 'Code vérifié avec succès',
                'email' => $email
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la vérification du code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réinitialiser le mot de passe.
     *
     * @group Authentification
     *
     * Cette route permet de réinitialiser le mot de passe d'un utilisateur
     * après vérification du code OTP.
     *
     * @bodyParam email string requis L'adresse email de l'utilisateur. Exemple: jean@example.com
     * @bodyParam password string requis Le nouveau mot de passe (min 8 caractères, majuscules, minuscules, chiffres, symboles). Exemple: MonMotDePasse123!
     * @bodyParam password_confirmation string requis Confirmation du nouveau mot de passe. Exemple: MonMotDePasse123!
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Votre mot de passe a été mis à jour avec succès. Vous pouvez maintenant vous connecter."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Une erreur est survenue. Veuillez recommencer le processus."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Erreur de validation",
     *   "errors": {
     *     "password": ["Le mot de passe doit contenir au moins 8 caractères"]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Une erreur est survenue lors de la réinitialisation du mot de passe",
     *   "error": "Message d'erreur détaillé"
     * }
     */
    public function resetpassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|max:255',
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                ],
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue. Veuillez recommencer le processus.'
                ], 404);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            // Delete any remaining OTPs for this email
            Passwordotp::where('email', $request->email)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Votre mot de passe a été mis à jour avec succès. Vous pouvez maintenant vous connecter.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la réinitialisation du mot de passe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le profil utilisateur.
     *
     * @group Profil Utilisateur
     *
     * Cette route permet de mettre à jour les informations du profil de l'utilisateur connecté.
     * Seuls les champs fournis seront mis à jour.
     *
     * @bodyParam name string optionnel Le nom de l'utilisateur. Exemple: Jean
     * @bodyParam first_last_name string optionnel Le prénom et nom de famille. Exemple: Jean Dupont
     * @bodyParam email string optionnel L'adresse email principale (doit être unique). Exemple: jean@example.com
     * @bodyParam email_snd string optionnel L'adresse email secondaire. Exemple: jean.pro@example.com
     * @bodyParam number string optionnel Le numéro de téléphone principal. Exemple: +33123456789
     * @bodyParam number_snd string optionnel Le numéro de téléphone secondaire. Exemple: +33987654321
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Profil mis à jour avec succès",
     *   "data": {
     *     "id": 1,
     *     "name": "Jean",
     *     "first_last_name": "Jean Dupont",
     *     "email": "jean@example.com",
     *     "email_snd": "jean.pro@example.com",
     *     "number": "+33123456789",
     *     "number_snd": "+33987654321"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Erreur de validation",
     *   "errors": {
     *     "email": ["Cette adresse email est déjà utilisée"]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Une erreur est survenue lors de la mise à jour du profil",
     *   "error": "Message d'erreur détaillé"
     * }
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'first_last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'email_snd' => 'nullable|email',
            'number' => 'nullable|string|max:20',
            'number_snd' => 'nullable|string|max:20',
        ]);

        $dataToUpdate = array_filter($validatedData, function ($value) {
            return $value !== null;
        });

        try {
            User::where('id', $user->id)->update($dataToUpdate);

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'data' => User::find($user->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour la photo de profil.
     *
     * @group Profil Utilisateur
     *
     * Cette route permet de télécharger et mettre à jour la photo de profil de l'utilisateur
     * en utilisant Cloudinary pour le stockage des images.
     *
     * @bodyParam profile_picture file requis L'image de profil à télécharger (JPEG, PNG, JPG, max 2MB).
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Image mise à jour avec succès",
     *   "data": {
     *     "image_url": "https://res.cloudinary.com/example/image/upload/v123456789/profile/user_photo.jpg",
     *     "image_public_id": "profile/user_photo"
     *   }
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Le fichier est invalide."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Erreur de validation",
     *   "errors": {
     *     "profile_picture": ["Le fichier doit être une image"]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Une erreur est survenue lors de la mise à jour de l'image",
     *   "error": "Message d'erreur détaillé"
     * }
     */
    public function updateprofilepic(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $file = $request->file('profile_picture');

        // Vérifier si le fichier est valide
        if (!$file || !$file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier est invalide.'
            ], 400);
        }

        try {
            $cloudinary = new Cloudinary();
            $image = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => 'profile', // Dossier où l'image sera stockée
                'use_filename' => true, // Utiliser le nom du fichier comme public_id
                'overwrite' => true,   // Remplacer une image existante avec le même public_id
            ]);
            $url = $image['secure_url'];
            $publicId = $image['public_id'];

            User::where('id', Auth::user()->id)->update([
                'image_url' => $url,
                'image_public_id' => $publicId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image mise à jour avec succès',
                'data' => [
                    'image_url' => $url,
                    'image_public_id' => $publicId
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour de l\'image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initier un paiement.
     *
     * @group Paiement
     *
     * Cette route permet d'initier un paiement pour le téléchargement d'un business plan
     * via FedaPay. L'utilisateur sera redirigé vers la page de paiement FedaPay.
     *
     * @response 302 {
     *   "message": "Redirection vers la page de paiement FedaPay"
     * }
     * @response 302 {
     *   "message": "Redirection avec erreur en cas d'échec",
     *   "error": "Une erreur est survenue : message d'erreur"
     * }
     */
    public function payment()
    {
        $paiement = Paiement::create([
            'user_id' => Auth::user()->id,
            'amount' => 10000,
        ]);

        FedaPay::setApiKey(config('fedapay.secret_key'));
        FedaPay::setEnvironment(config('fedapay.environment'));

        try {
            $transaction = Transaction::create([
                'description' => 'Paiement téléchargement sur BusinessPlan',
                'amount' => 10000, // Montant en XOF
                'currency' => ['iso' => 'XOF'],
                'callback_url' => route('client.payment.success', ['paiement_id' => $paiement->id]),
            ]);

            $token = $transaction->generateToken();
            $urlfeda = $token->url;
            return redirect($urlfeda);
        } catch (\Exception $e) {
            return redirect()->route('client.addpblan')->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Traiter le retour de paiement.
     *
     * @group Paiement
     *
     * Cette route traite le retour de paiement depuis FedaPay après qu'un utilisateur
     * ait effectué ou annulé un paiement. Elle vérifie le statut de la transaction
     * et met à jour les données en conséquence.
     *
     * @urlParam paiement_id integer requis L'ID du paiement. Exemple: 1
     * @queryParam id string requis L'ID de la transaction FedaPay. Exemple: trans_123456
     * @queryParam status string requis Le statut de la transaction. Exemple: approved
     *
     * @response 302 {
     *   "message": "Redirection avec succès",
     *   "success": "Votre paiement a été traité avec succès."
     * }
     * @response 302 {
     *   "message": "Redirection avec erreur",
     *   "error": "La transaction a été annulée."
     * }
     * @response 302 {
     *   "message": "Redirection avec erreur",
     *   "error": "Transaction introuvable."
     * }
     */
    public function paymentSucess(Request $request)
    {
        $transactionId = $request->query('id');
        $status = $request->query('status');
        $paiement_id = $request->get('paiement_id');

        // Vérifiez la transaction auprès de FedaPay
        FedaPay::setApiKey(config('fedapay.secret_key'));
        FedaPay::setEnvironment(config('fedapay.environment'));

        try {
            $transaction = Transaction::retrieve($transactionId);
        } catch (\Exception $e) {
            return redirect()->route('client.addpblan')->with('error', 'Transaction introuvable.');
        }
        if ($transaction->status == 'approved') {
            // Récupérer les informations de réservation depuis la session
            $paiementData = Paiement::find($paiement_id);
            $paiementData->status = $transaction->status;
            $paiementData->id_transaction = $transactionId;
            $paiementData->save();

            // Envoyer l'email de confirmation
            // $mailabler = new ReservationMarkdownMail($reservationData->email, $reservationData->id, $reservationData->nom);
            // Mail::to($reservationData->email)->send($mailabler);
            $pdf = Pdf::loadView('client.template.business-plan.template4');
            $pdf->download('BusinessPlan.pdf');

            return redirect()->route('client.addpblan')->with('success', 'Votre paiement a été traité avec succès.');
        }
        if ($transaction->status == 'canceled') {
            $paiementData = Paiement::find($paiement_id);
            $paiementData->status = 'canceled';
            $paiementData->save();
            return redirect()->route('client.addpblan')->with('error', 'La transaction a été annulée.');
        }

        return redirect()->route('client.addpblan')->with('error', 'La transaction a été annulé.');
    }
}