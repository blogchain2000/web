<?php

namespace Blogchain\Web\Controller;

use Blogchain\Core\Application\Blogchain;
use Blogchain\Core\Database\PostDatabase;
use Blogchain\Core\Exception\NotFoundException;
use Blogchain\Core\PostProcessor\PostProcessorChain;
use Blogchain\Core\Search\SearchProvider;
use Blogchain\Web\OutputRenderer\OutputRenderer;

class WebController
{

    public function index($page = 0, array $filter = []): string
    {
        $args = [
            'mode' => 'index',
            'numberOfPages' => 0,
            'currentPage' => $page
        ];

        if (isset($filter['query']) && $this->getBlogchain()->has(SearchProvider::class)) {
            $searchProvider = $this->getBlogchain()->get(SearchProvider::class);
            $result = $searchProvider->search($filter['query'], $page, $this->getBlogchain()->getConfig()->perPage);

            $posts = $result['results'];
            $args['query'] = $filter['query'];
            $args['mode'] = 'search';
            $args['numberOfPages'] = ceil($result['count'] / $this->getBlogchain()->getConfig()->perPage);

        } else {
            $filter = ['published' => true];
            $posts = $this->getDatabase()->listPosts($filter, $page, $this->getBlogchain()->getConfig()->perPage);
            $args['numberOfPages'] = ceil($this->getDatabase()->countPosts($filter) / $this->getBlogchain()->getConfig()->perPage);
        }


        $this->getProcessor()->processPosts($posts);

        return $this->getOutputRenderer()->renderList($posts, $args);
    }

    public function single($slug): string
    {
        $post = $this->getDatabase()->singlePost($slug);
        if (empty($post)) {
            throw new NotFoundException();
        }

        $this->getProcessor()->processPost($post);

        return $this->getOutputRenderer()->renderSingle($post, []);
    }

    public function feed(): string
    {
        $posts = $this->getDatabase()->listPosts(['published' => true], 0, 10);
        $this->getProcessor()->processPosts($posts);

        return $this->getOutputRenderer()->renderFeed($posts, []);
    }

    public function error(): string
    {
        return $this->getOutputRenderer()->renderList([], []);
    }

    private function getProcessor(): PostProcessorChain
    {
        return $this->getBlogchain()->get(PostProcessorChain::class);
    }

    private function getDatabase(): PostDatabase
    {
        return $this->getBlogchain()->get(PostDatabase::class);
    }

    private function getOutputRenderer(): OutputRenderer
    {
        return $this->getBlogchain()->get(OutputRenderer::class);
    }

    private function getBlogchain(): Blogchain
    {
        return $this->getBlogchain();
    }


}