## Template 1.1.0

Classe para renderização de templates, views e elements (pequenas partes de templates reaproveitáveis)


## Recursos
  - render($view) - Renderiza uma view, deve-se informar o nome da view sem a extenção .php
  - includeElement($fileName, array $parameters) - Inclui um elemento, deve-se informar nome do elemento sem a extensão .php e um array com lista de parametros
  - getContent() - Utilizado dentro do arquivo de template, este método é reponsável por renderizar a view
  - setParameters(array $parameters) - Seta uma lista de parametros para ser usado na view ou template, deve-se informar um array onde o indice será uma variavel na view
  - setTemplate($template) - Seta o nome do template (template padrão chama-se default), deve-se informar o nome do template sem a extensão .php


## Utilização via composer

```sh
    "require": {
        ...
        "tayron/template" : "1.0.0"
        ... 
    },    
```
