{* HEADER *}

{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}

{* remove this message, since we have some meaningful content below.
 * Later, if that meaningful content is based on configurabel options (based on
 * on the idea that we might support configurable list of "properties you can
 * edit while cloning"), we  might want such a message when there are no
 * such options available.
<div class="messages status no-popup">
  <div class="icon inform-icon"></div>
  {ts}Are you sure, create a clone of this contribution?{/ts}
</div>
*}

  <fieldset><legend>Optionally change these values in the new contribution:</legend>

  {foreach from=$elementNames item=elementName}
    <div class="crm-section">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">{$form.$elementName.html}<div class="description">{$descriptions.$elementName}</div></div>
      <div class="clear"></div>
    </div>
  {/foreach}
</fieldset>
{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
