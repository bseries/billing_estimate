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
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace billing_estimate\config;

use AD\Finance\Money\MoniesIntlFormatter as MoniesFormatter;
use AD\Finance\Price\Prices;
use base_core\extensions\cms\Widgets;
use billing_estimate\models\EstimatePositions;
use billing_estimate\models\Estimates;
use lithium\core\Environment;
use lithium\g11n\Message;

extract(Message::aliases());

Widgets::register('estimates', function() use ($t) {
	$formatter = new MoniesFormatter(Environment::get('locale'));

	$positions = EstimatePositions::find('all', [
		'conditions' => [
			'Estimate.status' => 'accepted',
			'EstimatePositions.is_optional' => false
		],
		'fields' => [
			'amount_currency',
			'amount_type',
			'amount_rate',
			'ROUND(SUM(EstimatePositions.amount * EstimatePositions.quantity)) AS amount'
		],
		'group' => [
			'amount_currency',
			'amount_type',
			'amount_rate'
		],
		'with' => ['Estimate']
	]);

	$estimated = new Prices();
	foreach ($positions as $position) {
		$estimated = $estimated->add($position->amount());
	}

	$pending = Estimates::find('count', [
		'conditions' => [
			'status'  => 'sent'
		]
	]);

	$rejected = Estimates::find('count', [
		'conditions' => [
			'status'  => ['rejected', 'no-response']
		]
	]);
	$accepted = Estimates::find('count', [
		'conditions' => [
			'status'  => 'accepted'
		]
	]);

	// sum of both may be zero, and we cannot divide by 0.
	if ($accepted + $rejected > 0) {
		$rate = round(($accepted * 100) / ($accepted + $rejected), 0);
	} else {
		$rate = 100;
	}

	return [
		'title' => $t('Estimates', ['scope' => 'billing_estimate']),
		'data' => [
			$t('successfully estimated', ['scope' => 'billing_estimate']) => $formatter->format($estimated->getNet()),
			$t('pending', ['scope' => 'billing_estimate']) => $pending,
			$t('accepted', ['scope' => 'billing_estimate']) =>  $rate . '%',
		],
		'url' => [
			'library' => 'billing_estimate',
			'controller' => 'Estimates',
			'action' => 'index'
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD
]);

?>