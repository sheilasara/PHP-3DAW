const formLogin = document.getElementById("formLogin");
const divMensagem = document.getElementById("mensagem");

formLogin.addEventListener("submit", function (evento) {
    evento.preventDefault(); 

    const email = document.getElementById("email").value;
    const senha = document.getElementById("senha").value;

    fetch("../backend/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email: email, senha: senha })
    })
        .then(resposta => resposta.json())
        .then(dados => {
            if (dados.sucesso) {
                exibirMensagem(dados.mensagem, "sucesso");
                setTimeout(() => {
                    window.location.href = "veiculos.html";
                }, 1000);
            } else {
                exibirMensagem(dados.mensagem, "erro");
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

