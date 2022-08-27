<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['hazards'] = [
	[
		[
			'label'=>'Work Location Hazards',
			'data'=>[
				'Public and vehicular traffic',
				'Terrain conditions',
				'Trip objects / slippery surfaces',
				'Pinch points or sharp edges',
				'Electrical/utility hazards',
				'Other trades within work zone'
			]
		],
		[
			'label'=>'Tree-Specific Hazards',
			'data'=>[
				'Signs of root or basal decay',
				'Trunk/stem cracking',
				'Open cavities',
				'Dead/hanging branches',
				'Weak branch attachments',
				'Wildlife',
			]
		],
		[
			'label'=>'Equipment Hazards',
			'data'=>[
				'Burning hazard from heat sources',
				'Airborne particles / chipping / grinding',
				'Activity creates vibration / noise hazard',
				'Sharp cutting surfaces',
				'Spill potential',
			]
		],
		[
			'label'=>'Environmental Hazards',
			'data'=>[
				'Weather Conditions: heat, cold, rain, lightning, high winds, etc.',
				'Dust, chemicals, debris',
			]
		],
		[
			'label'=>'Ergonomic Hazards',
			'data'=>[
				'Working in confined area',
				'Working over your head',
				'Repetitive motion',
				'Repetitive work in awkward position',
				'Lifting heavy or awkward loads',
			]
		],
		[
			'label'=>'Property Damage Hazards',
			'data'=>[
				'Work or machine damage to lawns and understorey plants',
				'Vehicle and machine damage to driveways and paved surfaces',
				'Movable items within the work zone',
			]
		]
	]
];
  	
$config['controls'] = [
	[
		[
			'label'=>'Procedures / Permits / Plans',
			'data'=>[
				'Traffic control plan and roadside set-up',
				'Safe work procedures reviewed',
				'Equipment inspections completed: proper operation, sharpness, cracks, damage, loose connections and leaks, appropriate certification, safety features operational',
				'Valid permits/locates present onsite',
			]
		],
		[
			'label'=>'PPE Requirements',
			'data'=>[
				'CSA Approved hard hat',
				'Hearing protection',
				'Safety boots',
				'Safety glasses',
				'High-visibility clothing',
				'Chainsaw leg protection',
			]
		],
		[
			'label'=>'Property Damage Controls',
			'data'=>[
				'Remove furniture, planters, BBQ’s, etc. from work zone where possible',
				'Protect lawns and understory plants',
				'Install plywood along walkways, driveways, paved surfaces, siding and stucco where work activities may damage',
				'Maintain orderly work site',
			]
		],
	],
];
