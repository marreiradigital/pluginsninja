# Plugins Ninja

Plugin de integração para gerenciamento remoto e sincronização de sites WordPress com o ecossistema Plugins Ninja.

## Funcionalidades

- **Conexão Remota**: Autenticação via `authorize-application.php` para vincular o site ao painel `ltd.marreira.site`.
- **Sincronização de Dados**: Envia periodicamente informações de saúde do site, plugins e temas instalados.
- **Instalação Remota**: Permite instalação de plugins e temas via API.

## Endpoints da API

O plugin adiciona endpoints na rota `/wp-json/pluginsninja/`.

### 1. Receber ID do Site
- **Rota**: `POST /wp-json/pluginsninja/receber-id/`
- **Descrição**: Define o ID único de conexão do site.
- **Parâmetros Body**:
  - `site_id`: String (Obrigatório)

### 2. Sincronizar / Comandos
- **Rota**: `POST /wp-json/pluginsninja/sincronizar/`
- **Autenticação**: Requer `Application Passwords` ou usuário logado com permissão `install_plugins`.
- **Parâmetros Body**:
  - `command`: `install`
  - `type`: `plugin` ou `theme`
  - `slug`: Slug do repositório (ex: `elementor`)
  - `url`: URL direta do ZIP (opcional)

## Sincronização de Dados (Data Sync)

O plugin coleta e envia dados para `https://ltd.marreira.site/wp-json/pluginsninja/json/sincronizar/sites/dados/` a cada inicialização do admin (`admin_init`), de forma não bloqueante.

**Dados Enviados:**
- Versão do WP / PHP / Servidor
- Lista de Plugins (Ativos/Inativos)
- Lista de Temas
- Status SSL / URL

## Estrutura de Arquivos

- `plugins-ninja.php`: Arquivo principal.
- `includes/`: Classes de lógica (Admin, API, Sync, Installer).
- `assets/`: Recursos estáticos (CSS).
