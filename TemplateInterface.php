<?php

namespace Tayron;

/**
 * Classe determina os métodos que uma classe de template deve implementar
 *
 * @author Tayron Miranda <dev@tayron.com.br>
 */
interface TemplateInterface 
{    
    /**
     * TemplateInterface::render
     *
     * Método que seta a view e renderia no template
     *
     * @param string $view Nome da view
     * 
     * @return void
     */
    public function render($view);   
    
    /**
     * TemplateInterface::includeElement
     *
     * Faz inclusão de arquivos contidos em src/view/elementos
     *
     * @param string $fileName Nome do elemento a ser incluido
     *
     * @return void
     */
    public function includeElement($fileName, array $parameters = null);
    
    /**
     * TemplateInterface::getContent
     *
     * Método que pega o conteúdo da view e exibe dentro de um template
     * 
     * @return void
     */
    public function getContent();   
    
    /**
     * TemplateInterface::setParameters
     *
     * Método que seta os parametros a ser utilizado nas views
     *
     * @param array $parameters Lista com os parametros
     * @return void
     */
    public function setParameters(array $parameters);
    
    /**
     * TemplateInterface::setTemplate
     *
     * Método que informa qual template deverá ser usado
     *
     * @param string $template Nome do template
     * @return void
     */
    public function setTemplate($template);
}
