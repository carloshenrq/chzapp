# CHZApp
É um pequeno framework de uso pessoal que estou disponibilizando aqui no github. Facilitou alguns serviços meus, claro que de outras formas, mas esta versão deve ajudar alguém que possa estar precisando também.

### Conjunto de Bibliotecas

É compatível apartir da versão **PHP 5.6.4** e testado até a versão **PHP 7.1.5** (2017-05)

* Slim Framework (v3.8.1)
* Client IP address middleware (v0.5)
* Smarty 3 template engine (v3.1.31)
* Guzzle, PHP HTTP client (v6.2.3)
* Illuminate Database (v5.4.19)
* Swift Mailer (v5.4.8)
* scssphp (v0.6.7)
* Minify (v1.3.44)
* coffeescript (v1.3.4)

*Todas as bibliotecas acima, podem ser encontradas dentro do arquivo 'composer.json'*

## Instalação

Para fazer a instalação basta rodar no composer o comando:

    composer require carloshlfz/framework --prefer-dist dev-master

Ou se você preferir, adicione a suas depêndencias...

    {
        "require" : {
            "carloshlfz/framework":"dev-master"
        }
    }

## Como usar

Para utilizar, é bem tranquilo, primeiramente, tenha em mente em ter seu arquivo ***.htaccess*** já configurado e pronto para usar.

Eu gosto de usar este aqui como base:

    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [QSA,L]

    <FilesMatch "\.(xml|xsd|db|tpl|json|lock|zip|md|sql|log|cache|index|dist|txt|test|scss|js|png|gif|jpg|jpeg|gitignore|yaml|bat)$">
        Order allow,deny
        Deny from all
        Satisfy All
    </FilesMatch>

    Options -Indexes

### Iniciando a Aplicação

Tendo o framework instalado e tudo em mãos... você deverá criar a classe de execução da aplicação.

    <?php
    // Carrega o framework e suas dependencias utilizando o composer.
    require_once 'vendor/autoload.php';

    // Cria a classe com herança em application
    class MyApp extends CHZApp\Application
    {
        public function __construct()
        {
            parent::__construct(true, // true: Modo desenvolvedor ligado
                [], // Configurações de sessão.
                [], // Configurações do smarty.
                [], // Configurações do swift-mailer
                []  // Configurações de conexão para o eloquent (essas, são de forma literal,
                                                                 todas as configurações do proprio eloquent)
            );
        }
    }

    // Cria a instância de MyApp
    $obj = new MyApp;
    // Inicia a execução da aplicação.
    $obj->run();

### Rotas e Controllers

Para criar as rotas você deve ter em mente que quando sua aplicação faz a chamada da rota da seguinte forma:

    /home/index

Ele irá chamar a classe ***Controller\Home*** e a ação ***index***, o tipo de requisição é definido ao final do nome da ação como sulfixo, pode ser: ***_GET, _POST, _PUT, etc...***

Então, se você fizer a chamada acima, com ***_GET*** a chamada será ***Controller\Home->index_GET()***

    <?php

    namespace Controller;

    class Home extends \CHZApp\Controller
    {
        public function index_GET(\Psr\Http\Message\ResponseInterface $response, $args)
        {

            // Processamento de rota

            return $response;
        }
    }

Para criar rotas como:

    /home/teste/huehue/br

Adicione separadores ***_*** entre os actions...

    public function teste_huehue_br_GET();

***Sempre, por padrão, quando for chamado apenas o controller pela requisição, a rota chamada será index_GET()***

Explicando a afirmação acima...

    /home/

Irá realizar a chamada de ***\Controller\Home->index_GET***

    /perfil/

Irá realizar a chamada de ***\Controller\Perfil->index_GET***
