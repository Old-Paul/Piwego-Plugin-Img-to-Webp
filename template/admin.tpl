<div class="titrePage">
  <h2>{'Img2Webp'|@translate}</h2>
</div>

{if !$IMG2WEBP_IMAGICK_AVAILABLE}
<fieldset>
  <legend>{'Not available'|@translate}</legend>
  <p>{'ImageMagick was not found on this server. WebP conversion requires ImageMagick - contact your host or system administrator to have it installed. Piwigo\'s own image resizing still works fine without it; only this WebP upload tool is affected.'|@translate}</p>
</fieldset>
{else}

<form method="post" action="{$IMG2WEBP_ADMIN_URL}">
  <fieldset>
    <legend>{'Settings'|@translate}</legend>
    <input type="hidden" name="img2webp_action" value="save_settings">
    <p>
      {'WebP quality (1-100)'|@translate}
      <input type="number" name="quality" value="{$IMG2WEBP_QUALITY}" min="1" max="100" size="4">
    </p>
    <p class="bottomButtons"><input type="submit" value="{'Save Settings'|@translate}"></p>
  </fieldset>
</form>

<form method="post" action="{$IMG2WEBP_ADMIN_URL}" enctype="multipart/form-data">
  <fieldset>
    <legend>{'Upload Photos'|@translate}</legend>
    <p>{'Choose JPG, PNG, GIF, or WebP files - each one is automatically converted to WebP before it\'s added to your gallery.'|@translate}</p>
    <input type="hidden" name="img2webp_action" value="upload">
    <p>
      {'Album'|@translate}
      <select name="category_id">
        <option value="">{'None'|@translate}</option>
        {foreach from=$IMG2WEBP_CATEGORIES item=cat}
        <option value="{$cat.id}">{$cat.name}</option>
        {/foreach}
      </select>
    </p>
    <p>
      <input type="file" name="photos[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple required>
    </p>
    <p class="bottomButtons"><input type="submit" value="{'Upload'|@translate}"></p>
  </fieldset>
</form>

{if $IMG2WEBP_RESULTS}
<fieldset>
  <legend>{'Results'|@translate}</legend>
  <ul>
    {foreach from=$IMG2WEBP_RESULTS item=r}
      <li>{if $r.ok}&#10003;{else}&#10007;{/if} {$r.name} - {$r.message}</li>
    {/foreach}
  </ul>
</fieldset>
{/if}

{/if}
