<?php

namespace application\modules\settings\integrations\twilio\libraries\AppletUI;

use application\modules\user\models\User;

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
class UserGroupPickerWidget extends AppletUIWidget
{
    protected $template = 'UserGroupPicker';
    protected $name;
    protected $label;
    protected $value;

    /**
     * UserGroupPickerWidget constructor.
     * @param $name
     * @param $label
     * @param null $value
     */
    public function __construct($name, $label, $value = null)
    {
        $this->name = $name;
        $this->label = $this->buildLabel($label, $value);
        $this->value = $value;
        $this->owner_type = $this->buildOwnerType($value);
        $this->owner_id = $this->buildOwnerId($value);

        parent::__construct($this->template);
    }

    /**
     * @param $label
     * @param $value
     * @return string
     */
    private function buildLabel($label, $value)
    {
        if (!empty($value)) {
            if ($value instanceof User) {
                return $value->fullname . $value->firstname . " (" . $value->user_email . ")";
            } else {
                return $value->name;
            }
        }

        return $label;
    }

    /**
     * @param $value
     * @return string
     * @throws \ReflectionException
     */
    private function buildOwnerType($value)
    {
        $owner_type = '';
        if (!empty($value)) {
            $owner_type = (new \ReflectionClass($value))->getShortName();
            $owner_type = strtolower($owner_type);
        }

        return $owner_type;
    }

    /**
     * @param $value
     * @return string
     */
    private function buildOwnerId($value)
    {
        $owner_id = '';
        if (!empty($value)) {
            $owner_id = $value->id;
        }

        return $owner_id;
    }

    /**
     * @param array $data
     * @return false|string
     * @throws Exceptions\AppletUIWidgetException
     */
    public function render($data = [])
    {

        $defaults = [
            'name' => $this->name,
            'label' => $this->label,
            'owner_id' => $this->owner_id,
            'owner_type' => $this->owner_type,
        ];

        $data = array_merge($defaults, $data);

        return parent::render($data);
    }
}
