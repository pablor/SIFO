<?php
{if isset( $instance_parent )}
include ROOT_PATH . '/instances/{$instance_parent}/config/{$file_name}';
{/if}

{foreach from=$config item=c key=k}
{if is_array($c) }
$config['{$k}'] = {$c|var_export};
{else}
$config['{$k}'] = '{$c}';
{/if}
{/foreach}
?>