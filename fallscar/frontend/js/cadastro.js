const formCadastro = document.getElementById("formCadastro");
const divMensagem = document.getElementById("mensagem");

formCadastro.addEventListener("submit", function (evento) {
    evento.preventDefault();

    const dados = {
        nome: document.getElementById("nome").value,
        cpf: document.getElementById("cpf").value,
        email: document.getElementById("email").value,
        senha: document.getElementById("senha").value,
        habilitacao: document.getElementById("habilitacao").value,
        endereco: document.getElementById("endereco").value
    };

    fetch("../backend/cadastro.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dados)
    })
        .then(resposta => resposta.json())
        .then(resultado => {
            if (resultado.sucesso) {
                exibirMensagem(resultado.mensagem, "sucesso");
                formCadastro.reset();
                setTimeout(() => {
                    window.location.href = "login.html";
                }, 1200);
            } else {
                exibirMensagem(resultado.mensagem, "erro");
            }
        })
        .catch(() => {
            exibirMensagem("Erro ao conectar com o servidor.", "erro");
        });
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

