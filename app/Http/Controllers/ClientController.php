<?php

namespace App\Http\Controllers;

use App\Models\User;
use FedaPay\FedaPay;
use App\Models\Document;
use App\Models\Paiement;
use FedaPay\Transaction;
use Cloudinary\Cloudinary;
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
// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ClientController extends Controller
{
    /**
     * Home Page
     *
     */
    public function index()
    {
        $testimonial = Testimonial::all();
        $testimonialcount = Testimonial::count();
        if($testimonialcount > 0) {
            $testimonialnote = Testimonial::sum('note')/$testimonialcount;
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
     * Forget Password Page
     *
     */
    public function forgetpassword()
    {
        return response()->json([
            'success' => true,
            'message' => 'Page de réinitialisation de mot de passe'
        ]);
    }

    /**
     * Client Dashboard
     *
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
        $bplans = Document::where('user_id', $user->id)->where('type', 'business_plan')->with('templateCustoms.template')->get();
        $cvs = Document::where('user_id', $user->id)->where('type', 'cv')->get();
        $lms = Document::where('user_id', $user->id)->where('type', 'lettre_motivation')->get();
        $lastactivity = Document::where('user_id', $user->id)->latest()->get();
        $notificationcount = Notification::where('user_id', $user->id)->where('read', 0)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'bplans' => $bplans,
                'cvs' => $cvs,
                'lms' => $lms,
                'lastactivity' => $lastactivity,
                'notificationcount' => $notificationcount
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
        $bplans = Document::where('user_id', $user->id)->where('type', 'business_plan')->with('templateCustoms.template')->get();
        $cvs = Document::where('user_id', $user->id)->where('type', 'cv')->get();
        $lms = Document::where('user_id', $user->id)->where('type', 'lettre_motivation')->get();
        $lastactivity = Document::where('user_id', $user->id)->latest()->get();
        $notificationcount = Notification::where('user_id', $user->id)->where('read', 0)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'bplans' => $bplans,
                'cvs' => $cvs,
                'lms' => $lms,
                'lastactivity' => $lastactivity,
                'notificationcount' => $notificationcount
            ]
        ]);
    }

    /**
     * Business Plan Page
     *
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
     * Curriculum Vitae Page
     *
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
     * Lettre de Motivation Page
     *
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
     * Profile Page
     *
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
     * Notifications Page
     *
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
     * Mark notification as read
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
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
            'message' => 'Notification introuvable'
        ], 404);
    }

    /**
     * Testimonial
     *
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
     * Send OTP
     *
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
     * Check OTP
     *
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
     * Reset Password
     *
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
     * Update Profile
     *
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
     * Update Profile Picture
     *
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
     * Payment
     *
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
