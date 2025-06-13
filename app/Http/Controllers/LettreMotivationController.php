<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;

class LettreMotivationController extends Controller
{
    public function addpblan()
    {
        return view('client.lettre-motivation.edit');
    }

    public function downloadblmotivation(){
        $pdf = Pdf::loadView('client.template.lmotivation');
        return $pdf->download('lettre-motivation.pdf');
    }
}
