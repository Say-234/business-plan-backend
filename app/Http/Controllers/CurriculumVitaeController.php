<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;

class CurriculumVitaeController extends Controller
{
    public function addcvitae(){
        return view('client.curriculum-vitae.edit');
    }
    
    public function downloadcvitae(){

        $pdf = Pdf::loadView('client.template.bcvitae');
        return $pdf->download('curriculum-vitae.pdf');
    }
}
