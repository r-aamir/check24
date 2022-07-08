<?php

namespace App\Factory;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ViewRendererFactory
{
    public function __construct(protected Environment $viewRenderer)
    {
    }

    /**
     * Render the given template, and wrap into Response.
     *
     * @param string $template The view template
     * @param array  $args     Template render data
     * @param int    $status   The response status
     */
    public function render(string $template, array $args = [], int $status = 200) : Response
    {
        /**
         * The raw response.
         */
        $response = $this->viewRenderer->render($template, $args);

        return new Response($response, $status);
    }
}
