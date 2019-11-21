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

use base_core\extensions\cms\Panes;
use lithium\g11n\Message;

extract(Message::aliases());

Panes::register('billing.estimates', [
	'title' => $t('Estimates', ['scope' => 'billing_estimate']),
	'url' => [
		'library' => 'billing_estimate',
		'controller' => 'Estimates', 'action' => 'index',
		'admin' => true
	],
	'weight' => 50
]);

?>