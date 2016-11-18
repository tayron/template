<?php
namespace Tayron;

use Tayron\Request;
use Tayron\exceptions\ParameterNotFoundException;
use Tayron\exceptions\ElementNotFoundException;
use Tayron\exceptions\ViewNotFoundException;
use Tayron\exceptions\TemplateNotFoundException;
use Tayron\exceptions\FileNotFoundException;
use Tayron\exceptions\Exception;

/**
 * Classe que gerancia o carregamento de templates e views
 *
 * @author Tayron Miranda <dev@tayron.com.br>
 */
final class Template implements TemplateInterface
{
    /**
     * Armazena o caminho absoluto de onde fica os templates,
     * exemplo: /var/www/html/projeto/src/view/template
     *
     * @var string
     */
    private $pathTemplate = null;

    /**
     * Armazena o caminho absoluto de onde fica as views,
     * exemplo: /var/www/html/projeto/src/view
     *
     * @var string
     */
    private $pathView = null;

    /**
     * Armazena o caminho absoluto de onde fica os elementos de views,
     * exemplo: /var/www/html/projeto/src/view/elements
     *
     * @var string
     */
    private $pathElements = null;

    /**
     * Armazena o nome do template a ser usado no sitema
     *
     * @var string
     */
    private $template = 'default';

    /**
     * Armazena o nome do controller a ser invocado
     *
     * @var string
     */
    private $controller = null;

    /**
     * Armazena o nome do método a ser invocado no controller
     *
     * @var string
     */
    private $view = 'index';

    /**
     * Armazena os parametros a serem passados para a view
     *
     * @var array
     */
    private $parameters = null;

    /**
     * Armazena objeto que trata requisições
     *
     * @var Request
     */
    private $request = null;

    /**
     * @var Singleton reference to singleton instance
     */
    private static $instance;

    /**
     * Template::__construct
     *
     * Impede com que o objeto seja instanciado
     */
    final private function __construct()
    {
    }

    /**
     * Template::__clone
     *
     * Impede que a classe Requisição seja clonada
     *
     * @throws Exception Dispara execção caso o usuário tente clonar este classe
     *
     * @return void
     */
    final public function __clone()
    {
        throw new Exception('A classe Requisicao não pode ser clonada.');
    }

    /**
     * Template::__wakeup
     *
     * Impede que a classe Requisição execute __wakeup
     *
     * @throws Exception Dispara execção caso o usuário tente executar este método
     *
     * @return void
     */
    final public function __wakeup()
    {
        throw new Exception('A classe Requisicao não pode executar __wakeup.');
    }

    /**
     * Template::getInstance
     *
     * Retorna uma instância única de uma classe.
     *
     * @param string $controllerName Nome do controller que está sendo invocado
     * @param Request $request Objeto que trata requisição
     * @param string $pathView Caminho absoluto de onde fica as views
     * @param string $pathTemplate Caminho absoluto de onde fica os templates
     * @param string $pathElements Caminho absoluto de onde fica os elementos de views
     *
     * @return Template Retorna instancia de Template
     */
    public static function getInstance($controllerName = null, Request $request = null, $pathView, $pathTemplate, $pathElements)
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        self::$instance->controller = $controllerName;
        self::$instance->request = $request;
        self::$instance->setPathView($pathView);
        self::$instance->setPathTemplate($pathTemplate);
        self::$instance->setPathElements($pathElements);

        return self::$instance;
    }

    /**
     * Template::render
     *
     * Método que seta a view e renderia no template
     *
     * @param string $view Nome da view
     * 
     * @throws ParameterNotFoundException Dispara exceção caso não tenha sido informado o diretório onde fica o template
     * @throws TemplateNotFoundException Dispara exceção caso o template informado não seja encontrado
     * 
     * @return void
     */
    public function render($view)
    {
        if ($this->pathTemplate == null) {
            throw new ParameterNotFoundException('pathTemplate', 'Caminho para o diretório template não informado, %s está nulo');
        }

        $this->view = strtolower($view);
        $pathTemplate = $this->pathTemplate . DS . $this->template . '.php';

        try {
            $this->includeFile($pathTemplate);
        } catch (FileNotFoundException $e) {
            throw new TemplateNotFoundException($pathTemplate);
        }
    }

    /**
     * Template::includeElement
     *
     * Faz inclusão de arquivos contidos em src/view/elementos
     *
     * @param string $fileName Nome do elemento a ser incluido
     *
     * @throws ParameterNotFoundException Dispara exceção quando nome do arquivo não for informado
     * @throws ElementNotFoundException Dispara exceção quando arquivo não for encontrado
     *
     * @return void
     */
    public function includeElement($fileName, array $parameters = null)
    {
        if ($fileName == null) {
            throw new ParameterNotFoundException('elemento', 'O parametro %s informado não pode ser nulo');
        }

        if ($this->pathElements == null) {
            throw new ParameterNotFoundException('pathElements', 'Caminho para o diretório elements não informado, %s está nulo');
        }

        $pathElement = $this->pathElements . DS . $fileName . '.php';

        try {
            $this->includeFile($pathElement, $parameters);
        } catch (FileNotFoundException $e) {
            throw new ElementNotFoundException($pathElement);
        }
    }

    /**
     * Template::getContent
     *
     * Método que pega o conteúdo da view e exibe dentro de um template
     *
     * @throws ParameterNotFoundException Dispara exceção caso o diretório da view não tenha sido informado
     * @throws ViewNotFoundException Dispara exceção caso a view informada não seja encontrada
     * 
     * @return void
     */
    public function getContent()
    {
        if ($this->pathView == null) {
            throw new ParameterNotFoundException('pathView', 'Caminho para o diretório view não informado, %s está nulo');
        }

        $paramClasse = explode('\\', $this->controller);
        $dirController = strtolower(str_replace('Controller', null, end($paramClasse)));
        $pathView = $this->pathView . DS . $dirController . DS . $this->view . '.php';

        try {
            $this->includeFile($pathView);
        } catch (FileNotFoundException $e) {
            throw new ViewNotFoundException($this->view . '.php', "/view/$dirController/{$this->view}.php");
        }
    }

    /**
     * Template::includeFile
     *
     * Método que inclui arquivo
     *
     * @param string $pathFile Caminho para o arquivo a ser incluído
     * @param array $additionalParameters Lista de parametros adicionais a serem usadas na view
     *
     * @throws FileNotFoundException Dispara exceção caso o arquivo infomado não seja encontrado
     *
     * @return void
     */
    private function includeFile($pathFile, array $additionalParameters = null)
    {
        if (is_array($this->parameters)) {
            extract($this->parameters);
        }

        if (is_array($additionalParameters)) {
            extract($additionalParameters);
        }

        if (!file_exists($pathFile)) {
            throw new FileNotFoundException('Arquivo não encontrado em: ' . $pathFile);
        }

        require_once($pathFile);
    }

    /**
     * Template::setParameters
     *
     * Método que seta os parametros a ser utilizado nas views
     *
     * @param array $parameters Lista com os parametros
     * @return void
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Template::setPathTemplate
     *
     * Armazena o caminho absoluto de onde fica os templates,
     * exemplo: /var/www/html/projeto/src/view/template
     *
     * @throws ParameterNotFoundException Dispara exceção caso o caminho para o diretório de template seja informado vazio
     * 
     * @param string $pathTemplate Caminho absoluto do diretório template
     * @return void
     */
    private function setPathTemplate($pathTemplate)
    {
        if ($pathTemplate == null) {
            throw new ParameterNotFoundException('pathTemplate', 'O parametro %s informado não pode ser nulo');
        }

        $this->pathTemplate = $pathTemplate;
    }

    /**
     * Template::setPathTemplate
     *
     * Armazena o caminho absoluto de onde fica as views,
     * exemplo: /var/www/html/projeto/src/view
     *
     * @throws ParameterNotFoundException Dispara exceção caso o caminho para o diretório de view seja informado vazio
     * 
     * @param string $pathView Caminho absoluto do diretório view
     * @return void
     */
    private function setPathView($pathView)
    {
        if ($pathView == null) {
            throw new ParameterNotFoundException('pathView', 'O parametro %s informado não pode ser nulo');
        }

        $this->pathView = $pathView;
    }

    /**
     * Template::setPathElements
     *
     * Armazena o caminho absoluto de onde fica os elementos de views,
     * exemplo: /var/www/html/projeto/src/view/elements
     *
     * @throws ParameterNotFoundException Dispara exceção caso o caminho para o diretório de elements seja informado vazio
     * 
     * @param string $pathView Caminho absoluto do diretório elements
     * @return void
     */
    private function setPathElements($pathElements)
    {
        if ($pathElements == null) {
            throw new ParameterNotFoundException('pathElements', 'O parametro %s informado não pode ser nulo');
        }

        $this->pathElements = $pathElements;
    }

    /**
     * Template::setTemplate
     *
     * Método que informa qual template deverá ser usado
     *
     * @param string $template Nome do template
     * @return void
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}