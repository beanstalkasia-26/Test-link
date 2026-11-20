<?php

namespace Tests;

use Illuminate\Testing\TestResponse;

class PageAssert
{
    public function __construct(private TestResponse $response) {}

    public function component(string $component): self
    {
        $this->response->assertJsonPath('component', $component);

        return $this;
    }

    public function where(string $path, mixed $expected): self
    {
        $this->response->assertJsonPath('props.'.$path, $expected);

        return $this;
    }

    public function has(string $path, ?int $count = null): self
    {
        if ($count === null) {
            $this->response->assertJsonStructure(['props' => $this->structure($path)]);
        } else {
            $this->response->assertJsonCount($count, 'props.'.$path);
        }

        return $this;
    }

    private function structure(string $path): array
    {
        $segments = explode('.', $path);
        $last = array_pop($segments);
        $structure = [$last];
        foreach (array_reverse($segments) as $segment) {
            $structure = [$segment => $structure];
        }

        return $structure;
    }
}
