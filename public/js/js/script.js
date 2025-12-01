document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCarro');
    
    // Validação do formulário
    form.addEventListener('submit', function(e) {
        let valid = true;
        
        // Validar ano
        const ano = document.getElementById('ano').value;
        const anoAtual = new Date().getFullYear();
        if (ano < 1900 || ano > anoAtual + 1) {
            alert('Por favor, insira um ano válido (entre 1900 e ' + (anoAtual + 1) + ')');
            valid = false;
        }
        
        // Validar preço
        const preco = document.getElementById('preco').value;
        if (preco <= 0) {
            alert('Por favor, insira um preço válido');
            valid = false;
        }
        
        // Validar data
        const dataCompra = document.getElementById('data_compra').value;
        if (!dataCompra) {
            alert('Por favor, selecione uma data de compra');
            valid = false;
        }
        
        if (!valid) {
            e.preventDefault();
        }
    });
    
    // Exibir mensagens de sucesso/erro
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const erro = urlParams.get('erro');
    
    if (msg) {
        showMessage(msg, 'success');
    }
    
    if (erro) {
        showMessage(erro, 'error');
    }
    
    function showMessage(text, type) {
        const msgDiv = document.createElement('div');
        msgDiv.className = type === 'success' ? 'msg msg-success' : 'msg msg-error';
        msgDiv.textContent = text;
        
        const container = document.querySelector('.container');
        container.insertBefore(msgDiv, container.firstChild);
        
        // Remover a mensagem após 5 segundos
        setTimeout(function() {
            msgDiv.remove();
        }, 5000);
    }
});