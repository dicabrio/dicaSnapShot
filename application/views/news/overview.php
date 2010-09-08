	<h1>Nieuws aanpassen</h1>
	<table>
		<thead>
			<tr>
				<td colspan="4"><a href="<?php echo Conf::get('general.url.www'); ?>/news/edititem">Voeg toe</a></td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th>Titel</th>
				<th>Actief</th>
				<th>Toondatum</th>
				<th>Acties</th>
			</tr>
			<?php foreach ($aNieuwtjes as $oNieuwtje) : ?>
			<tr>
				<td><?php echo $oNieuwtje->getTitle(); ?></td>
				<td><?php echo $oNieuwtje->isActive(); ?></td>
				<td><?php echo $oNieuwtje->getDatum(); ?></td>
				<td><a href="<?php echo Conf::get('general.url.www'); ?>/news/edititem/<?php echo $oNieuwtje->getID(); ?>">Wijzigen</a></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
