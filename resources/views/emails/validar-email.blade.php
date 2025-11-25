<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Email - PoleEventos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F9F4EE 0%, #F1EBDF 50%, #E8DDD0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        a {
            color: #ffff !important;
            text-decoration: none !important;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(16, 147, 155, 0.15);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #10939B 0%, #0F6971 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(ellipse 100% 60% at 50% -30%, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .logo {
            font-size: 32px;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: -0.01em;
            position: relative;
            z-index: 1;
        }

        .header p {
            font-size: 14px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        .content {
            padding: 40px;
            color: #2B2B2B;
        }

        .greeting {
            font-size: 16px;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .greeting strong {
            color: #10939B;
        }

        .description {
            font-size: 14px;
            color: rgba(43, 43, 43, 0.8);
            margin-bottom: 32px;
            line-height: 1.7;
        }

        .button-container {
            text-align: center;
            margin-bottom: 32px;
        }

        .verify-button {
            display: inline-block;
            background: linear-gradient(135deg, #10939B 0%, #0F6971 100%);
            color: white !important;
            text-decoration: none;
            padding: 16px 48px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(16, 147, 155, 0.25);
            border: none;
            cursor: pointer;
            letter-spacing: -0.01em;
        }

        .verify-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(16, 147, 155, 0.35);
            background: linear-gradient(135deg, #0F6971 0%, #072E33 100%);
        }

        .code-section {
            background: #F9F4EE;
            border-left: 4px solid #10939B;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .code-label {
            font-size: 12px;
            color: #ffff;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .code {
            font-size: 24px;
            font-weight: 700;
            color: #ffff;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }

        .footer-text {
            font-size: 13px;
            color: rgba(43, 43, 43, 0.6);
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .warning {
            background: #FFF4E6;
            border-left: 4px solid #F59E0B;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
        }

        .warning-text {
            font-size: 12px;
            color: #92400E;
            margin: 0;
        }

        .footer {
            background: #F9F4EE;
            padding: 24px 40px;
            text-align: center;
            border-top: 1px solid rgba(16, 147, 155, 0.1);
        }

        .footer p {
            font-size: 12px;
            color: rgba(43, 43, 43, 0.6);
            margin: 8px 0;
        }

        .social-links {
            margin-top: 16px;
            display: flex;
            justify-content: center;
            gap: 16px;
        }

        .social-links a {
            display: inline-block;
            width: 36px;
            height: 36px;
            background: #10939B;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 36px;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: #0F6971;
            transform: translateY(-2px);
        }

        .divider {
            height: 1px;
            background: rgba(16, 147, 155, 0.1);
            margin: 24px 0;
        }

        @media (max-width: 600px) {
            .content {
                padding: 24px;
            }

            .header {
                padding: 32px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .footer {
                padding: 16px 24px;
            }

            .verify-button {
                padding: 14px 40px;
                color: white;
                font-size: 14px;
            }

            .code {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="logo">📧</div>
        <h1>Verificar Email</h1>
        <p>Confirme seu endereço de email para acessar PoleEventos</p>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="greeting">
            Olá <strong>{{ $nome }}</strong>! 👋
        </div>

        <div class="description">
            Obrigado por se registrar em <strong>PoleEventos</strong>! Para garantir a segurança da sua conta e começar a aproveitar todos os nossos recursos, precisamos que você verifique seu endereço de email.
        </div>

        <!-- Código de Verificação -->
        <div class="button-container">
            <a href="{{ $url }}" class="verify-button">
                ✓ Verificar Email
            </a>
        </div>

        <div class="divider"></div>

        <div class="warning">
            <p class="warning-text">
                <strong>⏰ Este código expira em 24 horas.</strong> Se você não solicitou este email, ignore-o ou entre em contato com nosso suporte.
            </p>
        </div>

        <div class="footer-text">
            Se você tiver dúvidas ou problemas ao verificar seu email, entre em contato com nosso time de suporte através de <strong>suporte@poleeventos.com</strong>.
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>© 2024 PoleEventos</strong> - Gerenciamento de Eventos</p>
        <p>Estamos aqui para tornar seus eventos incríveis!</p>

        <div class="social-links">
            <a href="#" title="Facebook">f</a>
            <a href="#" title="Instagram">i</a>
            <a href="#" title="LinkedIn">in</a>
            <a href="#" title="Twitter">𝕏</a>
        </div>

        <p style="margin-top: 16px; font-size: 11px; opacity: 0.5;">
            Este é um email transacional. Por favor, não responda a este email.
        </p>
    </div>
</div>
</body>
</html>
