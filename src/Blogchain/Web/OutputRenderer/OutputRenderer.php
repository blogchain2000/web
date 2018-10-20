<?php


namespace Blogchain\Web\OutputRenderer;

use Blogchain\Core\Model\Post;

interface OutputRenderer
{
    public function getRenderer();

    public function renderList(array $posts, array $args): string;

    public function renderSingle(Post $post, array $args): string;

    public function renderFeed(array $posts, array $args): string;

}
