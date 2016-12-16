<?php
namespace ByTorsten\NeosPluginBase\View;

use ByTorsten\NeosPluginBase\TypoScriptObjects\PluginImplementation;
use TYPO3\Fluid\View\TemplateView;

class PluginView extends TemplateView {

    /**
     * @var PluginImplementation
     */
    protected $typoScriptObject;

    /**
     * @return PluginImplementation
     */
    public function getTypoScriptObject()
    {
        return $this->typoScriptObject;
    }

    /**
     * @param PluginImplementation $typoScriptObject
     * @return void
     */
    public function setTypoScriptObject($typoScriptObject)
    {
        $this->typoScriptObject = $typoScriptObject;
    }
}