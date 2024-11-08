<?php declare(strict_types=1);

namespace App;
use Core\Http\Router as BaseRouter,
    Core\Http\ServerRequest;

class Router extends BaseRouter
{

    /**
     * @inheritDoc
     */
    public function handle(ServerRequest $request): ?array
    {
        $target = $request->getTarget() ?: '/';
        $path = explode('/', $target);
        if (isset($target[1]) && in_array($target[1], App::$languages)) {
            unset($path[1]);
            $target = implode('/', $path);
        }

        return $this->find($target);
    }

}
