<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Business Plan – {{ $content['information_genrale']['nom_projet'] ?? 'Gletche' }}</title>
    <style>
        body {
            margin: 0;
            font-family: {{ $style['font-family'] ?? '\'Segoe UI\', sans-serif' }};
            color: #333;
            line-height: {{ $style['line-height'] ?? '1.6' }};
            font-size: {{ $style['font-size'] ?? '14px' }};
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .page {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            page-break-after: always;
        }

        header {
            background: {{ $style['color'] ?? '#0084FF' }};
            color: white;
            text-align: center;
            padding: 20px 10px;
        }

        .info {
            background: #EEF6FE;
            padding: 20px;
            margin-top: 20px;
        }

        .section {
            background: #EEF6FE;
            padding: 20px;
            margin-top: 20px;
        }

        .info h2,
        .section h2 {
            color: #FFB50A;
            margin-top: 0;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }

        .grid section {
            background: #f9f9f9;
            padding: 15px;
            border-left: 5px solid #FFB50A;
        }

        h3, strong {
            color: {{ $style['color'] ?? '#FFB50A' }};
            margin-top: 20px;
            margin-bottom: 10px;
        }

        footer {
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #ddd;
            margin-top: 30px;
            padding-top: 15px;
        }

        .circles {
            position: relative;
            height: 100px;
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
            margin-top: -60px;
        }

        .circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            position: relative;
            z-index: 1;
        }

        .blue {
            background-color: #FFB50A;
            margin-right: -40px;
            z-index: 1;
        }

        .yellow {
            background-color: {{ $style['color'] ?? '#0084FF' }};
            z-index: 2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f7fafc;
            font-weight: bold;
        }

        ul,
        ol {
            margin-bottom: 15px;
            padding-left: 20px;
        }

        li {
            margin-bottom: 5px;
        }

        p {
            color: #333;
        }

        @media print {
            .page {
                box-shadow: none;
                margin-bottom: 0;
                page-break-after: always;
            }
        }
    </style>
</head>

<body>
    <!-- <div class="container"> -->
    <!-- Page de couverture -->
    <div class="page">
        <header>
            <h1>{{ $content['information_genrale']['nom_projet'] ?? 'Gletche' }}</h1>
        </header>

        <section class="info">
            <h2>Informations Générales</h2>
            <p>
                <strong>Nom du projet :</strong> {{ $content['information_genrale']['nom_projet'] ?? 'Gletche' }}<br>
                <strong>Nom de l'entrepreneur :</strong> {{ $content['information_genrale']['nom_entreprise'] ?? 'Raoul AGODOHAN' }}<br>
                <strong>Contact :</strong> {{ $content['information_genrale']['adresse_contact'] ?? 'raoul.gletche@agritech.bj | +229 XX XX XX XX' }}<br>
                <strong>Secteur d'activité :</strong> {{ $content['information_genrale']['secteur_activite'] ?? 'Agriculture – Technologie – Développement Durable' }}<br>
                <strong>Statut juridique prévu :</strong> {{ $content['information_genrale']['statut_juridique'] ?? 'SARL' }}
            </p>
        </section>
    </div>

    <!-- Résumé Exécutif -->
    <div class="page">
        <div class="section">
            <h2>Résumé Exécutif</h2>

            @if(isset($content['resume_executif']['description_courte_projet']) && !empty($content['resume_executif']['description_courte_projet']))
            <h3>Description du Projet</h3>
            <p>{{ $content['resume_executif']['description_courte_projet'] }}</p>
            @endif

            @if(isset($content['resume_executif']['objectifs_principaux']) && !empty($content['resume_executif']['objectifs_principaux']))
            <h3>Objectifs Principaux</h3>
            <p>{{ $content['resume_executif']['objectifs_principaux'] }}</p>
            @endif

            @if(isset($content['resume_executif']['proposition_valeur']) && !empty($content['resume_executif']['proposition_valeur']))
            <h3>Proposition de Valeur</h3>
            <p>{{ $content['resume_executif']['proposition_valeur'] }}</p>
            @endif
        </div>
    </div>

    <!-- Étude de Marché -->
    <div class="page">
        <div class="section">
            <h2>Étude de Marché</h2>

            @if(isset($content['etude_marche']['public_cible']) && !empty($content['etude_marche']['public_cible']))
            <h3>Public Cible</h3>
            <p>{{ $content['etude_marche']['public_cible'] }}</p>
            @endif

            @if(isset($content['etude_marche']['concurrence_existante']) && !empty($content['etude_marche']['concurrence_existante']))
            <h3>Concurrence Existante</h3>
            <p>{{ $content['etude_marche']['concurrence_existante'] }}</p>
            @endif

            @if(isset($content['etude_marche']['analyse_tendances_marche']) && !empty($content['etude_marche']['analyse_tendances_marche']))
            <h3>Analyse des Tendances du Marché</h3>
            <p>{{ $content['etude_marche']['analyse_tendances_marche'] }}</p>
            @endif

            @if(isset($content['etude_marche']['avantages_concurrentiels']) && !empty($content['etude_marche']['avantages_concurrentiels']))
            <h3>Avantages Concurrentiels</h3>
            <p>{{ $content['etude_marche']['avantages_concurrentiels'] }}</p>
            @endif
        </div>
    </div>

    <!-- Stratégie Commerciale et Marketing -->
    <div class="page">
        <div class="section">
            <h2>Stratégie Commerciale et Marketing</h2>

            @if(isset($content['strategie_commerciale_marketing']['canaux_distribution']) && !empty($content['strategie_commerciale_marketing']['canaux_distribution']))
            <h3>Canaux de Distribution</h3>
            <p>{{ $content['strategie_commerciale_marketing']['canaux_distribution'] }}</p>
            @endif

            @if(isset($content['strategie_commerciale_marketing']['strategie_marketing']) && !empty($content['strategie_commerciale_marketing']['strategie_marketing']))
            <h3>Stratégie Marketing</h3>
            <p>{{ $content['strategie_commerciale_marketing']['strategie_marketing'] }}</p>
            @endif

            @if(isset($content['strategie_commerciale_marketing']['plan_communication']) && !empty($content['strategie_commerciale_marketing']['plan_communication']))
            <h3>Plan de Communication</h3>
            <p>{{ $content['strategie_commerciale_marketing']['plan_communication'] }}</p>
            @endif
        </div>
    </div>

    <!-- Modèle Économique et Prévisions Financières -->
    <div class="page">
        <div class="section">
            <h2>Modèle Économique et Prévisions Financières</h2>

            @if(isset($content['modele_economique_previsions_financieres']['sources_revenus']) && !empty($content['modele_economique_previsions_financieres']['sources_revenus']))
            <h3>Sources de Revenus</h3>
            <p>{{ $content['modele_economique_previsions_financieres']['sources_revenus'] }}</p>
            @endif

            @if(isset($content['modele_economique_previsions_financieres']['budget_previsionnel']) && !empty($content['modele_economique_previsions_financieres']['budget_previsionnel']))
            <h3>Budget Prévisionnel</h3>
            <p>{{ $content['modele_economique_previsions_financieres']['budget_previsionnel'] }}</p>
            @endif

            @if(isset($content['modele_economique_previsions_financieres']['besoin_financement']) && !empty($content['modele_economique_previsions_financieres']['besoin_financement']))
            <h3>Besoins en Financement</h3>
            <p>{{ $content['modele_economique_previsions_financieres']['besoin_financement'] }}</p>
            @endif

            @if(isset($content['modele_economique_previsions_financieres']['point_mort']) && !empty($content['modele_economique_previsions_financieres']['point_mort']))
            <h3>Point Mort / Seuil de Rentabilité</h3>
            <p>{{ $content['modele_economique_previsions_financieres']['point_mort'] }}</p>
            @endif
        </div>
    </div>

    <!-- Organisation et Gestion -->
    <div class="page">
        <div class="section">
            <h2>Organisation et Gestion</h2>

            @if(isset($content['organisation_gestion']['equipe_responsabilites']) && !empty($content['organisation_gestion']['equipe_responsabilites']))
            <h3>Équipe et Responsabilités</h3>
            <table>
                <thead>
                    <tr>
                        <th>Membre de l'équipe</th>
                        <th>Responsabilités</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($content['organisation_gestion']['equipe_responsabilites']['equipe1']) && !empty($content['organisation_gestion']['equipe_responsabilites']['equipe1']))
                    <tr>
                        <td>{{ $content['organisation_gestion']['equipe_responsabilites']['equipe1'] }}</td>
                        <td>{{ $content['organisation_gestion']['equipe_responsabilites']['responsabilites1'] ?? '' }}</td>
                    </tr>
                    @endif

                    @if(isset($content['organisation_gestion']['equipe_responsabilites']['equipe2']) && !empty($content['organisation_gestion']['equipe_responsabilites']['equipe2']))
                    <tr>
                        <td>{{ $content['organisation_gestion']['equipe_responsabilites']['equipe2'] }}</td>
                        <td>{{ $content['organisation_gestion']['equipe_responsabilites']['responsabilites2'] ?? '' }}</td>
                    </tr>
                    @endif

                    @if(isset($content['organisation_gestion']['equipe_responsabilites']['equipe3']) && !empty($content['organisation_gestion']['equipe_responsabilites']['equipe3']))
                    <tr>
                        <td>{{ $content['organisation_gestion']['equipe_responsabilites']['equipe3'] }}</td>
                        <td>{{ $content['organisation_gestion']['equipe_responsabilites']['responsabilites3'] ?? '' }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
            @endif

            @if(isset($content['organisation_gestion']['besoins_recrutement']) && !empty($content['organisation_gestion']['besoins_recrutement']))
            <h3>Besoins en Recrutement</h3>
            <p>{{ $content['organisation_gestion']['besoins_recrutement'] }}</p>
            @endif

            @if(isset($content['organisation_gestion']['partenariats_strategiques']) && !empty($content['organisation_gestion']['partenariats_strategiques']))
            <h3>Partenariats Stratégiques</h3>
            <p>{{ $content['organisation_gestion']['partenariats_strategiques'] }}</p>
            @endif
        </div>
    </div>

    <!-- Plan d'Action et Calendrier -->
    <div class="page">
        <div class="section">
            <h2>Plan d'Action et Calendrier</h2>

            @if(isset($content['plan_daction_calendrier']['etapes_cles']) && !empty($content['plan_daction_calendrier']['etapes_cles']))
            <h3>Étapes Clés</h3>
            <p>{{ $content['plan_daction_calendrier']['etapes_cles'] }}</p>
            @endif

            @if(isset($content['plan_daction_calendrier']['plan_croissance']) && !empty($content['plan_daction_calendrier']['plan_croissance']))
            <h3>Plan de Croissance</h3>
            <p>{{ $content['plan_daction_calendrier']['plan_croissance'] }}</p>
            @endif
        </div>
        <!-- Footer -->
        <div>
            <footer>
                <p>Create by Diha's<br>
                    <a href="#">www.businessplan.com</a><br>
                    {{ date('d/m/y') }}
                </p>

                <div class="circles">
                    <div class="circle blue"></div>
                    <div class="circle yellow"></div>
                </div>
            </footer>
        </div>
    </div>


    <!-- </div> -->
</body>

</html>