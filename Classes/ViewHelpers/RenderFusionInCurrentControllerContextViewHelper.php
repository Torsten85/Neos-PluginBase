<?php
namespace ByTorsten\NeosPluginBase\ViewHelpers;

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\Neos\Domain\Service\FusionService;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Flow\Annotations as Flow;

class RenderFusionInCurrentControllerContextViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @Flow\Inject
     * @var FusionService
     */
    protected $fusionService;

    /**
     * @Flow\Inject
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @param string $path
     * @param array $context
     * @return string
     * @throws \InvalidArgumentException
     */
    public function render($path, array $context = array())
    {
        if (strpos($path, '/') === 0 || strpos($path, '.') === 0) {
            throw new \InvalidArgumentException('When calling the Fusion render view helper only relative paths are allowed.', 1368740480);
        }
        if (preg_match('/^[a-z0-9.]+$/i', $path) !== 1) {
            throw new \InvalidArgumentException('Invalid path given to the Fusion render view helper ', 1368740484);
        }

        /** @var $fusionObject AbstractFusionObject */
        $fusionObject = $this->viewHelperVariableContainer->getView()->getFusionObject();
        $currentPath = $fusionObject->getPath();

        /** @var NodeInterface $currentNode */
        $currentNode = $this->templateVariableContainer->get('node');

        $slashSeparatedPath = str_replace('.', '/', $path);
        $currentSiteNode = $currentNode->getContext()->getCurrentSiteNode();
        $fusionRuntime = $this->fusionService->createRuntime($currentSiteNode, $this->controllerContext);

        $contextVariables = [
            'node' => $currentNode,
            'documentNode' => $this->getClosestDocumentNode($currentNode),
            'site' => $currentSiteNode,
            'account' => $this->securityContext->canBeInitialized() ? $this->securityContext->getAccount() : NULL,
            'editPreviewMode' => $this->templateVariableContainer->exists('editPreviewMode') ? $this->templateVariableContainer->get('editPreviewMode') : NULL
        ];

        $contextVariables = array_merge($contextVariables, $context);
        $fusionRuntime->pushContextArray($contextVariables);

        $absolutePath = $currentPath . '/' . $slashSeparatedPath;
        $output = $fusionRuntime->render($absolutePath);

        $fusionRuntime->popContext();

        return $output;
    }

    /**
     * @param NodeInterface $node
     * @return NodeInterface
     */
    protected function getClosestDocumentNode(NodeInterface $node) {
        while ($node !== NULL && !$node->getNodeType()->isOfType('Neos.Neos:Document')) {
            $node = $node->getParent();
        }
        return $node;
    }
}
