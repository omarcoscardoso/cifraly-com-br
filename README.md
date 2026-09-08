# Cifraly (cifraly.com.br) 🎸🎵

> Plataforma moderna para gestão de ministérios de louvor, bandas, escalas de voluntários, repertório de cifras e Modo Palco em tempo real com transposição inteligente.

---

## 🎯 Sobre o Projeto

O **Cifraly** foi desenvolvido para solucionar os desafios de liderança, ensaios e execução musical em igrejas e bandas. A plataforma centraliza em um ambiente multi-tenant colaborativo o gerenciamento de equipes, escalas de pessoas, montagem de setlists e a experiência ao vivo no palco com cifras transpostas dinamicamente.

---

## 🚀 Principais Funcionalidades

### 🏢 Multi-Tenancy Nativo & Onboarding
- **Isolamento Completo por Organização**: Igrejas ou bandas possuem dados estritamente segregados.
- **Códigos de Convite Alfanuméricos (`/join/{code}`)**: Novos voluntários entram na organização de forma ágil através de um código de convite ou link direto.
- **Autenticação Flexível**: Suporte a login tradicional (e-mail/senha) e autenticação social com **Google OAuth** vinculando convites automaticamente.
- **Controle de Acesso Baseado em Papéis (RBAC)**:
  - **Super Administrador (Global)**: Gestão de todas as organizações e usuários da plataforma.
  - **Administrador da Organização**: Gestão completa de eventos, equipes, membros, funções e configurações da igreja.
  - **Membro da Organização**: Acesso ao repertório, consulta e contribuição com cifras e visualização de eventos e escalas.

### 🎸 Repertório de Músicas & Cifras
- **Importador de Cifras da Web (Web Scraper)**: Busca integrada com 1 clique (Cifra Club e outras fontes) com conversão automática para notação limpa e ChordPro.
- **Proteção Avançada contra SSRF**: Sanitização rigorosa de URLs externas bloqueando redes privadas, loopbacks e metadados de nuvem.
- **Transpositor Inteligente de Tons (`ChordTransposerService`)**:
  - Reconhece acordes complexos, tétrades, notas com baixo invertido e seções (`[Intro]`, `[Verso]`, `[Refrão]`).
  - Lógica harmônica de enarmônicos (escalas com sustenidos e bemóis preferenciais).
  - *Fast-path* e cache em memória para cálculo instantâneo durante apresentações ao vivo.

### 👥 Equipes, Funções & Voluntários (Teams & Roles)
- **Equipes Temáticas**: Criação de grupos e ministérios (ex: Banda Principal, Banda Jovem, Equipe de Apoio).
- **Funções com Categorias Visuais**:
  - 🎸 `Músico / Instrumentista` (Violão, Guitarra, Baixo, Bateria, Teclado, etc.)
  - 🎤 `Vocal / Voz` (Ministro de Louvor, Backing Vocal, Soprano, etc.)
  - 🎚️ `Equipe Técnica` (Operador de Mesa, Iluminação, Projeção, etc.)
- **Atribuição de Função Padrão**: Cada voluntário vinculado a uma equipe possui sua função musical pré-definida.

### 🗓️ Gestão de Eventos, Escalas & Setlists
- **Agendamento de Eventos**: Cultos regulares, conferências, ensaios e apresentações com horários e notas gerais.
- **Escalar Equipe Completa**: Ação no painel que escala automaticamente todos os integrantes de uma equipe com suas funções padrão, evitando voluntários duplicados.
- **Setlist Personalizado com Tom Alvo**: Músicas ordenadas com definição do tom específico para aquele culto (transposição sob medida para o cantor do dia).

### 📲 Confirmação Pública de Presença (`/r/{token}`)
- **Link Individual sem Login**: O músico recebe um token único de confirmação de presença no evento.
- **Interface Mobile-First**: O voluntário visualiza o evento, sua função, o setlist e pode:
  - **Confirmar Presença** instantaneamente.
  - **Recusar Presença** com preenchimento opcional de justificativa (com validação e proteção de caracteres).
- **Notificações Integradas**:
  - Envio de convites por **E-mail** com layout responsivo.
  - Disparo direto para o **WhatsApp** com mensagem pronta e link formatado.
- **Rate Limiting**: Rotas públicas protegidas contra abusos e força bruta (`throttle:60,1`).

### 🎤 Modo Palco ao Vivo (Stage View)
- Interface Livewire de alto contraste e legibilidade, desenhada especificamente para iPads, tablets, smartphones e monitores no altar.
- Cifras exibidas automaticamente no **Tom Alvo do Evento**.
- **Transposição Dinâmica On-the-Fly**: Permite subir ou descer o tom (+1 / -1 semitom) em tempo real caso o cantor mude de ideia na hora.
- **Rolagem Automática (Auto-Scroll)** com controle de velocidade suave.
- Ajuste de tamanho da fonte e navegação rápida entre as músicas do setlist.

### 📊 Dashboard da Organização Personalizado
- **Boas-Vindas & Atalhos Rápidos**: Card com código de convite da igreja e botões rápidos para Novo Evento, Nova Cifra, Repertório e Equipes.
- **Métricas em Tempo Real**:
  - Contagem de próximos cultos/eventos e data do próximo compromisso.
  - Total de cifras cadastradas no repertório da organização.
  - Número de voluntários e equipes ativas.
  - Percentual e taxa de confirmação de presença das escalas abertas.
- **Tabela de Próximos Eventos**: Com botão de 1 clique para abrir o **Modo Palco**.
- **Repertório Recente**: Lista com as últimas músicas atualizadas e seus respectivos tons principais.

---

## 🛠️ Stack Tecnológica

- **Backend**: PHP 8.4 / Laravel 12+
- **Painel Administrativo**: Filament PHP (TALL Stack)
- **Frontend Reativo**: Livewire 3 & Alpine.js
- **Estilização**: Tailwind CSS & Heroicons
- **Banco de Dados**: MySQL / PostgreSQL / SQLite
- **Ambiente de Desenvolvimento**: Docker / Laravel Sail

---

## 🏗️ Princípios de Arquitetura & SOLID

O Cifraly adota estritamente os princípios de Clean Architecture e SOLID:

- **Single Responsibility Principle (SRP)**: Lógicas de negócio isoladas em Action classes dedicadas (ex: `AddTeamToEventRosterAction`).
- **Open/Closed Principle (OCP)**: Arquitetura de drivers para extração de cifras (`ChordScraperManager` com `CifraClubDriver` e `GenericHtmlDriver`), permitindo adicionar novos provedores sem alterar o código existente.
- **Liskov Substitution Principle (LSP)**: Políticas de acesso coesas (`SongPolicy`, `EventPolicy`, `TeamPolicy`, `RolePolicy`, `UserPolicy`, `OrganizationPolicy`).
- **Dependency Inversion Principle (DIP)**: Uso intensivo de injeção de dependências e contratos de interface via Service Container.
- **Tenancy Scoping Assíncrono (`TenancyContext`)**: Permite que Jobs de fila, comandos de console e crons processem dados de uma organização específica com isolamento rigoroso, mesmo fora do ciclo de vida HTTP do Filament.
- **Integridade Histórica com `SoftDeletes`**: Músicas, eventos e equipes possuem exclusão lógica para resguardar o histórico de relatórios e cultos passados.

---

## 💻 Instalação & Execução Local

### Pré-requisitos
- [Docker](https://www.docker.com/) e [Docker Compose](https://docs.docker.com/compose/) instalados.

### Passo a Passo

1. **Clonar o Repositório**:
   ```bash
   git clone https://github.com/seu-usuario/cifraly-com-br.git
   cd cifraly-com-br
   ```

2. **Configurar as Variáveis de Ambiente**:
   ```bash
   cp .env.example .env
   ```

3. **Subir os Containers com Laravel Sail**:
   ```bash
   ./vendor/bin/sail up -d
   ```

4. **Gerar a Chave da Aplicação**:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

5. **Executar as Migrations e Seeders**:
   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```

6. **Compilar os Assets do Frontend**:
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run build
   # ou para desenvolvimento com live-reload:
   ./vendor/bin/sail npm run dev
   ```

7. **Acessar a Aplicação**:
   - Painel Administrativo: `http://localhost:8000/admin`
   - Registro de Nova Organização: `http://localhost:8000/admin/new`

---

## 🧪 Suíte de Testes Automatizados

O projeto possui uma cobertura abrangente de testes unitários e de integração utilizando PHPUnit e Pest, cobrindo políticas de segurança, transposição de cifras, fluxos de tenancy, modo palco e confirmação de presença:

```bash
# Executar toda a suíte de testes
./vendor/bin/sail artisan test

# Executar testes específicos
./vendor/bin/sail artisan test --filter=StageAndConfirmationTest
./vendor/bin/sail artisan test --filter=ChordTransposerServiceTest
./vendor/bin/sail artisan test --filter=DashboardWidgetsTest
./vendor/bin/sail artisan test --filter=ResourceAuthorizationPoliciesTest
```

---

## 🎨 Padronização de Código (Laravel Pint)

O projeto segue as diretrizes PSR-12 e os padrões oficiais do ecossistema Laravel:

```bash
./vendor/bin/sail bin pint
```

---

## ☁️ Deploy no Google Cloud Run (com Cloud SQL)

O projeto está totalmente configurado para deploy contínuo no **Google Cloud Run** via **Google Cloud Build**, conectando-se a uma instância gerenciada do **Cloud SQL (MySQL)** através de Unix Sockets:

1. **Dockerfile Multi-stage Otimizado**:
   - Compilação dos assets com Vite / Tailwind CSS em Node 22 Alpine.
   - Imagem final enxuta em PHP 8.4-FPM Alpine com Nginx integrado e Opcache ativado.
   - Tratamento dinâmico da variável `$PORT` injetada pelo Cloud Run via template Nginx (`.docker/cloudrun/nginx.conf.template`).
   - Entrypoint com geração de caches de produção (`config:cache`, `route:cache`, `view:cache`).

2. **Pipeline Google Cloud Build (`cloudbuild.yaml`)**:
   - Criação da imagem Docker e push para o Google Artifact Registry.
   - Execução isolada de migrações (`php artisan migrate --force`) via Cloud Run Job com conexão ao Cloud SQL antes da atualização do serviço.
   - Deploy automático com `--add-cloudsql-instances` e integração segura com o **Secret Manager** (`APP_KEY`, credenciais do banco, etc.).

Para disparar o build manualmente via `gcloud`:
```bash
gcloud builds submit \
  --config=cloudbuild.yaml \
  --substitutions=_CLOUD_SQL_INSTANCE="PROJETO:REGIAO:INSTANCIA",_SERVICE_NAME="cifraly-app"
```

---

## 📄 Licença

Este projeto é um software proprietário desenvolvido para a plataforma **Cifraly** ([cifraly.com.br](https://cifraly.com.br)). Todos os direitos reservados.

