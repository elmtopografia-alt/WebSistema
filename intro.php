<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desafio SGT</title>
    
    <!-- Fonte Manuscrita (Estilo Caneta) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nanum+Pen+Script&display=swap" rel="stylesheet">

    <style>
        body, html {
            margin: 0; padding: 0;
            height: 100%;
            width: 100%;
            background-color: #ffffff; /* Fundo Branco Puro */
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .container-texto {
            max-width: 900px;
            padding: 40px;
            text-align: center;
            /* Leve rotação para parecer papel jogado na mesa */
            transform: rotate(-2deg); 
        }

        .texto-mao {
            font-family: 'Nanum Pen Script', cursive; /* Fonte estilo Canetinha */
            font-size: 5rem; /* Letra Gigante para o Vídeo */
            color: #1a237e; /* Azul Caneta "Bic" Escuro */
            line-height: 1.3;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1); /* Leve sombra para dar realismo */
        }

        /* O Link da Interrogação */
        .link-secreto {
            text-decoration: none;
            color: #d32f2f; /* Vermelho para destacar a dúvida (opcional) ou Azul mesmo */
            color: #1a237e; /* Mantendo Azul para parecer a mesma frase */
            cursor: pointer;
            font-weight: bold;
            position: relative;
            display: inline-block;
            transition: transform 0.3s;
        }

        .link-secreto:hover {
            transform: scale(1.5) rotate(10deg); /* Cresce quando passa o mouse */
        }

        /* Animação de escrita (opcional, mas legal) */
        .fade-in {
            animation: fadeIn 2s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="container-texto fade-in">
        <div class="texto-mao">
            Tenho que fazer uma proposta de um serviço de topografia, para entregar daqui a 15 minutos, vai dar<a href="index.php" class="link-secreto">?</a>
        </div>
    </div>

</body>
</html>