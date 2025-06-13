<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Evaluation;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\CommonMark\CommonMarkConverter;
use App\Http\Controllers\Controller;

class EvaluationController extends Controller
{
    protected $IA;

    public function __construct(\App\Http\Controllers\AIController $IA){
        $this->IA = $IA;
    }

    public function index(){
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        try {
            $user = Auth::user();
            $documents = Document::where('user_id', $user->id)->get();
            $notificationcount = Notification::where('user_id', $user->id)->where('read', 0)->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'notificationcount' => $notificationcount,
                    'documents' => $documents
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving documents: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getDocumentEvaluation($id) {
        $evaluation = Evaluation::where('document_id', $id)->first();
        
        if ($evaluation) {
            return response()->json([
                'success' => true,
                'data' => $evaluation
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Aucune évaluation trouvée pour ce document'
        ]);
    }

    public function generateResponse(Request $request){
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        try {
            // Récupérer toutes les données du formulaire
            $formData = $request->all();
            
            // Extraire les données pour l'IA
            $age = $request->input('age');
            $situation = $request->input('situation');
            $diplome = $request->input('diplome');
            $experience = $request->input('experience');
            $soutien = $request->input('soutien');
            $department = $request->input('department');
            $motivation = $request->input('motivation');
            $change_region = $request->input('change_region');
            $project = $request->input('project');
            $reason = $request->input('reason');
            $alone_team = $request->input('alone_team');
            $echeance = $request->input('echeance');
            $employe = $request->input('employe');
            $employe_three = $request->input('employe_three');
            $investment_amount = $request->input('investment_amount');
            $investment_share = $request->input('investment_share');
            $finance = $request->input('finance');
            $activity = $request->input('activity');
            $development_stage = $request->input('development_stage');
            $marketing_condition = $request->input('marketing_condition');
            $season = $request->input('season');
            $eco_modal = $request->input('eco_modal');
            $start_strategy = $request->input('start_strategy');
            $market_qualification = $request->input('market_qualification');
            $market_geography = $request->input('market_geography');
            $clients_identified = $request->input('clients_identified');
            $client_knowledge = $request->input('client_knowledge');
            $competitors_identified = $request->input('competitors_identified');
            $suppliers_identified = $request->input('suppliers_identified');
            $supplier_characteristics = $request->input('supplier_characteristics');
            
            $documentId = null;
            // Sauvegarder ou mettre à jour l'évaluation dans la base de données
            if ($request->has('document_id')) {
                $documentId = $request->input('document_id');
                
                // Vérifier si une évaluation existe déjà pour ce document
                $evaluation = Evaluation::where('document_id', $documentId)->first();
                
                if ($evaluation) {
                    // Mettre à jour l'évaluation existante
                    $evaluation->update($formData);
                } else {
                    // Créer une nouvelle évaluation
                    $evaluation = new Evaluation($formData);
                    $evaluation->document_id = $documentId;
                    $evaluation->save();
                }
            }
            
            // Générer la réponse de l'IA
            $response = $this->IA->askQuestion($age, $situation, $diplome, $experience, $soutien, $department, $motivation, $change_region, $project, $reason, $alone_team, $echeance, $employe, $employe_three, $investment_amount, $investment_share, $finance, $activity, $development_stage, $marketing_condition, $season, $eco_modal, $start_strategy, $market_qualification, $market_geography, $clients_identified, $client_knowledge, $competitors_identified, $suppliers_identified, $supplier_characteristics);
            $ai_response = [
                'positifs' => $response['positifs'] ?? 'Non spécifié',
                'negatifs' => $response['negatifs'] ?? 'Non spécifié',
                'ameliorations' => $response['ameliorations'] ?? 'Non spécifié'
            ];
            
            // Sauvegarder la réponse de l'IA dans la table des évaluations si un document est sélectionné
            if ($request->has('document_id') && !empty($request->input('document_id'))) {
                $documentId = $request->input('document_id');
                $evaluation = Evaluation::where('document_id', $documentId)->first();
                
                if ($evaluation) {
                    // Si la réponse est un tableau, la sauvegarder directement
                    if (is_array($ai_response)) {
                        $evaluation->ai_response = $ai_response;
                    } else {
                        // Sinon, essayer de la convertir en tableau ou la sauvegarder comme une chaîne
                        $evaluation->ai_response = ['response' => $ai_response];
                    }
                    $evaluation->save();
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'document_id' => $documentId,
                    'ai_response' => $ai_response
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating response: ' . $e->getMessage()
            ], 500);
        }
    }

    public function responsepage($id){
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        try {
            $user = Auth::user();
            $evaluation = Evaluation::where('document_id', $id)->first();
            
            if (!$evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Evaluation not found'
                ], 404);
            }
            
            $ia_response = $evaluation->ai_response;
            $converter = new CommonMarkConverter();
            $response = [
                'positifs' => $converter->convert($ia_response['positifs'])->getContent(),
                'negatifs' => $converter->convert($ia_response['negatifs'])->getContent(),
                'ameliorations' => $converter->convert($ia_response['ameliorations'])->getContent()
            ];
            
            $notificationcount = Notification::where('user_id', $user->id)->where('read', 0)->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'response' => $response,
                    'notificationcount' => $notificationcount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving response: ' . $e->getMessage()
            ], 500);
        }
    }
}
