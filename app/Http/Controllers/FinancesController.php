<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Finance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class FinancesController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        try {
            $user = Auth::user();
            $notificationcount = Notification::where('user_id', $user->id)->where('read', 0)->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'notificationcount' => $notificationcount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Fonction pour ajouter les produits
    public function storeProduit(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        try {
            // Validation des données
            $validator = validator($request->all(), [
                'document_id' => 'required|exists:documents,id',
                'produits' => 'required|json'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            // Récupérer les données
            $documentId = $request->input('document_id');
            $produitsJson = $request->input('produits');
            $produitsData = json_decode($produitsJson, true);
    
            // Récupérer l'enregistrement Finance existant ou en créer un nouveau
            $finance = Finance::firstOrNew(['document_id' => $documentId]);
    
            // Récupérer les produits existants ou initialiser un tableau vide
            $produits = $finance->produits ?? [];
    
            // Calculer les coûts totaux pour ce produit
            $totalMatieresPremieresHT = 0;
            $totalMainDoeuvreDirecteHT = 0;
            $totalCoutsIndirectsHT = 0;
    
            // Matières premières
            if (isset($produitsData['matieres_premieres']) && is_array($produitsData['matieres_premieres'])) {
                foreach ($produitsData['matieres_premieres'] as $matiere) {
                    $totalMatieresPremieresHT += $matiere['sous_total'] ?? 0;
                }
            }
    
            // Main d'œuvre directe
            if (isset($produitsData['main_doeuvre_directe']) && is_array($produitsData['main_doeuvre_directe'])) {
                foreach ($produitsData['main_doeuvre_directe'] as $main) {
                    $totalMainDoeuvreDirecteHT += $main['sous_total'] ?? 0;
                }
            }
    
            // Coûts indirects
            if (isset($produitsData['couts_indirects']) && is_array($produitsData['couts_indirects'])) {
                foreach ($produitsData['couts_indirects'] as $cout) {
                    $totalCoutsIndirectsHT += $cout['sous_total'] ?? 0;
                }
            }
    
            // Ajouter les totaux au produit
            $produitsData['total_matieres_premieres_ht'] = $totalMatieresPremieresHT;
            $produitsData['total_main_doeuvre_directe_ht'] = $totalMainDoeuvreDirecteHT;
            $produitsData['total_couts_indirects_ht'] = $totalCoutsIndirectsHT;
    
            // Calculer le coût total du produit (hors taxes)
            $totalHT = $totalMatieresPremieresHT + $totalMainDoeuvreDirecteHT + $totalCoutsIndirectsHT;
            $produitsData['total_ht'] = $totalHT;
            
            // Calculer le total des coûts opérationnels sans taxes
            $produitsData['total_couts_operationnels_ht'] = $totalHT;

            // Calculer le coût total avec taxes
            $taxes = $produitsData['taxes'] ?? 0;
            $montantTaxes = $totalHT * ($taxes / 100);
            $produitsData['total_ttc'] = $totalHT + $montantTaxes;
            
            // Calculer le coût par unité avec taxes
            $quantite = $produitsData['quantite'] ?? 1; // Utiliser 1 comme valeur par défaut si non défini
            if ($quantite > 0) {
                $produitsData['cout_unitaire_ttc'] = ($totalHT + $montantTaxes) / $quantite;
            } else {
                $produitsData['cout_unitaire_ttc'] = 0;
            }
    
            // Ajouter le nouveau produit
            $produits[] = $produitsData;
    
            // Mettre à jour le champ produits
            $finance->produits = $produits;
            $finance->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Produit ajouté avec succès',
                'data' => $produitsData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error storing product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storePreExploitation(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        try {
            // Validation des données
            $validator = validator($request->all(), [
                'document_id' => 'required|exists:documents,id',
                'preexploitation' => 'required|json'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            // Récupérer les données
            $documentId = $request->input('document_id');
            $preexploitationJson = $request->input('preexploitation');
            $preexploitationData = json_decode($preexploitationJson, true);
    
            // Récupérer l'enregistrement Finance existant ou en créer un nouveau
            $finance = Finance::firstOrNew(['document_id' => $documentId]);
    
            // Récupérer les produits existants ou initialiser un tableau vide
            $capitalDemarrage = $finance->capital_demarrage ?? [
                'preexploitation' => [],
                'immobilisation' => [],
                'fonds_de_roulement' => [],
                'total' => 0
            ];
    
            // Calculer le coût total pour cette préexploitation
            $totalPreexploitationHT = $preexploitationData['sous_total'] ?? 0;
            
            // Ajouter le total HT aux données
            $preexploitationData['total_ht'] = $totalPreexploitationHT;
            
            // Ajouter les nouvelles données de préexploitation
            $capitalDemarrage['preexploitation'][] = $preexploitationData;
    
            // Recalculer le total global
            $capitalDemarrage['total'] =
                $this->calculateTotalFromSection($capitalDemarrage['preexploitation']) +
                $this->calculateTotalFromSection($capitalDemarrage['immobilisation']) +
                $this->calculateTotalFromSection($capitalDemarrage['fonds_de_roulement']);
    
            // Mettre à jour le champ produits
            $finance->capital_demarrage = $capitalDemarrage;
            $finance->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Préexploitation ajoutée avec succès',
                'data' => [
                    'preexploitation' => $preexploitationData,
                    'capital_demarrage_total' => $capitalDemarrage['total']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error storing preexploitation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeImmobilisation(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        try {
            // Validation des données
            $validator = validator($request->all(), [
                'document_id' => 'required|exists:documents,id',
                'immobilisation' => 'required|json'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            // Récupérer les données
            $documentId = $request->input('document_id');
            $immobilisationJson = $request->input('immobilisation');
            $immobilisationData = json_decode($immobilisationJson, true);
    
            // Récupérer l'enregistrement Finance existant ou en créer un nouveau
            $finance = Finance::firstOrNew(['document_id' => $documentId]);
    
            // Récupérer les produits existants ou initialiser un tableau vide
            $capitalDemarrage = $finance->capital_demarrage ?? [
                'preexploitation' => [],
                'immobilisation' => [],
                'fonds_de_roulement' => [],
                'total' => 0
            ];
    
            // Calculer le coût total pour cette immobilisation
            $totalImmobilisationHT = $immobilisationData['sous_total'] ?? 0;
            
            // Ajouter le total HT aux données
            $immobilisationData['total_ht'] = $totalImmobilisationHT;
    
            $capitalDemarrage['immobilisation'][] = $immobilisationData;
    
            // Recalculer le total global
            $capitalDemarrage['total'] =
                $this->calculateTotalFromSection($capitalDemarrage['preexploitation']) +
                $this->calculateTotalFromSection($capitalDemarrage['immobilisation']) +
                $this->calculateTotalFromSection($capitalDemarrage['fonds_de_roulement']);
    
            // Mettre à jour le champ produits
            $finance->capital_demarrage = $capitalDemarrage;
            $finance->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Immobilisation ajoutée avec succès',
                'data' => [
                    'immobilisation' => $immobilisationData,
                    'capital_demarrage_total' => $capitalDemarrage['total']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error storing immobilisation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeFondsDeRoulement(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        try {
            // Validation des données
            $validator = validator($request->all(), [
                'document_id' => 'required|exists:documents,id',
                'fonds_de_roulement' => 'required|json'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            // Récupérer les données
            $documentId = $request->input('document_id');
            $fondsDeRoulementJson = $request->input('fonds_de_roulement');
            $fondsDeRoulementData = json_decode($fondsDeRoulementJson, true);
    
            // Récupérer l'enregistrement Finance existant ou en créer un nouveau
            $finance = Finance::firstOrNew(['document_id' => $documentId]);
    
            // Récupérer les produits existants ou initialiser un tableau vide
            $capitalDemarrage = $finance->capital_demarrage ?? [
                'preexploitation' => [],
                'immobilisation' => [],
                'fonds_de_roulement' => [],
                'total' => 0
            ];
    
            /// Calculer le coût total pour ce fonds de roulement
            $totalFondsDeRoulementHT = $fondsDeRoulementData['sous_total'] ?? 0;
            
            // Ajouter le total HT aux données
            $fondsDeRoulementData['total_ht'] = $totalFondsDeRoulementHT;
            
            // Ajouter les nouvelles données de fonds de roulement
            $capitalDemarrage['fonds_de_roulement'][] = $fondsDeRoulementData;
    
            // Recalculer le total global
            $capitalDemarrage['total'] =
                $this->calculateTotalFromSection($capitalDemarrage['preexploitation']) +
                $this->calculateTotalFromSection($capitalDemarrage['immobilisation']) +
                $this->calculateTotalFromSection($capitalDemarrage['fonds_de_roulement']);
    
            // Mettre à jour le champ produits
            $finance->capital_demarrage = $capitalDemarrage;
            $finance->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Fonds de roulement ajouté avec succès',
                'data' => [
                    'fonds_de_roulement' => $fondsDeRoulementData,
                    'capital_demarrage_total' => $capitalDemarrage['total']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error storing fonds de roulement: ' . $e->getMessage()
            ], 500);
        }
    }

    // Fonction utilitaire pour calculer le total d'une section
    private function calculateTotalFromSection($section)
    {
        $total = 0;
        foreach ($section as $item) {
            $total += $item['total_ht'] ?? 0;
        }
        return $total;
    }
}
