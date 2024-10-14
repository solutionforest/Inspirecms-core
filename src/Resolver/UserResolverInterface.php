<?php

namespace SolutionForest\InspireCms\Resolver;

interface UserResolverInterface
{
    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function resolve();
}
