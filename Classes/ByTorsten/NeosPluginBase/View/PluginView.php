<?php
namespace ByTorsten\NeosPluginBase\View;

use ByTorsten\NeosPluginBase\Fusion\PluginImplementation;
use Neos\FluidAdaptor\View\TemplateView;

class PluginView extends TemplateView {

    /**
     * @var PluginImplementation
     */
    protected $fusionObject;

    /**
     * @return PluginImplementation
     */
    public function getFusionObject()
    {
        return $this->fusionObject;
    }

    /**
     * @param PluginImplementation $fusionObject
     * @return void
     */
    public function setFusionObject($fusionObject)
    {
        $this->fusionObject = $fusionObject;
    }
}