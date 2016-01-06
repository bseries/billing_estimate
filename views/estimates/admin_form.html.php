<?php

use base_core\extensions\cms\Settings;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'billing_estimate', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'single',
		'title' => $item->number,
		'empty' => false,
		'object' => $t('estimate')
	],
	'meta' => [
		'status' => $statuses[$item->status]
	]
]);

?>
<article>

	<?=$this->form->create($item) ?>
		<?= $this->form->field('id', ['type' => 'hidden']) ?>

		<?php if ($useOwner): ?>
			<div class="grid-row">
				<h1><?= $t('Access') ?></h1>

				<div class="grid-column-left"></div>
				<div class="grid-column-right">
					<?= $this->form->field('owner_id', [
						'type' => 'select',
						'label' => $t('Owner'),
						'list' => $users
					]) ?>
				</div>
			</div>
		<?php endif ?>

		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->form->field('number', [
					'type' => 'text',
					'label' => $t('Number'),
					'class' => 'use-for-title'
				]) ?>
				<div class="help"><?= $t('Leave empty to autogenerate number.') ?></div>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('status', [
					'type' => 'select',
					'label' => $t('Status'),
					'list' => $statuses
				]) ?>
				<?= $this->form->field('date', [
					'type' => 'date',
					'label' => $t('Date'),
					'value' => $item->date ?: date('Y-m-d')
				]) ?>
				<div class="help"><?= $t('Estimate date.') ?></div>
			</div>
		</div>
		<div class="grid-row">
			<h1 class="h-gamma"><?= $t('Recipient') ?></h1>

			<div class="grid-column-left">
				<?= $this->form->field('address', [
					'type' => 'textarea',
					'label' => $t('Address'),
					'disabled' => true,
					'value' => $item->address()->format('postal', $locale)
				]) ?>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('user_id', [
					'type' => 'select',
					'label' => $t('User'),
					'list' => $users
				]) ?>
			</div>
		</div>

		<div class="grid-row">
			<h1 class="h-gamma"><?= $t('Positions') ?></h1>
			<section class="use-nested">
				<table>
					<thead>
						<tr>
							<td class="position-description--f">
							<td class="numeric--f quantity--f"><?= $t('Quantity') ?>
							<td class="currency--f"><?= $t('Currency') ?>
							<td class="price-type--f"><?= $t('Type') ?>
							<td class="money--f price-amount--f"><?= $t('Unit price') ?>
							<td class="numeric--f price-rate--f"><?= $t('Tax rate (%)') ?>
							<td class="money--f position-total--f"><?= $t('Total (net)') ?>
							<td class="actions">
					</thead>
					<tbody>
					<?php foreach ($item->positions() as $key => $child): ?>
						<tr class="nested-item">
							<td class="position-description--f">
								<?= $this->form->field("positions.{$key}.id", [
									'type' => 'hidden',
									'value' => $child->id
								]) ?>
								<?= $this->form->field("positions.{$key}._delete", [
									'type' => 'hidden'
								]) ?>
								<?= $this->form->field("positions.{$key}.description", [
									'type' => 'textarea',
									'label' => false,
									'value' => $child->description,
									'placeholder' => $t('Description')
								]) ?>
								<?= $this->form->field("positions.{$key}.tags", [
									'type' => 'text',
									'label' => false,
									'value' => $child->tags(),
									'placeholder' => $t('Tags'),
									'class' => 'input--tags'
								]) ?>
							<td class="numeric--f quantity--f">
								<?= $this->form->field("positions.{$key}.quantity", [
									'type' => 'text',
									'label' => false,
									'value' => $this->number->format($child->quantity, 'decimal'),
									'class' => 'input--numeric'
								]) ?>
							<td class="currency--f">
								<?= $this->form->field("positions.{$key}.amount_currency", [
									'type' => 'select',
									'label' => false,
									'list' => $currencies,
									'value' => $child->amount_currency
								]) ?>
							<td class="price-type--f">
								<?= $this->form->field("positions.{$key}.amount_type", [
									'type' => 'select',
									'label' => false,
									'value' => $child->amount_type,
									'list' => ['net' => $t('net'), 'gross' => $t('gross')]
								]) ?>
							<td class="money--f price-amount--f">
								<?= $this->form->field("positions.{$key}.amount", [
									'type' => 'text',
									'label' => false,
									'value' => $this->money->format($child->amount, ['currency' => false]),
									'placeholder' => $this->money->format(0, ['currency' => false]),
									'class' => 'input--money'
								]) ?>
							<td class="numeric--f price-rate--f">
								<?= $this->form->field("positions.{$key}.amount_rate", [
									'type' => 'text',
									'label' => false,
									'value' => $child->amount_rate,
									'class' => 'input--numeric'
								]) ?>
							<td class="money--f position-total--f">
								<?= $this->money->format($child->total()->getNet()) ?>
							<td class="actions">
								<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
					<?php endforeach ?>
					<tr class="nested-add nested-item">
						<td class="position-description--f">
							<?= $this->form->field('positions.new.description', [
								'type' => 'textarea',
								'label' => false,
								'placeholder' => $t('Description')
							]) ?>
							<?= $this->form->field("positions.new.tags", [
								'type' => 'text',
								'label' => false,
								'placeholder' => $t('Tags'),
								'class' => 'input--tags'
							]) ?>
						<td class="numeric--f quantity--f">
							<?= $this->form->field('positions.new.quantity', [
								'type' => 'text',
								'value' => 1,
								'label' => false,
								'class' => 'input--numeric'
							]) ?>
						<td class="currency--f">
							<?= $this->form->field("positions.new.amount_currency", [
								'type' => 'select',
								'label' => false,
								'value' => $item->exists() ? $item->clientGroup()->amountCurrency : 'EUR',
								'list' => $currencies
							]) ?>
						<td class="price-type--f">
							<?= $this->form->field("positions.new.amount_type", [
								'type' => 'select',
								'label' => false,
								'value' => $item->exists() ? $item->clientGroup()->amountType : 'net',
								'list' => ['net' => $t('net'), 'gross' => $t('gross')]
							]) ?>
						<td class="money--f price-amount--f">
							<?= $this->form->field('positions.new.amount', [
								'type' => 'text',
								'label' => false,
								'placeholder' => $this->money->format(0, ['currency' => false]),
								'class' => 'input--money'
							]) ?>
						<td class="numeric--f price-rate--f">
							<?= $this->form->field("positions.new.amount_rate", [
								'type' => 'text',
								'value' => $item->exists() ? $item->taxType()->rate : '19',
								'label' => false,
								'class' => 'input--numeric'
							]) ?>
						<td class="position-total--f">
						<td class="actions">
							<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="9" class="nested-add-action">
								<?= $this->form->button($t('add position'), ['type' => 'button', 'class' => 'button add-nested']) ?>
						<tr class="totals">
							<td colspan="6"><?= $t('Total (net)') ?>
							<td><?= $this->money->format($item->totals()->getNet()) ?>

						<?php foreach ($item->taxes() as $rate => $tax): ?>
						<tr class="totals">
							<td colspan="6"><?= $t('Tax ({:rate}%)', ['rate' => $rate]) ?>
							<td><?= $this->money->format($tax) ?>
						<?php endforeach ?>

						<tr class="totals">
							<td colspan="6"><?= $t('Total (gross)') ?>
							<td><?= $this->money->format($item->totals()->getGross()) ?>
					</tfoot>
				</table>
			</section>
		</div>

		<?php if (Settings::read('estimate.letter')): ?>
			<div class="grid-row">
				<?= $this->form->field('letter', [
					'type' => 'textarea',
					'label' => $t('Letter'),
					'class' => 'textarea-size--gamma'
				]) ?>
				<div class="help"><?= $t('Visible to recipient.') ?></div>
			</div>
		<?php endif ?>

		<div class="grid-row">
			<section class="grid-column-left">
				<?= $this->form->field('terms', [
					'type' => 'textarea',
					'label' => $t('Terms')
				]) ?>
				<div class="help"><?= $t('Visible to recipient.') ?></div>
			</section>
			<section class="grid-column-right">
				<?= $this->form->field('note', [
					'type' => 'textarea',
					'label' => $t('Note')
				]) ?>
				<div class="help"><?= $t('Visible to recipient.') ?></div>
			</section>
		</div>

		<div class="grid-row">
			<h1 class="h-gamma"><?= $t('Tax') ?></h1>

			<section class="grid-column-right">
				<?= $this->form->field('tax_type', [
					'type' => 'select',
					'list' => $taxTypes,
					'disabled' => true
				]) ?>
				<?= $this->form->field('tax_note', [
					'type' => 'text',
					'label' => $t('Tax note'),
					'value' => $item->tax_note,
					'disabled' => true
				]) ?>
				<div class="help">
					<?= $t('Visible to recipient.') ?>
					<?= $t('Automatically generated.') ?>
				</div>
			</section>
		</div>

		<div class="bottom-actions">
			<div class="bottom-actions__left">
				<?php if ($item->exists()): ?>
					<?= $this->html->link($t('delete'), [
						'action' => 'delete', 'id' => $item->id
					], ['class' => 'button large delete']) ?>
				<?php endif ?>
			</div>
			<div class="bottom-actions__right">
				<?php if ($item->exists()): ?>
					<?= $this->html->link($t('PDF'), [
						'controller' => 'Estimates',
						'id' => $item->id, 'action' => 'export_pdf',
						'library' => 'billing_estimate'
					], ['class' => 'button large']) ?>
				<?php endif ?>
				<?= $this->form->button($t('save'), [
					'type' => 'submit',
					'class' => 'button large save'
				]) ?>
			</div>
		</div>

	<?=$this->form->end() ?>
</article>