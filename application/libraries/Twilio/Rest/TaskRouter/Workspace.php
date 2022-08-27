<?php

class Services_Twilio_Rest_TaskRouter_Workspace extends Services_Twilio_TaskRouterInstanceResource {

    protected function init($client, $uri)
    {
        $this->setupSubresources(
            'activities',
            'events',
            'tasks',
            'Task_',
            'workers',
            'workflows'
        );
		$this->setupSubresource('statistics');
    }
}
