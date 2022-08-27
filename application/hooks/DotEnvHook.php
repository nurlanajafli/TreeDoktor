<?php


use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\Adapter\ServerConstAdapter;
use Dotenv\Repository\RepositoryBuilder;

class DotEnvHook
{
    public function init()
    {
        $adapters = [
            new EnvConstAdapter(),
            new PutenvAdapter(),
            new ServerConstAdapter(),
        ];

        $repository = RepositoryBuilder::create()
            ->withReaders($adapters)
            ->withWriters($adapters)
            ->immutable()
            ->make();

        Dotenv::create($repository, FCPATH, null)->load();
    }
}
