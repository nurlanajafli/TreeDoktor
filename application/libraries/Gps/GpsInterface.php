<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Interface GpsInterface
 * @see https://code.tutsplus.com/ru/tutorials/how-to-create-custom-drivers-in-codeigniter--cms-29339
 */
interface GpsInterface
{
    public function tracks();

    public function currentTracks();

    public function parkings($id, $date);

    public function distance($id, $date);

    public function route($id, $date);
}