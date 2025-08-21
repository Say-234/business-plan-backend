<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;

class LettreMotivationController extends Controller
{
    /**
     * Afficher la page d'édition de la lettre de motivation.
     *
     * @group Lettre de Motivation
     *
     * Cette route affiche la page permettant de créer ou modifier
     * la lettre de motivation de l'utilisateur.
     *
     * @response 200 "Page d'édition de la lettre de motivation affichée"
     */
    public function addpblan()
    {
        return view('client.lettre-motivation.edit');
    }

    /**
     * Télécharger la lettre de motivation en PDF.
     *
     * @group Lettre de Motivation
     *
     * Cette route génère et télécharge la lettre de motivation
     * de l'utilisateur au format PDF.
     *
     * @response 200 "Téléchargement du fichier PDF lettre-motivation.pdf"
     * @response 500 {
     *   "message": "Erreur lors de la génération du PDF"
     * }
     */
    public function downloadblmotivation(){
        $pdf = Pdf::loadView('client.template.lmotivation');
        return $pdf->download('lettre-motivation.pdf');
    }
}
