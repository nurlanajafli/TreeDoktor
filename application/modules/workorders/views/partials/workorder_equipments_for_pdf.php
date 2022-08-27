<table width="100%" class="m-t-10" style="padding-bottom: 10px">
    <tbody>
    <tr>
        <td valign="top" width="250px">
            <label>
                <strong>Team:</strong>
            </label>
            <?php foreach($service_data->crew as $jkey=>$crew) : ?>
                <div>
                    <i class="fa fa-check"></i>
                    <?php echo $crew->crew_name; ?><br>
                </div>
            <?php endforeach; ?>
        </td>
        <td valign="top" width="250px">
            <label>
                <strong>Equipment:</strong>
            </label>
            <?php foreach($service_data->equipments as $jkey=>$equipment) : ?>
                <div>
                    <?php if($equipment->vehicle_name) : ?>
                        <i class="fa fa-check"></i>
                        <?php echo $equipment->vehicle_name;?>
                        <?php if($equipment->equipment_item_option && count(json_decode($equipment->equipment_item_option))) : ?>
                            <?php $opts = json_decode($equipment->equipment_item_option); ?>
                            <?php foreach($opts as $j=>$opt) : ?>
                                <?php if(!$j) : ?>(<?php endif?><?php echo trim($opt); ?><?php if(isset($opts[$j +1 ])) : ?> or <?php else : ?>)<?php endif; ?>
                            <?php endforeach; ?>
                        <?php else : ?>
                            (Any)
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($equipment->trailer_name) : ?>
                        <?php if(!isset($equipment->vehicle_name) || !$equipment->vehicle_name) : ?>
                            <i class="fa fa-check"></i>
                            <?php echo $equipment->trailer_name; ?>
                        <?php else : ?>
                            <?php echo ', ' . $equipment->trailer_name ?>
                        <?php endif; ?>
                        <?php if($equipment->equipment_attach_option && count(json_decode($equipment->equipment_attach_option))) : ?>
                            <?php $opts = json_decode($equipment->equipment_attach_option); ?>
                            <?php foreach((array)$opts as $j=>$opt) : ?>
                                <?php if(!$j) : ?>(<?php endif?><?php echo trim($opt); ?><?php if(isset($opts[$j +1 ])) : ?> or <?php else : ?>)<?php endif; ?>
                            <?php endforeach; ?>
                        <?php else : ?>
                            (Any)
                        <?php endif;?>
                    <?php endif;?>
                    <?php if($equipment->equipment_attach_tool) : ?>
                        <br><i class="fa fa-check"></i>
                        <?php foreach(json_decode($equipment->equipment_attach_tool) as $k=>$v) : ?>
                            <?php $opts = $equipment->equipment_tools_option ? json_decode($equipment->equipment_tools_option) : [];?>
                            <?php if($tools) : foreach($tools as $tool) : ?>
                                <?php if($tool->vehicle_id == $v) : //var_dump($opts->$v); die;?>
                                    <?php //echo  $tool->vehicle_name; ?>
                                    <?php if(isset($opts->$v) && count($opts->$v)) : ?>
                                        <?php //$opts = json_decode($equipment->equipment_tools_option); ?>
                                        <?php foreach($opts->$v as $j=>$opt) : ?>
                                            <?php echo trim($opt); ?><?php if(isset($opts->$v[$j +1]) || isset(json_decode($equipment->equipment_attach_tool)[$k+1])) : ?>, <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        (Any)
                                    <?php endif;?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>

                    <?php endif;?>
                </div>
            <?php endforeach; ?>
        </td>
    </tr>
    </tbody>
</table>
