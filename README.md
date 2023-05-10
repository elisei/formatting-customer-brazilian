# Formatação de Clientes Legados

Módulo para formatar dados de clientes já existes para o padrão:

- Copia o valor taxvat para o VatID.
- FAX é verificado para identificar se tem 11 dígitos, caso exista é atribuido para o campo PHONE se esse não tiver também 11 dígitos.
- Validação de atribuição para endereço padrão.

## Advertência de uso

É fundamental que realize primeiro em seu ambiente de desenvolvimento (realizando todos os testes). Após isso faça um backup e só então aplique em produção.

## Isenção de responsabilidade

Esse é um módulo gratuito e sem suporte avançado, use por sua conta e risco.

Se encontrar um problema sugiro abrir um Issue e na medida do possível iremos lhe atender.

## Instalação e Uso

Via Composer

```bash
composer require o2ti/formatting-legacy-brazilian
```

Uso
```bash
bin/magento o2ti:customer:brazilian_formatting
```
