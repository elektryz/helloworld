{capture name=path}
    {l s='Hello, hello, hello!' mod='helloworld'}
{/capture}

{if $helloworld_title|count_characters > 0}
    <h1>{$helloworld_title}</h1>
{/if}

{if $helloworld_desc|count_characters > 0}
    <p>{$helloworld_desc}</p>
{/if}