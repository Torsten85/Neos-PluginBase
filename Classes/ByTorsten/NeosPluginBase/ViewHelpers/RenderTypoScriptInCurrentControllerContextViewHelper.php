<?php
namespace ByTorsten\NeosPluginBase\ViewHelpers;

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Neos\Domain\Service\TypoScriptService;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;

class RenderTypoScriptInCurrentControllerContextViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @Flow\Inject
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Security\Context
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
            throw new \InvalidArgumentException('When calling the TypoScript render view helper only relative paths are allowed.', 1368740480);
        }
        if (preg_match('/^[a-z0-9.]+$/i', $path) !== 1) {
            throw new \InvalidArgumentException('Invalid path given to the TypoScript render view helper ', 1368740484);
        }

        /** @var $typoScriptObject AbstractTypoScriptObject */
        $typoScriptObject = $this->viewHelperVariableContainer->getView()->getTypoScriptObject();
        $currentPath = $typoScriptObject->getPath();

        /** @var NodeInterface $currentNode */
        $currentNode = $this->templateVariableContainer->get('node');

        $slashSeparatedPath = str_replace('.', '/', $path);
        $currentSiteNode = $currentNode->getContext()->getCurrentSiteNode();
        $typoScriptRuntime = $this->typoScriptService->createRuntime($currentSiteNode, $this->controllerContext);

        $contextVariables = [
            'node' => $currentNode,
            'documentNode' => $this->getClosestDocumentNode($currentNode),
            'site' => $currentSiteNode,
            'account' => $this->securityContext->canBeInitialized() ? $this->securityContext->getAccount() : NULL,
            'editPreviewMode' => $this->templateVariableContainer->exists('editPreviewMode') ? $this->templateVariableContainer->get('editPreviewMode') : NULL
        ];

        $contextVariables = array_merge($contextVariables, $context);
        $typoScriptRuntime->pushContextArray($contextVariables);

        $absolutePath = $currentPath . '/' . $slashSeparatedPath;
        $output = $typoScriptRuntime->render($absolutePath);

        $typoScriptRuntime->popContext();

        return $output;
    }

    /**
     * @param NodeInterface $node
     * @return NodeInterface
     */
    protected function getClosestDocumentNode(NodeInterface $node) {
        while ($node !== NULL && !$node->getNodeType()->isOfType('TYPO3.Neos:Document')) {
            $node = $node->getParent();
        }
        return $node;
    }
}
