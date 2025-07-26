@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            {{-- Always show logo, remove conditional --}}
            <img src="https://alf2.arellanolaw.edu/images/email_alflogo.png"
                class="logo" 
                alt="RPV System Logo"
                width="150"
                height="180"
                style="display: block; max-width: 100%;">
        </a>
    </td>
</tr>