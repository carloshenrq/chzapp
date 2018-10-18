# CHZApp [![GitHub license](https://img.shields.io/github/license/carloshenrq/chzapp.svg)](https://github.com/carloshenrq/chzapp/blob/master/LICENSE)

[![Build Status](https://travis-ci.com/carloshenrq/chzapp.svg?branch=master)](https://travis-ci.com/carloshenrq/chzapp) [![Build status](https://ci.appveyor.com/api/projects/status/mf73tqydrwalg8gw/branch/master?svg=true)](https://ci.appveyor.com/project/carloshenrq/chzapp/branch/master) [![codecov](https://codecov.io/gh/carloshenrq/chzapp/branch/master/graph/badge.svg)](https://codecov.io/gh/carloshenrq/chzapp) [![Packagist](https://img.shields.io/packagist/v/carloshlfz/framework.svg)](https://packagist.org/packages/carloshlfz/framework) [![GitHub release](https://img.shields.io/github/release/carloshenrq/chzapp.svg)](https://github.com/carloshenrq/chzapp/releases) [![GitHub issues](https://img.shields.io/github/issues/carloshenrq/chzapp.svg)](https://github.com/carloshenrq/chzapp/issues)
[![GitHub pull requests](https://img.shields.io/github/issues-pr/carloshenrq/chzapp.svg)](https://github.com/carloshenrq/chzapp/pulls)

É um pequeno framework de uso pessoal que estou disponibilizando aqui no github. Facilitou alguns serviços meus, claro que de outras formas, mas esta versão deve ajudar alguém que possa estar precisando também.

### Informações de versão

Este framework é compatível com as versões de PHP 5.6, 7.0, 7.1 e 7.2

### Dependências

* slim/slim: 3.9.1
* akrabat/ip-address-middleware: 0.6
* smarty/smarty: 3.1.33
* guzzlehttp/guzzle: 6.3.3
* illuminate/database: 5.4.36
* illuminate/events: 5.4.36
* swiftmailer/swiftmailer: 5.4.12
* leafo/scssphp: 0.7.5
* matthiasmullie/minify: 1.3.60
* kylekatarnls/coffeescript: 1.3.4
* dompdf/dompdf: 0.8.2
* clickalicious/memcached.php: 1.0.1

## Instalação

Para fazer a instalação basta rodar no composer o comando:

    composer require carloshlfz/framework

## Como Usar

Basicamente, você irá preciar iniciar a aplicação e para iniciar, você pode usar o seguinte código:

    <?php

    require_once 'vendor/autoload.php';

    class App extends CHZApp\Application
    {
        /**
         * @see CHZApp\Application::init()
         */
        public function init()
        {

        }
    }

    $app = new App;
    $app->run();

Isso deixará sua aplicação em pleno funcionamento e permitirá fazer as chamadas dos controllers de forma automática.

Os controllers devem estar no namespace `Controller`
Exemplo:

    <?php

    namespace Controller;

    class Home extends \CHZApp\Controller
    {
        public function index_GET($response, $args)
        {
            return $response->write('hello world');
        }
    }

Por padrão, as rotas são definidas pelo action da sequinte forma:

    http://127.0.0.1/
    http://127.0.0.1/home
    http://127.0.0.1/home/index

Será usado o `Controller\Home` na rota `index_GET`

Para colocar novos controllers, basta herdar `CHZApp\Controller` e colocar o nome de seu controller corretamente e realizar as chamadas.

    http://127.0.0.1/test/hello

Será usado o `Controller\Test` na rota `hello_GET`

    http://127.0.0.1/test/hello/new/user

Será usado o `Controller\Test` na rota `hello_new_user_GET`

O Mesmo vale para rotas do tipo post, o final será trocado de GET para POST.

As variáveis `$_POST, $_GET e $_REQUEST` podem ser acessadas dentro do controller da seguinte forma:

    <?php

    namespace Controller;

    class Home extends \CHZApp\Controller
    {
        public function index_GET($response, $args)
        {
            $name = $this->get['name'];

            return $response->write('hello world');
        }

        public function index_POST($response, $args)
        {
            $name = $this->post['name'];

            return $response->write('hello world');
        }
    }

Para que as rotas funcionem de acordo, é necessário que seu `.htaccess` também esteja de acordo. Recomendo usar:

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

Este `.htaccess` funciona muito bem com o framework.