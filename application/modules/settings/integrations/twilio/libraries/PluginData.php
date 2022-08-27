<?php

namespace application\modules\settings\integrations\twilio\libraries;

use application\modules\settings\integrations\twilio\libraries\AppletUI\Exceptions\PluginDataException;

/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.
 *  The Original Code is OpenVBX, released June 15, 2010.
 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.
 * Contributor(s):
 **/
class PluginData
{
    public static $plugin_id;

    /**
     * @param $plugin_id
     */
    public static function setPluginId($plugin_id)
    {
        self::$plugin_id = $plugin_id;
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public static function startswith($key, $default = null)
    {
        return $default;
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public static function get($key, $default = null)
    {
        return $default;
    }

    /**
     * todo:: Maybe remove
     * @param $key
     * @param $value
     * @throws PluginDataException
     */
    public static function set($key, $value)
    {
        if (is_null(self::$plugin_id)) {
            throw new PluginDataException("Plugin id not set");
        }

        try {
            $store = VBX_Plugin_Store::get(array(
                'key' => $key,
                'plugin_id' => self::$plugin_id
            ));
            if (!$store) {
                $store = new VBX_Plugin_Store();
                $store->key = $key;
                $store->plugin_id = self::$plugin_id;
                $store->value = json_encode($value);
                $store->save();
            } else {
                $store->value = json_encode($value);
                $store->save(($force_update = true));
            }
        } catch (VBX_Plugin_StoreException $e) {
            error_log($e->getMessage());
            error_log("VBX_Plugin_StoreException while setting values for $key => " .
                var_export($value, true));
            throw new PluginDataException("Failed to set values in plugin store: " .
                $e->getMessage());
        }
    }

    public static function delete($key)
    {
        try {
            $store = VBX_Plugin_Store::get(array(
                'key' => $key,
                'plugin_id' => self::$plugin_id
            ));
            $store->delete();
        } catch (VBX_Plugin_StoreException $e) {
            error_log($e->getMessage());
            error_log("VBX_Plugin_StoreException while deleting	 `$key`");
            throw new PluginDataException("Failed to set values in plugin store: " .
                $e->getMessage());
        }
    }

    // Returns all key/value pairs for plugin
    public static function getKeyValues()
    {
        try {
            return VBX_Plugin_Store::search(array(
                'plugin_id' => self::$plugin_id
            ));
        } catch (VBX_Plugin_StoreException $e) {
            error_log($e->getMessage());
            error_log("VBX_Plugin_StoreException while retrieving all keys");
            throw new PluginDataException("Failed to set values in plugin store: " .
                $e->getMessage());
        }
    }

    public static function sqlQuery($sql)
    {
        if (empty($sql)) {
            throw new PluginDataException('Empty SQL statement');
        }

        $ci = &get_instance();

        /** @var CI_DB_Result $result */
        $result = $ci->db->query($sql);

        if (is_object($result)) {
            return $result->result_array();
        }

        return null;
    }

    public static function one($sql)
    {
        if (empty($sql)) {
            throw new PluginDataException('Empty SQL statement');
        }

        $ci = &get_instance();
        return $ci->db->query($sql)->first_row('array');
    }


}