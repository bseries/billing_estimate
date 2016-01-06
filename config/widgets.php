<?php
/**
 * Billing Estimate
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see http://atelierdisko.de/licenses.
 */

namespace billing_estimate\config;

use base_core\extensions\cms\Widgets;
use billing_estimate\models\Estimates;
use lithium\core\Environment;
use lithium\g11n\Message;

extract(Message::aliases());

Widgets::register('estimates', function() use ($t) {
	$open = Estimates::find('count', [
		'conditions' => [
			'status'  => 'created'
		]
	]);
	$accepted = Estimates::find('count', [
		'conditions' => [
			'status'  => 'accepted'
		]
	]);

	return [
		'data' => [
			$t('open', ['scope' => 'billing_estimate']) => $open,
			$t('accepted', ['scope' => 'billing_estimate']) => $accepted,
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD
]);

?>