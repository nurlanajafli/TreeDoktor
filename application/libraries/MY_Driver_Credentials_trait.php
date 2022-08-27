<?php


trait MY_Driver_Credentials_trait
{

    protected $credentials;

    /**
     * @param bool|string $var
     * @return bool|mixed
     * @throws Exception
     */
    public function getCredentials($var = false, $adapter = false)
    {
        if(!$adapter)
            $adapter = $this->_adapter;
        if(empty($this->credentials[$adapter])){
            // with internal payment config
            $item = 'payment_' . ($this->internalPayment ? 'internal_' : '') . $adapter;

            if($json = config_item($item))
                $this->credentials[$adapter] = json_decode($json,true);
            else
                throw new Exception('can`t get payment credentials!');
            if(json_last_error() !== JSON_ERROR_NONE)
                throw new Exception('payment credentials json decode fail!');
        }

        if (!empty($this->credentials[$adapter])) {
            if ($var && isset($this->credentials[$adapter][$var])) {
                return $this->credentials[$adapter][$var];
            } elseif($var)
                return false;
            return $this->credentials[$adapter];
        }
        return false;
    }
}