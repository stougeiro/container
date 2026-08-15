<?php 

    namespace Tests\Support;

    use STDW\Container\ServiceManager;


    class TestableServiceManager extends ServiceManager
    {
        public function getCollection(): array
        { return $this->collection; }
    }
