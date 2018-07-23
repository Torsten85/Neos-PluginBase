<?php
namespace ByTorsten\NeosPluginBase\Controller;

use ByTorsten\NeosPluginBase\Fusion\PluginImplementation;
use ByTorsten\NeosPluginBase\View\PluginView;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\RequestInterface;
use Neos\Flow\Mvc\ResponseInterface;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Neos\Service\LinkingService;
use Neos\ContentRepository\Domain\Model\NodeInterface;

use Neos\Flow\Annotations as Flow;

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
    protected function tsValue($path, array $contextVariables = null)
    {
        /** @var PluginImplementation $fusionObject */
        $fusionObject = $this->request->getInternalArgument('__fusionObject');

        $fullPath = $fusionObject->getPath() . '/' . $path;
        $runtime = $fusionObject->getRuntime();

        if ($contextVariables) {
            $contextVariables = array_merge($contextVariables,[
                'node' => $this->node
            ]);
            $runtime->pushContextArray($contextVariables);
        }

        $output = $runtime->evaluate($fullPath, $fusionObject);

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

        /** @var PluginImplementation $fusionObject */
        $fusionObject = $this->request->getInternalArgument('__fusionObject');

        if ($view instanceof PluginView) {
            $view->setFusionObject($fusionObject);
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
