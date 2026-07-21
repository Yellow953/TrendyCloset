<?php

namespace App\Support;

/**
 * The anonymous browser identity behind product analytics. Resolved per-request
 * by {@see \App\Http\Middleware\TrackVisitor}.
 */
class Visitor
{
    public const COOKIE = 'tc_visitor';

    public function __construct(public readonly string $id) {}

    public function __toString(): string
    {
        return $this->id;
    }
}
