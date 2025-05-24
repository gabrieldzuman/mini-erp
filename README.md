# 🧾 Mini ERP - Sistema de Controle de Pedidos, Produtos, Estoque e Cupons

Este é um projeto de **Mini ERP** desenvolvido com **Laravel** que permite o gerenciamento completo de **produtos**, **estoque**, **pedidos**, **cupons**, **frete**, **verificação de CEP**, **sessão de carrinho**, **envio de e-mail ao finalizar pedido** e **webhook de atualização de status de pedidos**.

---

## 🚀 Funcionalidades

- CRUD de Produtos e Estoque com controle de variações.
- Carrinho de compras utilizando sessão.
- Cálculo de frete com base no valor do pedido.
- Verificação de endereço pelo CEP via API [ViaCEP](https://viacep.com.br/).
- Aplicação de cupons com regras de valor mínimo e validade.
- Envio de e-mail ao finalizar o pedido.
- Webhook que atualiza ou remove pedidos com base no status recebido.

---

## 🛠️ Tecnologias Utilizadas

- **PHP 8.x**
- **Laravel 10+**
- **MySQL**
- **Bootstrap 5** (front-end simples e funcional)
- **Blade** (motor de templates do Laravel)
- **Session API** do Laravel
- **Mailtrap** ou outro provedor SMTP para envio de e-mail
- **ViaCEP API** para consulta de endereço
- **Postman** ou outro cliente HTTP para testar Webhook

---

## 🗃️ Estrutura do Banco de Dados

**Tabelas:**

- `produtos`: nome, preço.
- `estoques`: id_produto, variação, quantidade.
- `pedidos`: subtotal, frete, total, endereço, status.
- `cupons`: código, desconto, validade, valor_mínimo.

---

## 📦 Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/seu-usuario/mini-erp.git
cd mini-erp
```

### 2. Instale as dependências

```bash
composer install
npm install && npm run dev
```

### 3. Configure o ambiente

Crie o arquivo `.env`:

```bash
cp .env.example .env
```

Edite as configurações do banco de dados:

```env
DB_DATABASE=mini_erp
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Gere a chave da aplicação

```bash
php artisan key:generate
```

### 5. Execute as migrações

```bash
php artisan migrate
```

### 6. Execute o servidor local

```bash
php artisan serve
```

Acesse em: [http://localhost:8000](http://localhost:8000)

---

## 🛍️ Como usar

### Cadastro de Produtos

- Vá para `/produtos`
- Preencha nome, preço, variação (se houver), e quantidade em estoque
- Clique em "Salvar"

### Atualização de Produtos

- Produtos listados na mesma página com botão de "Editar"
- Alterações de estoque ou valores são salvas na mesma rota

### Carrinho

- Após cadastrar produtos, clique em "Comprar"
- Carrinho em sessão é iniciado
- O sistema calcula frete automaticamente:

| Subtotal           | Frete      |
|--------------------|------------|
| R$ 0,00 - R$ 51,99 | R$ 20,00   |
| R$ 52 - R$ 166,59  | R$ 15,00   |
| R$ 200+            | **Grátis** |

### Verificação de CEP

- Ao inserir um CEP no formulário de finalização, o sistema busca o endereço via [ViaCEP](https://viacep.com.br/)
- Preenche automaticamente cidade, estado e rua

### Cupons

- Aplique um cupom válido na tela de carrinho
- Cupons são verificados por:
  - Código válido
  - Data de validade
  - Valor mínimo do carrinho

### Finalizar Pedido

- Preencha os dados de endereço
- E-mail de confirmação é enviado com os dados do pedido (necessário configurar SMTP)

---

## 📬 Envio de E-mail

Configure seu `.env` com as credenciais SMTP (exemplo usando [Mailtrap](https://mailtrap.io)):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=erp@example.com
MAIL_FROM_NAME="Mini ERP"
```

---

## 📡 Webhook

O sistema escuta requisições `POST` na rota:

```
POST /api/webhook/pedido
```

**Payload esperado:**

```json
{
  "pedido_id": 1,
  "status": "cancelado"
}
```

- Se o status for `"cancelado"` o pedido será removido
- Caso contrário, o status do pedido será atualizado

---

## 🧪 Testes

> Testes automatizados ainda não foram implementados neste MVP. Recomenda-se uso de Postman ou Insomnia para testes de rotas.

---

## 📌 Considerações Finais

- Projeto foi desenvolvido com foco em simplicidade e boas práticas de MVC.
- Toda lógica está dividida entre Models, Controllers e Views.
- Pode ser expandido facilmente com novos módulos (clientes, notas fiscais, dashboard etc).

---

## 👨‍💻 Autor

Desenvolvido por **Gabriel Dzuman**  

---
