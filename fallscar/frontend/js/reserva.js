const parametros = new URLSearchParams(window.location.search);
const veiculoId = parametros.get("veiculo_id");

const resumoVeiculo = document.getElementById("resumoVeiculo");
const divMensagem = document.getElementById("mensagem");
const selectRetirada = document.getElementById("loja_retirada");
const selectDevolucao = document.getElementById("loja_devolucao");
const selectPeriodo = document.getElementById("periodo_dias");
const checkboxMotorista = document.getElementById("temMotorista");
const dadosMotorista = document.getElementById("dadosMotorista");
const valorEstimado = document.getElementById("valorEstimado");

let veiculoEscolhido = null;


if (!veiculoId) {
    window.location.href = "veiculos.html";
}

document.getElementById("veiculo_id").value = veiculoId;


fetch("../backend/obter_veiculos.php")
    .then(resposta => resposta.json())
    .then(dados => {
        if (!dados.sucesso) {
            exibirMensagem("Nao foi possivel carregar os dados do veiculo.", "erro");
            return;
        }

        veiculoEscolhido = dados.veiculos.find(v => v.id == veiculoId);

        if (!veiculoEscolhido) {
            exibirMensagem("Veiculo nao encontrado ou indisponivel.", "erro");
            return;
        }

        resumoVeiculo.innerHTML = `
            <div class="card-veiculo resumo-veiculo">
                <div class="retrato">
                    <div class="categoria-grande">${veiculoEscolhido.categoria}</div>
                    <div class="cidade-tag">${veiculoEscolhido.loja_cidade}</div>
                </div>
                <div class="corpo">
                    <h3>${veiculoEscolhido.marca} ${veiculoEscolhido.modelo}</h3>
                    <p class="preco">R$ ${parseFloat(veiculoEscolhido.valor_diaria).toFixed(2)} <small>/ dia</small></p>
                </div>
            </div>
        `;

        preencherSelectsLoja(dados.lojas);
    })
    .catch(() => exibirMensagem("Erro ao conectar com o servidor.", "erro"));


function preencherSelectsLoja(lojas) {
    lojas.forEach(loja => {
        const opcao1 = document.createElement("option");
        opcao1.value = loja.id;
        opcao1.textContent = `${loja.nome} - ${loja.cidade}`;
        selectRetirada.appendChild(opcao1);

        const opcao2 = opcao1.cloneNode(true);
        selectDevolucao.appendChild(opcao2);
    });
}

checkboxMotorista.addEventListener("change", function () {
    dadosMotorista.style.display = this.checked ? "block" : "none";
});


selectPeriodo.addEventListener("change", calcularValorEstimado);

function calcularValorEstimado() {
    const dias = parseInt(selectPeriodo.value);
    if (!dias || !veiculoEscolhido) {
        valorEstimado.textContent = "";
        return;
    }
    const total = dias * parseFloat(veiculoEscolhido.valor_diaria);
    valorEstimado.textContent = `Valor estimado: R$ ${total.toFixed(2)} (${dias} dias)`;
}


document.getElementById("formReserva").addEventListener("submit", function (evento) {
    evento.preventDefault();

    const dadosReserva = {
        veiculo_id: veiculoId,
        loja_retirada_id: selectRetirada.value,
        loja_devolucao_id: selectDevolucao.value,
        periodo_dias: selectPeriodo.value,
        data_inicio: document.getElementById("data_inicio").value,
        motorista: null
    };

    
    if (checkboxMotorista.checked) {
        dadosReserva.motorista = {
            nome: document.getElementById("motorista_nome").value,
            cpf: document.getElementById("motorista_cpf").value,
            habilitacao: document.getElementById("motorista_habilitacao").value
        };
    }

    fetch("../backend/reservar_veiculo.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dadosReserva)
    })
        .then(resposta => resposta.json())
        .then(resultado => {
            if (resultado.sucesso) {
                exibirMensagem(resultado.mensagem, "sucesso");
                setTimeout(() => {
                    window.location.href = `pagamento.html?reserva_id=${resultado.reserva_id}`;
                }, 1000);
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

