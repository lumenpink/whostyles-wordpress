# Especificação Técnica de CI/CD (Forgejo Actions)
**Ticket:** #101  
**Padrão Normativo:** GOST v1.4.0 (Seção 7 - Automação de CI/CD)  
**Status:** Aprovado para Implementação

---

## 1. Escopo e Propósito

Esta especificação define o pipeline automatizado de Integração Contínua para o repositório `whostyles-wordpress` no Forgejo Actions (`.forgejo/workflows/ci.yaml`). O pipeline tem como objetivo validar rigorosamente a segurança das dependências, a qualidade do código (análise estática), a conformidade estrutural GOST e a integridade da suíte de testes unitários e de integração a cada push e pull request.

---

## 2. Matriz de Disparo (Triggers)

O workflow deve ser disparado automaticamente nos seguintes eventos:
* **`push`**:
  * Branches: `main`, `master`
* **`pull_request`**:
  * Branches alvo: `main`, `master`

---

## 3. Topologia e Jobs do Pipeline

O pipeline é composto pelas seguintes etapas sequenciais ou paralelas:

### 3.1 Job: `quality` (Auditoria e Análise Estática)
* **Runner**: `ubuntu-latest` / `codeberg-tiny`
* **Ambiente PHP**: PHP 8.2 (baseline de compatibilidade)
* **Passos**:
  1. Checkout do repositório (`actions/checkout@v4`).
  2. Instalação e cache de dependências Composer (`composer install --no-interaction --prefer-dist`).
  3. **Auditoria de Segurança**: Execução de `composer audit` para detecção de vulnerabilidades conhecidas em pacotes.
  4. **Análise Estática Estrita**: Execução de `vendor/bin/phpstan analyse --level=max` (garantindo zero erros e zero warnings de tipagem).
  5. **Verificação Semgrep**: Varredura estática de segurança (`composer run-script semgrep`).

### 3.2 Job: `test` (Suíte de Testes Automatizados)
* **Runner**: `ubuntu-latest` / `codeberg-tiny`
* **Ambiente PHP**: PHP 8.2
* **Passos**:
  1. Checkout do repositório (`actions/checkout@v4`).
  2. Instalação de dependências Composer.
  3. **Execução de Testes Pest/PHPUnit**: Execução de `vendor/bin/pest` cobrindo 100% dos testes unitários e de integração (`tests/Unit`, `tests/Integration`).

### 3.3 Job: `gost-audit` (Conformidade GOST)
* **Runner**: `ubuntu-latest`
* **Passos**:
  1. Checkout do repositório (`actions/checkout@v4`).
  2. Verificação de presença e integridade de arquivos de anatomia da raiz (`LICENSE`, `VERSION`, `CHANGELOG.md`, `.editorconfig`, `GOSTVER`).
  3. Validação de cabeçalhos em `site/_headers` e artefatos de documentação estática.

---

## 4. Critérios de Aceite e Bloqueio
* **Zero Falhas**: Qualquer falha em `composer audit`, `phpstan`, `pest` ou auditoria GOST bloqueia o merge no branch principal (`main`).
* **Idempotência**: O workflow deve rodar de maneira determinística e hermética sem depender de serviços externos dinâmicos.
