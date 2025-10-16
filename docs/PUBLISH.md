# Guia de Publicação no Packagist

Este guia explica como publicar o pacote Open Location Code PHP no Packagist para instalação via Composer.

## Pré-requisitos

1. Conta no [GitHub](https://github.com)
2. Conta no [Packagist](https://packagist.org)
3. O código deve estar em um repositório Git

## Passos para Publicação

### 1. Preparar o Repositório

Certifique-se de que:
- ✅ O `composer.json` está configurado corretamente
- ✅ Todos os testes estão passando
- ✅ O código segue os padrões PSR-12
- ✅ A documentação está completa
- ✅ O CHANGELOG.md está atualizado

### 2. Criar uma Tag de Versão

```bash
# Certifique-se de estar no diretório php
cd php

# Crie uma tag seguindo o Versionamento Semântico
git tag -a v1.0.0 -m "Release version 1.0.0"

# Envie a tag para o GitHub
git push origin v1.0.0
```

### 3. Publicar no Packagist

1. Acesse [Packagist.org](https://packagist.org)
2. Faça login com sua conta GitHub
3. Clique em "Submit" no menu superior
4. Cole a URL do repositório GitHub
5. Clique em "Check"

Exemplo de URL:
```
https://github.com/google/open-location-code
```

**Nota**: Se o pacote estiver em uma subpasta (como `php/`), você pode precisar ajustar o `composer.json` ou criar um repositório separado.

### 4. Configurar Auto-Update

Para que o Packagist atualize automaticamente quando você fizer novos releases:

1. No Packagist, vá para a página do seu pacote
2. Clique em "Settings"
3. Configure o webhook do GitHub (o Packagist fornece a URL)

Ou configure manualmente no GitHub:
1. Vá para Settings → Webhooks no seu repositório
2. Adicione o webhook do Packagist
3. URL: `https://packagist.org/api/github?username=SeuUsuario`
4. Content type: `application/json`
5. Selecione "Just the push event"

### 5. Verificar a Instalação

Após publicado, qualquer pessoa pode instalar com:

```bash
composer require google/openlocationcode
```

## Versionamento

Seguimos o [Versionamento Semântico](https://semver.org/lang/pt-BR/) (SemVer):

- **MAJOR** (1.x.x): Mudanças incompatíveis na API
- **MINOR** (x.1.x): Novas funcionalidades compatíveis
- **PATCH** (x.x.1): Correções de bugs compatíveis

Exemplos:
```bash
git tag v1.0.0  # Release inicial
git tag v1.0.1  # Correção de bug
git tag v1.1.0  # Nova funcionalidade
git tag v2.0.0  # Mudança incompatível
```

## Criar um Release no GitHub

Além das tags, é recomendado criar releases formais:

1. Vá para a página do repositório no GitHub
2. Clique em "Releases"
3. Clique em "Create a new release"
4. Selecione a tag
5. Adicione um título e descrição
6. Publique o release

## Badges para o README

Adicione badges ao seu README.md:

```markdown
[![Latest Stable Version](https://poser.pugx.org/google/openlocationcode/v/stable)](https://packagist.org/packages/google/openlocationcode)
[![Total Downloads](https://poser.pugx.org/google/openlocationcode/downloads)](https://packagist.org/packages/google/openlocationcode)
[![License](https://poser.pugx.org/google/openlocationcode/license)](https://packagist.org/packages/google/openlocationcode)
```

## Checklist de Publicação

- [ ] Todos os testes passando (`composer test`)
- [ ] PHPStan sem erros (`composer phpstan`)
- [ ] Código formatado (`composer cs-fix`)
- [ ] CHANGELOG.md atualizado
- [ ] README.md completo
- [ ] Tag de versão criada
- [ ] Release publicado no GitHub
- [ ] Pacote submetido ao Packagist
- [ ] Webhook configurado (opcional)
- [ ] Instalação testada em um projeto limpo

## Atualizações Futuras

Para publicar uma nova versão:

1. Faça as alterações necessárias
2. Atualize o CHANGELOG.md
3. Rode os testes
4. Crie uma nova tag
5. Faça push da tag
6. O Packagist atualizará automaticamente (se webhook configurado)

## Repositório Separado (Alternativa)

Se preferir ter o pacote PHP em um repositório separado:

1. Crie um novo repositório: `google/open-location-code-php`
2. Copie os arquivos da pasta `php/`
3. Ajuste os caminhos no `composer.json`
4. Publique normalmente no Packagist

Esta abordagem facilita:
- Versionamento independente
- Issues específicas do PHP
- CI/CD dedicado
- Releases separados

## Suporte

Para questões sobre publicação:
- [Documentação do Packagist](https://packagist.org/about)
- [Documentação do Composer](https://getcomposer.org/doc/)
- [Issues do Open Location Code](https://github.com/google/open-location-code/issues)

