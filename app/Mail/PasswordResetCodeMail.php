<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Le code de réinitialisation
     *
     * @var string
     */
    public $code;

    /**
     * Créer une nouvelle instance du message.
     *
     * @param  string  $code
     * @return void
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Construire le message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Votre code de réinitialisation de mot de passe')
                    ->view('emails.password-reset-code')
                    ->with([
                        'code' => $this->code,
                    ]);
    }
}
