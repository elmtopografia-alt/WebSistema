<!-- components/dashboard/head.php -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SGT Pro | Dashboard</title>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

<!-- Phosphor Icons -->
<script src="https://unpkg.com/@phosphor-icons/web"></script>

<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                    display: ['Exo 2', 'sans-serif'],
                },
                colors: {
                    background: '#0a0f1a',
                    surface: '#111827',
                    primary: '#f97316', // Orange 500
                    secondary: '#3b82f6', // Blue 500
                    success: '#10b981', // Emerald 500
                },
                animation: {
                    'float': 'float 3s ease-in-out infinite',
                    'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
                },
                keyframes: {
                    float: {
                        '0%, 100%': { transform: 'translateY(0px)' },
                        '50%': { transform: 'translateY(-10px)' },
                    },
                    'pulse-glow': {
                        '0%, 100%': { boxShadow: '0 0 20px rgba(249, 115, 22, 0.4)' },
                        '50%': { boxShadow: '0 0 40px rgba(249, 115, 22, 0.6)' },
                    }
                }
            }
        }
    }
</script>

<style>
    body {
        background-color: #0a0f1a;
        color: #f8fafc;
    }
    
    /* Glass Effect */
    .glass {
        background: rgba(17, 24, 39, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(249, 115, 22, 0.5);
        transform: translateY(-4px);
        box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.3);
    }

    /* Text Gradients */
    .text-gradient {
        background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #fbbf24 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Grid Background Pattern */
    .grid-bg {
        background-image: 
            linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 50px 50px;
    }
</style>
