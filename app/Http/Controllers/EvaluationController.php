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

    /**
     * Lister les documents de l'utilisateur.
     *
     * @group Évaluation
     *
     * Cette route retourne tous les documents de l'utilisateur connecté
     * ainsi que le nombre de notifications non lues.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "notificationcount": 3,
     *     "documents": [
     *       {
     *         "id": 1,
     *         "title": "Mon Business Plan",
     *         "content": "Contenu du document...",
     *         "created_at": "2024-01-01T10:00:00Z"
     *       }
     *     ]
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Error retrieving documents: message d'erreur"
     * }
     */
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
    
    /**
     * Récupérer l'évaluation d'un document.
     *
     * @group Évaluation
     *
     * Cette route retourne l'évaluation associée à un document spécifique.
     *
     * @urlParam id integer requis L'ID du document. Exemple: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "document_id": 1,
     *     "ai_response": {
     *       "positifs": "Points positifs du projet...",
     *       "negatifs": "Points négatifs à améliorer...",
     *       "ameliorations": "Suggestions d'amélioration..."
     *     },
     *     "created_at": "2024-01-01T10:00:00Z"
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Aucune évaluation trouvée pour ce document"
     * }
     */
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

    /**
     * Générer une évaluation IA d'un projet.
     *
     * @group Évaluation
     *
     * Cette route génère une évaluation détaillée d'un projet entrepreneurial
     * en utilisant l'intelligence artificielle. Elle analyse tous les aspects
     * du projet et fournit des recommandations.
     *
     * @bodyParam document_id integer optionnel L'ID du document à évaluer. Exemple: 1
     * @bodyParam age integer requis L'âge du porteur de projet. Exemple: 30
     * @bodyParam situation string requis La situation actuelle. Exemple: Employé
     * @bodyParam diplome string requis Le niveau de diplôme. Exemple: Master
     * @bodyParam experience string requis L'expérience professionnelle. Exemple: 5 ans
     * @bodyParam soutien string requis Le soutien disponible. Exemple: Famille
     * @bodyParam department string requis Le département. Exemple: Paris
     * @bodyParam motivation string requis La motivation. Exemple: Indépendance
     * @bodyParam change_region string requis Volonté de changer de région. Exemple: Non
     * @bodyParam project string requis Description du projet. Exemple: E-commerce
     * @bodyParam reason string requis Raison du choix. Exemple: Marché porteur
     * @bodyParam alone_team string requis Seul ou en équipe. Exemple: Seul
     * @bodyParam echeance string requis Échéance de lancement. Exemple: 6 mois
     * @bodyParam employe integer requis Nombre d'employés prévus. Exemple: 2
     * @bodyParam employe_three integer requis Nombre d'employés à 3 ans. Exemple: 5
     * @bodyParam investment_amount integer requis Montant d'investissement. Exemple: 50000
     * @bodyParam investment_share integer requis Part d'investissement personnel. Exemple: 20000
     * @bodyParam finance string requis Mode de financement. Exemple: Prêt bancaire
     * @bodyParam activity string requis Secteur d'activité. Exemple: Commerce
     * @bodyParam development_stage string requis Stade de développement. Exemple: Idée
     * @bodyParam marketing_condition string requis Conditions de commercialisation. Exemple: En ligne
     * @bodyParam season string requis Saisonnalité. Exemple: Non
     * @bodyParam eco_modal string requis Modèle économique. Exemple: Vente directe
     * @bodyParam start_strategy string requis Stratégie de lancement. Exemple: Marketing digital
     * @bodyParam market_qualification string requis Qualification du marché. Exemple: Porteur
     * @bodyParam market_geography string requis Géographie du marché. Exemple: National
     * @bodyParam clients_identified string requis Clients identifiés. Exemple: Oui
     * @bodyParam client_knowledge string requis Connaissance client. Exemple: Bonne
     * @bodyParam competitors_identified string requis Concurrents identifiés. Exemple: Oui
     * @bodyParam suppliers_identified string requis Fournisseurs identifiés. Exemple: Oui
     * @bodyParam supplier_characteristics string requis Caractéristiques fournisseurs. Exemple: Fiables
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "document_id": 1,
     *     "ai_response": {
     *       "positifs": "Points positifs identifiés par l'IA...",
     *       "negatifs": "Points négatifs à améliorer...",
     *       "ameliorations": "Suggestions d'amélioration détaillées..."
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Error generating response: message d'erreur"
     * }
     */
    public function generateResponse(Request $request){
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        try {
            $validatedData = $request->validate([
                'age' => 'required|string',
                'situationPro' => 'required|string',
                'activiteCorrespond' => 'required|string',
                'experiencePro' => 'required|numeric',
                'soutienEntourage' => 'required|string',
                'departement' => 'required|string',
                'critereChoix' => 'required|string',
                'mobilite' => 'required|string',
                'projetDescription' => 'required|string',
                'projetRaison' => 'required|string',
                'projetEquipe' => 'required|string',
                'projetEcheance' => 'required|string',
                'emploisDemarrage' => 'required|numeric',
                'emploisTroisAns' => 'required|numeric',
                'montantInvestissements' => 'required|numeric',
                'apportPersonnelPourcentage' => 'required|numeric',
                'projetFinancement' => 'required|string',
                'produitSecteurActivite' => 'required|string',
                'produitStadeDeveloppement' => 'required|string',
                'produitConditionCommercialisation' => 'required|string',
                'produitSaisonnalite' => 'required|string',
                'produitModeleEconomique' => 'required|string',
                'produitStrategieDemarrage' => 'required|string',
                'marcheQualification' => 'required|string',
                'marcheDimensionGeographique' => 'required|string',
                'marcheFutursClientsIdentification' => 'required|string',
                'marcheFutursClientsConnaissance' => 'required|string',
                'marcheConcurrentsIdentification' => 'required|string',
                'marcheFournisseursIdentification' => 'required|string',
                'marcheFournisseursCaracteristiques' => 'required|string',
                'document_id' => 'nullable|integer',
            ]);

            $formData = $validatedData;
            
            // Extraire les données pour l'IA
            $age = $validatedData['age'];
            $situation = $validatedData['situationPro'];
            $diplome = $validatedData['activiteCorrespond'];
            $experience = $validatedData['experiencePro'];
            $soutien = $validatedData['soutienEntourage'];
            $department = $validatedData['departement'];
            $motivation = $validatedData['critereChoix'];
            $change_region = $validatedData['mobilite'];
            $project = $validatedData['projetDescription'];
            $reason = $validatedData['projetRaison'];
            $alone_team = $validatedData['projetEquipe'];
            $echeance = $validatedData['projetEcheance'];
            $employe = $validatedData['emploisDemarrage'];
            $employe_three = $validatedData['emploisTroisAns'];
            $investment_amount = $validatedData['montantInvestissements'];
            $investment_share = $validatedData['apportPersonnelPourcentage'];
            $finance = $validatedData['projetFinancement'];
            $activity = $validatedData['produitSecteurActivite'];
            $development_stage = $validatedData['produitStadeDeveloppement'];
            $marketing_condition = $validatedData['produitConditionCommercialisation'];
            $season = $validatedData['produitSaisonnalite'];
            $eco_modal = $validatedData['produitModeleEconomique'];
            $start_strategy = $validatedData['produitStrategieDemarrage'];
            $market_qualification = $validatedData['marcheQualification'];
            $market_geography = $validatedData['marcheDimensionGeographique'];
            $clients_identified = $validatedData['marcheFutursClientsIdentification'];
            $client_knowledge = $validatedData['marcheFutursClientsConnaissance'];
            $competitors_identified = $validatedData['marcheConcurrentsIdentification'];
            $suppliers_identified = $validatedData['marcheFournisseursIdentification'];
            $supplier_characteristics = $validatedData['marcheFournisseursCaracteristiques'];
            
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

    /**
     * Afficher la page de réponse d'évaluation.
     *
     * @group Évaluation
     *
     * Cette route retourne l'évaluation IA d'un document avec le contenu
     * formaté en HTML via CommonMark pour l'affichage.
     *
     * @urlParam id integer requis L'ID du document. Exemple: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "response": {
     *       "positifs": "<p>Points positifs formatés en HTML...</p>",
     *       "negatifs": "<p>Points négatifs formatés en HTML...</p>",
     *       "ameliorations": "<p>Améliorations formatées en HTML...</p>"
     *     },
     *     "notificationcount": 3
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Evaluation not found"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Error retrieving response: message d'erreur"
     * }
     */
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
