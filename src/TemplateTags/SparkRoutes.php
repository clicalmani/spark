<?php
namespace Tonka\Spark\TemplateTags;

use Clicalmani\Foundation\Resources\TemplateTag;
use Clicalmani\Routing\Memory;

class SparkRoutes extends TemplateTag
{
    protected string $tag = '@SparkRoutes';

    public function render(array $matches) : string
    {
        $config = config('spark');
        $container = \Clicalmani\Foundation\Acme\Container::getInstance();
        
        $rules = [];
        $isExceptMode = false;
        
        if (config('spark.group') && isset($config['groups'][config('spark.group')])) {
            $groupRoutes = $config['groups'][config('spark.group')];
            $rules = array_map(fn($name) => [
                'name' => $name,
                'policy' => null
            ], $groupRoutes);
        } else {
            // Global
            if (!empty($config['only'])) {
                $rules = $config['only'];
            } elseif (!empty($config['except'])) {
                $isExceptMode = true;
                $rules = $config['except'];
            } else {
                // No configuration = Maximum security (nothing is transmitted)
                return json_encode([
                    'url' => rtrim(app()->getUrl(), '/'),
                    'port' => parse_url(client_uri(), PHP_URL_PORT) ?? null,
                    'defaults' => [],
                    'routes' => []
                ]);
            }
        }

        $allRoutes = [];
        foreach (Memory::getRoutes() as $verb => $routes) {
            foreach ($routes as $route) {
                if (!$route->name) continue;
                $allRoutes[$route->name] = $route;
            }
        }

        $finalRoutes = [];
        foreach ($allRoutes as $name => $route) {
            
            $matchedRule = null;

            foreach ($rules as $rule) {
                if ($this->matches($name, $rule['name'])) {
                    $matchedRule = $rule;
                    break;
                }
            }

            if (!$matchedRule) {
                // In 'only' mode, if there's no match, we ignore it.
                if (!$isExceptMode) continue;
                
                // In 'except' mode, if it doesn't match, we accept.
                $finalRoutes[$name] = $route;
                continue;
            }

            $policyPassed = true;
            
            if (!empty($matchedRule['policy']) && is_subclass_of($matchedRule['policy'], \Clicalmani\Foundation\Auth\Contract::class)) {
                try {
                    $policy = $container->getClassIntance($matchedRule['policy'], true);
                    
                    if (method_exists($policy, 'authorize')) {
                        $policyPassed = $policy->authorize();
                    }
                } catch (\Exception $e) {
                    $policyPassed = false;
                }
            }

            if ($isExceptMode) {
                if ($policyPassed) {
                    continue; 
                } else {
                    $finalRoutes[$name] = $route;
                }

            } else {
                if ($policyPassed) {
                    $finalRoutes[$name] = $route;
                }
            }
        }

        return json_encode([
            'url' => rtrim(app()->getUrl(), '/'),
            'port' => parse_url(client_uri(), PHP_URL_PORT) ?? null,
            'defaults' => [],
            'routes' => $finalRoutes
        ]);
    }

    protected function matches(string $name, string $pattern): bool
    {
        $regex = str_replace('\*', '.*', preg_quote($pattern, '/'));
        return (bool) preg_match('/^' . $regex . '$/', $name);
    }
}
