<table class="z-datatable">
	<tbody>
		<tr class="{cycle values='z-odd,z-even'}">
			<td class="z-bold">{gt text='IP address'}</td>
			<td>{$item.ip|safetext}</td>
		</tr>
		<tr class="{cycle values='z-odd,z-even'}">
			<td class="z-bold">{gt text='Host name'}</td>
			<td>{$item.hostname|safetext}</td>
		</tr>
		<tr class="{cycle values='z-odd,z-even'}">
			<td class="z-bold">{gt text='City'}</td>
			<td>{$item.city|safetext}</td>
		</tr>
		<tr class="{cycle values='z-odd,z-even'}">
			<td class="z-bold">{gt text='Region'}</td>
			<td>{$item.region|safetext}</td>
		</tr>
		<tr class="{cycle values='z-odd,z-even'}">
			<td class="z-bold">{gt text='Country'}</td>
			<td>{if $item.country_name}{$item.country_name|safetext} ({$item.country_code|safetext}){else}{$item.country_code|safetext}{/if}</td>
		</tr>
		<tr class="{cycle values='z-odd,z-even'}">
			<td class="z-bold">{gt text='Location'}</td>
			<td>{$item.latitude|safetext}, {$item.longitude|safetext}</td>
		</tr>
		<tr class="{cycle values='z-odd,z-even'}">
			<td class="z-bold">{gt text='Organization'}</td>
			<td>{$item.org|safetext}</td>
		</tr>
	</tbody>
</table>
<a href="{$serviceLinks}" title="{$serviceName}" target="_blank">{gt text='Visit service site'}</a>