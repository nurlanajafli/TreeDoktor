<?php
defined('BASEPATH') OR exit('No direct script access allowed');

interface JobsInterface
{
    // RG 10.03.2020
    // должен возвращать данные необходимые для выполнения job в методе execute
    // данные будут помещены в поле job_payload в виде JSON строки возвращает массив или в чистом виде если возвращает примитив
    // если возвращает FALSE задача не будет добавлена в БД
    public function getPayload($data);

    // должен реализовать выполнение задачи $job
    public function execute($job);
}
