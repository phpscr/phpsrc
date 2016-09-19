<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<div id="footer">
 <a href="javascript:;" onfocus="this.blur();" class="goTop">{$lang.go_top}</a>
 <ul>
  <li class="footNav"><!-- {if $open.user} --><!-- {if $user} --><a href="{$url.user}">{$user.user_name}，{$lang.user_welcom_top}</a><a href="{$url.logout}">{$lang.user_logout}</a><!-- {else} --><a href="{$url.login}">{$lang.user_login_nav}</a><s></s><a href="{$url.register}">{$lang.user_register_nav}</a><!-- {/if} --><!-- {/if} --><a href="{$site.m_url}">{$lang.dou_mobile}</a><a href="{$site.root_url}?mobile">{$lang.dou_pc}</a></li>
  <li class="copyRight">{$lang.copyright}</li>
  <li class="powered">{$lang.powered_by} <!-- {if $site.icp} -->{$site.icp}<!-- {/if} --></li>
 </ul>
</div>
<!-- {if $site.code} -->
<div style="display:none">{$site.code}</div>
<!-- {/if} -->