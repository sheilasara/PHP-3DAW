const listaVeiculos = document.getElementById("listaVeiculos");
const divMensagem = document.getElementById("mensagem");

let lojasCarregadas = [];

carregarVeiculos();

function carregarVeiculos() {
    fetch("../backend/obter_veiculos.php")
        .then(resposta => resposta.json())
        .then(dados => {
            if (!dados.sucesso) {
                listaVeiculos.innerHTML = "";
                exibirMensagem("Nao foi possivel carregar os veiculos.", "erro");
                return;
            }

            lojasCarregadas = dados.lojas;
            localStorage.setItem("lojas", JSON.stringify(dados.lojas));

            montarCards(dados.veiculos);
        })
        .catch(() => {
            listaVeiculos.innerHTML = "";
            exibirMensagem("Erro ao conectar com o servidor.", "erro");
        });
}

function montarCards(veiculos) {
    if (veiculos.length === 0) {
        listaVeiculos.innerHTML = "<p>Nenhum veiculo disponivel no momento.</p>";
        return;
    }

    listaVeiculos.innerHTML = "";

    veiculos.forEach(veiculo => {
        const card = document.createElement("div");
        card.className = "card-veiculo";
        card.innerHTML = `
            <div class="retrato">
                <div class="categoria-grande">${veiculo.categoria}</div>
                <div class="cidade-tag">${veiculo.loja_cidade}</div>
            </div>
            <div class="corpo">
                <h3>${veiculo.marca} ${veiculo.modelo}</h3>
                <p class="ficha">
                    <span>${veiculo.ano}</span>
                    <span>${veiculo.cor}</span>
                </p>
                <p class="preco">R$ ${parseFloat(veiculo.valor_diaria).toFixed(2)} <small>/ dia</small></p>
                <button onclick="irParaReserva(${veiculo.id})">Reservar</button>
            </div>
        `;
        listaVeiculos.appendChild(card);
    });
}

function irParaReserva(veiculoId) {
    window.location.href = `reserva.html?veiculo_id=${veiculoId}`;
}

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

