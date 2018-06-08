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

	return [
		'title' => $t('Estimates', ['scope' => 'billing_estimate']),
		'data' => [
			$t('total ({:year}, ongoing)', [
				'scope' => 'billing_invoice',
				'year' => date('Y')
			]) => $formatter->format(Estimates::totalEstimated(date('Y'))->getNet()),
			$t('total ({:year})', [
				'scope' => 'billing_invoice',
				'year' => date('Y') - 1
			]) => $formatter->format(Estimates::totalEstimated(date('Y') - 1)->getNet()),
			$t('pending', ['scope' => 'billing_estimate']) => Estimates::countPending(),
			$t('accepted', ['scope' => 'billing_estimate']) => round(Estimates::successRate(), 0) . '%',
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
