# API Teste Técnico: Backend 

Esta é uma API RESTful, construída com o framework Laravel. Ela fornece funcionalidades para gerenciamento de usuários, produtos e pedidos, utilizando filas para processamento assíncrono de pedidos e autenticação baseada em JWT.

## Índice

- [Visão Geral](#visão-geral)
- [Pré-requisitos](#pré-requisitos)
- [Instalação](#instalação)
- [Configuração do Ambiente](#configuração-do-ambiente)
- [Executando a Aplicação](#executando-a-aplicação)
- [Endpoints da API](#endpoints-da-api)
- [Executando os Testes](#executando-os-testes)

## Visão Geral

A API permite:

- Autenticação de usuários (login, logout, refresh token).
- Gerenciamento de perfil de usuário.
- CRUD completo para Usuários.
- CRUD completo para Produtos.
- Criação e gerenciamento de Pedidos.
- Processamento assíncrono de pedidos usando filas e jobs.
- Notificações por e-mail sobre o status do pedido.

## Pré-requisitos

- PHP 8.2 ou superior
- Composer
- Servidor de Banco de Dados (MySQL, PostgreSQL, etc.)
- Redis

## Instalação

1.  **Clone o repositório:**

    ```bash
    git clone https://github.com/seu-usuario/oto-teste-backend.git
    cd oto-teste-backend
    ```

2.  **Instale as dependências do Composer:**

    ```bash
    composer install
    ```

3.  **Copie o arquivo de ambiente:**

    ```bash
    cp .env.example .env
    ```

4.  **Gere a chave da aplicação:**

    ```bash
    php artisan key:generate
    ```

5.  **Gere a chave JWT:**

    ```bash
    php artisan jwt:secret
    ```

6.  **Execute as migrações do banco de dados:**

    ```bash
    php artisan migrate --seed
    ```

**Nota:** Após executar os seeders, um usuário padrão será criado com as seguintes credenciais:
- **E-mail:** `oto@gmail.com`
- **Senha:** `oto123456`


## Configuração do Ambiente

O arquivo `.env.example` serve como um modelo com as variáveis de ambiente utilizadas no ambiente de desenvolvimento.

Abra o arquivo `.env` e configure as seguintes variáveis:

### Banco de Dados

Configure as credenciais do seu banco de dados:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oto_teste_backend
DB_USERNAME=root
DB_PASSWORD=
```

### Filas (Queues) e Redis

Para o processamento assíncrono de pedidos, a aplicação está configurada para usar Redis.

1.  **Certifique-se de que o Redis está instalado e em execução.**

2.  **Configure a conexão da fila para Redis:**

    ```ini
    QUEUE_CONNECTION=redis
    ```

3.  **Configure os detalhes de conexão do Redis:**

    ```ini
    REDIS_CLIENT=phpredis
    REDIS_HOST=127.0.0.1
    REDIS_PASSWORD=null
    REDIS_PORT=6379
    ```

### E-mail (Mailtrap)

Para testar o envio de e-mails de notificação em um ambiente de desenvolvimento, é recomendado o uso do [Mailtrap](https://mailtrap.io/).

1.  Crie uma conta no Mailtrap e obtenha suas credenciais SMTP.

2.  Configure as variáveis de e-mail no `.env`:

    ```ini
    MAIL_MAILER=smtp
    MAIL_HOST=sandbox.smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=seu_usuario_mailtrap
    MAIL_PASSWORD=sua_senha_mailtrap
    MAIL_FROM_ADDRESS="hello@example.com"
    MAIL_FROM_NAME="${APP_NAME}"
    ```

## Executando a Aplicação

Para rodar a aplicação, você precisará iniciar o servidor web do Laravel e o worker da fila.

1.  **Inicie o servidor de desenvolvimento:**

    ```bash
    php artisan serve
    ```

2.  **Inicie o worker da fila para processar os jobs:**

    ```bash
    php artisan queue:work
    ```

## Endpoints da API

Todos os endpoints, exceto `/login`, requerem um token de autenticação JWT no cabeçalho `Authorization: Bearer <token>`.

### Autenticação

-   `POST /api/login` - Realiza o login do usuário e retorna um token JWT.
-   `POST /api/v1/logout` - Invalida o token JWT do usuário.
-   `POST /api/v1/refresh` - Atualiza um token JWT expirado.
-   `GET /api/v1/user-profile` - Retorna os dados do usuário autenticado.

### Usuários

-   `GET /api/v1/users` - Lista todos os usuários.
-   `POST /api/v1/users` - Cria um novo usuário.
-   `GET /api/v1/users/{id}` - Obtém os detalhes de um usuário.
-   `PUT/PATCH /api/v1/users/{id}` - Atualiza um usuário.
-   `DELETE /api/v1/users/{id}` - Exclui um usuário.
-   `GET /api/v1/users/{id}/orders` - Lista todos os pedidos de um usuário específico.

### Produtos

-   `GET /api/v1/products` - Lista todos os produtos.
-   `POST /api/v1/products` - Cria um novo produto.
-   `GET /api/v1/products/{id}` - Obtém os detalhes de um produto.
-   `PUT/PATCH /api/v1/products/{id}` - Atualiza um produto.
-   `DELETE /api/v1/products/{id}` - Exclui um produto.

### Pedidos (Orders)

-   `GET /api/v1/orders` - Lista todos os pedidos.
-   `POST /api/v1/orders` - Cria um novo pedido.
-   `GET /api/v1/orders/{id}` - Obtém os detalhes de um pedido.
-   `PUT/PATCH /api/v1/orders/{id}` - Atualiza um pedido.
-   `DELETE /api/v1/orders/{id}` - Exclui um pedido.
-   `PATCH /api/v1/orders/{id}/status` - Atualiza o status de um pedido.

## Executando os Testes

Para executar a suíte de testes automatizados, utilize o seguinte comando:

```bash
php artisan test
```