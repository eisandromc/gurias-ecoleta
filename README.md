# Écoleta

Aplicativo que mostra ao morador a rota do caminhão de coleta até o seu endereço e o horário previsto de chegada. A ideia é resolver um problema comum: as pessoas não sabem quando o caminhão vai passar, perdem o horário e o lixo acaba acumulando nas calçadas.

Projeto do grupo **2025-2MA-cidadeconectada-G01**. Equipe: Ana, Camila, Duda e Estela.

## Sumário

- [Visão geral](#visão-geral)
- [Arquitetura](#arquitetura)
- [Tecnologias](#tecnologias)
- [Pré-requisitos](#pré-requisitos)
- [Configuração](#configuração)
- [Como rodar](#como-rodar)
- [Como funciona](#como-funciona)
- [Referência das APIs](#referência-das-apis)
- [Banco de dados](#banco-de-dados)
- [Segurança](#segurança)
- [Testes](#testes)
- [Solução de problemas](#solução-de-problemas)
- [Pontos de atenção](#pontos-de-atenção)

## Visão geral

O projeto tem três partes:

| Pasta | O que é | Tecnologia |
| --- | --- | --- |
| `app/` | O aplicativo: login, cadastro e o mapa com a rota do caminhão. | React (Vite) + servidor Node.js (Express) |
| `api/` | API de usuários: cadastro, login e endereço do usuário. | PHP + MySQL, servida pelo Apache |
| `db/` | Backup completo do banco (`script.sql`), com dados de exemplo. | SQL (MariaDB/MySQL) |
| `website/` | Site institucional: apresentação do projeto, da equipe e páginas próprias de login e cadastro. | HTML, CSS e PHP |

O caminho de quem usa o aplicativo:

1. Na primeira visita, abre a tela de **Login**.
2. Quem não tem conta clica em **Criar uma conta** e preenche nome, e-mail, CEP e senha. O CEP é conferido no ViaCEP.
3. Depois do cadastro, volta ao Login com o e-mail já preenchido.
4. Depois do login, abre o **Mapa**, com a rota do caminhão até o endereço do CEP e o horário previsto de chegada. O mapa só fica disponível com login.
5. **Sair do Perfil**, no menu, encerra a sessão.

## Arquitetura

```text
 Navegador
    │  http://localhost:5173 (desenvolvimento) ou :8787 (produção)
    ▼
 app/  ── Vite (tela React) + servidor Express (porta 8787)
    │        ├─ /api/login, /api/cadastro, /api/sessao, /api/logout
    │        ├─ /api/usuario e /api/usuario/stream  (dados do usuário logado)
    │        └─ /api/route  ──────────────► openrouteservice (geocodificação e rota)
    │
    │  chamadas servidor → servidor, com o token interno (X-Api-Token)
    ▼
 api/  ── Apache + PHP  (http://localhost/ecoleta/api/)
    │        ├─ usuarios.php       cadastro e login
    │        └─ dados_usuario.php  endereço do usuário + endereço do caminhão
    │                                  └──────► ViaCEP (endereço a partir do CEP)
    ▼
 MySQL  ── banco ecoleta, tabela usuarios  ◄── website/ (login e cadastro do site)
```

O navegador nunca chama a pasta `api/` diretamente. Tudo passa pelo servidor Express, que guarda a sessão e envia o token interno que a API PHP exige.

### Estrutura de pastas

```text
ecoleta/
├── README.md               este arquivo
├── .gitignore
├── db/
│   └── script.sql          backup do banco do grupo (phpMyAdmin), com usuários de teste
├── api/                    API PHP
│   ├── usuarios.php        cadastro, login e manutenção de usuários
│   ├── dados_usuario.php   endereço do usuário logado e do caminhão (constante CAMINHAO)
│   ├── database.sql        estrutura do banco
│   ├── config.exemplo.php  modelo do config.php (token interno)
│   └── classes/
│       ├── Api.php         resposta JSON e verificação do token
│       ├── Database.php    conexão com o MySQL
│       ├── Endereco.php    consulta ao ViaCEP
│       └── Usuarios.php    operações na tabela usuarios
├── app/                    aplicativo
│   ├── .env.example        modelo do .env
│   ├── package.json
│   ├── server/
│   │   ├── index.js        servidor Express: rota, dados do usuário, atualização ao vivo
│   │   ├── auth.js         login, cadastro e sessão
│   │   ├── auth.test.js    testes automatizados (npm test)
│   │   └── dev.js          sobe Express e Vite juntos (npm run dev)
│   └── src/
│       ├── App.jsx         escolhe Login, Cadastro ou Mapa conforme a sessão
│       ├── api.js          chamadas ao servidor Express
│       ├── sidebar.jsx     menu lateral
│       ├── styles.css      cores, fontes e medidas por tamanho de tela
│       ├── assets/         imagens (caminhão, lixeira, folhas)
│       └── Pages/
│           ├── AuthLayout.jsx, auth.css   moldura das telas de acesso
│           ├── Login/Login.jsx
│           ├── Cadastro/Cadastro.jsx
│           └── Mapa/Mapa.jsx, Mapa.css, routeCache.js
└── website/                site institucional
    ├── inicio.html, projeto.html
    ├── login.php, cadastro.php, act/   formulários e processamento
    └── conexao.php         conexão com o MySQL
```

## Tecnologias

- **Front-end:** React 19, Vite 8, Leaflet e React Leaflet (mapa), fonte Poppins.
- **Servidor do app:** Node.js com Express 5 e dotenv.
- **API:** PHP 8 com PDO (MySQL) e cURL.
- **Banco:** MySQL.
- **Serviços externos:**
  - [openrouteservice](https://openrouteservice.org): geocodificação e rota para caminhões. Precisa de chave gratuita.
  - [ViaCEP](https://viacep.com.br): endereço a partir do CEP. Não precisa de chave.
  - Mapas do [OpenStreetMap](https://www.openstreetmap.org).

## Pré-requisitos

| Programa | Versão | Observação |
| --- | --- | --- |
| Node.js | 20.19 ou superior (ou 22.12+) | Exigência do Vite 8. |
| PHP | 8.1 ou superior | Com as extensões `pdo_mysql`, `curl` e `mbstring`. |
| MySQL | 8 ou superior | Também funciona com o MariaDB do XAMPP. |
| Apache | 2.4 | Servindo a pasta onde o projeto está. |
| Conta no openrouteservice | — | Para a chave `ORS_API_KEY`. |

O computador também precisa de internet, para acessar o ViaCEP, o openrouteservice e os mapas.

**macOS com Homebrew:**

```bash
brew install node php mysql httpd
brew services start httpd
brew services start php
brew services start mysql
```

A pasta pública do Apache do Homebrew é `/opt/homebrew/var/www`.

**Windows com XAMPP:** instale o [XAMPP](https://www.apachefriends.org) e o [Node.js](https://nodejs.org), e inicie **Apache** e **MySQL** no painel do XAMPP. A pasta pública é `C:\xampp\htdocs`.

## Configuração

Faça estes passos uma vez, na primeira instalação. Os comandos partem da raiz do projeto (a pasta `ecoleta/`), exceto onde indicado.

### 1. Baixe o projeto e coloque-o na pasta pública do Apache

O repositório se chama `gurias-ecoleta`, mas o `.env.example` espera que a pasta se chame `ecoleta`. Clone já com esse nome, dentro da pasta pública do Apache:

```bash
git clone <URL-do-repositório> ecoleta
```

Pastas públicas:

- macOS (Homebrew): `/opt/homebrew/var/www/ecoleta`
- Windows (XAMPP): `C:\xampp\htdocs\ecoleta`

Confira se a API responde. Abra `http://localhost/ecoleta/api/usuarios.php` no navegador; deve aparecer `{"sucesso":false,"mensagem":"Acesso não autorizado."}`. Essa é a resposta certa, porque a API recusa quem não envia o token.

> Se o seu Apache usa outra porta ou outra pasta (por exemplo, `http://localhost:8080/meu-projeto/`), anote o endereço: ele vai no `.env` do passo 5.

### 2. Crie o banco de dados

```bash
mysql -uroot < api/database.sql
```

Isso cria o banco `ecoleta` e a tabela `usuarios`, se ainda não existirem. Esse é o caminho mínimo para o app funcionar. No XAMPP, você também pode abrir o phpMyAdmin (`http://localhost/phpmyadmin`), ir em **Importar** e escolher o arquivo `api/database.sql`.

> **Alternativa:** `db/script.sql` é um backup do banco usado pelo grupo. Ele traz usuários de teste e outras tabelas (`bairros`, `caminhoes`, `ecopontos`, `logradouros`, `programacoes_coleta`) que o app ainda não usa. Ele não cria o banco nem as colunas de endereço (`Logradouro`, `Bairro`, `Cidade`, `Uf`); se preferir usá-lo, crie o banco `ecoleta`, importe o arquivo nele e rode o `ALTER TABLE` abaixo.

Se a tabela `usuarios` já existia sem as colunas de endereço, rode o `ALTER TABLE` que está no comentário do início de `api/database.sql`.

### 3. Confira o usuário e a senha do MySQL

O projeto usa o usuário `root` sem senha, o padrão do XAMPP e do MySQL do Homebrew. Se o seu for diferente, altere nos dois arquivos:

- `api/classes/Database.php` (usado pelo app)
- `website/conexao.php` (usado pelo site)

### 4. Crie o `api/config.php`

Copie o modelo:

```bash
cp api/config.exemplo.php api/config.php          # macOS / Linux
copy api\config.exemplo.php api\config.php        # Windows
```

Gere um token:

```bash
node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"
```

Cole o valor em `token_interno`, no `api/config.php`. Esse arquivo não vai para o Git.

### 5. Crie o `app/.env`

```bash
cd app
cp .env.example .env          # macOS / Linux
copy .env.example .env        # Windows
```

Preencha:

| Variável | O que colocar |
| --- | --- |
| `ORS_API_KEY` | Token do openrouteservice: crie uma conta em <https://openrouteservice.org/dev/#/signup> e copie o token do painel. |
| `PORT` | Porta do servidor Express. Padrão: `8787`. |
| `USER_API_URL` | Endereço do `dados_usuario.php`. Padrão: `http://localhost/ecoleta/api/dados_usuario.php`. |
| `USUARIOS_API_URL` | Endereço do `usuarios.php`. Padrão: `http://localhost/ecoleta/api/usuarios.php`. |
| `SESSION_SECRET` | Um token novo, gerado com o mesmo comando do passo 4. |
| `INTERNAL_API_TOKEN` | **O mesmo valor** de `token_interno` do `api/config.php`. |
| `USER_API_FILE` | Opcional: caminho do `dados_usuario.php` que o servidor observa. Padrão: `../api/dados_usuario.php`. |

O `.env` não vai para o Git.

### 6. Instale as dependências do app

Ainda dentro da pasta `app/`:

```bash
npm install
```

## Como rodar

### Aplicativo em desenvolvimento

Com Apache, PHP e MySQL ligados:

```bash
cd app
npm run dev
```

Esse comando sobe o servidor Express (porta 8787) e o Vite. Abra **<http://localhost:5173>**. Alterações no código aparecem na hora no navegador; alterações em `app/server/` exigem parar (Ctrl+C) e rodar de novo.

### Aplicativo em produção

```bash
cd app
npm run build
npm run preview
```

Abra **<http://localhost:8787>** (ou a porta definida em `PORT`). Nesse modo, o próprio Express entrega a tela pronta da pasta `dist/`.

### Site institucional

Abra `http://localhost/ecoleta/website/inicio.html`. O site é servido direto pelo Apache e não precisa do `npm`.

### Comandos do app

Rode dentro da pasta `app/`:

| Comando | O que faz |
| --- | --- |
| `npm run dev` | Sobe Express + Vite para desenvolvimento. |
| `npm run build` | Gera a versão de produção em `dist/`. |
| `npm run preview` | Sobe o Express servindo a pasta `dist/`. |
| `npm test` | Roda os testes automatizados. |
| `npm run lint` | Verifica o código com o ESLint. |
| `npm run server` | Sobe só o servidor Express. |
| `npm run dev:client` | Sobe só o Vite. |

## Como funciona

### Login e sessão

O servidor Express recebe login e cadastro e repassa para `api/usuarios.php`. Se o login der certo, cria uma sessão num cookie assinado, que dura 7 dias. As rotas do mapa só respondem com a sessão; sem ela, a tela volta para o Login.

### Endereço do usuário

- No cadastro, o PHP busca o CEP no ViaCEP e grava rua, bairro, cidade e UF na tabela `usuarios`. Um CEP inexistente é recusado.
- Depois do login, o Express chama `dados_usuario.php?id=<usuário logado>`, que devolve o endereço do usuário e o do caminhão.
- Usuários cadastrados sem o endereço gravado (por exemplo, pelo site) têm o endereço buscado pelo CEP e gravado no primeiro acesso ao mapa.
- Sem CEP, ou com CEP inexistente, o mapa mostra uma mensagem explicando o motivo.

### Rota e mapa

- O endereço do caminhão é a constante `CAMINHAO` em `api/dados_usuario.php`.
- O servidor localiza os dois endereços e calcula a rota para caminhão no openrouteservice, considerando altura, largura, peso e carga por eixo.
- O CEP não traz o número da casa, então o ponto de coleta é localizado pela rua. Quando o serviço não conhece a rua, usa o centro do bairro, e o marcador mostra "Localização aproximada".
- O caminhão anda pela rota em uma **animação**. A posição mostrada é simulada, não vem de GPS.
- O horário previsto é a hora atual mais o tempo estimado da rota.
- Controles do mapa:
  - **+** e **−**: aproximar e afastar.
  - **◎**: acompanhar o caminhão, mantendo-o no centro. Arrastar o mapa desliga o acompanhamento; tocar de novo no ◎ volta a mostrar a rota inteira.
- A última rota fica salva no navegador, separada por usuário, e é recalculada depois de 24 horas.
- A tela se adapta a celular (em pé e deitado), tablet, desktop e monitores ultrawide.

### Atualização automática

Com o mapa aberto, a página mantém uma conexão com o servidor (Server-Sent Events, em `/api/usuario/stream`). O servidor observa o arquivo `api/dados_usuario.php`: ao salvá-lo (por exemplo, com um novo endereço em `CAMINHAO`), quem estiver com o mapa aberto recebe os dados novos e a rota é recalculada, sem recarregar a página.

## Referência das APIs

### Servidor Express (usado pelo navegador)

| Método e rota | Precisa de login | Descrição |
| --- | --- | --- |
| `POST /api/cadastro` | Não | Cria o usuário. Corpo: `nome`, `email`, `cep`, `senha`. |
| `POST /api/login` | Não | Valida `email` e `senha` e cria a sessão. |
| `GET /api/sessao` | — | Devolve o usuário logado, ou 401. |
| `POST /api/logout` | — | Encerra a sessão. |
| `GET /api/usuario` | Sim | Endereço do usuário logado e do caminhão. |
| `GET /api/usuario/stream` | Sim | O mesmo, em conexão contínua (Server-Sent Events). |
| `POST /api/route` | Sim | Calcula a rota. Corpo: `origin`, `destination` e `vehicle`. |
| `GET /api/health` | Não | Verifica se o servidor está no ar. |

### API PHP (`api/`, só para o servidor Express)

Toda chamada precisa do cabeçalho `X-Api-Token` com o valor de `api/config.php`. Sem ele, a resposta é 403.

| Arquivo | Chamada | Descrição |
| --- | --- | --- |
| `usuarios.php` | `POST` com `{"acao": "cadastrar", "nome", "email", "cep", "senha"}` | Cadastra o usuário. |
| `usuarios.php` | `POST` com `{"acao": "login", "email", "senha"}` | Valida o login. |
| `usuarios.php` | `POST` com `{"acao": "recuperarSenha", "email", "novaSenha"}` | Troca a senha. |
| `usuarios.php` | `GET ?id=` / `PUT {id, nome, email}` / `DELETE {id}` | Consulta, altera ou exclui um usuário. |
| `dados_usuario.php` | `GET ?id=` | Endereço do usuário e do caminhão. |

## Banco de dados

Banco `ecoleta`, tabela `usuarios`:

| Coluna | Tipo | Observação |
| --- | --- | --- |
| `UsuarioID` | INT, auto incremento | Chave primária. |
| `Nome` | VARCHAR(20) | O limite de 20 caracteres é validado no cadastro do app. |
| `Email` | VARCHAR(150) | |
| `Senha` | VARCHAR(150) | Hash bcrypt (`password_hash`); a senha nunca é gravada em texto. |
| `CEP` | VARCHAR(11) | Só números (ex.: `90020000`). |
| `Logradouro`, `Bairro`, `Cidade`, `Uf` | VARCHAR / CHAR(2) | Preenchidos pelo ViaCEP. |

O app e o site gravam na mesma tabela.

## Segurança

- **Sessão:** cookie `HttpOnly` (o JavaScript da página não consegue ler) e assinado com `SESSION_SECRET` (não dá para forjar).
- **Tentativas de login:** depois de 10 senhas erradas para o mesmo e-mail e IP, o login fica bloqueado por 15 minutos.
- **API PHP:** só aceita chamadas com o token interno, então não dá para usá-la direto pelo navegador para ler, alterar ou excluir usuários.
- **Senhas:** guardadas com hash bcrypt.
- **Segredos:** `.env` e `api/config.php` ficam fora do Git (veja o `.gitignore`). Nunca publique esses arquivos; se vazarem, gere valores novos.

## Testes

```bash
cd app
npm test        # testes automatizados de login, cadastro, sessão e token
npm run lint    # verificação do código
```

Os testes automatizados usam uma API PHP simulada, então não precisam de Apache nem MySQL.

## Solução de problemas

| Sintoma | Causa provável | Como resolver |
| --- | --- | --- |
| "Não foi possível conectar ao serviço de usuários" no login | Apache desligado. | Ligue o Apache e confira o passo 1. |
| "O serviço de usuários está indisponível no momento" no login | URL errada no `.env` ou token diferente entre `.env` e `api/config.php`. | Confira `USUARIOS_API_URL`, `USER_API_URL` e `INTERNAL_API_TOKEN`. O terminal do `npm run dev` mostra o motivo exato. |
| `{"sucesso":false,"mensagem":"Acesso não autorizado."}` | Chamada à API PHP sem o token certo. | É o esperado no navegador. No app, confira se os dois tokens são iguais. |
| "Erro interno do servidor" no cadastro ou login | MySQL desligado, ou usuário e senha do banco errados. | Ligue o MySQL e confira o passo 3. |
| "Configure ORS_API_KEY…" ou a rota não é calculada | `ORS_API_KEY` vazia ou inválida. | Preencha a chave no `.env` e reinicie o `npm run dev`. |
| "Seu cadastro não tem CEP" ou "CEP … não foi encontrado" | O usuário foi cadastrado sem CEP, ou com um CEP inexistente (por exemplo, pelo site). | Corrija o CEP do usuário no banco. |
| `EADDRINUSE` ao rodar `npm run dev` | A porta 8787 ou 5173 já está em uso. | Feche o outro processo ou troque `PORT` no `.env`. |
| O mapa não aparece ou fica cinza | Sem internet para carregar os mapas. | Verifique a conexão. |
| "Localização aproximada" no ponto de coleta | O serviço de mapas não conhece a rua; foi usado o centro do bairro. | É uma limitação do serviço (veja abaixo). |

## Pontos de atenção

- **Localização aproximada:** o CEP não traz o número da casa. Um campo "Número" no cadastro deixaria o ponto de coleta mais exato.
- **Um só caminhão, em Porto Alegre:** o endereço é fixo em `api/dados_usuario.php`, e a posição no mapa é uma animação. Usuários de outras cidades terão rotas longas.
- **Login do site e do app são diferentes:** o site (`website/`) entra pelo **nome de usuário** e grava o CEP como foi digitado, sem validar. O app entra pelo **e-mail** e confere o CEP. Os dois usam a mesma tabela.
- **E-mail sem índice único:** a tabela não impede e-mails repetidos. O app recusa duplicados no próprio cadastro, mas o site não. Para impedir de vez, remova os duplicados e crie um índice único na coluna `Email`.
- **Recuperação de senha:** o botão "Esqueceu a senha?" do app ainda não tem fluxo; a ação `recuperarSenha` da API troca a senha sem confirmação por e-mail.
- **Menu do app:** os itens "Meu perfil", "Mudar Local", "Col. Seletiva" e "Configurações" ainda não têm telas. "Sair do Perfil" funciona.
- **PHP do site:** `website/act/*.php` usa `FILTER_SANITIZE_STRING`, que está obsoleto desde o PHP 8.1 e gera avisos.
