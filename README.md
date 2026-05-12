# EduHub, rede social educacional

![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white) ![Yii2](https://img.shields.io/badge/Yii2-40B3D8?logo=yii&logoColor=white) ![AWS](https://img.shields.io/badge/AWS-232F3E?logo=amazonaws&logoColor=white) ![MySQL](https://img.shields.io/badge/MySQL-00000F?logo=mysql&logoColor=white) ![HumHub](https://img.shields.io/badge/HumHub-1B75BB?logoColor=white) ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?logo=javascript&logoColor=black) ![HTML5](https://img.shields.io/badge/HTML5-E34F26?logo=html5&logoColor=white) ![CSS3](https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white)

## Resumo

O **EduHub** foi uma rede social multiplataforma voltada ao intercâmbio de ideias e conteúdos educacionais para pessoas empreendedoras. A aplicação teve versões para navegador e aplicativo Android, chegando a entrar em fase de testes com interações reais entre usuários dentro da plataforma.

O projeto contou com domínio próprio, endereço de e-mail especializado e sistema de notificações automáticas por e-mail. Toda a infraestrutura tecnológica foi levantada do zero e arquitetada com base no **framework Yii 2.0**, na plataforma **HumHub**, em banco de dados **MySQL** e em serviços da **AWS**.

Na prática, o EduHub oferecia uma base social educacional com:

- perfis de usuários;
- publicações e comentários;
- espaços de interação;
- notificações;
- frontend responsivo para browsers, celulares e tablets;
- customizações de frontend e backend;
- banco relacional MySQL;
- hospedagem em nuvem;
- integração com serviços de e-mail e notificação.


| Celular | Tablet 7" | Tablet 9" |
| --- | --- | --- |
| <img src="GooglePlayImgs/Cel/WhatsApp Image 2024-04-13 at 17.07.25.jpeg" alt="EduHub em celular" width="180"> | <img src="GooglePlayImgs/7po/WhatsApp Image 2024-04-14 at 01.41.05.jpeg" alt="EduHub em tablet de 7 polegadas" width="220"> | <img src="GooglePlayImgs/9po/WhatsApp Image 2024-04-13 at 20.40.27.jpeg" alt="EduHub em tablet de 9 polegadas" width="260"> |

## Sumário

- [1. Introdução e motivação](#1-introdução-e-motivação)
  - [1.1. EduHub como rede social educacional](#11-eduhub-como-rede-social-educacional)
- [2. Visão geral do projeto](#2-visão-geral-do-projeto)
- [3. O que é o HumHub](#3-o-que-é-o-humhub)
- [4. Arquitetura cliente-servidor](#4-arquitetura-cliente-servidor)
  - [4.1. Cliente](#41-cliente)
  - [4.2. Servidor](#42-servidor)
  - [4.3. Banco de dados](#43-banco-de-dados)
- [5. Comunicação entre cliente e servidor](#5-comunicação-entre-cliente-e-servidor)
  - [5.1. HTTP GET/POST](#51-http-getpost)
  - [5.2. AJAX/jQuery](#52-ajaxjquery)
  - [5.3. PJAX/AJAX](#53-pjaxajax)
  - [5.4. Polling HTTP](#54-polling-http)
- [6. Customizações realizadas](#6-customizações-realizadas)
- [7. App Android EduHub: a tecnologia de WebView](#7-app-android-eduhub-a-tecnologia-de-webview)
- [8. Infraestrutura e serviços AWS](#8-infraestrutura-e-serviços-aws)
- [9. Resumo técnico](#9-resumo-técnico)

## 1. Introdução e motivação

A motivação do EduHub era criar um ambiente digital onde pessoas empreendedoras pudessem trocar conhecimento, publicar conteúdos, interagir com outros usuários e organizar conversas em torno de temas educacionais.

Em vez de construir uma rede social inteira do zero, o projeto utilizou o HumHub como base. Essa escolha permitiu partir de uma estrutura já preparada para autenticação, perfis, publicações, comentários, permissões, espaços, notificações e módulos. A partir dessa base, o EduHub recebeu adaptações próprias para o seu contexto educacional.

### 1.1. EduHub como rede social educacional

O EduHub foi pensado como uma plataforma social com foco em aprendizado, empreendedorismo e colaboração. A proposta era permitir que usuários acessassem a plataforma pelo navegador ou por aplicativo Android, criassem conteúdo, acompanhassem atualizações e interagissem com outros participantes.

Esse tipo de aplicação precisa unir recursos de rede social com recursos de plataforma educacional. Por isso, a base do HumHub foi útil: ela já oferecia a estrutura social, enquanto as customizações permitiam moldar a experiência para o objetivo específico do EduHub.

## 2. Visão geral do projeto

O EduHub era uma aplicação web baseada em PHP/Yii2, usando o HumHub como plataforma principal. O projeto seguia uma arquitetura monolítica web tradicional: o servidor PHP processava as regras de negócio, renderizava páginas, respondia chamadas AJAX e persistia dados no MySQL.

Os principais blocos do projeto eram:

- **Aplicação HumHub/Yii2**: núcleo da plataforma, responsável por rotas, controllers, models, views e módulos.
- **Frontend web**: interface entregue ao navegador com HTML, CSS e JavaScript.
- **App Android**: cliente móvel voltado ao acesso multiplataforma com tecnologia WebView.
- **Banco MySQL**: camada de persistência dos dados de usuários, conteúdos, relações e configurações.
- **Serviços AWS**: infraestrutura de hospedagem, e-mail e notificações.
- **Customizações do projeto**: ajustes de regra de negócio, entidades, CRUDs, eventos, frontend e responsividade.

## 3. O que é o HumHub

O **HumHub** é um software open-source modular, escrito em **PHP**, usado para criar redes sociais, intranets, bases de conhecimento e plataformas de comunicação ou colaboração.

A estrutura geral da aplicação gira em torno de usuários, espaços, conteúdo e módulos. Na prática, 
ele fornece uma base pronta com perfis, publicações, comentários, permissões, notificações, busca, administração e extensões. No EduHub, o HumHub funcionou como a fundação da rede social, recebendo customizações massivas para se adaptar à proposta educacional.

## 4. Arquitetura cliente-servidor

O EduHub utilizava uma arquitetura **cliente-servidor web tradicional**.

```text
Navegador / App Android
  |
  | HTTP/HTTPS
  v
Servidor PHP/Yii2/HumHub
  |
  | Yii DB / PDO
  v
Banco MySQL
```

### 4.1. Cliente

O cliente principal era o navegador do usuário. Ele recebia HTML, CSS e JavaScript renderizados pelo servidor. Também havia uma versão em aplicativo Android para acesso móvel.

No navegador, a interface podia executar ações dinâmicas com JavaScript, como publicar, comentar, curtir, buscar conteúdo e carregar notificações sem recarregar a página inteira.

### 4.2. Servidor

O servidor era a aplicação PHP baseada em Yii2 e HumHub. O ponto de entrada principal do projeto é o arquivo:

```text
index.php
```

Esse arquivo carrega o Yii2, mescla as configurações do projeto e inicia a aplicação:

```text
humhub\components\Application
```

As requisições são direcionadas para controllers localizados em caminhos como:

```text
protected/humhub/modules/.../controllers
```

### 4.3. Banco de dados

O backend conecta em **MySQL** usando o componente de banco de dados do Yii2, baseado em PDO.

A configuração padrão do projeto aponta para:

```text
mysql:host=localhost;dbname=humhub
```

## 5. Comunicação entre cliente e servidor

O EduHub não era uma SPA pura, como uma aplicação React ou Vue com uma API REST totalmente separada. Ele seguia o modelo server-side tradicional do HumHub, com JavaScript no frontend para melhorar a experiência do usuário.

### 5.1. HTTP GET/POST

As páginas, formulários e ações principais usam requisições HTTP tradicionais:

- **GET**, para carregar páginas, listar conteúdos e buscar informações;
- **POST**, para enviar formulários, criar registros, alterar dados e executar ações.

### 5.2. AJAX/jQuery

AJAX permite que o JavaScript converse com o servidor sem recarregar a página inteira. O HumHub usa jQuery e módulos JavaScript próprios para executar ações como comentar, curtir, carregar notificações e atualizar partes da interface.

Exemplo conceitual:

```text
Clique do usuário
  |
  v
JavaScript / jQuery
  |
  | AJAX POST
  v
Controller PHP/Yii2
  |
  v
Resposta HTML ou JSON
  |
  v
Atualização parcial da tela
```

### 5.3. PJAX/AJAX

PJAX combina AJAX com histórico do navegador. Ele permite carregar apenas uma parte da página, mantendo a navegação mais rápida e atualizando a URL quando necessário.

No contexto do EduHub, isso ajuda a manter a aplicação com sensação mais dinâmica, sem transformar o projeto em uma SPA completa.

### 5.4. Polling HTTP

Para atualizações ao vivo, o projeto usa **polling HTTP** por padrão, não WebSocket.

A configuração `live` aponta para:

```text
humhub\modules\live\driver\Poll
```

O JavaScript chama periodicamente a rota:

```text
/live/poll
```

O servidor responde com JSON contendo novos eventos, como notificações ou atualizações relevantes.

## 6. Customizações realizadas

O EduHub exigiu customização em larga escala sobre a base do HumHub.

As principais frentes de customização incluíram:

- criação e alteração de regras de negócio;
- lógica de lançamento de eventos;
- novos campos em fluxos de CRUD;
- criação e ajuste de entidades relacionais no banco de dados MySQL;
- tratamento completo das requisições, desde a entrada no servidor até o envio da resposta;
- implementações em JavaScript no frontend;
- ajustes de responsividade para uso multiplataforma;
- integração com serviços de e-mail e notificação da AWS.

## 7. App Android EduHub: a tecnologia de WebView

Além da versão para navegador, o EduHub também contou com um aplicativo Android. Esse app foi criado usando a plataforma online [Kodular](https://www.kodular.io/), uma ferramenta visual para criação de aplicativos Android com componentes de arrastar e soltar e programação por blocos.

No caso do EduHub, a ideia central do app Android era usar a tecnologia de **WebView**. Um WebView é um componente nativo do Android capaz de carregar uma página web dentro do aplicativo. Na prática, ele permite que uma aplicação web responsiva seja apresentada dentro de um app instalado no celular, mantendo a experiência próxima à de um aplicativo dedicado.

Esse modelo fazia sentido para o projeto porque o EduHub já tinha uma interface web responsiva e uma arquitetura cliente-servidor baseada em HTTP/HTTPS. O app Android funcionava como uma camada de acesso móvel à plataforma, carregando a experiência web do EduHub dentro de um contêiner Android.

Com isso, foi possível entregar presença em aplicativo sem duplicar toda a regra de negócio em código Android nativo. O servidor continuava centralizando autenticação, usuários, publicações, comentários, notificações e persistência em MySQL, enquanto o app oferecia uma porta de entrada mobile para a mesma plataforma.

Referência: [Kodular - plataforma oficial](https://www.kodular.io/).

## 8. Infraestrutura e serviços AWS

A aplicação era hospedada em uma instância **Amazon EC2**. O acesso técnico ao servidor era feito via **SSH** e também por um ambiente visual remoto para interagir graficamente com o Ubuntu da máquina virtual.

Pelo contexto descrito, esse ambiente visual provavelmente era o **Chrome Remote Desktop**, ferramenta do Google que permite acessar remotamente uma máquina com interface gráfica pelo navegador.

Para o sistema de e-mails automáticos e endereços personalizados, foram utilizados serviços como:

- **Amazon WorkMail**, para gerenciamento de e-mails corporativos;
- **Amazon Simple Email Service (SES)**, para envio de e-mails transacionais;
- **Amazon Simple Notification Service (SNS)**, para suporte a notificações.

## 9. Resumo técnico

O EduHub foi construído sobre uma arquitetura **navegador/app Android <-> servidor PHP/Yii2/HumHub <-> banco MySQL**.

O cliente se comunica com o servidor por **HTTP/HTTPS**. O servidor renderiza páginas, processa regras de negócio, responde chamadas AJAX/PJAX, consulta e grava dados no MySQL e integra serviços externos da AWS para hospedagem, e-mail e notificações.

Em termos práticos, o EduHub aproveitou a base social do HumHub e adicionou customizações específicas para criar uma rede social educacional voltada a pessoas empreendedoras.

---

Rafael Medrano

[![LinkedIn](https://img.shields.io/badge/LinkedIn-0077B5?logo=linkedin&logoColor=white)](https://www.linkedin.com/in/rafaelsmedrano/) [![Gmail](https://img.shields.io/badge/Gmail-333333?logo=gmail&logoColor=red)](mailto:rafael.smedrano@gmail.com)