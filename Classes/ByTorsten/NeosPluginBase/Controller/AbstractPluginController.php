<?php
namespace ByTorsten\NeosPluginBase\Controller;

use ByTorsten\NeosPluginBase\TypoScriptObjects\PluginImplementation;
use ByTorsten\NeosPluginBase\View\PluginView;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Mvc\RequestInterface;
use TYPO3\Flow\Mvc\ResponseInterface;
use TYPO3\Flow\Mvc\View\ViewInterface;
use TYPO3\Neos\Service\LinkingService;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

use TYPO3\Flow\Annotations as Flow;

abstract class AbstractPluginController extends ActionController
{
    /**
     * @Flow\Inject
     * @var LinkingService
     */
    protected $linkingService;

    /**
     * @var NodeInterface
     */
    protected $node;

    /**
     * @var string
     */
    protected $defaultViewObjectName = PluginView::class;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    protected function initializeController(RequestInterface $request, ResponseInterface $response)
    {
        parent::initializeController($request, $response);
        $this->node = $this->request->getInternalArgument('__node');
    }

    /**
     * @param $path
     * @param array $contextVariables
     * @return mixed
     */
    protected function tsValue($path, array $contextVariables = NULL)
    {
        /** @var PluginImplementation $tsObject */
        $tsObject = $this->request->getInternalArgument('__typoScriptObject');
        $fullPath = $tsObject->getPath() . '/' . $path;
        $runtime = $tsObject->getTsRuntime();

        if ($contextVariables) {
            $contextVariables = array_merge($contextVariables,[
                'node' => $this->node
            ]);
            $runtime->pushContextArray($contextVariables);
        }

        $output = $tsObject->getTsRuntime()->evaluate($fullPath, $tsObject);

        if ($contextVariables) {
            $runtime->popContext();
        }

        return $output;
    }

    /**
     * @param ViewInterface $view
     */
    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);

        $view->assign('node', $this->node);

        /** @var PluginImplementation $tsObject */
        $tsObject = $this->request->getInternalArgument('__typoScriptObject');

        if ($view instanceof PluginView) {
            $view->setTypoScriptObject($tsObject);
        }
    }

    /**
     * @param NodeInterface $node
     * @return void
     */
    protected function redirectToNode(NodeInterface $node)
    {
        $uri = $this->linkingService->createNodeUri(
            $this->controllerContext,
            $node,
            null,
            null,
            false
        );

        $this->redirectToUri($uri);
    }
}
