const API = {
    BASE_URL: 'http://localhost/falls-car/backend/api',

    getToken() {
        return localStorage.getItem('fc_token');
    },

    setSessao(token, cliente) {
        localStorage.setItem('fc_token', token);
        localStorage.setItem('fc_cliente', JSON.stringify(cliente));
    },

    getClienteLogado() {
        const bruto = localStorage.getItem('fc_cliente');
        return bruto ? JSON.parse(bruto) : null;
    },

    limparSessao() {
        localStorage.removeItem('fc_token');
        localStorage.removeItem('fc_cliente');
    },

    estaLogado() {
        return !!this.getToken();
    },

    async chamar(caminho, { metodo = 'GET', corpo = null, autenticado = false } = {}) {
        const headers = { 'Content-Type': 'application/json' };

        if (autenticado) {
            const token = this.getToken();
            if (!token) {
                throw new Error('Sessão expirada. Faça login novamente.');
            }
            headers['Authorization'] = `Bearer ${token}`;
        }

        let resposta;
        try {
            resposta = await fetch(`${this.BASE_URL}${caminho}`, {
                method: metodo,
                headers,
                body: corpo ? JSON.stringify(corpo) : null,
            });
        } catch (erroRede) {
            throw new Error('Não foi possível conectar à API. Verifique se o backend PHP está em execução.');
        }

        const json = await resposta.json().catch(() => ({}));

        if (!resposta.ok || json.sucesso === false) {
            throw new Error(json.mensagem || 'Ocorreu um erro inesperado.');
        }

        return json.dados;
    },

    
    cadastrar(dados) {
        return this.chamar('/cadastro', { metodo: 'POST', corpo: dados });
    },

    async login(email, senha) {
        const dados = await this.chamar('/login', { metodo: 'POST', corpo: { email, senha } });
        this.setSessao(dados.token, dados.cliente);
        return dados;
    },

    async logout() {
        try {
            await this.chamar('/logout', { metodo: 'POST', autenticado: true });
        } finally {
            this.limparSessao();
        }
    },

    
    listarVeiculos(filtros = {}) {
        const params = new URLSearchParams(filtros).toString();
        return this.chamar(`/veiculos${params ? '?' + params : ''}`);
    },

    detalharVeiculo(id) {
        return this.chamar(`/veiculos/${id}`);
    },

    
    obterPerfil() {
        return this.chamar('/cliente/perfil', { autenticado: true });
    },

    atualizarPerfil(dados) {
        return this.chamar('/cliente/perfil', { metodo: 'PUT', corpo: dados, autenticado: true });
    },

    
    criarReserva(dados) {
        return this.chamar('/reservas', { metodo: 'POST', corpo: dados, autenticado: true });
    },

    listarMinhasReservas(status = '') {
        const params = status ? `?status=${status}` : '';
        return this.chamar(`/reservas/cliente${params}`, { autenticado: true });
    },

    detalharReserva(id) {
        return this.chamar(`/reservas/${id}`, { autenticado: true });
    },

    pagarReserva(id, formaPagamento) {
        return this.chamar(`/reservas/${id}/pagamento`, {
            metodo: 'POST',
            corpo: { forma_pagamento: formaPagamento },
            autenticado: true,
        });
    },

    cancelarReserva(id, motivo = '') {
        return this.chamar(`/reservas/${id}/cancelar`, {
            metodo: 'PUT',
            corpo: { motivo },
            autenticado: true,
        });
    },

    confirmarRetirada(id, kmRetirada) {
        return this.chamar(`/reservas/${id}/retirada`, {
            metodo: 'PUT',
            corpo: { km_retirada: kmRetirada },
            autenticado: true,
        });
    },

    confirmarDevolucao(id, kmDevolucao) {
        return this.chamar(`/reservas/${id}/devolucao`, {
            metodo: 'PUT',
            corpo: { km_devolucao: kmDevolucao },
            autenticado: true,
        });
    },
};

function exigirLogin() {
    if (!API.estaLogado()) {
        window.location.href = 'login.html';
    }
}

function mostrarAlerta(elemento, mensagem, tipo = 'erro') {
    elemento.textContent = mensagem;
    elemento.className = `alerta alerta-${tipo} mostrar`;
}

function ocultarAlerta(elemento) {
    elemento.className = 'alerta';
}

function formatarMoeda(valor) {
    return Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function formatarData(dataIso) {
    const data = new Date(dataIso.replace(' ', 'T'));
    return data.toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
}

function montarTopbar() {
    const areaUsuario = document.getElementById('area-usuario');
    if (!areaUsuario) return;

    if (API.estaLogado()) {
        const cliente = API.getClienteLogado();
        const primeiroNome = cliente?.nome ? cliente.nome.split(' ')[0] : 'Cliente';
        areaUsuario.innerHTML = `
            <a href="perfil.html">${primeiroNome}</a>
            <button class="btn btn-fantasma" id="btn-sair" style="padding:0.4rem 0.8rem;">Sair</button>
        `;
        document.getElementById('btn-sair').addEventListener('click', async () => {
            await API.logout();
            window.location.href = 'index.html';
        });
    } else {
        areaUsuario.innerHTML = `
            <a href="login.html">Entrar</a>
            <a href="cadastro.html" class="btn btn-primario" style="padding:0.4rem 0.8rem;">Criar conta</a>
        `;
    }
}

const ROTULOS_STATUS = {
    pendente_pagamento: 'Aguardando pagamento',
    confirmada: 'Confirmada',
    em_andamento: 'Em andamento',
    concluida: 'Concluída',
    cancelada: 'Cancelada',
};
