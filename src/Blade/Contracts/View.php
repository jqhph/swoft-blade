<?php

namespace Swoft\Blade\Contracts;

use Psr\Http\Message\ResponseInterface;
use Swoft\Support\Contracts\Renderable;

interface View extends Renderable
{
    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function name();

    /**
     * Add a piece of data to the view.
     *
     * @param  string|array  $key
     * @param  mixed   $value
     * @return $this
     */
    public function with($key, $value = null);

    /**
     * @return ResponseInterface
     */
    public function toResponse(): ResponseInterface;
}
