const listaReservas = document.getElementById("listaReservas");
const divMensagem = document.getElementById("mensagem");
const linkSair = document.getElementById("linkSair");

verificarLogin();
carregarReservas();

function verificarLogin() {
    fetch("../backend/verifica_login.php")
        .then(resposta => resposta.json())
        .then(dados => {
            const linkLogin = document.getElementById("linkLogin");
            const linkCadastro = document.getElementById("linkCadastro");

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

function carregarReservas() {
    fetch("../backend/obter_reservas.php")
        .then(resposta => resposta.json())
        .then(dados => {
            if (!dados.sucesso) {
                listaReservas.innerHTML = "";
                exibirMensagem(dados.mensagem, "erro");
                setTimeout(() => window.location.href = "login.html", 1500);
                return;
            }

            montarLista(dados.reservas);
        })
        .catch(() => exibirMensagem("Erro ao conectar com o servidor.", "erro"));
}


function montarLista(reservas) {
    if (reservas.length === 0) {
        listaReservas.innerHTML = "<p>Voce ainda nao possui reservas.</p>";
        return;
    }

    listaReservas.innerHTML = "";

    reservas.forEach(reserva => {
        const card = document.createElement("div");
        card.className = "card-reserva";

        
        const botaoCancelar = reserva.status !== "cancelada"
            ? `<button class="btn-cancelar" onclick="cancelarReserva(${reserva.id})">Cancelar</button>`
            : "";

        const botaoPagar = reserva.status === "pendente"
            ? `<a class="btn" href="pagamento.html?reserva_id=${reserva.id}">Pagar agora</a>`
            : "";

        card.innerHTML = `
            <div>
                <h3>${reserva.marca} ${reserva.modelo}</h3>
                <p>${reserva.loja_retirada} &rarr; ${reserva.loja_devolucao}</p>
                <p>${reserva.periodo_dias} dias (${reserva.data_inicio} a ${reserva.data_fim})</p>
                <p class="valor-linha">R$ ${parseFloat(reserva.valor_total).toFixed(2)}</p>
                <span class="status ${reserva.status}">${reserva.status}</span>
            </div>
            <div>
                ${botaoPagar}
                ${botaoCancelar}
            </div>
        `;
        listaReservas.appendChild(card);
    });
}


function cancelarReserva(reservaId) {
    if (!confirm("Tem certeza que deseja cancelar esta reserva?")) {
        return;
    }

    fetch("../backend/cancelar_reserva.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ reserva_id: reservaId })
    })
        .then(resposta => resposta.json())
        .then(resultado => {
            exibirMensagem(resultado.mensagem, resultado.sucesso ? "sucesso" : "erro");
            if (resultado.sucesso) {
                carregarReservas(); 
            }
        })
        .catch(() => exibirMensagem("Erro ao conectar com o servidor.", "erro"));
}

linkSair.addEventListener("click", function (evento) {
    evento.preventDefault();
    fetch("../backend/logout.php")
        .then(() => window.location.href = "login.html");
});

function exibirMensagem(texto, tipo) {
    divMensagem.innerHTML = `<div class="mensagem ${tipo}">${texto}</div>`;
}
