<?php

$config['leads_services'] = [
	[
		'name' => 'Tree Work',
		'list' =>  [
			[
				'name' => 'tree_removal',
				'label' => 'Tree Removal',
				'reffid' => 4,//Tree Removal
			],
			[
				'name' => 'tree_pruning',
				'label' => 'Tree Pruning, Trimming',
				'reffid' => 19,//Pruning
			],
			[
				'name' => 'hedge_maintenance',
				'label' => 'Hedge/ Shrub Maintenance',
				'reffid' => NULL,
			],
			[
				'name' => 'wood_disposal',
				'label' => 'Brush/ Wood Disposal',
				'reffid' => NULL,
			],
			[
				'name' => 'stump_removal',
				'label' => 'Stump Removal',
				'reffid' => 11,
			],
		],
	],
	[
		'name' => 'Liquid Services',
		'list' =>  [
			[
				'name' => 'root_fertilizing',
				'label' => 'Deep Root Fertilizing',
				'reffid' => 24,//DRF
			],
			[
				'name' => 'spraying',
				'label' => 'Spraying/ Fungicide Application',
				'reffid' => 12,//Sparaing
			],
			[
				'name' => 'trunk_injection',
				'label' => 'Trunk Injection',
				'reffid' => 22,//TI
			],
			[
				'name' => 'air_spading',
				'label' => 'Air Spading',
				'reffid' => 37,//AS
			],
			[
				'name' => 'planting',
				'label' => 'Planting',
				'reffid' => 17,//PAlnt ser
			],
		],
	],
	[
		'name' => 'Arborist Services',
		'list' =>  [
			[
				'name' => 'arborist_consultation',
				'label' => 'Arborist Consultation',
				'reffid' => 1,//AR
			],
			[
				'name' => 'arborist_report',
				'label' => 'Regular Arborist Report',
				'reffid' => 2,//GAR
			],
			[
				'name' => 'construction_arborist_report',
				'label' => 'Construction Arborist Report',
				'reffid' => 3,//CAR
			],
			[
				'name' => 'tree_cabling',
				'label' => 'Cabling/ Bracing',
				'reffid' => 13,//Cabling
			],
			[
				'name' => 'tpz_installation',
				'label' => 'TPZ Installation',
				'reffid' => NULL,//
			],
		],
	],
	[
		'name' => 'Other',
		'list' =>  [
			[
				'name' => 'lights_installation',
				'label' => 'Lights Installation',
				'reffid' => 18,//Lights inst
			],
			[
				'name' => 'landscaping',
				'label' => 'Landscaping',
				'reffid' => NULL,
			],
			[
				'name' => 'emergency',
				'label' => 'Emergency Work',
				'reffid' => NULL,
			],
			[
				'name' => 'snow_removal',
				'label' => 'Snow Removal',
				'reffid' => NULL,
			],
			[
				'name' => 'other',
				'label' => 'Other Services',
				'reffid' => NULL,
			],
		],
	]
];