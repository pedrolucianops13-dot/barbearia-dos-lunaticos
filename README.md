# 💈 Barbearia dos Lunáticos — Sistema Web

Sistema web completo desenvolvido como projeto acadêmico (UNIALFA) para gerenciamento de agendamentos, catálogo de serviços/produtos e painel administrativo de barbearia.

## 🚀 Tecnologias Utilizadas
* **Front-End:** HTML5 Semântico, CSS3 (Flexbox & Responsividade) e JavaScript (`script.js`)
* **Back-End:** PHP 8+ (PDO e controle de sessões)
* **Banco de Dados:** MySQL (`banco/barbearia_lunaticos.sql`)

## 📁 Estrutura do Projeto
* `/banco` — Script SQL para criação e povoamento das tabelas.
* `/site` — Páginas públicas (`index.html`, `servicos.html`, `contato.html`), sistema de agendamento (`agendamento.html`, `compra.html`), autenticação (`login.php`), painel administrativo (`dashboard.php`) e endpoints de API (`/api`).

## ⚙️ Como Executar Localmente
1. Instale e inicie os serviços **Apache** e **MySQL** via **XAMPP**.
2. Copie a pasta do projeto para dentro de `C:\xampp\htdocs\`.
3. Acesse `http://localhost/phpmyadmin`, crie o banco `barbearia_lunaticos` e importe o arquivo `banco/barbearia_lunaticos.sql`.
4. Acesse no navegador: `http://localhost/barbearia-dos-lunaticos/site/index.html`.
