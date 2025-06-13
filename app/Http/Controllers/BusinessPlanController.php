<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Template;
use App\Models\TemplateCustom;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Barryvdh\Snappy\Facades\SnappyPdf;

class BusinessPlanController extends Controller
{
    public function addpblan()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        // Vérifie s'il existe déjà un brouillon
        $draft = Document::where('user_id', Auth::id())
            ->where('type', 'business_plan')
            ->where('status', 'draft')
            ->first();

        if ($draft) {
            Session::put('current_business_plan_id', $draft->id);
            return response()->json([
                'success' => true,
                'message' => 'Brouillon existant',
                'data' => [
                    'draft_id' => $draft->id
                ]
            ]);
        }

        // Crée un nouveau document vide si aucun brouillon n'existe
        $document = Document::create([
            'user_id' => Auth::id(),
            'type' => 'business_plan',
            'content' => [],
            'status' => 'draft'
        ]);

        $templates = Template::where('type', 'business-plan')->get();
        $templatecustom = new TemplateCustom();
        $templatecustom->template_id = Template::where('type', 'business-plan')->where('is_default', true)->first()->id;
        $templatecustom->document_id = $document->id;
        $templatecustom->style = [];
        $templatecustom->save();

        Session::put('current_business_plan_id', $document->id);
        return response()->json([
            'success' => true,
            'message' => 'Nouveau business plan créé',
            'data' => [
                'business' => $document,
                'templates' => $templates,
                'templatecustom' => $templatecustom
            ]
        ]);
    }

    public function editpblan($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        try {
            $business = Document::where('user_id', Auth::id())
                ->where('type', 'business_plan')
                ->findOrFail($id);
    
            // Initialise les sections si elles n'existent pas
            $business->content = array_merge([
                'information_genrale' => [
                    'nom_projet' => '',
                    'secteur_activite' => '',
                    'nom_entreprise' => '',
                    'statut_juridique' => '',
                    'adresse_contact' => ''
                ],
                'resume_executif' => [
                    'description_courte_projet' => '',
                    'objectifs_principaux' => '',
                    'proposition_valeur' => ''
                ],
                'etude_marche' => [
                    'public_cible' => '',
                    'concurrence_existante' => '',
                    'analyse_tendances_marche' => '',
                    'avantages_concurrentiels' => ''
                ],
                'strategie_commerciale_marketing' => [
                    'canaux_distribution' => '',
                    'strategie_marketing' => '',
                    'plan_communication' => ''
                ],
                'organisation_gestion' => [
                    'equipe_responsabilites' => [
                        'equipe1' => '',
                        'responsabilites1' => '',
                        'equipe2' => '',
                        'responsabilites2' => '',
                        'equipe3' => '',
                        'responsabilites3' => '',
                    ],
                    'besoins_recrutement' => '',
                    'partenariats_strategiques' => ''
                ],
                'modele_economique_previsions_financieres' => [
                    'sources_revenus' => '',
                    'budget_previsionnel' => '',
                    'besoin_financement' => '',
                    'point_mort' => '',
                ],
                'plan_daction_calendrier' => [
                    'etapes_cles' => '',
                    'plan_croissance' => ''
                ],
            ], $business->content ?? []);
            $templates = Template::where('type', 'business-plan')->get();
            $templatecustom = TemplateCustom::where('document_id', $business->id)->first();
    
            Session::put('current_business_plan_id', $business->id);
    
            return response()->json([
                'success' => true,
                'message' => 'Business plan chargé avec succès',
                'data' => [
                    'business' => $business,
                    'templates' => $templates,
                    'templatecustom' => $templatecustom
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Business plan introuvable',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $content = json_decode($request->content, true);

        // Récupère l'ID du business plan en cours depuis la session
        $businessPlanId = Session::get('current_business_plan_id');

        if (!$businessPlanId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expirée, veuillez rafraîchir la page'
            ], 400);
        }

        $document = Document::where('id', $businessPlanId)
            ->where('user_id', Auth::id())
            ->where('type', 'business_plan')
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document non trouvé'
            ], 404);
        }

        // Met à jour le contenu existant avec les nouvelles valeurs
        $existingContent = $document->content ?? [];
        foreach ($content as $section => $fields) {
            foreach ($fields as $field => $value) {
                Arr::set($existingContent, $section . '.' . $field, $value);
            }
        }

        $document->content = $existingContent;

        // Si tous les champs requis sont remplis, on peut publier le document
        if ($this->isComplete($existingContent)) {
            $document->status = 'published';
        }

        $document->save();

        return response()->json([
            'success' => true,
            'message' => $document->status === 'published' ? 'Business plan publié' : 'Business plan sauvegardé',
            'data' => $document
        ]);
    }

    /**
     * Sauvegarde les paramètres du template (style ou template_id)
     */
    public function saveTemplateSettings(Request $request)
    {
        $document_id = $request->document_id ?? Session::get('current_business_plan_id');

        $document = Document::where('id', $document_id)
            ->where('user_id', Auth::id())
            ->where('type', 'business_plan')
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document non trouvé'
            ], 404);
        }

        // Trouver ou créer l'enregistrement TemplateCustom
        $templateCustom = TemplateCustom::firstOrNew([
            'document_id' => $document->id
        ]);

        // Mettre à jour le template si spécifié
        if ($request->has('template_id')) {
            $templateCustom->template_id = $request->template_id;
        }

        // Mettre à jour le style si spécifié
        if ($request->has('style_key') && $request->has('style_value')) {
            // Récupérer le style existant ou initialiser un tableau vide
            $style = $templateCustom->style ?? [];

            // Mettre à jour la propriété spécifique
            $style[$request->style_key] = $request->style_value;

            // Sauvegarder le style mis à jour
            $templateCustom->style = $style;
        }

        $templateCustom->save();

        return response()->json([
            'success' => true,
            'message' => 'Paramètres du template sauvegardés',
            'data' => $templateCustom
        ]);
    }

    /**
     * Vérifie si tous les champs requis sont remplis
     */
    private function isComplete($content)
    {
        $requiredSections = [
            'information_genrale' => ['nom_projet', 'secteur_activite', 'nom_entreprise', 'statut_juridique', 'adresse_contact'],
            'resume_executif' => ['description_courte_projet', 'objectifs_principaux', 'proposition_valeur'],
            'etude_marche' => ['public_cible', 'concurrence_existante', 'analyse_tendances_marche', 'avantages_concurrentiels'],
            'strategie_commerciale_marketing' => ['canaux_distribution', 'strategie_marketing', 'plan_communication'],
            'organisation_gestion' => ['equipe_responsabilites', 'besoins_recrutement', 'partenariats_strategiques'],
            'modele_economique_previsions_financieres' => ['sources_revenus', 'budget_previsionnel', 'besoin_financement', 'point_mort'],
            'plan_daction_calendrier' => ['etapes_cles', 'plan_croissance']
        ];

        foreach ($requiredSections as $section => $fields) {
            if (!isset($content[$section])) {
                return false;
            }

            foreach ($fields as $field) {
                if (!isset($content[$section][$field]) || empty($content[$section][$field])) {
                    return false;
                }
            }
        }

        return true;
    }

    public function update(Request $request, $id)
    {
        $document = Document::where('user_id', Auth::id())
            ->where('type', 'business_plan')
            ->findOrFail($id);

        $content = json_decode($request->content, true);
        $existingContent = $document->content ?? [];

        // Met à jour uniquement les champs modifiés
        foreach ($content as $section => $fields) {
            foreach ($fields as $field => $value) {
                Arr::set($existingContent, $section . '.' . $field, $value);
            }
        }

        $document->content = $existingContent;
        $document->save();

        return response()->json([
            'success' => true,
            'message' => 'Business plan mis à jour',
            'data' => $document
        ]);
    }

    public function preview(Request $request)
    {
        $content = json_decode($request->content, true);
        $templatecustom = TemplateCustom::where('document_id', Session::get('current_business_plan_id'))->first();
        $template = Template::where('id', $templatecustom->template_id)->first();
        $style = $templatecustom->style;

        // Retourne directement la vue HTML
        return view($template->path, [
            'content' => $content,
            'style' => $style,
            'preview' => true // Flag pour indiquer que c'est un preview
        ]);
    }

    public function destroy($id)
    {
        try {
            $document = Document::where('user_id', Auth::id())
                ->where('type', 'business_plan')
                ->findOrFail($id);

            $document->delete();
            // Supprime l'ID de la session si c'est le document en cours
            if (Session::get('current_business_plan_id') == $id) {
                Session::forget('current_business_plan_id');
            }

            return response()->json([
                'success' => true,
                'message' => 'Business plan supprimé avec succès',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression du business plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
