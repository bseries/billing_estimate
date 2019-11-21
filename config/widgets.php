<?php
/**
 * Billing Estimate
 *
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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
