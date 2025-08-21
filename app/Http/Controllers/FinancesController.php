<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Finance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class FinancesController extends Controller
{
    /**
     * Afficher la page des finances.
     *
     * @group Finances
     *
     * Cette route retourne les informations de base pour la page des finances
     * avec le nombre de notifications non lues de l'utilisateur.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "notificationcount": 3
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Error retrieving data: message d'erreur"
     * }
     */
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

    /**
     * Ajouter un produit financier.
     *
     * @group Finances
     *
     * Cette route permet d'ajouter un nouveau produit avec ses coûts détaillés
     * (matières premières, main d'œuvre, coûts indirects) à un document.
     *
     * @bodyParam document_id integer requis L'ID du document. Exemple: 1
     * @bodyParam produits string requis Les données du produit au format JSON. Exemple: {"nom":"Produit A","matieres_premieres":[{"designation":"Matière 1","quantite":10,"prix_unitaire":5,"sous_total":50}],"main_doeuvre_directe":[{"designation":"Ouvrier","heures":8,"taux_horaire":15,"sous_total":120}],"couts_indirects":[{"designation":"Electricité","montant":30,"sous_total":30}]}
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Produit ajouté avec succès",
     *   "data": {
     *     "produit": {
     *       "nom": "Produit A",
     *       "total_matieres_premieres_ht": 50,
     *       "total_main_doeuvre_directe_ht": 120,
     *       "total_couts_indirects_ht": 30,
     *       "total_ht": 200
     *     },
     *     "produits_total": 200
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "document_id": ["Le champ document_id est requis"]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Error storing produit: message d'erreur"
     * }
     */
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

    /**
     * Ajouter des frais de pré-exploitation.
     *
     * @group Finances
     *
     * Cette route permet d'ajouter des frais de pré-exploitation au capital de démarrage
     * d'un document. Ces frais sont nécessaires avant le lancement de l'activité.
     *
     * @bodyParam document_id integer requis L'ID du document. Exemple: 1
     * @bodyParam preexploitation string requis Les données de pré-exploitation au format JSON. Exemple: {"designation":"Frais de constitution","montant":1500,"sous_total":1500}
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Pré-exploitation ajoutée avec succès",
     *   "data": {
     *     "preexploitation": {
     *       "designation": "Frais de constitution",
     *       "montant": 1500,
     *       "total_ht": 1500
     *     },
     *     "capital_demarrage_total": 1500
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "document_id": ["Le champ document_id est requis"]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Error storing preexploitation: message d'erreur"
     * }
     */
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

    /**
     * Ajouter une immobilisation.
     *
     * @group Finances
     *
     * Cette route permet d'ajouter une immobilisation (matériel, équipement, etc.)
     * au capital de démarrage d'un document.
     *
     * @bodyParam document_id integer requis L'ID du document. Exemple: 1
     * @bodyParam immobilisation string requis Les données d'immobilisation au format JSON. Exemple: {"designation":"Ordinateur","quantite":2,"prix_unitaire":800,"sous_total":1600}
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Immobilisation ajoutée avec succès",
     *   "data": {
     *     "immobilisation": {
     *       "designation": "Ordinateur",
     *       "quantite": 2,
     *       "prix_unitaire": 800,
     *       "total_ht": 1600
     *     },
     *     "capital_demarrage_total": 1600
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "document_id": ["Le champ document_id est requis"]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Error storing immobilisation: message d'erreur"
     * }
     */
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

    /**
     * Ajouter des fonds de roulement.
     *
     * @group Finances
     *
     * Cette route permet d'ajouter des fonds de roulement nécessaires
     * au fonctionnement quotidien de l'entreprise.
     *
     * @bodyParam document_id integer requis L'ID du document. Exemple: 1
     * @bodyParam fonds_de_roulement string requis Les données de fonds de roulement au format JSON. Exemple: {"designation":"Stock initial","montant":5000,"sous_total":5000}
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Fonds de roulement ajouté avec succès",
     *   "data": {
     *     "fonds_de_roulement": {
     *       "designation": "Stock initial",
     *       "montant": 5000,
     *       "total_ht": 5000
     *     },
     *     "capital_demarrage_total": 5000
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "document_id": ["Le champ document_id est requis"]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Error storing fonds de roulement: message d'erreur"
     * }
     */
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

    /**
     * Récupérer les données financières d'un document.
     *
     * @group Finances
     *
     * Cette route récupère toutes les données financières d'un document spécifique,
     * y compris le calcul automatique du flux de trésorerie sur 12 mois.
     *
     * @urlParam id integer requis L'ID du document. Exemple: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "document_id": 1,
     *     "produits": [],
     *     "capital_demarrage": {},
     *     "vente_previsionnels": [],
     *     "emprunts": [],
     *     "cash_flow": {
     *       "encaissements": {},
     *       "decaissements": {},
     *       "totaux": {}
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Aucune donnée financière trouvée pour ce document"
     * }
     */
    public function getDocumentFinances($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $finances = Finance::where('document_id', $id)->first();

            if ($finances) {
                // Calculer les données de flux de trésorerie
                $cashFlow = $this->calculateCashFlow($finances);
                
                // Ajouter les données de flux de trésorerie à la réponse
                $finances->cash_flow = $cashFlow;
                
                return response()->json([
                    'success' => true,
                    'data' => $finances
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune donnée financière trouvée pour ce document'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving financial data: ' . $e->getMessage()
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

    /**
     * Calcule les données du tableau de flux de trésorerie
     * 
     * @param Finance $finance Objet Finance contenant les données financières
     * @return array Données de flux de trésorerie pour les 12 mois
     */
    private function calculateCashFlow($finance)
    {
        if (!$finance) {
            return null;
        }
        
        // Initialiser le tableau de flux de trésorerie pour 12 mois
        $cashFlow = [
            'encaissements' => [
                'ventes_comptant' => array_fill(0, 12, 0),
                'comptes_debiteurs' => array_fill(0, 12, 0),
                'fonds_propres' => array_fill(0, 12, 0),
                'emprunts' => array_fill(0, 12, 0),
                'ventes_immobilisations' => array_fill(0, 12, 0)
            ],
            'decaissements' => [
                'actifs_immobilises' => array_fill(0, 12, 0),
                'avances_depenses' => array_fill(0, 12, 0),
                'charges_preexploitation' => array_fill(0, 12, 0),
                'biens_investissements' => array_fill(0, 12, 0),
                'main_doeuvre' => array_fill(0, 12, 0),
                'frais_production' => array_fill(0, 12, 0),
                'depenses_marketing' => array_fill(0, 12, 0),
                'frais_administratifs' => array_fill(0, 12, 0),
                'remboursement_pret' => array_fill(0, 12, 0),
                'tva' => array_fill(0, 12, 0),
                'impots' => array_fill(0, 12, 0)
            ],
            'totaux' => [
                'total_encaissements' => array_fill(0, 12, 0),
                'total_decaissements' => array_fill(0, 12, 0),
                'flux_tresorerie_net' => array_fill(0, 12, 0),
                'solde_initial' => array_fill(0, 12, 0),
                'solde_final' => array_fill(0, 12, 0)
            ]
        ];
        
        // Traiter les ventes prévisionnelles pour les encaissements
        if ($finance->vente_previsionnels && is_array($finance->vente_previsionnels)) {
            foreach ($finance->vente_previsionnels as $vente) {
                if (isset($vente['date_debut']) && isset($vente['duree_mois'])) {
                    $dateDebut = new \DateTime($vente['date_debut']);
                    $moisDebut = (int)$dateDebut->format('n') - 1;
                    $duree = min((int)$vente['duree_mois'], 12 - $moisDebut);
                    
                    $venteMensuelle = ($vente['total_produits_ht'] ?? 0) / $duree;
                    
                    for ($i = 0; $i < $duree; $i++) {
                        $mois = ($moisDebut + $i) % 12;
                        
                        if (isset($vente['delai_paiement']) && $vente['delai_paiement'] > 0) {
                            $moisPaiement = ($mois + $vente['delai_paiement']) % 12;
                            $cashFlow['encaissements']['comptes_debiteurs'][$moisPaiement] += $venteMensuelle;
                        } else {
                            $cashFlow['encaissements']['ventes_comptant'][$mois] += $venteMensuelle;
                        }
                    }
                }
            }
        }
        
        // Calculer les totaux
        for ($i = 0; $i < 12; $i++) {
            $cashFlow['totaux']['total_encaissements'][$i] = 
                $cashFlow['encaissements']['ventes_comptant'][$i] +
                $cashFlow['encaissements']['comptes_debiteurs'][$i] +
                $cashFlow['encaissements']['fonds_propres'][$i] +
                $cashFlow['encaissements']['emprunts'][$i] +
                $cashFlow['encaissements']['ventes_immobilisations'][$i];
            
            $cashFlow['totaux']['total_decaissements'][$i] = 
                $cashFlow['decaissements']['actifs_immobilises'][$i] +
                $cashFlow['decaissements']['avances_depenses'][$i] +
                $cashFlow['decaissements']['charges_preexploitation'][$i] +
                $cashFlow['decaissements']['biens_investissements'][$i] +
                $cashFlow['decaissements']['main_doeuvre'][$i] +
                $cashFlow['decaissements']['frais_production'][$i] +
                $cashFlow['decaissements']['depenses_marketing'][$i] +
                $cashFlow['decaissements']['frais_administratifs'][$i] +
                $cashFlow['decaissements']['remboursement_pret'][$i] +
                $cashFlow['decaissements']['tva'][$i] +
                $cashFlow['decaissements']['impots'][$i];
            
            $cashFlow['totaux']['flux_tresorerie_net'][$i] = 
                $cashFlow['totaux']['total_encaissements'][$i] - 
                $cashFlow['totaux']['total_decaissements'][$i];
            
            if ($i === 0) {
                $cashFlow['totaux']['solde_initial'][$i] = 0;
            } else {
                $cashFlow['totaux']['solde_initial'][$i] = $cashFlow['totaux']['solde_final'][$i - 1];
            }
            
            $cashFlow['totaux']['solde_final'][$i] = 
                $cashFlow['totaux']['solde_initial'][$i] + 
                $cashFlow['totaux']['flux_tresorerie_net'][$i];
        }
        
        return $cashFlow;
    }

    /**
     * Récupérer un produit spécifique.
     *
     * @group Finances
     *
     * Cette route récupère les détails d'un produit spécifique d'un document
     * avec tous ses coûts détaillés.
     *
     * @urlParam id integer required L'ID du document. Exemple: 1
     * @urlParam produit_index integer required L'index du produit dans le tableau. Exemple: 0
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "produit": {
     *       "nom": "Produit A",
     *       "matieres_premieres": [{"designation": "Matière 1", "quantite": 10, "prix_unitaire": 5}],
     *       "main_doeuvre_directe": [{"designation": "Ouvrier", "heures": 8, "taux_horaire": 15}],
     *       "couts_indirects": [{"designation": "Electricité", "montant": 30}],
     *       "total_ht": 200
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Produit non trouvé"
     * }
     */
    public function getProduit($id, $produit_index)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $finance = Finance::where('document_id', $id)->first();
            
            if (!$finance || !$finance->produits || !is_array($finance->produits)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun produit trouvé pour ce document'
                ], 404);
            }

            if (!isset($finance->produits[$produit_index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'produit' => $finance->produits[$produit_index]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du produit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les données de capital de démarrage (pré-exploitation).
     *
     * @group Finances
     *
     * Cette route récupère les frais de pré-exploitation d'un document spécifique
     * avec le calcul du total.
     *
     * @urlParam id integer required L'ID du document. Exemple: 1
     * @urlParam preexploitation_index integer required L'index de la pré-exploitation. Exemple: 0
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "preexploitation": {
     *       "designation": "Frais de constitution",
     *       "cout_unitaire": 500,
     *       "quantite": 1,
     *       "total_ht": 500
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Données de pré-exploitation non trouvées"
     * }
     */
    public function getCapitalDemarrage($id, $preexploitation_index)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $finance = Finance::where('document_id', $id)->first();
            
            if (!$finance || !$finance->capital_demarrage || !is_array($finance->capital_demarrage)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune donnée de capital de démarrage trouvée pour ce document'
                ], 404);
            }

            if (!isset($finance->capital_demarrage['preexploitation'][$preexploitation_index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de pré-exploitation non trouvées'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'preexploitation' => $finance->capital_demarrage['preexploitation'][$preexploitation_index]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer une immobilisation spécifique.
     *
     * @group Finances
     *
     * Cette route récupère les détails d'une immobilisation spécifique
     * du capital de démarrage d'un document.
     *
     * @urlParam id integer required L'ID du document. Exemple: 1
     * @urlParam immobilisation_index integer required L'index de l'immobilisation. Exemple: 0
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "immobilisation": {
     *       "designation": "Ordinateur portable",
     *       "cout_unitaire": 1200,
     *       "quantite": 2,
     *       "total_ht": 2400
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Immobilisation non trouvée"
     * }
     */
    public function getImmobilisation($id, $immobilisation_index)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $finance = Finance::where('document_id', $id)->first();
            
            if (!$finance || !$finance->capital_demarrage || !is_array($finance->capital_demarrage)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune donnée de capital de démarrage trouvée pour ce document'
                ], 404);
            }

            if (!isset($finance->capital_demarrage['immobilisation'][$immobilisation_index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Immobilisation non trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'immobilisation' => $finance->capital_demarrage['immobilisation'][$immobilisation_index]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'immobilisation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer des fonds de roulement spécifiques.
     *
     * @group Finances
     *
     * Cette route récupère les détails des fonds de roulement spécifiques
     * du capital de démarrage d'un document.
     *
     * @urlParam id integer required L'ID du document. Exemple: 1
     * @urlParam fonds_index integer required L'index des fonds de roulement. Exemple: 0
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "fonds_de_roulement": {
     *       "designation": "Stock initial",
     *       "cout_unitaire": 5000,
     *       "quantite": 1,
     *       "total_ht": 5000
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Fonds de roulement non trouvés"
     * }
     */
    public function getFondsDeRoulement($id, $fonds_index)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $finance = Finance::where('document_id', $id)->first();
            
            if (!$finance || !$finance->capital_demarrage || !is_array($finance->capital_demarrage)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune donnée de capital de démarrage trouvée pour ce document'
                ], 404);
            }

            if (!isset($finance->capital_demarrage['fonds_de_roulement'][$fonds_index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fonds de roulement non trouvés'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'fonds_de_roulement' => $finance->capital_demarrage['fonds_de_roulement'][$fonds_index]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des fonds de roulement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer une vente prévisionnelle spécifique.
     *
     * @group Finances
     *
     * Cette route récupère les détails d'une vente prévisionnelle spécifique
     * d'un document avec tous ses paramètres.
     *
     * @urlParam id integer required L'ID du document. Exemple: 1
     * @urlParam vente_index integer required L'index de la vente prévisionnelle. Exemple: 0
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "vente": {
     *       "date_debut": "2024-01-01",
     *       "duree_mois": 12,
     *       "total_produits_ht": 120000,
     *       "delai_paiement": 30,
     *       "produits": [{"nom": "Produit A", "quantite": 100, "prix_unitaire": 50}]
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Vente prévisionnelle non trouvée"
     * }
     */
    public function getVente($id, $vente_index)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $finance = Finance::where('document_id', $id)->first();
            
            if (!$finance || !$finance->vente_previsionnels || !is_array($finance->vente_previsionnels)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune vente prévisionnelle trouvée pour ce document'
                ], 404);
            }

            if (!isset($finance->vente_previsionnels[$vente_index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vente prévisionnelle non trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'vente' => $finance->vente_previsionnels[$vente_index]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la vente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un emprunt spécifique.
     *
     * @group Finances
     *
     * Cette route récupère les détails d'un emprunt spécifique
     * d'un document avec tous ses paramètres de remboursement.
     *
     * @urlParam id integer required L'ID du document. Exemple: 1
     * @urlParam emprunt_index integer required L'index de l'emprunt. Exemple: 0
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "emprunt": {
     *       "montant_emprunte": 50000,
     *       "taux_interet": 5.5,
     *       "duree_mois": 60,
     *       "mensualite": 955.65,
     *       "date_debut": "2024-01-01"
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Emprunt non trouvé"
     * }
     */
    public function getEmprunt($id, $emprunt_index)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $finance = Finance::where('document_id', $id)->first();
            
            if (!$finance || !$finance->emprunts || !is_array($finance->emprunts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun emprunt trouvé pour ce document'
                ], 404);
            }

            if (!isset($finance->emprunts[$emprunt_index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Emprunt non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'emprunt' => $finance->emprunts[$emprunt_index]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'emprunt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter une vente prévisionnelle.
     *
     * @group Finances
     *
     * Cette route permet d'ajouter une nouvelle vente prévisionnelle
     * avec ses produits et paramètres de paiement.
     *
     * @bodyParam document_id integer requis L'ID du document. Exemple: 1
     * @bodyParam vente_previsionnelle string requis Les données de la vente au format JSON. Exemple: {"date_debut":"2024-01-01","duree_mois":12,"delai_paiement":30,"produits":[{"nom":"Produit A","quantite":100,"prix_unitaire":50}]}
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Vente prévisionnelle ajoutée avec succès",
     *   "data": {
     *     "vente": {
     *       "date_debut": "2024-01-01",
     *       "duree_mois": 12,
     *       "total_produits_ht": 5000,
     *       "delai_paiement": 30,
     *       "produits": [{"nom": "Produit A", "quantite": 100, "prix_unitaire": 50}]
     *     },
     *     "ventes_total": 5000
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "document_id": ["Le champ document_id est requis"]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Error storing vente: message d'erreur"
     * }
     */
    public function storeVente(Request $request)
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
                'vente_previsionnelle' => 'required|json'
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
            $venteJson = $request->input('vente_previsionnelle');
            $venteData = json_decode($venteJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format JSON invalide pour vente_previsionnelle'
                ], 422);
            }
    
            // Calculer le total des produits
            $totalProduitsHt = 0;
            if (isset($venteData['produits']) && is_array($venteData['produits'])) {
                foreach ($venteData['produits'] as $produit) {
                    $quantite = $produit['quantite'] ?? 0;
                    $prixUnitaire = $produit['prix_unitaire'] ?? 0;
                    $totalProduitsHt += $quantite * $prixUnitaire;
                }
            }
            
            $venteData['total_produits_ht'] = $totalProduitsHt;
    
            // Récupérer ou créer l'enregistrement Finance
            $finance = Finance::where('document_id', $documentId)->first();
            if (!$finance) {
                $finance = new Finance();
                $finance->document_id = $documentId;
                $finance->vente_previsionnels = [];
            }
    
            // Ajouter la nouvelle vente
            $ventesPrevisionnelles = $finance->vente_previsionnels ?? [];
            $ventesPrevisionnelles[] = $venteData;
            $finance->vente_previsionnels = $ventesPrevisionnelles;
    
            // Sauvegarder
            $finance->save();
    
            // Calculer le total des ventes
            $ventesTotal = 0;
            foreach ($ventesPrevisionnelles as $vente) {
                $ventesTotal += $vente['total_produits_ht'] ?? 0;
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Vente prévisionnelle ajoutée avec succès',
                'data' => [
                    'vente' => $venteData,
                    'ventes_total' => $ventesTotal
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error storing vente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter un emprunt.
     *
     * @group Finances
     *
     * Cette route permet d'ajouter un nouvel emprunt avec ses paramètres
     * de remboursement et calcul automatique des mensualités.
     *
     * @bodyParam document_id integer requis L'ID du document. Exemple: 1
     * @bodyParam emprunt string requis Les données de l'emprunt au format JSON. Exemple: {"montant_emprunte":50000,"taux_interet":5.5,"duree_mois":60,"date_debut":"2024-01-01"}
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Emprunt ajouté avec succès",
     *   "data": {
     *     "emprunt": {
     *       "montant_emprunte": 50000,
     *       "taux_interet": 5.5,
     *       "duree_mois": 60,
     *       "mensualite": 955.65,
     *       "date_debut": "2024-01-01"
     *     },
     *     "emprunts_total": 50000
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "document_id": ["Le champ document_id est requis"]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Error storing emprunt: message d'erreur"
     * }
     */
    public function storeEmprunt(Request $request)
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
                'emprunt' => 'required|json'
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
            $empruntJson = $request->input('emprunt');
            $empruntData = json_decode($empruntJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format JSON invalide pour emprunt'
                ], 422);
            }
    
            // Calculer la mensualité si les données sont présentes
            if (isset($empruntData['montant_emprunte'], $empruntData['taux_interet'], $empruntData['duree_mois'])) {
                $montant = (float) $empruntData['montant_emprunte'];
                $tauxMensuel = (float) $empruntData['taux_interet'] / 100 / 12;
                $duree = (int) $empruntData['duree_mois'];
                
                if ($tauxMensuel > 0 && $duree > 0) {
                    $mensualite = $montant * ($tauxMensuel * pow(1 + $tauxMensuel, $duree)) / (pow(1 + $tauxMensuel, $duree) - 1);
                    $empruntData['mensualite'] = round($mensualite, 2);
                } else {
                    $empruntData['mensualite'] = $duree > 0 ? round($montant / $duree, 2) : 0;
                }
            }
    
            // Récupérer ou créer l'enregistrement Finance
            $finance = Finance::where('document_id', $documentId)->first();
            if (!$finance) {
                $finance = new Finance();
                $finance->document_id = $documentId;
                $finance->emprunts = [];
            }
    
            // Ajouter le nouvel emprunt
            $emprunts = $finance->emprunts ?? [];
            $emprunts[] = $empruntData;
            $finance->emprunts = $emprunts;
    
            // Sauvegarder
            $finance->save();
    
            // Calculer le total des emprunts
            $empruntsTotal = 0;
            foreach ($emprunts as $emprunt) {
                $empruntsTotal += $emprunt['montant_emprunte'] ?? 0;
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Emprunt ajouté avec succès',
                'data' => [
                    'emprunt' => $empruntData,
                    'emprunts_total' => $empruntsTotal
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error storing emprunt: ' . $e->getMessage()
            ], 500);
        }
    }
}
