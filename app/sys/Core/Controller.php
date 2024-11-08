<?php declare(strict_types=1);

namespace Core;
use Core\Http\ServerRequest,
    ReflectionMethod,
    ReflectionParameter,
    Exception;

abstract class Controller
{

    /**
     * @var ServerRequest
     */
    protected ServerRequest $request;

    /**
     * @param ServerRequest $request
     */
    public function __construct(ServerRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param string $action
     * @param array $args
     * @param string[] $unknown
     *
     * @return mixed
     * @throws \Exception
     */
    public function action400(string $action, array $args, array $unknown)
    {
        if (method_exists($this, "action{$action}400"))
            return $this->{"action{$action}400"}($args, $unknown);

        throw new Exception($action . ': ' . implode($unknown));
    }

    /**
     * @param string $action
     * @param array|null $params
     *
     * @return mixed
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function run(string $action, ?array $params = null)
    {
        $args = $unknown = [];

        $refl = new ReflectionMethod($this, "action{$action}");
        foreach ($refl->getParameters() as $param) {
            $pos = $param->getPosition();
            if (isset($params[$param->getName()])) {
                $value = $params[$param->getName()];
                if ($param->hasType())
                    settype($value, $param->getType()->getName());
            } else {
                try {
                    $value = $this->findArg($param);
                } catch (Exception $e) {
                    $unknown[] = $param->getName();
                    continue;
                }
            }

            $args[$pos] = $value;
        }

        if ($unknown)
            return $this->action400($action, $args, $unknown);

        $refl->setAccessible(true);
        return $args ? $refl->invokeArgs($this, $args) : $refl->invoke($this);
    }

    /**
     * @param ReflectionParameter $param
     *
     * @return mixed
     * @throws \Exception
     */
    protected function findArg(ReflectionParameter $param)
    {
        if (!($value = $this->request->get($param->getName()))) {
            if (!($value = $this->request->post($param->getName()))) {
                if ($param->isOptional())
                    return $param->getDefaultValue();
                else
                    throw new Exception;
            }
        }

        if ($param->hasType())
            settype($value, $param->getType());

        return $value;
    }

}
