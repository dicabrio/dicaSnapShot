<style type="text/css">
	.error {
		background: red;
		color: #fff;
		padding: 10px 30px;
		float: left;
}
</style>
	<h1>Nieuwtjes aanpassen</h1>
	<?php echo $form->begin(); ?>
			<fieldset>
				<legend>Wijzig</legend>
				<?php if (count($aError) > 0) : ?>
				<ul class="error">
				<?php foreach ($aError as $sError) : ?>
					<li><?php echo Lang::get('news.error.'.$sError); ?></li>
				<?php endforeach; ?>
				</ul>
				<?php endif; ?>
				<table>
					<tr>
						<th><label for="title">Titel:</label></th>
						<td><?php echo $form->getFormElement('title'); ?></td>
					</tr>
					<tr>
						<th><label for="datum">Datum:</label></th>
						<td><?php echo $form->getFormElement('datum'); ?> <?php echo date("Y-m-d"); ?><br /></td>
					</tr>
					<tr>
						<th><label for="youtube">Youtubelink:</label></th>
						<td><?php echo $form->getFormElement('youtube'); ?></td>
					</tr>
					<tr>
						<th><label for="image">Afbeelding:</label></th>
						<td><?php echo $form->getFormElement('image'); ?></td>
					</tr>
<?php if ($image->getID() > 0) : ?>
					<tr>
						<th>&nbsp;</th>
						<td>
							<img src="<?php echo Conf::get('general.url.www').Conf::get('upload.url.general').'/'.$image->getFile()->getFilename(); ?>" alt="" style="border: 1px solid black;" /><br />
							<a href="<?php echo Conf::get('general.url.www'); ?>/news/deleteimage/<?php echo $form->getFormElement('id')->getValue(); ?>">verwijder afbeelding</a>
						</td>
					</tr>
<?php endif; ?>
					<tr>
						<th>
							<label for="body">Tekst:</label>
						</th>
						<td>
							<?php echo $form->getFormElement('body')->addAttribute('cols', 50)->addAttribute('rows', 10); ?>
						</td>
					</tr>
					<tr>
						<th><label for="active">Actief:</label></th>
						<td><?php echo $form->getFormElement('active'); ?></td>
					</tr>
				</table>
				<?php echo $form->getFormElement('id'); ?>
				<?php echo $form->getSubmitButton('savebutton'); ?>
				<a href="<?php echo Conf::get('general.url.www').'/news/'; ?>">Cancel</a>
			</fieldset>
		<?php echo $form->end(); ?>
