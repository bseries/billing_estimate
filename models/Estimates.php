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

namespace billing_estimate\models;

use AD\Finance\Money\Monies;
use AD\Finance\Price;
use AD\Finance\Price\Prices;
use DateInterval;
use DateTime;
use Exception;
use base_address\models\Addresses;
use base_address\models\Contacts;
use base_core\extensions\cms\Settings;
use base_tag\models\Tags;
use billing_core\models\ClientGroups;
use billing_core\models\TaxTypes;
use billing_estimate\models\EstimatePositions;
use billing_payment\models\Payments;
use li3_mailer\action\Mailer;
use lithium\core\Libraries;
use lithium\g11n\Message;

class Estimates extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'billing_estimates'
	];

	protected $_actsAs = [
		'base_core\extensions\data\behavior\Ownable',
		'base_core\extensions\data\behavior\RelationsPlus',
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\ReferenceNumber',
		'base_core\extensions\data\behavior\Searchable' => [
			'fields' => [
				'number',
				'status',
				'date',
				'address_recipient',
				'address_organization',
				'User.number'
			]
		]
	];

	public $belongsTo = [
		'User' => [
			'to' => 'base_core\models\Users',
			'key' => 'user_id'
		]
	];

	public $hasMany = [
		'Positions' => [
			'to' => 'billing_estimate\models\EstimatePositions',
			'key' => 'billing_estimate_id'
		]
	];

	public static $enum = [
		'status' => [
			'created',
			'cancelled',
			'accepted',
			'declined',
		]
	];

	public static function init() {
		$model = static::_object();

		static::behavior('base_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('estimate.number')
		);
	}

	public function positionsGroupedByTags($entity, array $customTagsOrder = []) {
		$positions = $entity->positions();

		$seen = [];
		$groups = [];

		if ($customTagsOrder) {
			$groups = array_fill_keys($customTagsOrder, null);
		}

		foreach ($positions as $position) {
			// Search for first dollar prefixed tag and use it
			// as the main tag.
			$tags = $position->tags(['serialized' => false, 'entities' => true]);
			$group = Tags::create();

			foreach ($tags as $tag) {
				if ($tag->name[0] === '$') {
					$group = $tag;
					break;
				}
			}

			if (!isset($groups[$group->name])) {
				$groups[$group->name] = [
					'positions' => [],
					'tag' => $group
				];
			}
			$groups[$group->name]['positions'][] = $position;
		}
		return array_filter($groups);
	}

	public function title($entity) {
		return '#' . $entity->number;
	}

	public function quantity($entity) {
		$result = preg_match('/^([0-9])\sx\s/', $entity->title, $matches);

		if (!$result) {
			return 1;
		}
		return (integer) $matches[1];
	}

	public function date($entity) {
		return DateTime::createFromFormat('Y-m-d', $entity->date);
	}

	// Returns Prices.
	public function totals($entity) {
		$result = new Prices();

		foreach ($entity->positions() as $position) {
			$result = $result->add($position->total());
		}
		return $result;
	}

	// Monies keyed by rate.
	public function taxes($entity) {
		$results = [];

		foreach ($entity->totals()->sum() as $rate => $currencies) {
			foreach ($currencies as $currency => $price) {
				if (!isset($results[$rate])) {
					$results[$rate] = new Monies();
				}
				$results[$rate] = $results[$rate]->add($price->getTax());
			}
		}
		return $results;
	}

	// May return positive or negative values.
	// We need to convert to gross here as payments will be gross only.
	public function balance($entity) {
		$result = new Monies();

		foreach ($entity->positions() as $position) {
			$result = $result->subtract($position->total()->getGross());
		}
		foreach ($entity->payments() as $payment) {
			$result = $result->add($payment->amount());
		}
		return $result;
	}

	// Returns Monies.
	public function paid($entity) {
		$result = new Monies();

		foreach ($entity->payments() as $payment) {
			$result = $result->add($payment->amount());
		}
		return $result;
	}

	public function pay($entity, $payment) {
		if ($entity->isPaidInFull()) {
			throw new Exception("Estimate is already paid in full.");
		}
		$user = $entity->user();

		$payment->set([
			'billing_estimate_id' => $entity->id,
			'user_id' => $user->id
		]);
		return $payment->save(null, [
			'localize' => false
		]);
	}

	// Generate a payment for each currency in the open positions.
	public function payInFull($entity) {
		if ($entity->isPaidInFull()) {
			throw new Exception("Estimate is already paid in full.");
		}

		foreach ($entity->balance()->sum() as $currency => $money) {
			$payment = Payments::create([
				'method' => 'user',
				'amount_currency' => $currency,
				'amount' => $money->negate()->getAmount(),
				'date' => date('Y-m-d')
			]);
			if (!$entity->pay($payment)) {
				return false;
			}
		}
		return $entity->save([
			'status' => 'paid'
		], [
			'whitelist' => ['status']
		]);
	}

	public function isPaidInFull($entity) {
		foreach ($entity->balance()->sum() as $money) {
			if ($money->getAmount() < 0) {
				return false;
			}
		}
		return true;
	}

	public function address($entity) {
		return Addresses::createFromPrefixed('address_', $entity->data());
	}

	public function isCancelable($entity) {
		return in_array($entity->status, [
			'created'
		]);
	}

	public function exportAsPdf($entity) {
		extract(Message::aliases());

		$stream = fopen('php://temp', 'w+');

		$user = $entity->user();
		$sender = Contacts::create(Settings::read('contact.billing'));

		$document = Libraries::locate('document', 'Estimate');
		$document = new $document();

		$document
			->type($t('Estimate', [
				'scope' => 'billing_estimate',
				'locale' => $user->locale
			]))
			->entity($entity)
			->recipient($user)
			->sender($sender)
			->subject($t('Estimate #{:number}', [
				'number' => $entity->number,
				'locale' => $user->locale,
				'scope' => 'billing_estimate'
			]));

		$document->compile();
		$document->render($stream);

		rewind($stream);
		return $stream;
	}

	public function taxType($entity) {
		return TaxTypes::find('first', ['conditions' => ['name' => $entity->tax_type]]);
	}

	public function clientGroup($entity) {
		return ClientGroups::find('first', ['conditions' => ['user' => $entity->user()]]);
	}
}

Estimates::applyFilter('save', function($self, $params, $chain) {
	$entity = $params['entity'];
	$data =& $params['data'];

	if (!$entity->exists()) {
		$user = $entity->user(['conditions' => ['id' => $entity->user_id ?: $data['user_id']]]);

		$group = ClientGroups::find('first', [
			'conditions' => compact('user')
		]);
		if (!$group) {
			return false;
		}
		$terms = Settings::read('billing.paymentTerms');

		$data = array_filter($data) + [
			'user_id' => $user->id,
			'user_vat_reg_no' => $user->vat_reg_no,
			'tax_type' => $group->taxType,
			'tax_note' => $group->taxType()->note(),
			'date' => date('Y-m-d'),
			'status' => 'created',
			'terms' => is_callable($terms) ? $terms($user) : $terms
		];
		$data = $user->address('billing')->copy($data, 'address_');
	} else {
		$user = $entity->user();
	}

	if (!$result = $chain->next($self, $params, $chain)) {
		return false;
	}

	// Save nested positions.
	$new = isset($data['positions']) ? $data['positions'] : [];
	foreach ($new as $key => $value) {
		if ($key === 'new') {
			continue;
		}
		if (isset($value['id'])) {
			$item = EstimatePositions::find('first', [
				'conditions' => ['id' => $value['id']]
			]);

			if ($value['_delete']) {
				if (!$item->delete()) {
					return false;
				}
				continue;
			}
		} else {
			$item = EstimatePositions::create($value + [
				'billing_estimate_id' => $entity->id,
				'user_id' => $user->id
			]);
		}

		if (!$item->save($value)) {
			return false;
		}
	}

	return true;
});
Estimates::applyFilter('delete', function($self, $params, $chain) {
	$entity = $params['entity'];
	$result = $chain->next($self, $params, $chain);

	if ($result) {
		$positions = EstimatePositions::find('all', [
			'conditions' => ['billing_estimate_id' => $entity->id]
		]);
		foreach ($positions as $position) {
			$position->delete();
		}
	}
	return $result;
});

Estimates::init();

?>