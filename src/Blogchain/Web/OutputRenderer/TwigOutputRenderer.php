<?php

namespace Blogchain\Web\OutputRenderer;


use Blogchain\Core\Application\Blogchain;
use Blogchain\Core\Model\Post;
use Blogchain\Core\Shortcode\VideoShortcode;
use Blogchain\Core\Utils\ParsedownEx;

class TwigOutputRenderer implements OutputRenderer
{
    private $twig;


    public function __construct($options)
    {
        $this->twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem($options['templatesPath']),
            ['cache' => false]
        );

        $this->twig->addFilter(new \Twig_Filter('bust', function ($arg) {
            $md5 = md5_file(Blogchain::instance()->getConfig()->publicPath . $arg);
            return $arg . '?c=' . $md5;
        }));

        $this->twig->addFilter(new \Twig_Filter('markdown', function ($arg) {
            return ParsedownEx::instance()->line($arg);
        }));

        $this->twig->addFilter(new \Twig_Filter('rssDate', function ($arg) {
            return date('r', $arg);
        }));

        $this->twig->addFilter(new \Twig_Filter('htmlDate', function ($arg) {
            return strftime('%d. %B %Y', $arg);
        }));

        $this->twig->addFilter(new \Twig_Filter('urlGetHost', function ($arg) {
            $url = parse_url($arg);
            return str_replace('www.', '', $url['host']);
        }));

        $this->twig->addFunction(new \Twig_Function('video', function ($post, $args) {
            $v = new VideoShortcode();
            return $v->transformCode($post, $args);
        }));

    }


    public function getRenderer()
    {
        return $this->twig;
    }


    public function renderList(array $posts, array $args): string
    {
        return $this->twig
            ->load('index.twig')
            ->render(array_merge(
                    $args,
                    [
                        'isSingle' => false,
                        'posts' => $posts
                    ]
                )
            );
    }

    public function renderSingle(Post $post, array $args): string
    {
        return $this->twig
            ->load('index.twig')
            ->render([
                'isSingle' => true,
                'posts' => [$post]
            ]);
    }

    public function renderFeed(array $posts, array $args): string
    {
        return $this->twig
            ->load('rss.twig')
            ->render([
                'currentDate' => time(),
                'posts' => $posts
            ]);
    }

}