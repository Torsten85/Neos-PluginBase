<?php
namespace ByTorsten\NeosPluginBase\Fusion;

use Neos\Flow\Mvc\ActionRequest;
use Neos\ContentRepository\Domain\Model\NodeInterface;

class PluginImplementation extends \Neos\Neos\Fusion\PluginImplementation
{

    /**
     * Build the pluginRequest object
     *
     * @return ActionRequest
     */
    protected function buildPluginRequest()
    {
        /** @var $parentRequest ActionRequest */
        $parentRequest = $this->runtime->getControllerContext()->getRequest();
        $pluginRequest = new ActionRequest($parentRequest);
        $pluginRequest->setArgumentNamespace('--' . $this->getPluginNamespace());
        $this->passArgumentsToPluginRequest($pluginRequest);

        if ($this->node instanceof NodeInterface) {
            $pluginRequest->setArgument('__node', $this->node);
            if ($pluginRequest->getControllerPackageKey() === null) {
                $pluginRequest->setControllerPackageKey($this->node->getProperty('package') ?: $this->getPackage());
            }
            if ($pluginRequest->getControllerSubpackageKey() === null) {
                $pluginRequest->setControllerSubpackageKey($this->node->getProperty('subpackage') ?: $this->getSubpackage());
            }
            if ($pluginRequest->getControllerName() === null) {
                $pluginRequest->setControllerName($this->node->getProperty('controller') ?: $this->getController());
            }
            if ($pluginRequest->getControllerActionName() === null) {
                $actionName = $this->node->getProperty('action');
                if ($actionName === null || $actionName === '') {
                    $actionName = $this->getAction() !== null ? $this->getAction() : 'index';
                }
                $pluginRequest->setControllerActionName($actionName);
            }

            $pluginRequest->setArgument('__node', $this->node);
            $pluginRequest->setArgument('__documentNode', $this->documentNode);
        } else {
            $pluginRequest->setControllerPackageKey($this->getPackage());
            $pluginRequest->setControllerSubpackageKey($this->getSubpackage());
            $pluginRequest->setControllerName($this->getController());
            $pluginRequest->setControllerActionName($this->getAction());
        }

        $pluginRequest->setArgument('__fusionObject', $this);
        return $pluginRequest;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}