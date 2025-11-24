<?php
session_start();
// Se não estiver logado, redireciona para a tela de login
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

include 'backend/conexao.php';

// Buscar carros do banco de dados
$sql = "SELECT * FROM carros";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Compras de Carros</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Loading overlay -->
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">Carregando...</div>
    </div>

    <div class="container">
        <div class="app-header">
            <div class="app-title">Registro de Compras de Carros</div>
            <div class="controls">
                <button id="themeToggle" class="theme-toggle" aria-label="Alternar tema">🌙</button>
                <button id="btnLogout" class="btn btn-ghost">Sair</button>
            </div>
        </div>
        
        <div class="form-container">
            <h2>Adicionar Novo Carro</h2>
            <form id="formCarro" action="processa_carro.php" method="POST">
                <div class="form-group">
                    <label for="marca">Marca:</label>
                    <input type="text" id="marca" name="marca" required>
                </div>
                
                <div class="form-group">
                    <label for="modelo">Modelo:</label>
                    <input type="text" id="modelo" name="modelo" required>
                </div>
                
                <div class="form-group">
                    <label for="ano">Ano:</label>
                    <input type="number" id="ano" name="ano" min="1900" max="2030" required>
                </div>
                
                <div class="form-group">
                    <label for="cor">Cor:</label>
                    <input type="text" id="cor" name="cor" required>
                </div>
                
                <div class="form-group">
                    <label for="preco">Preço (R$):</label>
                    <input type="number" id="preco" name="preco" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="data_compra">Data da Compra:</label>
                    <input type="date" id="data_compra" name="data_compra" required>
                </div>
                
                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea id="observacoes" name="observacoes" rows="4"></textarea>
                </div>
                
                <button type="submit" class="btn-primary">Registrar Carro</button>
            </form>
        </div>
        
        <div class="carros-container">
            <div class="header-with-search">
                <h2>Carros Registrados</h2>
                <input type="text" id="searchInput" class="search-box" placeholder="Procurar por marca, modelo ou cor...">
            </div>
            
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="carros-grid">
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <div class="carro-card">
                            <div class="carro-info">
                                <h3><?php echo $row['marca'] . ' ' . $row['modelo']; ?></h3>
                                <p><strong>Ano:</strong> <?php echo $row['ano']; ?></p>
                                <p><strong>Cor:</strong> <?php echo $row['cor']; ?></p>
                                <p><strong>Preço:</strong> R$ <?php echo number_format($row['preco'], 2, ',', '.'); ?></p>
                                <p><strong>Data da Compra:</strong> <?php echo date('d/m/Y', strtotime($row['data_compra'])); ?></p>
                                <?php if (!empty($row['observacoes'])): ?>
                                    <p><strong>Observações:</strong> <?php echo $row['observacoes']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="carro-actions">
                                <button class="btn-primary" style="margin-right:8px" 
                                        onclick="editarCarro(<?php 
                                            echo htmlspecialchars(json_encode([
                                                'id' => $row['id'],
                                                'marca' => $row['marca'],
                                                'modelo' => $row['modelo'],
                                                'ano' => $row['ano'],
                                                'cor' => $row['cor'],
                                                'preco' => $row['preco'],
                                                'data_compra' => $row['data_compra'],
                                                'observacoes' => $row['observacoes']
                                            ])); 
                                        ?>)">
                                    Editar
                                </button>
                                <a href="processa_carro.php?excluir=<?php echo $row['id']; ?>" class="btn-danger" onclick="return confirm('Tem certeza que deseja excluir este carro?')">Excluir</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-cars">Nenhum carro registrado ainda.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Editar Carro</h3>
                <button class="modal-close" onclick="fecharModal()">&times;</button>
            </div>
            <form id="formEditarCarro">
                <input type="hidden" id="editId" name="id">
                <div class="form-group">
                    <label for="editMarca">Marca:</label>
                    <input type="text" id="editMarca" name="marca" required>
                </div>
                <div class="form-group">
                    <label for="editModelo">Modelo:</label>
                    <input type="text" id="editModelo" name="modelo" required>
                </div>
                <div class="form-group">
                    <label for="editAno">Ano:</label>
                    <input type="number" id="editAno" name="ano" min="1900" max="2030" required>
                </div>
                <div class="form-group">
                    <label for="editCor">Cor:</label>
                    <input type="text" id="editCor" name="cor" required>
                </div>
                <div class="form-group">
                    <label for="editPreco">Preço (R$):</label>
                    <input type="number" id="editPreco" name="preco" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="editDataCompra">Data da Compra:</label>
                    <input type="date" id="editDataCompra" name="data_compra" required>
                </div>
                <div class="form-group">
                    <label for="editObservacoes">Observações:</label>
                    <textarea id="editObservacoes" name="observacoes" rows="4"></textarea>
                </div>
                <button type="submit" class="btn-primary">Salvar Alterações</button>
            </form>
        </div>
    </div>
    
    <script src="js/script.js"></script>
    <script>
    // Sistema de busca em tempo real
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            
            searchTimeout = setTimeout(() => {
                if (searchTerm.length === 0) {
                    // Se vazio, recarregar página
                    location.reload();
                } else {
                    showLoading('Pesquisando...');
                    
                    fetch('backend/api/carros.php?acao=listar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ acao: 'listar', busca: searchTerm })
                    })
                    .then(res => res.json())
                    .then(data => {
                        hideLoading();
                        if (data.status === 'ok') {
                            atualizarCarrosListagem(data.dados);
                        } else {
                            alert('Erro na busca: ' + (data.mensagem || ''));
                        }
                    })
                    .catch(err => {
                        hideLoading();
                        alert('Erro ao buscar carros.');
                    });
                }
            }, 500); // aguarda 500ms após parar de digitar
        });
    }
    
    function atualizarCarrosListagem(carros) {
        const container = document.querySelector('.carros-grid');
        if (!container) return;
        
        if (carros.length === 0) {
            container.innerHTML = '<p class="no-cars">Nenhum carro encontrado com esses critérios.</p>';
            return;
        }
        
        container.innerHTML = carros.map(carro => `
            <div class="carro-card">
                <div class="carro-info">
                    <h3>${carro.marca} ${carro.modelo}</h3>
                    <p><strong>Ano:</strong> ${carro.ano}</p>
                    <p><strong>Cor:</strong> ${carro.cor}</p>
                    <p><strong>Preço:</strong> R$ ${parseFloat(carro.preco).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                    <p><strong>Data da Compra:</strong> ${new Date(carro.data_compra).toLocaleDateString('pt-BR')}</p>
                    ${carro.observacoes ? `<p><strong>Observações:</strong> ${carro.observacoes}</p>` : ''}
                </div>
                <div class="carro-actions">
                    <button class="btn-primary" style="margin-right:8px" 
                            onclick="editarCarro(${JSON.stringify({
                                id: carro.id,
                                marca: carro.marca,
                                modelo: carro.modelo,
                                ano: carro.ano,
                                cor: carro.cor,
                                preco: carro.preco,
                                data_compra: carro.data_compra,
                                observacoes: carro.observacoes
                            }).replace(/"/g, '&quot;')})">
                        Editar
                    </button>
                    <a href="processa_carro.php?excluir=${carro.id}" class="btn-danger" onclick="return confirm('Tem certeza que deseja excluir este carro?')">Excluir</a>
                </div>
            </div>
        `).join('');
    }
    </script>
    <script>
    (function(){
        const key = 'rc_theme';
        const btn = document.getElementById('themeToggle');
        if(!btn) return;

        function setTheme(theme){
            if(theme === 'light') document.documentElement.setAttribute('data-theme', 'light');
            else document.documentElement.removeAttribute('data-theme');
            localStorage.setItem(key, theme);
            updateButton();
        }

        function updateButton(){
            const current = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
            btn.textContent = current === 'light' ? '🥵' : '🌚';
            btn.title = current === 'light' ? 'Tema claro' : 'Tema escuro';
        }

        btn.addEventListener('click', function(){
            const now = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            setTheme(now);
        });

        // inicializar com preferência salva ou preferencia do sistema
        const saved = localStorage.getItem(key);
        if(saved) setTheme(saved);
        else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) setTheme('light');
        else setTheme('dark');
    })();
    </script>
    <script>
    // Loading control
    function showLoading(text = 'Carregando...') {
        const overlay = document.querySelector('.loading-overlay');
        const loadingText = overlay.querySelector('.loading-text');
        loadingText.textContent = text;
        overlay.classList.add('active');
    }

    function hideLoading() {
        const overlay = document.querySelector('.loading-overlay');
        overlay.classList.remove('active');
    }

    // Funções do modal de edição
    function editarCarro(carro) {
        document.getElementById('editId').value = carro.id;
        document.getElementById('editMarca').value = carro.marca;
        document.getElementById('editModelo').value = carro.modelo;
        document.getElementById('editAno').value = carro.ano;
        document.getElementById('editCor').value = carro.cor;
        document.getElementById('editPreco').value = carro.preco;
        document.getElementById('editDataCompra').value = carro.data_compra;
        document.getElementById('editObservacoes').value = carro.observacoes || '';
        
        document.getElementById('editModal').classList.add('active');
    }

    function fecharModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    // Handler do formulário de edição
    document.getElementById('formEditarCarro').addEventListener('submit', function(e) {
        e.preventDefault();
        showLoading('Salvando alterações...');

        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => data[key] = value);

        fetch('processa_carro.php?acao=editar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                fecharModal();
                location.reload(); // recarrega para mostrar alterações
            } else {
                hideLoading();
                alert('Erro ao salvar alterações: ' + (data.mensagem || ''));
            }
        })
        .catch(err => {
            hideLoading();
            alert('Erro ao salvar alterações. Tente novamente.');
        });
    });

    document.getElementById('btnLogout').addEventListener('click', function() {
        if (!confirm('Deseja realmente sair da sua conta?')) return;
        showLoading('Saindo...');
        fetch('backend/api/usuarios.php?acao=logout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                // redirecionar para login
                showLoading('Redirecionando...');
                window.location.href = 'login.php';
            } else {
                alert('Erro ao deslogar: ' + (data.mensagem || ''));
            }
        })
        .catch(err => {
            alert('Erro de rede ao deslogar.');
        });
    });
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>