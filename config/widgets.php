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
use AD\Finance\Money\Monies;
use AD\Finance\Money\MoniesIntlFormatter as MoniesFormatter;

extract(Message::aliases());

Widgets::register('estimates', function() use ($t) {
	$formatter = new MoniesFormatter(Environment::get('locale'));

	$estimated = new Monies();
	$estimates = Estimates::find('all', [
		'conditions' => [
			'status' => 'accepted'
		],
		'fields' => [
			'id'
		]
	]);
	foreach ($estimates as $estimate) {
		foreach ($estimate->totals()->sum() as $rate => $currencies) {
			foreach ($currencies as $currency => $price) {
				$estimated = $estimated->add($price->getNet());
			}
		}
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
	$rate = round(($accepted * 100) / ($accepted + $rejected), 0);

	return [
		'title' => $t('Estimates', ['scope' => 'billing_estimate']),
		'data' => [
			$t('successfully estimated', ['scope' => 'billing_estimate']) => $formatter->format($estimated),
			$t('pending', ['scope' => 'billing_estimate']) => $pending,
			$t('accept rate', ['scope' => 'billing_estimate']) =>  $rate . '%',
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