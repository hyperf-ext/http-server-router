<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/http-server-router.
 *
 * @link     https://github.com/hyperf-ext/http-server-router
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/http-server-router/blob/master/LICENSE
 */
namespace HyperfExt\HttpServer\Router;

use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\Utils\Arr;
use InvalidArgumentException;

class Route
{
    public string $rule;

    public array $data;

    public ?string $name;

    public function __construct(string $rule, array $data, ?string $name = null)
    {
        $this->rule = $rule;
        $this->data = $data;
        $this->name = empty($name) ? null : $name;
    }

    public function createUri(array $parameters = []): Uri
    {
        for ($i = count($this->data) - 1; $i >= 0; --$i) {
            $url = '';
            $keys = [];
            $last = count($this->data[$i]) - 1;
            foreach ($this->data[$i] as $n => $part) {
                if (is_string($part)) {
                    $url .= $part;
                } else {
                    [$key, $pattern] = $part;
                    if (isset($parameters[$key]) && preg_match('~^' . $pattern . '$~', (string) $parameters[$key])) {
                        $url .= $parameters[$key];
                        $keys[] = $key;
                    } else {
                        unset($url, $keys);
                        continue 2;
                    }
                }

                if ($n === $last) {
                    if ($i === 0) {
                        throw new InvalidArgumentException(
                            sprintf('The parameters does not matched for the route \'%s\'.', $this->rule)
                        );
                    }
                    break 2;
                }
            }
        }

        if (! empty($keys)) {
            Arr::forget($parameters, $keys);
        }

        return (new Uri($url ?? '/'))->withQuery(empty($parameters) ? '' : http_build_query($parameters));
    }
}
