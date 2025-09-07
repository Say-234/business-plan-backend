<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Réinitialisation de votre mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .code {
            background-color: #f5f5f5;
            padding: 15px 20px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 5px;
            margin: 30px 0;
            border-radius: 5px;
            color: #2196F3;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Réinitialisation de votre mot de passe</h2>
        </div>

        <p>Bonjour,</p>

        <p>Vous avez demandé à réinitialiser votre mot de passe. Voici votre code de vérification :</p>

        <div class="code">
            {{ $code }}
        </div>

        <p>Ce code est valable pendant 60 minutes. Si vous n'avez pas demandé de réinitialisation de mot de passe, vous pouvez ignorer cet email.</p>

        <p>Cordialement,<br>L'équipe de Business Plan</p>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
