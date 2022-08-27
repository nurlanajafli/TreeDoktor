<?php
namespace application\modules\estimates\models;

use application\core\Database\EloquentModel;

class EstimatesServicesEquipments extends EloquentModel
{
    const ATTR_EQUIPMENT_ID = 'equipment_id';
    const ATTR_EQUIPMENT_SERVICE_ID = 'equipment_service_id';
    const ATTR_EQUIPMENT_ITEM_ID = 'equipment_item_id';
    const ATTR_EQUIPMENT_ESTIMATE_ID = 'equipment_estimate_id';
    const ATTR_EQUIPMENT_ATTACH_ID = 'equipment_attach_id';
    const ATTR_EQUIPMENT_ITEM_OPTION = 'equipment_item_option';
    const ATTR_EQUIPMENT_ATTACH_OPTION = 'equipment_attach_option';
    const ATTR_EQUIPMENT_ATTACH_TOOL = 'equipment_attach_tool';
    const ATTR_EQUIPMENT_TOOLS_OPTION = 'equipment_tools_option';

    protected $table = 'estimates_services_equipments';

    protected $primaryKey = 'equipment_id';

    protected $appends = ['equipment_tools_option_array', 'equipment_item_option_string', 'equipment_attach_option_string'];

    public function estimate_service() {
        return $this->hasOne(EstimatesService::class, 'id', 'equipment_service_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attachment()
    {
        return $this->hasOne(Vehicles::class, 'vehicle_id', 'equipment_attach_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function equipment()
    {
        return $this->hasOne(Vehicles::class, 'vehicle_id', 'equipment_item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function trailer()
    {
        return $this->hasOne(Vehicles::class, 'vehicle_id', 'equipment_attach_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vehicle()
    {
        return $this->hasOne(Vehicles::class, 'vehicle_id', 'equipment_item_id');
    }

    public function getEquipmentToolsOptionArrayAttribute()
    {
        if(!$this->attributes['equipment_tools_option'])
            return [];
        return json_decode($this->attributes['equipment_tools_option'], true);
    }

    public function getEquipmentItemOptionArrayAttribute(){
        if(!$this->attributes['equipment_item_option'])
            return collect(["Any"]);

        $result = json_decode($this->attributes['equipment_item_option'], true);
        $result = (empty($result))?["Any"]:$result;
        return collect($result);
    }

    public function getEquipmentAttachOptionArrayAttribute(){
        if(!$this->attributes['equipment_attach_option'])
            return collect(["Any"]);

        $result = json_decode($this->attributes['equipment_attach_option'], true);
        $result = (empty($result))?["Any"]:$result;
        return collect($result);
    }

    public function getEquipmentItemOptionStringAttribute(){
        $item_options = $this->getEquipmentItemOptionArrayAttribute();
        return ' (' . $item_options->implode(' OR ') . ')';
    }

    public function getEquipmentAttachOptionStringAttribute(){
        $attach_option = $this->getEquipmentAttachOptionArrayAttribute();
        return ' (' . $attach_option->implode(' OR ') . ')';
    }

}
