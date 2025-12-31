<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Gera Proposta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            /* Mínima altura da tela */
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        /* Define a barra lateral */
        .sidebar {
            width: 260px; /* Largura da barra lateral */
            min-height: 100vh; /* Ocupa 100% da altura */
            position: fixed; /* Fixa na tela */
            top: 0;
            left: 0;
            background-color: #212529; /* Cor escura */
            padding-top: 56px; /* Espaço para a navbar superior */
            z-index: 100;
        }

        /* Define a área de conteúdo principal */
        .content-wrapper {
            /* Ocupa o espaço restante, empurrado pela sidebar */
            margin-left: 260px; 
            padding-top: 56px; /* Espaço para a navbar superior */
        }

        /* Define a barra do topo */
        .top-navbar {
            z-index: 101; /* Fica acima da sidebar */
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top top-navbar shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-file-earmark-code-fill me-2"></i> Gera Proposta
            </a>
            
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Sair
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="sidebar shadow">
        <div class="list-group list-group-flush p-3">
            
            <a href="index.php" class="list-group-item list-group-item-action list-group-item-light active mb-2 fw-bold">
                <i class="bi bi-house-door-fill me-2"></i>Painel Principal
            </a>

            <small class="text-secondary text-uppercase fw-bold ps-2 mb-1 mt-2">Propostas</small>
            <a href="criar_proposta.php" class="list-group-item list-group-item-action list-group-item-dark">
                <i class="bi bi-plus-circle-fill me-2"></i>Criar Nova Proposta
            </a>
            <a href="listar_propostas.php" class="list-group-item list-group-item-action list-group-item-dark mb-2">
                <i class="bi bi-list-task me-2"></i>Ver Propostas Salvas
            </a>

            <small class="text-secondary text-uppercase fw-bold ps-2 mb-1 mt-2">Administrativo</small>
            <a href="gerenciar_empresa.php" class="list-group-item list-group-item-action list-group-item-dark">Gerenciar Empresa</a>
            <a href="gerenciar.php?tabela=Clientes" class="list-group-item list-group-item-action list-group-item-dark">Gerenciar Clientes</a>
            <a href="gerenciar.php?tabela=Tipo_Servicos" class="list-group-item list-group-item-action list-group-item-dark">Gerenciar Serviços</a>
            <a href="gerenciar.php?tabela=Tipo_Funcoes" class="list-group-item list-group-item-action list-group-item-dark">Gerenciar Funções</a>
            <a href="gerenciar.php?tabela=Tipo_Estadia" class="list-group-item list-group-item-action list-group-item-dark">Gerenciar Estadia</a>
            <a href="gerenciar.php?tabela=Tipo_Locacao" class="list-group-item list-group-item-action list-group-item-dark">Gerenciar Locação</a>
            <a href="gerenciar.php?tabela=Tipo_Consumo" class="list-group-item list-group-item-action list-group-item-dark">Gerenciar Consumo</a>
            <a href="gerenciar.php?tabela=Tipo_Custo_Admin" class="list-group-item list-group-item-action list-group-item-dark">Gerenciar Custos Admin</a>
        </div>
    </div>

    <main class="content-wrapper">
        <div class="container-fluid p-4">
            
            <div class="mb-4">
                <h1 class="h3">Painel Principal</h1>
                <p class="text-muted">Seu assistente para criação de propostas comerciais.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center d-flex flex-column justify-content-center p-4">
                            <h5 class="card-title">Módulo de Propostas</h5>
                            <p class="card-text text-muted">Crie uma nova proposta do zero ou visualize e edite as propostas salvas.</p>
                            
                            <a href="criar_proposta.php" class="btn btn-success btn-lg mb-2">
                                🚀 Criar Nova Proposta
                            </a>
                            
                            <a href="listar_propostas.php" class="btn btn-primary btn-lg">
                                📄 Ver Propostas Salvas
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                     <div class="card h-100 shadow-sm">
                        <div class="card-body p-4">
                             <h5 class="card-title text-center">Acesso Rápido</h5>
                             <p class="card-text text-center text-muted small">Gerencie os dados que alimentam os formulários.</p>
                             <div class="list-group list-group-flush">
                                <a href="gerenciar.php?tabela=Clientes" class="list-group-item list-group-item-action"><i class="bi bi-people-fill me-2"></i>Gerenciar Clientes</a>
                                <a href="gerenciar.php?tabela=Tipo_Servicos" class="list-group-item list-group-item-action"><i class="bi bi-tools me-2"></i>Gerenciar Tipos de Serviços</a>
                                <a href="gerenciar_empresa.php" class="list-group-item list-group-item-action fw-bold"><i class="bi bi-building me-2"></i>Gerenciar Dados da Empresa</a>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

        </div> </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>