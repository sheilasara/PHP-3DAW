const parametros = new URLSearchParams(window.location.search);
const reservaId = parametros.get("reserva_id");

const resumoReserva = document.getElementById("resumoReserva");
const divMensagem = document.getElementById("mensagem");
const camposCartao = document.getElementById("camposCartao");
const radiosMetodo = document.querySelectorAll('input[name="metodo"]');

if (!reservaId) {
    window.location.href = "minhas_reservas.html";
}

document.getElementById("reserva_id").value = reservaId;

fetch(`../backend/obter_detalhes_reserva.php?id=${reservaId}`)
    .then(resposta => resposta.json())
    .then(dados => {
        if (!dados.sucesso) {
            exibirMensagem(dados.mensagem, "erro");
            return;
        }

        const reserva = dados.reserva;
        resumoReserva.innerHTML = `
            <div class="painel-resumo">
                <span class="eyebrow" style="color:#cfcdc8;">Resumo da reserva</span>
                <h3>${reserva.marca} ${reserva.modelo}</h3>
                <p>Retirada: ${reserva.loja_retirada} (${reserva.cidade_retirada})</p>
                <p>Devolucao: ${reserva.loja_devolucao}</p>
                <p>Periodo: ${reserva.periodo_dias} dias (${reserva.data_inicio} a ${reserva.data_fim})</p>
                <p class="total">R$ ${parseFloat(reserva.valor_total).toFixed(2)}</p>
            </div>
        `;
    })
    .catch(() => exibirMensagem("Erro ao conectar com o servidor.", "erro"));

radiosMetodo.forEach(radio => {
    radio.addEventListener("change", function () {
        camposCartao.style.display = this.value === "cartao" ? "block" : "none";
    });
});

document.getElementById("formPagamento").addEventListener("submit", function (evento) {
    evento.preventDefault();

    const metodoSelecionado = document.querySelector('input[name="metodo"]:checked').value;

    const dadosPagamento = {
        reserva_id: reservaId,
        metodo: metodoSelecionado,
        nome_impresso: document.getElementById("nome_impresso").value,
        numero_cartao: document.getElementById("numero_cartao").value,
        validade: document.getElementById("validade").value,
        cvv: document.getElementById("cvv").value
    };

    fetch("../backend/processa_pagamento.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dadosPagamento)
    })
        .then(resposta => resposta.json())
        .then(resultado => {
            if (resultado.sucesso) {
                exibirMensagem(resultado.mensagem, "sucesso");
                setTimeout(() => {
                    window.location.href = "minhas_reservas.html";
                }, 1500);
            } else {
                exibirMensagem(resultado.mensagem, "erro");
            }
        })
        .catch(() => exibirMensagem("Erro ao conectar com o servidor.", "erro"));
});

function exibirMensagem(texto, tipo) {
    divMensagem.innerHTML = `<div class="mensagem ${tipo}">${texto}</div>`;
}


verificarLogin();

function verificarLogin() {
    fetch("../backend/verifica_login.php")
        .then(resposta => resposta.json())
        .then(dados => {
            const linkLogin = document.getElementById("linkLogin");
            const linkCadastro = document.getElementById("linkCadastro");
            const linkSair = document.getElementById("linkSair");

            if (dados.logado) {
                linkLogin.style.display = "none";
                linkCadastro.style.display = "none";
                linkSair.style.display = "inline";
            } else {
                linkLogin.style.display = "inline";
                linkCadastro.style.display = "inline";
                linkSair.style.display = "none";
            }
        });
}


document.getElementById("linkSair").addEventListener("click", function (evento) {
    evento.preventDefault();
    fetch("../backend/logout.php").then(() => window.location.href = "login.html");
});

