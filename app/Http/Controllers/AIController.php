<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use League\CommonMark\CommonMarkConverter;
use LucianoTonet\GroqPHP\Groq;

class AIController extends Controller
{
    public function askQuestion($age, $situation, $diplome, $experience, $soutien, $department, $motivation, $change_region, $project, $reason, $alone_team, $echeance, $employe, $employe_three, $investment_amount, $investment_share, $finance, $activity, $development_stage, $marketing_condition, $season, $eco_modal, $start_strategy, $market_qualification, $market_geography, $clients_identified, $client_knowledge, $competitors_identified, $suppliers_identified, $supplier_characteristics)
    {
        $client = new Client();

        $groq = new Groq(env('GROQ_API_KEY'));

        $userQuestion = "Son âge: $age\n";
        $userQuestion .= "Sa situation professionnelle actuelle: $situation\n";
        $userQuestion .= "L'activité de la future entreprise correspond à son diplôme et/ou son parcours professionnel: $diplome\n";
        $userQuestion .= "Son expérience professionnelle en lien avec le projet: $experience\n";
        $userQuestion .= "Son entourage (conjoint, famille, amis) le soutient-il ?? : $soutien\n";
        $userQuestion .= "Le département où il veut implanté son entreprise: $department\n";
        $userQuestion .= "La principale raison qui motive son choix concernant le département: $motivation\n";
        $userQuestion .= "Sera-t-il prêt à changer de région pour augmenter ses chances de succès?: $change_region\n";
        $userQuestion .= "Son projet: $project\n";
        $userQuestion .= "La raison de ce projet entrepreunarial: $reason\n";
        $userQuestion .= "Est-il seul ou en équipe?: $alone_team\n";
        $userQuestion .= "L'échance de la création de l'entreprise: $echeance\n";
        $userQuestion .= "Hormis lui, le nombre d'emplois qui seront créés dès le démarrage de l'activité?: $employe\n";
        $userQuestion .= "Hormis lui, le nombre d'emplois qui seront créés au bout de 3 ans d'activité?: $employe_three\n";
        $userQuestion .= "Le montant d'investissement que nécessite son projet (en FCFA): $investment_amount\n";
        $userQuestion .= "La part du montant des investissements nécessaires représente votre apport personnel (en %): $investment_share\n";
        $userQuestion .= "Comment son projet sera financé: $finance\n";
        $userQuestion .= "Le secteur d'activité de son projet: $activity\n";
        $userQuestion .= "Le stade de développement de son produit/service: $development_stage\n";
        $userQuestion .= "La condition de commercialisation de son produit/service: $marketing_condition\n";
        $userQuestion .= "La saisonnalité de son produit/service: $season\n";
        $userQuestion .= "Le modèle économique de l'entreprise: $eco_modal\n";
        $userQuestion .= "Sa stratégie de démarrage: $start_strategy\n";
        $userQuestion .= "La qualification du marché sur lequel son offre est positionnée: $market_qualification\n";
        $userQuestion .= "La géographie du marché ciblé: $market_geography\n";
        $userQuestion .= "Les clients identifiés: $clients_identified\n";
        $userQuestion .= "Ses connaissances sur les clients: $client_knowledge\n";
        $userQuestion .= "Les concurrents identifiés: $competitors_identified\n";
        $userQuestion .= "Les fournisseurs identifiés: $suppliers_identified\n";
        $userQuestion .= "Les caractéristiques de ses fournisseurs fournisseurs: $supplier_characteristics\n";

        $prompt = <<<EOT
Vous êtes un expert en Business Plans, création d’entreprise, stratégie de développement et évaluation de projets entrepreneuriaux.

Mission :
Évaluez strictement et sans introduction le projet décrit ci-dessous en produisant directement les trois analyses suivantes, chacune sous forme de texte structuré et rédigé :

✅ Points positifs
Décrivez de manière détaillée les forces du projet. Soulignez les éléments pertinents tels que la clarté de l’idée, la solidité du modèle économique, la motivation du porteur, la cohérence avec les tendances du marché, les avantages concurrentiels, ou tout autre point fort. Donnez des exemples concrets s’il y a lieu.

❌ Points négatifs / risques
Exposez les faiblesses du projet de façon professionnelle mais franche. Mettez en évidence les zones d’ombre, incohérences, imprécisions, risques liés au marché, au modèle économique, au profil du porteur ou aux contraintes réglementaires. Analysez les conséquences potentielles si ces points ne sont pas adressés.

🔧 Recommandations concrètes
Proposez des pistes d’amélioration précises et applicables à court terme. Orientez le porteur sur les ajustements prioritaires à effectuer : repositionnement stratégique, validation du modèle économique, renforcement de l’équipe, études de marché, recherche de financements, partenariats, etc.

Règles impératives :
Aucune introduction ni résumé : commencez directement par les analyses.

Chaque partie doit être rédigée comme un avis professionnel, sous forme de paragraphes explicites.

Langage clair, structuré et professionnel, avec un ton bienveillant mais exigeant.

Rédigez exclusivement en français.

Critères d’évaluation :
Humain : profil du porteur, équipe, motivation, réseau.

Financier : besoins, viabilité, modèle économique.

Commercial : marché, clients, concurrence, lancement.

Géographique : implantation, logistique, accessibilité.

Sectoriel : attractivité, réglementation, tendances.

Projet à analyser :
$userQuestion
EOT;

        // $response = $client->request('POST', 'https://deepseek-all-in-one.p.rapidapi.com/deepseek', [
        //     'body' => json_encode([
        //         'model' => 'deepseek/deepseek-r1:free',
        //         'messages' => [
        //             ['role' => 'user', 'content' => $prompt]
        //         ]
        //     ]),
        //     'headers' => [
        //         'Content-Type' => 'application/json',
        //         'x-rapidapi-host' => 'deepseek-all-in-one.p.rapidapi.com',
        //         'x-rapidapi-key' => env('RAPIDAPI_KEY'), // Stockée dans .env
        //     ],
        // ]);

        $response = $groq->chat()->completions()->create([
            'model' => 'deepseek-r1-distill-llama-70b',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
        ]);

        // Traiter la réponse brute de l'IA
        $rawResponse = $response['choices'][0]['message']['content'];

        // Diviser la réponse en sections
        Log::debug($rawResponse);
        $sections = $this->parseResponseSections($rawResponse);
        Log::debug($sections);

        // Convertir chaque section en HTML avec Markdown
        // $converter = new CommonMarkConverter();
        $formattedResponse = [
            'positifs' => $sections['positifs'],
            'negatifs' => $sections['negatifs'],
            'ameliorations' => $sections['ameliorations']
        ];

        return $formattedResponse;
    }

    /**
     * Divise la réponse de l'IA en sections (points positifs, points négatifs, recommandations)
     *
     * @param string $rawResponse La réponse brute de l'IA
     * @return array Les sections de la réponse
     */
    private function parseResponseSections($rawResponse)
    {
        // Initialiser les sections avec des valeurs par défaut
        // $sections = [
        //     'positifs' => 'Aucun point positif identifié.',
        //     'negatifs' => 'Aucun point négatif identifié.',
        //     'ameliorations' => 'Aucune recommandation identifiée.'
        // ];

        // // Rechercher les sections dans la réponse
        // preg_match('/Point Positifs\s*:\s*(?P<positifs>.*?)(?=Point Négatif\s*:|Point A Améliorer\s*:|$)/si', $rawResponse, $matches);
        // $sections['positifs'] = trim($matches['positifs'] ?? '');

        // preg_match('/Point Négatif\s*:\s*(?P<negatifs>.*?)(?=Point A Améliorer\s*:|Point Positifs\s*:|$)/si', $rawResponse, $matches);
        // $sections['negatifs'] = trim($matches['negatifs'] ?? '');

        // preg_match('/Point A Améliorer\s*:\s*(?P<ameliorations>.*?)(?=Point Positifs\s*:|Point Négatif\s*:|$)/si', $rawResponse, $matches);
        // $sections['ameliorations'] = trim($matches['ameliorations'] ?? '');

        // Normaliser la réponse pour faciliter l'analyse
        $normalizedResponse = strtolower(trim($rawResponse));

        // Définir les marqueurs possibles pour chaque section
        $positiveMarkers = ['points positifs', '✅', 'positifs', 'atouts', 'forces'];
        $negativeMarkers = ['points négatifs', '❌', 'négatifs', 'risques', 'faiblesses'];
        $improvementMarkers = ['points à améliorer', '🔧', 'recommandations', 'améliorations', 'suggestions'];

        // Initialiser les sections
        $sections = [
            'positifs' => 'Aucun point positif identifié.',
            'negatifs' => 'Aucun point négatif identifié.',
            'ameliorations' => 'Aucune recommandation identifiée.'
        ];

        // Trouver les positions de chaque marqueur
        $positions = [];
        foreach ([$positiveMarkers, $negativeMarkers, $improvementMarkers] as $index => $markers) {
            foreach ($markers as $marker) {
                $pos = strpos($normalizedResponse, strtolower($marker));
                if ($pos !== false) {
                    $positions[$index] = $pos;
                    break;
                }
            }
        }

        // Trier les sections par ordre d'apparition
        asort($positions);
        $orderedSections = array_keys($positions);

        // Extraire le contenu de chaque section
        foreach ($orderedSections as $i => $sectionIndex) {
            $startPos = $positions[$sectionIndex];
            $nextPos = $i < count($orderedSections) - 1 ? $positions[$orderedSections[$i + 1]] : strlen($normalizedResponse);

            $content = substr($rawResponse, $startPos, $nextPos - $startPos);
            $content = preg_replace('/^(.*?)([:]|-\n)/', '', $content); // Correction du motif regex
            $content = trim($content);

            switch ($sectionIndex) {
                case 0:
                    $sections['positifs'] = $content ?: $sections['positifs'];
                    break;
                case 1:
                    $sections['negatifs'] = $content ?: $sections['negatifs'];
                    break;
                case 2:
                    $sections['ameliorations'] = $content ?: $sections['ameliorations'];
                    break;
            }
        }

        // Nettoyer les résultats
        foreach ($sections as &$section) {
            $section = preg_replace('/\s+/', ' ', trim($section));
            $section = ucfirst($section);
        }

        return $sections;
    }
}
