# Falls Car — Sistema de Locação de Veículos (Área do Cliente)

Sistema full stack acadêmico desenvolvido a partir do minimundo da
locadora fictícia **XptoTec / Falls Car**, com foco exclusivo na
**área do cliente**, conforme solicitado.

- **Backend/API:** PHP puro (sem frameworks), padrão MVC simplificado
- **Frontend:** HTML, CSS e JavaScript puro (sem frameworks)
- **Banco de dados:** MySQL
- **Comunicação:** API REST em JSON, consumida via `fetch()`

---

## 1. Estrutura do projeto

```
falls-car/
├── database/
│   ├── schema.sql        -- criação de todas as tabelas, FKs e índices
│   └── seed.sql          -- dados de exemplo (lojas, veículos, 1 cliente)
│
├── backend/
│   ├── api/
│   │   └── index.php     -- front controller (raiz pública da API)
│   ├── config/
│   │   ├── database.php  -- conexão PDO (singleton)
│   │   └── cors.php      -- cabeçalhos CORS e tratamento de OPTIONS
│   ├── helpers/
│   │   ├── Response.php  -- padroniza respostas JSON (sucesso/erro)
│   │   └── Auth.php      -- middleware de autenticação por token
│   ├── models/
│   │   ├── Cliente.php
│   │   ├── Veiculo.php
│   │   ├── Reserva.php   -- concentra as regras de negócio centrais
│   │   └── Pagamento.php
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── ClienteController.php
│   │   ├── VeiculoController.php
│   │   └── ReservaController.php
│   ├── routes/
│   │   └── api.php       -- roteador que despacha para os controllers
│   ├── utils/
│   │   └── gerar_hash.php-- utilitário CLI para gerar hash de senha
│   └── .htaccess         -- reescreve /api/* para o front controller
│
└── frontend/
    ├── pages/
    │   ├── index.html      -- página inicial
    │   ├── login.html
    │   ├── cadastro.html
    │   ├── veiculos.html   -- listagem/filtro de veículos disponíveis
    │   ├── reserva.html    -- fluxo de reserva + pagamento antecipado
    │   ├── historico.html  -- reservas ativas, histórico e ações
    │   └── perfil.html     -- dados do cliente logado
    ├── css/
    │   └── style.css       -- design system (paleta, tipografia, componentes)
    ├── js/
    │   └── api.js          -- cliente HTTP central + helpers de UI
    └── assets/              -- reservado para imagens/ícones
```

---

## 2. Como executar

1. **Banco de dados**
   ```sql
   -- No MySQL:
   SOURCE database/schema.sql;
   SOURCE database/seed.sql;
   ```
   Antes de rodar o `seed.sql` em um ambiente real, gere um hash de
   senha válido para o cliente de teste:
   ```
   php backend/utils/gerar_hash.php senha123
   ```
   e substitua o valor de `senha_hash` no `seed.sql`.

2. **Backend**
   Publique a pasta `backend/` em um servidor Apache com `mod_rewrite`
   habilitado (ex.: XAMPP/WAMP/MAMP), ajustando as credenciais em
   `backend/config/database.php`. A API ficará disponível em algo como:
   ```
   http://localhost/falls-car/backend/api
   ```

3. **Frontend**
   Abra `frontend/pages/index.html` em um servidor local (ex.: extensão
   Live Server do VS Code) e ajuste, se necessário, a constante
   `BASE_URL` em `frontend/js/api.js` para apontar para o endereço real
   da API.

---

## 3. Fluxo de funcionamento (área do cliente)

1. Cliente **cria conta** (`cadastro.html` → `POST /cadastro`).
2. Cliente **faz login** (`login.html` → `POST /login`), recebendo um
   token de sessão salvo no `localStorage` do navegador.
3. Cliente **navega pelos veículos disponíveis** (`veiculos.html` →
   `GET /veiculos`), filtrando por cidade de retirada e categoria.
4. Cliente **escolhe um veículo e faz a reserva** (`reserva.html`):
   - Define o período (7, 15 ou 30 dias);
   - Escolhe a loja de devolução (restrita à mesma cidade da retirada);
   - Opcionalmente adiciona motoristas adicionais (custo extra fixo);
   - `POST /reservas` cria a reserva com status `pendente_pagamento`
     e o veículo passa a `reservado` (fica indisponível para outros
     clientes).
5. Cliente **efetua o pagamento antecipado** (mesma tela, passo 2):
   `POST /reservas/{id}/pagamento` → aprova o pagamento (simulado) e
   muda o status da reserva para `confirmada`.
6. No dia da retirada, cliente **confirma a retirada física do carro**
   em "Minhas locações" (`PUT /reservas/{id}/retirada`): só a partir
   deste momento a locação realmente "inicia" — status passa a
   `em_andamento` e o veículo passa a `alugado`.
7. Ao final do período, cliente **confirma a devolução**
   (`PUT /reservas/{id}/devolucao`): status final `concluida`, veículo
   volta a `disponivel`.
8. A qualquer momento antes de 24h da retirada prevista, o cliente pode
   **cancelar a reserva** (`PUT /reservas/{id}/cancelar`), liberando o
   veículo novamente.
9. Cliente pode consultar **histórico completo e reservas ativas** a
   qualquer momento (`GET /reservas/cliente`), com filtro por status.
10. Cliente pode **editar telefone/endereço** em `perfil.html`.

---

## 4. Modelagem do banco (resumo)

| Tabela                  | Propósito                                                        |
|--------------------------|-------------------------------------------------------------------|
| `lojas`                  | Unidades físicas da locadora, por cidade                          |
| `clientes`                | Cadastro e login do cliente (área administrativa fora de escopo) |
| `sessoes`                 | Tokens de autenticação da API                                    |
| `veiculos`                | Frota, status e loja atual de cada veículo                        |
| `reservas`                | Intenção de locação: período, valores, status, retirada/devolução |
| `motoristas_adicionais`   | Motoristas extras vinculados a uma reserva (opcional)             |
| `pagamentos`              | Pagamentos antecipados vinculados a uma reserva                   |
| `locacoes`                | Só existe a partir da retirada física; registra km e devolução    |

Relacionamentos e chaves estrangeiras completos estão comentados em
`database/schema.sql`.

---

## 5. Rotas da API

| Método | Rota                          | Autenticado | Descrição                                   |
|--------|-------------------------------|:-----------:|----------------------------------------------|
| POST   | `/cadastro`                   | não         | Cria um novo cliente                          |
| POST   | `/login`                      | não         | Autentica e retorna um token                  |
| POST   | `/logout`                     | sim         | Encerra a sessão atual                        |
| GET    | `/veiculos`                   | não         | Lista veículos disponíveis (filtros: cidade, categoria) |
| GET    | `/veiculos/{id}`              | não         | Detalha um veículo                            |
| GET    | `/cliente/perfil`             | sim         | Dados do cliente logado                       |
| PUT    | `/cliente/perfil`             | sim         | Atualiza telefone/endereço                    |
| POST   | `/reservas`                   | sim         | Cria uma reserva                              |
| GET    | `/reservas/cliente`           | sim         | Lista reservas do cliente (filtro: status)    |
| GET    | `/reservas/{id}`               | sim         | Detalha uma reserva                           |
| POST   | `/reservas/{id}/pagamento`     | sim         | Processa o pagamento antecipado               |
| PUT    | `/reservas/{id}/cancelar`      | sim         | Cancela a reserva (até 24h antes)             |
| PUT    | `/reservas/{id}/retirada`      | sim         | Confirma retirada física (inicia a locação)   |
| PUT    | `/reservas/{id}/devolucao`     | sim         | Confirma devolução física (encerra a locação) |

Todas as respostas seguem o formato:
```json
{ "sucesso": true, "mensagem": "...", "dados": { } }
```

---

## 6. Decisões de projeto para pontos ambíguos do minimundo

O minimundo (documento de requisitos XptoTec) cobre um sistema maior,
incluindo administração, multas, relatórios gerenciais e parcelamento —
fora do escopo pedido pelo professor (apenas área do cliente). Para os
pontos relevantes à área do cliente que ficaram ambíguos, foram tomadas
as seguintes decisões, documentadas também como comentários no código:

1. **"Usuários" vs "Clientes":** como só a área do cliente foi pedida,
   não existe tabela `usuarios` de administração; a autenticação ocorre
   diretamente na tabela `clientes`.

2. **Alocação "por proximidade e mesma cidade":** interpretado como
   filtro obrigatório por cidade da loja de retirada, já que o
   minimundo não define uma métrica de distância entre cidades
   diferentes.

3. **Devolução "na mesma cidade":** a API valida que a loja de
   devolução escolhida esteja na mesma cidade da loja onde o veículo
   está alocado (pode ser uma loja diferente, mesma cidade).

4. **"Locação só inicia após retirada":** modelado com uma tabela
   separada `locacoes`, criada apenas quando o cliente confirma a
   retirada física — até lá, existe apenas a `reserva`.

5. **Pagamento antecipado obrigatório:** a reserva nasce com status
   `pendente_pagamento` e só avança para `confirmada` após um
   pagamento aprovado cobrindo o valor total. Não há integração real
   com gateway de pagamento (fora do escopo acadêmico); o pagamento é
   aprovado automaticamente para fins de demonstração.

6. **Cancelamento "até 24h antes":** validado no backend comparando a
   data/hora atual com a data de início prevista da reserva.

7. **Motorista adicional:** modelado como uma lista opcional de
   registros vinculados à reserva, cada um com um valor fixo de custo
   extra (R$ 50,00 por motorista, valor definido para fins de
   demonstração, já que o minimundo não especifica o valor exato).

8. **Categoria superior em caso de indisponibilidade (RCL 8):** ao
   listar veículos, se não houver nenhum disponível na categoria
   filtrada mas houver na cidade, a API sugere uma opção de categoria
   imediatamente superior.

9. **Lojas de devolução no frontend:** como o minimundo não define um
   endpoint de listagem pública de lojas, o frontend usa uma lista fixa
   de lojas por cidade (compatível com o `seed.sql`) para preencher o
   seletor de devolução. Em uma evolução do projeto, isso viraria um
   endpoint `GET /lojas?cidade=`.

---

## 7. Fora de escopo (deliberadamente)

Conforme solicitado, os seguintes itens do minimundo **não** foram
implementados, por pertencerem à área administrativa/financeira:

- Login e permissões de administrador
- Relatórios gerenciais
- Controle de manutenção/revisão de veículos por um administrador
- Multas por infração, atraso ou excesso de quilometragem
- Parcelamento de pagamento
- Transferência de veículos entre lojas
