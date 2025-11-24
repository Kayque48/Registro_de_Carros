<?php
session_start();
// Se já estiver logado, redireciona
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar - Registro de Carros</title>
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
    
    <div class="auth-page">
        <div class="auth-card">
            <div style="display:flex; justify-content:flex-end; margin-bottom:6px">
                <button id="themeToggle" class="theme-toggle" aria-label="Alternar tema">🌙</button>
            </div>
            <div class="logo">RC</div>
            <h1 style="text-align:center;margin-bottom:8px">Registrar</h1>
            <p class="small-muted">Crie a sua conta — você poderá usar o mesmo login no Web e no App.</p>

            <div class="form-container" style="margin-top:18px">
                <form id="registerForm">
                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                        <small id="emailStatus" class="input-status"></small>
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha:</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </form>
                <div style="margin-top:12px; text-align:center">
                    <a href="login.php" class="page-link">Já tem uma conta? Faça login</a>
                </div>
            </div>

            <div id="message" style="margin-top:12px"></div>
        </div>
    </div>

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

// Intercepta cliques em links para mostrar loading
document.querySelectorAll('.page-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const href = this.getAttribute('href');
        showLoading('Redirecionando...');
        setTimeout(() => { window.location.href = href; }, 300);
    });
});
// Verificação de email em tempo real
const emailInput = document.getElementById('email');
const emailStatus = document.getElementById('emailStatus');
let emailTimeout;

emailInput.addEventListener('input', function() {
    clearTimeout(emailTimeout);
    const email = this.value;
    
    if (!email || !email.includes('@')) return;
    
    emailStatus.textContent = 'Verificando...';
    emailStatus.className = 'input-status checking';
    
    emailTimeout = setTimeout(() => {
        fetch('backend/api/usuarios.php?acao=verificar_email', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        })
        .then(res => res.json())
        .then(data => {
            if (data.existe) {
                emailStatus.textContent = 'Email já cadastrado';
                emailStatus.className = 'input-status invalid';
            } else {
                emailStatus.textContent = 'Email disponível';
                emailStatus.className = 'input-status valid';
            }
        })
        .catch(() => {
            emailStatus.textContent = '';
            emailStatus.className = 'input-status';
        });
    }, 500);
});

document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (emailStatus.className.includes('invalid')) {
        alert('Por favor, use um email diferente.');
        return;
    }
    
    const nome = document.getElementById('nome').value;
    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;

    fetch('backend/api/usuarios.php?acao=criar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nome: nome, email: email, senha: senha })
    })
    .then(res => res.json())
    .then(data => {
        const msgDiv = document.getElementById('message');
        if (data.status === 'ok') {
            msgDiv.textContent = data.mensagem;
            msgDiv.className = 'msg msg-success';
            // redireciona para login após 1.5s
            setTimeout(() => { window.location.href = 'login.php'; }, 1500);
        } else {
            msgDiv.textContent = data.mensagem || 'Erro ao cadastrar';
            msgDiv.className = 'msg msg-error';
        }
    })
    .catch(err => {
        const msgDiv = document.getElementById('message');
        msgDiv.textContent = 'Erro de rede. Tente novamente.';
        msgDiv.className = 'msg msg-error';
    });
});
</script>
<script>
// theme toggle
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
        btn.textContent = current === 'light' ? '🌞' : '🌙';
        btn.title = current === 'light' ? 'Tema claro' : 'Tema escuro';
    }
    btn.addEventListener('click', function(){
        const now = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        setTheme(now);
    });
    const saved = localStorage.getItem(key);
    if(saved) setTheme(saved);
    else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) setTheme('light');
    else setTheme('dark');
})();
</script>
</body>
</html>
