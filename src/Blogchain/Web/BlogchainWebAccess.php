<?php


namespace Blogchain\Web;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Blogchain\Core\Application\Blogchain;
use Blogchain\Core\Application\BlogchainConfiguration;
use Blogchain\Core\Resolver\PostResolver;
use Blogchain\Core\Plugin\Plugin;
use Blogchain\Router\Router;
use Blogchain\Web\Controller\WebController;
use Blogchain\Web\OutputRenderer\OutputRenderer;
use Blogchain\Web\OutputRenderer\TwigOutputRenderer;

class BlogchainWebAccess implements Plugin
{
    public function register()
    {
        // Default dependencies
        Blogchain::instance()->share(OutputRenderer::class, function () {
            return new TwigOutputRenderer([
                'templatesPath' => Blogchain::instance()->getConfig()->templatesPath
            ]);
        });

        /** @var Router $router */
        $router = Blogchain::instance()->get(Router::class);

        /** @var WebController $controller */
        $controller = new WebController();

        $router->get('/', function (RequestInterface $request, ResponseInterface $response) use ($controller) {
            return $response->getBody()->write(
                $controller->index()
            );
        });

        $router->get('/search/{query}', function (RequestInterface $request, ResponseInterface $response, array $args) use ($controller) {
            return $response->getBody()->write(
                $controller->index(0, ['query' => $args['query']])
            );
        });

        $router->get('/search/{query}/{page}', function (RequestInterface $request, ResponseInterface $response, array $args) use ($controller) {
            return $response->getBody()->write(
                $controller->index($args['page'], ['query' => $args['query']])
            );
        });

        $router->get('/feed', function (RequestInterface $request, ResponseInterface $response) use ($controller) {

            Blogchain::instance()->getConfig()->context = BlogchainConfiguration::CONTEXT_FEED;

            $response
                ->getBody()->write(
                    $controller->feed()
                );

            return $response->withHeader('Content-type', 'application/xml');
        });

        $router->get('/page/{page}', function (RequestInterface $request, ResponseInterface $response, array $args) use ($controller) {
            return $response->getBody()->write(
                $controller->index($args['page'])
            );
        });

        $router->get('/{year}/{slug}', function (RequestInterface $request, ResponseInterface $response, array $args) use ($controller) {
            /** @var PostResolver $resolver */
            $resolver = Blogchain::instance()->get(PostResolver::class);
            $postId = $resolver->resolveSlug($args['year'] . '/' . $args['slug']);

            return $response->getBody()->write(
                $controller->single($postId)
            );
        });


        $router->get('/{slug}', function (RequestInterface $request, ResponseInterface $response, array $args) use ($controller) {
            $resolver = Blogchain::instance()->get(PostResolver::class);
            $postId = $resolver->resolveSlug($args['slug']);

            return $response->getBody()->write(
                $controller->single(
                    $postId
                ));
        });


        $router->setErrorHandler(function ($request, $response, $ex) use ($controller) {
            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'text/html')
                ->write(
                    $controller->error()
                );
        });
    }


}