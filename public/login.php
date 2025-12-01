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
    <title>Login - Registro de Carros</title>
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
            <h1 id="title" style="text-align:center;margin-bottom:8px">Login</h1>
            <p class="small-muted">Acesse sua conta para gerenciar seus carros — funciona no Web e no App.</p>

            <div class="form-container" style="margin-top:18px">
                <form id="loginForm">
                    <div class="form-group">
                        <label for="email" id="labelEmail">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="senha" id="labelSenha">Senha:</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>
                    <button type="submit" class="btn btn-primary" id="btnLogin">Entrar</button>
                </form>

                <div style="margin-top:12px; text-align:center">
                    <a href="register.php" class="page-link">Ainda não tem conta? Registre-se</a>
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
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showLoading('Entrando...');
    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;

    fetch('backend/api/usuarios.php?acao=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email, senha: senha })
    })
    .then(res => res.json())
    .then(data => {
    if (data.status === 'ok') {
        showLoading('Redirecionando...');
        window.location.href = 'index.php';
    } else {
        // mensagem vinda do backend: Usuário não encontrado OU Senha incorreta
        showMessage('⚠ ' + data.mensagem, 'error');
        hideLoading();
    }
})
    .catch(err => {
        showMessage(messages.networkError, 'error');
    });
});

function showMessage(text, type) {
    const msgDiv = document.getElementById('message');
    msgDiv.textContent = text;
    msgDiv.className = type === 'success' ? 'msg msg-success' : 'msg msg-error';
}

// Mensagens de erro
const messages = {
    loginError: 'Erro de login',
    networkError: 'Erro de rede. Tente novamente.'
};
</script>
<script>
// mesmo script de theme toggle usado na index
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
        btn.textContent = current === 'light' ? '╰(*°▽°*)╯' : '(┬┬﹏┬┬)';
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
