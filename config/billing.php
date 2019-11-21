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

use base_core\extensions\cms\Settings;
use lithium\g11n\Message;

extract(Message::aliases());

// Estimate number format specification.
Settings::register('estimate.number', [
	'sort' => '/([0-9]{4}[0-9]{4})/',
	'extract' => '/[0-9]{4}([0-9]{4})/',
	'generate' => '%Y%%04.d'
]);

// Period of time after which an estimate is considered overdue. Set to `false` if
// estimates never get overdue. Set to a `strtotime()` compatible string (i.e. `+2 weeks`)
// to enabled this feature.
Settings::register('estimate.overdueAfter', false);

// When sending out mails or financial notifications, BCC i.e. the billing contact email.
Settings::register('estimate.bcc', null);

// The default letter to use. Can either be `false` to disable feature, `true` to enable
// it. Provide a text string with the text or a callable which must return the text to
// enable and provide a default text.
//
// When a callable is passed, the first paramter will indiciate the context, in which the
// letter is used. This may be either `'entity'` or `'mail'`.
//
// ```
// Settings::register('...', true);
// Settings::register('...', 'foo');
// Settings::register('...', function($context, $user, $estimate) { return 'foo'; }));
// ```
Settings::register('estimate.letter', false);

// The default terms to use. Can either be `false` to disable feature, `true` to enable
// it. Provide a text string with the text or a callable which must return the text to
// enable and provide a default text.
//
// ```
// Settings::register('...', true);
// Settings::register('...', 'foo');
// Settings::register('...', function($user) { return 'foo'; }));
// ```
Settings::register('estimate.terms', false);

?>