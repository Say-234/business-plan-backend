<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;

class CurriculumVitaeController extends Controller
{
    /**
     * Afficher la page d'édition du CV.
     *
     * @group Curriculum Vitae
     *
     * Cette route affiche la page permettant de créer ou modifier
     * le curriculum vitae de l'utilisateur.
     *
     * @response 200 "Page d'édition du CV affichée"
     */
    public function addcvitae(){
        return view('client.curriculum-vitae.edit');
    }
    
    /**
     * Télécharger le CV en PDF.
     *
     * @group Curriculum Vitae
     *
     * Cette route génère et télécharge le curriculum vitae
     * de l'utilisateur au format PDF.
     *
     * @response 200 "Téléchargement du fichier PDF curriculum-vitae.pdf"
     * @response 500 {
     *   "message": "Erreur lors de la génération du PDF"
     * }
     */
    public function downloadcvitae(){

        $pdf = Pdf::loadView('client.template.bcvitae');
        return $pdf->download('curriculum-vitae.pdf');
    }
}
